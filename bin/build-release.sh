#!/bin/bash

# Build and package script for Product Roadmap Plugin
# This script builds the plugin and creates distribution zip files

echo "========================================="
echo "Building Release Packages..."
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Store current directory
CURRENT_DIR=$(pwd)
ROOT="$CURRENT_DIR"
slug=product-roadmap        # WordPress.org slug = zip folder. Locked by CI (wporg-folder-lock.yml).
main=product-roadmap.php
PLUGIN_SLUG="$slug"
PRO_SLUG="roadmap-pro-addon"

# Release gate flags
SKIP_BROWSER_SMOKE=0
ALLOW_STALE_DOCS=0
ALLOW_DIRTY=0
SKIP_PLUGIN_CHECK=0
for arg in "$@"; do
    case "$arg" in
        --skip-browser-smoke) SKIP_BROWSER_SMOKE=1 ;;
        --allow-stale-docs) ALLOW_STALE_DOCS=1 ;;
        --allow-dirty) ALLOW_DIRTY=1 ;;
        --skip-plugin-check) SKIP_PLUGIN_CHECK=1 ;;
    esac
done

# WP_PATH: derived from this plugin's own location (three levels up from
# wp-content/plugins/<slug>/), never a stored absolute path - the same repo
# is checked out on several machines under different $HOME values. Only
# used by the Plugin Check gate below; every other step in this script is
# already WP-independent (grunt/vite build + zip).
WP_PATH="$(cd "$CURRENT_DIR/../../.." && pwd)"
[ -n "$WP_SMOKE_PATH" ] && WP_PATH="$WP_SMOKE_PATH"

# Get version from plugin file
if [ -f "product-roadmap.php" ]; then
    VERSION=$(grep -i "Version:" product-roadmap.php | sed 's/.*Version: *//' | tr -d '\r\n' | awk '{print $1}')
    echo "Plugin version: $VERSION"
else
    echo -e "${RED}✗ Error: product-roadmap.php not found${NC}"
    exit 1
fi

# -----------------------------------------------------------------------
# Gate: static contract audit (wp-contract-audit skill) — key/hook drift.
# -----------------------------------------------------------------------
CONTRACT_AUDIT="$HOME/.claude/skills/wp-contract-audit/scripts/contract-audit.php"
if [ -f "$CONTRACT_AUDIT" ]; then
    echo ""
    echo "0. Contract audit (key/hook drift)..."
    echo "-------------------------------------"
    if ! php "$CONTRACT_AUDIT" "$ROOT" --pair="$ROOT/../$PRO_SLUG"; then
        echo -e "${RED}✗ Contract audit failed — fix findings or baseline with reasons (see wp-contract-audit skill).${NC}"
        exit 1
    fi
fi

# -----------------------------------------------------------------------
# Gate: browser smoke report — refuses to package unless a fresh green
# report exists. Protects first-hand customer experience: a run of
# docs/qa/AGENT_SMOKE_RUNBOOK.md (dispatched via the wp-plugin-smoke
# skill) must have reported zero failures and zero debug_log_issues.
# -----------------------------------------------------------------------
SMOKE_REPORT="$ROOT/docs/qa/.last-smoke-pass.json"
if [ "$SKIP_BROWSER_SMOKE" -eq 1 ]; then
    echo -e "${YELLOW}WARN: browser smoke gate skipped (--skip-browser-smoke). Not for customer releases.${NC}"
elif [ ! -f "$SMOKE_REPORT" ]; then
    echo -e "${RED}✗ FAIL: no browser smoke report at $SMOKE_REPORT${NC}"
    echo "      Run /wp-plugin-smoke combo first to generate it."
    echo "      Emergency only: rerun with --skip-browser-smoke."
    exit 30
