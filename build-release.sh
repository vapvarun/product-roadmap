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
PLUGIN_SLUG="product-roadmap"
PRO_SLUG="roadmap-pro-addon"

# Get version from plugin file
if [ -f "product-roadmap.php" ]; then
    VERSION=$(grep -i "Version:" product-roadmap.php | sed 's/.*Version: *//' | tr -d '\r\n' | awk '{print $1}')
    echo "Plugin version: $VERSION"
else
    echo -e "${RED}✗ Error: product-roadmap.php not found${NC}"
    exit 1
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
zip -r "$DIST_DIR/${PLUGIN_SLUG}-${VERSION}.zip" "$PLUGIN_SLUG" -q
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
        "assets/js/timeline.js"
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