else
    REPORT_VERSION="$(grep -oE '"release_version"[[:space:]]*:[[:space:]]*"[^"]+"' "$SMOKE_REPORT" | head -1 | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' || true)"
    if [ "$REPORT_VERSION" != "$VERSION" ]; then
        echo -e "${RED}✗ FAIL: smoke report version ($REPORT_VERSION) doesn't match release version ($VERSION)${NC}"
        echo "      Rerun /wp-plugin-smoke combo against HEAD before packaging."
        exit 30
    fi
    # Parse the smoke report as JSON. A grep on one line cannot see a pretty-printed
    # list, so failing reports used to pass this gate. Returns 0 only when every
    # named key is absent or empty; an unreadable report counts as not empty.
    smoke_list_empty() {
        node -e '
            const fs = require("fs");
            let d;
            try { d = JSON.parse(fs.readFileSync(process.argv[1], "utf8")); }
            catch (e) { console.error("  smoke report is not valid JSON: " + e.message); process.exit(1); }
            for (const k of process.argv.slice(2)) {
                const v = d[k];
                const n = v == null ? 0 : Array.isArray(v) ? v.length : typeof v === "object" ? Object.keys(v).length : (v ? 1 : 0);
                if (n) { console.error("  " + k + ": " + n + (n === 1 ? " entry" : " entries")); process.exit(1); }
            }' "$@"
    }
    if ! smoke_list_empty "$SMOKE_REPORT" failures hard_fails; then
        echo -e "${RED}✗ FAIL: smoke report has failures. Fix them before packaging.${NC}"
        exit 30
    fi
    if ! smoke_list_empty "$SMOKE_REPORT" debug_log_issues; then
        echo -e "${RED}✗ FAIL: smoke report recorded debug.log entries during the walk. Fix before packaging.${NC}"
        exit 30
    fi
    echo -e "${GREEN}✓ smoke report OK (version $REPORT_VERSION)${NC}"
fi

# -----------------------------------------------------------------------
# Gate: documentation truth — audit/manifest.json / CAPABILITIES.md must
# not lag the code. Skips cleanly on --allow-dirty builds or no git.
# -----------------------------------------------------------------------
if [ "$ALLOW_STALE_DOCS" -eq 0 ] && [ "$ALLOW_DIRTY" -eq 0 ] && git -C "$ROOT" rev-parse --git-dir >/dev/null 2>&1; then
    echo ""
    echo "Documentation-truth gate..."
    echo "-------------------------------------"
    CODE_TS="$(git -C "$ROOT" log -1 --format=%ct -- includes blocks templates src 2>/dev/null || echo 0)"; CODE_TS="${CODE_TS:-0}"
    MAN_TS="$(git -C "$ROOT" log -1 --format=%ct -- audit/manifest.json 2>/dev/null || echo 0)"; MAN_TS="${MAN_TS:-0}"
    if [ "$MAN_TS" -ne 0 ] && [ "$CODE_TS" -gt "$MAN_TS" ]; then
        echo -e "${RED}✗ FAIL: code changed after the last audit/manifest.json refresh.${NC}"
        echo "      Run /wp-plugin-onboard --refresh, commit the manifest, then rebuild."
        echo "      Override (surface-neutral commit only): --allow-stale-docs"
        exit 18
    fi
    if [ -f "$ROOT/CAPABILITIES.md" ]; then
        CAP_TS="$(git -C "$ROOT" log -1 --format=%ct -- CAPABILITIES.md 2>/dev/null || echo 0)"; CAP_TS="${CAP_TS:-0}"
        if [ "$CODE_TS" -gt "$CAP_TS" ]; then
            echo -e "${RED}✗ FAIL: code changed after the last CAPABILITIES.md update.${NC}"
            echo "      Override (surface-neutral commit only): --allow-stale-docs"
            exit 18
        fi
    fi
    echo -e "${GREEN}✓ manifest + CAPABILITIES current${NC}"
fi

# Create dist directory in current plugin folder
DIST_DIR="$CURRENT_DIR/dist"
mkdir -p "$DIST_DIR"

echo "Distribution directory: $DIST_DIR"
echo ""

# Build the plugin first
echo ""
echo "1. Building Product Roadmap Plugin..."
echo "-------------------------------------"
if [ -f "build.sh" ]; then
    ./build.sh
else
    echo "✗ Error: build.sh not found"
    exit 1
fi

# Create temporary directory for packaging
echo ""
echo "2. Creating Free Version Package..."
echo "-------------------------------------"
TEMP_DIR=$(mktemp -d)
FREE_DIR="$TEMP_DIR/$PLUGIN_SLUG"

# Create plugin directory structure
mkdir -p "$FREE_DIR"

# Copy files for free version
echo "Copying plugin files..."
cp -r assets "$FREE_DIR/"
cp -r includes "$FREE_DIR/"
cp -r languages "$FREE_DIR/"
cp -r templates "$FREE_DIR/"
cp -r vue-app "$FREE_DIR/"  # Include Vue source files for WordPress compliance
cp product-roadmap.php "$FREE_DIR/"

# Check the files that actually ship (WP_DEBUG off loads assets/js/roadmap-app.js,
# not vue-app/dist). The board bundle is a classic script: it must be exactly one
# IIFE, or a minified top-level name (once `wp`) shadows window.wp and breaks every
# editor screen. It must also be the bundle build.sh just made, so a zip cannot
# carry a stale frontend.
SHIPPED_JS="$FREE_DIR/assets/js/roadmap-app.js"
if [ "$(head -c 12 "$SHIPPED_JS" 2>/dev/null)" != '(function(){' ]; then
    echo "ERROR: shipped assets/js/roadmap-app.js is missing or does not start with a single IIFE."
    exit 1
fi
if ! cmp -s "$SHIPPED_JS" vue-app/dist/assets/index.js; then
    echo "ERROR: shipped assets/js/roadmap-app.js is not the bundle build.sh just built."
    exit 1
fi
cp readme.txt "$FREE_DIR/"
cp LICENSE "$FREE_DIR/" 2>/dev/null || true
cp package.json "$FREE_DIR/"  # Include package.json for build instructions
cp package-lock.json "$FREE_DIR/" 2>/dev/null || true

# Note: Build scripts (.sh files) and build-rtl.js are NOT included in distribution
# They remain in the repository for developers but are excluded from WordPress.org package

# Remove only truly unnecessary files
echo "Cleaning up unnecessary files..."

# Remove backup files - CRITICAL for WordPress.org compliance
find "$FREE_DIR" -name "*.bak" -delete 2>/dev/null || true
find "$FREE_DIR" -name "*~" -delete 2>/dev/null || true
find "$FREE_DIR" -name "*.backup" -delete 2>/dev/null || true
find "$FREE_DIR" -name "*.tmp" -delete 2>/dev/null || true
find "$FREE_DIR" -name "*.log" -delete 2>/dev/null || true
find "$FREE_DIR" -name ".DS_Store" -delete 2>/dev/null || true

# Remove source maps (optional, but usually not needed in distribution)
find "$FREE_DIR" -name "*.map" -delete 2>/dev/null || true

# Remove node_modules (these can be reinstalled from package.json)
rm -rf "$FREE_DIR/node_modules" 2>/dev/null || true
rm -rf "$FREE_DIR/vue-app/node_modules" 2>/dev/null || true

# Remove test files
rm -f "$FREE_DIR/test-*.html" 2>/dev/null || true
rm -f "$FREE_DIR/test-*.php" 2>/dev/null || true
rm -rf "$FREE_DIR/tests" 2>/dev/null || true

# Remove build scripts and internal files
rm -f "$FREE_DIR"/*.sh 2>/dev/null || true
rm -f "$FREE_DIR/build-rtl.js" 2>/dev/null || true
rm -f "$FREE_DIR/verify-compliance.sh" 2>/dev/null || true
rm -f "$FREE_DIR/build-release.sh" 2>/dev/null || true

# Remove unnecessary markdown files but keep README
rm -f "$FREE_DIR/MARKETING-IMAGES.md" 2>/dev/null || true
rm -f "$FREE_DIR/SNEAK-PEEK.md" 2>/dev/null || true
rm -f "$FREE_DIR/BUILD.md" 2>/dev/null || true
rm -f "$FREE_DIR/DEVELOPER.md" 2>/dev/null || true
rm -f "$FREE_DIR/WORDPRESS-COMPLIANCE.md" 2>/dev/null || true

# Remove sales pages
rm -f "$FREE_DIR"/sales-page-*.html 2>/dev/null || true

# Keep README.md for transparency
cp README.md "$FREE_DIR/" 2>/dev/null || true

# Verify POT file exists
if [ ! -f "$FREE_DIR/languages/product-roadmap.pot" ]; then
    echo "Warning: POT file not found. Generating..."
    mkdir -p "$FREE_DIR/languages"
    touch "$FREE_DIR/languages/product-roadmap.pot"
fi

# Final verification - ensure no backup files remain
BACKUP_COUNT=$(find "$FREE_DIR" -name "*.bak" -o -name "*~" -o -name "*.backup" | wc -l)
if [ $BACKUP_COUNT -gt 0 ]; then
    echo "✗ ERROR: Backup files still exist in distribution!"
    find "$FREE_DIR" -name "*.bak" -o -name "*~" -o -name "*.backup"
    exit 1
fi

# Create free version zip
echo "Creating free version zip..."
cd "$TEMP_DIR"
zip="$DIST_DIR/${PLUGIN_SLUG}-${VERSION}.zip"
rm -f "$zip"
# Deflate every entry: a PHP file stored uncompressed trips the store's upload firewall.
python3 -I -c 'import os,sys,zipfile
out,top=sys.argv[1],sys.argv[2]
with zipfile.ZipFile(out,"w",zipfile.ZIP_DEFLATED,compresslevel=9) as z:
    for r,d,f in os.walk(top):
        d.sort()
        for n in sorted(f): z.write(os.path.join(r,n))' "$zip" "$PLUGIN_SLUG" || exit 1

# --- WordPress.org folder lock (Wbcom standard - do not edit) ----------------------
top=$(unzip -Z1 "$zip" | head -1 | cut -d/ -f1)
[ "$top" = "$slug" ] || { echo "Zip folder '$top' is not the WordPress.org slug '$slug'"; exit 1; }
unzip -Z1 "$zip" | grep -qx "$slug/$main" || { echo "Main file $slug/$main missing from zip"; exit 1; }
[ "$(LC_ALL=C grep -a -c '<?php' "$zip" || true)" = 0 ] || { echo "Zip has plain-text PHP (upload firewall)"; exit 1; }
# -----------------------------------------------------------------------------------
echo -e "${GREEN}✓ Free version package created: ${PLUGIN_SLUG}-${VERSION}.zip${NC}"

# Build and package pro addon separately if it exists
# Pro addon is in the same plugins directory
PRO_ADDON_PATH="$CURRENT_DIR/../$PRO_SLUG"
if [ -d "$PRO_ADDON_PATH" ]; then
    echo ""
    echo "3. Building Pro Addon..."
    echo "-------------------------------------"
    cd "$PRO_ADDON_PATH"

    # Get pro version
    if [ -f "roadmap-pro-addon.php" ]; then
        PRO_VERSION=$(grep -i "Version:" roadmap-pro-addon.php | sed 's/.*Version: *//' | tr -d '\r\n' | awk '{print $1}')
        echo "Pro addon version: $PRO_VERSION"
    else
        PRO_VERSION=$VERSION
    fi

    # Build pro addon
    if [ -f "build.sh" ]; then
        if ! ./build.sh; then
            echo -e "${RED}✗ Error: Pro addon build failed${NC}"
            echo -e "${YELLOW}Cannot create pro package with build errors${NC}"
            cd "$CURRENT_DIR"
            exit 1
        fi
        echo -e "${GREEN}✓ Pro addon build successful${NC}"
    else
        echo -e "${RED}✗ Warning: build.sh not found in pro addon${NC}"
        cd "$CURRENT_DIR"
        exit 1
    fi

    echo ""
    echo "4. Creating Pro Addon Package (Separate)..."
    echo "-------------------------------------"

    # Create pro addon package directory
    PRO_PACKAGE_DIR="$TEMP_DIR/$PRO_SLUG"
    mkdir -p "$PRO_PACKAGE_DIR"

    # Copy pro addon files only (not the free version)
    echo "Copying pro addon files..."
    cp -r assets "$PRO_PACKAGE_DIR/"
    cp -r includes "$PRO_PACKAGE_DIR/"
    cp -r languages "$PRO_PACKAGE_DIR/" 2>/dev/null || true
    [ -d "blocks" ] && cp -r blocks "$PRO_PACKAGE_DIR/"
    [ -d "templates" ] && cp -r templates "$PRO_PACKAGE_DIR/"
    cp roadmap-pro-addon.php "$PRO_PACKAGE_DIR/"
    cp readme.txt "$PRO_PACKAGE_DIR/"
    cp LICENSE "$PRO_PACKAGE_DIR/" 2>/dev/null || true

    # Clean up pro addon development files
    echo "Cleaning up pro addon development files..."
    find "$PRO_PACKAGE_DIR" -name "*.scss" -delete 2>/dev/null || true
    find "$PRO_PACKAGE_DIR" -name "*.map" -delete 2>/dev/null || true
    find "$PRO_PACKAGE_DIR" -name ".DS_Store" -delete 2>/dev/null || true
    find "$PRO_PACKAGE_DIR" -name "*.sh" -delete 2>/dev/null || true
    find "$PRO_PACKAGE_DIR" -name "package.json" -delete 2>/dev/null || true
    find "$PRO_PACKAGE_DIR" -name "package-lock.json" -delete 2>/dev/null || true
    rm -rf "$PRO_PACKAGE_DIR/node_modules" 2>/dev/null || true
    rm -rf "$PRO_PACKAGE_DIR/docs" 2>/dev/null || true
    rm -f "$PRO_PACKAGE_DIR/*.md" 2>/dev/null || true

    # Create pro addon dist directory
    PRO_DIST_DIR="$PRO_ADDON_PATH/dist"
    mkdir -p "$PRO_DIST_DIR"

    # Create pro addon zip (standalone, requires free version)
    echo "Creating pro addon zip..."
    cd "$TEMP_DIR"
    zip -r "$PRO_DIST_DIR/${PRO_SLUG}-${PRO_VERSION}.zip" "$PRO_SLUG" -q

    # Verify critical assets exist in pro ZIP
    echo ""
    echo "Verifying pro addon assets..."
    PRO_ZIP="$PRO_DIST_DIR/${PRO_SLUG}-${PRO_VERSION}.zip"
    REQUIRED_PRO_ASSETS=(
        "assets/css/roadmap-pro.css"
        "assets/css/roadmap-pro-rtl.css"
        "assets/css/admin.css"
        "assets/js/roadmap-pro.js"
        "assets/js/admin.js"
    )

    MISSING_PRO_ASSETS=0
    for asset in "${REQUIRED_PRO_ASSETS[@]}"; do
        if ! unzip -l "$PRO_ZIP" | grep -q "$asset"; then
            echo -e "${RED}✗ Missing required pro asset: $asset${NC}"
            MISSING_PRO_ASSETS=$((MISSING_PRO_ASSETS + 1))
        fi
    done

    if [ $MISSING_PRO_ASSETS -gt 0 ]; then
        echo -e "${RED}✗ ERROR: $MISSING_PRO_ASSETS required pro asset(s) missing${NC}"
        echo -e "${YELLOW}The pro build may have failed. Check build output.${NC}"
        cd "$CURRENT_DIR"
        exit 1
    fi

    echo -e "${GREEN}✓ All required pro assets present${NC}"
    echo -e "${GREEN}✓ Pro addon package created: ${PRO_SLUG}-${PRO_VERSION}.zip${NC}"
    echo -e "${YELLOW}  Note: This addon requires the free version to be installed first${NC}"
else
    echo ""
    echo "3. Pro addon not found - skipping pro addon package"
fi

# -----------------------------------------------------------------------
# Gate: Plugin Check on the BUILT zips, not the source tree - a zip-only
# packaging bug (or a genuine security/i18n finding) only shows up on what
# actually ships. Requires `wp` (WP-CLI) and the Plugin Check plugin active
# in the WordPress install this repo sits under.
#
# Known false positive, filtered automatically: extracting a zip to a temp
# check-slug whose directory name differs from the plugin's real text
# domain trips WordPress.WP.I18n.TextDomainMismatch on every single
# translatable string. This is an artifact of the temp slug name, not a
# real defect - confirmed by re-running with a slug that matches.
#
# Known accepted finding, Pro only: plugin_header_invalid_license /
# license_mismatch. Plugin Check requires a GPL-compatible header for
# wordpress.org hosting; Pro is a commercial addon not distributed there,
# so a correctly-declared `License: Commercial` trips this check by
# design. Free must stay clean of this - it IS wp.org-hosted.
# -----------------------------------------------------------------------
if [ "$SKIP_PLUGIN_CHECK" -eq 1 ]; then
    echo ""
    echo -e "${YELLOW}WARN: Plugin Check gate skipped (--skip-plugin-check). Not for customer releases.${NC}"
elif ! command -v wp &> /dev/null; then
    echo ""
    echo -e "${YELLOW}WARN: wp-cli not found, Plugin Check gate skipped. Not for customer releases.${NC}"
elif [ ! -f "$WP_PATH/wp-load.php" ]; then
    echo ""
    echo -e "${YELLOW}WARN: WP_PATH ($WP_PATH) has no wp-load.php, Plugin Check gate skipped. Set WP_SMOKE_PATH to override.${NC}"
else
    echo ""
    echo "Plugin Check gate (built zips)..."
    echo "-------------------------------------"
    PLUGINS_DIR="$WP_PATH/wp-content/plugins"

    run_plugin_check() {
        local ZIP="$1" REAL_SLUG="$2" IS_PRO="$3"
        local CHECK_SLUG="${REAL_SLUG}-buildcheck"
        local CHECK_DIR="$PLUGINS_DIR/$CHECK_SLUG"
        rm -rf "$CHECK_DIR"
        local EXTRACT_TMP
        EXTRACT_TMP=$(mktemp -d)
        unzip -q "$ZIP" -d "$EXTRACT_TMP"
        mv "$EXTRACT_TMP/$REAL_SLUG" "$CHECK_DIR"
        rm -rf "$EXTRACT_TMP"

        local RAW
        RAW=$(wp plugin check "$CHECK_SLUG" --format=csv --severity=error --allow-root --path="$WP_PATH" 2>&1)
        rm -rf "$CHECK_DIR"

        local REAL_ERRORS
        REAL_ERRORS=$(echo "$RAW" | grep ",ERROR," | grep -v "TextDomainMismatch")
        if [ "$IS_PRO" -eq 1 ]; then
            REAL_ERRORS=$(echo "$REAL_ERRORS" | grep -v "plugin_header_invalid_license" | grep -v "license_mismatch")
        fi
        REAL_ERRORS=$(echo "$REAL_ERRORS" | grep -v "^$")

        if [ -n "$REAL_ERRORS" ]; then
            echo -e "${RED}✗ Plugin Check found real errors in $REAL_SLUG:${NC}"
            echo "$REAL_ERRORS"
            return 1
        fi
        echo -e "${GREEN}✓ Plugin Check clean: $REAL_SLUG${NC}"
        return 0
    }

    CHECK_FAILED=0
    run_plugin_check "$DIST_DIR/${PLUGIN_SLUG}-${VERSION}.zip" "$PLUGIN_SLUG" 0 || CHECK_FAILED=1
    if [ -d "$PRO_ADDON_PATH" ] && [ -f "$PRO_DIST_DIR/${PRO_SLUG}-${PRO_VERSION}.zip" ]; then
        run_plugin_check "$PRO_DIST_DIR/${PRO_SLUG}-${PRO_VERSION}.zip" "$PRO_SLUG" 1 || CHECK_FAILED=1
    fi

    if [ "$CHECK_FAILED" -eq 1 ]; then
        echo -e "${RED}✗ FAIL: Plugin Check found real errors on a built zip. Fix before packaging.${NC}"
        echo "      Emergency only: rerun with --skip-plugin-check."
        cd "$CURRENT_DIR"
        exit 40
    fi
fi

# Clean up
echo ""
echo "5. Cleaning up..."
echo "-------------------------------------"
rm -rf "$TEMP_DIR"
cd "$CURRENT_DIR"

# Final summary
echo ""
echo "========================================="
echo "✓ Release packages created successfully!"
echo "========================================="
echo ""
echo "Free version:"
echo "  Location: $DIST_DIR/${PLUGIN_SLUG}-${VERSION}.zip"
echo "  Status: WordPress.org compatible"
if [ -d "$PRO_ADDON_PATH" ]; then
    echo ""
    echo "Pro addon:"
    echo "  Location: $PRO_DIST_DIR/${PRO_SLUG}-${PRO_VERSION}.zip"
    echo "  Note: Requires free version installed"
fi
echo ""
echo "These packages are ready for distribution!"
echo ""