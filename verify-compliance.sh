#!/bin/bash

# WordPress.org Compliance Verification Script
# This script checks if the generated ZIP meets WordPress.org requirements

echo "========================================="
echo "WordPress.org Compliance Verification"
echo "========================================="

ZIP_FILE="$1"

if [ -z "$ZIP_FILE" ]; then
    # Find the latest ZIP in releases directory
    ZIP_FILE="../releases/product-roadmap-1.0.1.zip"
fi

if [ ! -f "$ZIP_FILE" ]; then
    echo "✗ ERROR: ZIP file not found: $ZIP_FILE"
    echo "Usage: ./verify-compliance.sh [path-to-zip]"
    exit 1
fi

echo ""
echo "Checking: $(basename $ZIP_FILE)"
echo "-----------------------------------------"

# Create temp directory for extraction
TEMP_DIR=$(mktemp -d)
cd "$TEMP_DIR"

# Extract ZIP
unzip -q "$ZIP_FILE"

# Find plugin directory
PLUGIN_DIR=$(ls -d */ | head -1)
cd "$PLUGIN_DIR"

echo ""
echo "1. Checking for backup files..."
echo "-----------------------------------------"
BACKUP_FILES=$(find . -name "*.bak" -o -name "*~" -o -name "*.backup" -o -name "*.tmp" 2>/dev/null)
if [ -z "$BACKUP_FILES" ]; then
    echo "✅ PASS: No backup files found"
else
    echo "❌ FAIL: Backup files found:"
    echo "$BACKUP_FILES"
    FAILED=1
fi

echo ""
echo "2. Checking for source files..."
echo "-----------------------------------------"
if [ -d "vue-app/src" ]; then
    SOURCE_COUNT=$(find vue-app/src -type f | wc -l)
    echo "✅ PASS: Vue source files included ($SOURCE_COUNT files)"
else
    echo "❌ FAIL: Vue source files missing (vue-app/src not found)"
    FAILED=1
fi

echo ""
echo "3. Checking for build documentation..."
echo "-----------------------------------------"
if [ -f "package.json" ] && [ -f "build.sh" ]; then
    echo "✅ PASS: Build files included (package.json, build.sh)"
else
    echo "❌ FAIL: Build files missing"
    [ ! -f "package.json" ] && echo "  - package.json missing"
    [ ! -f "build.sh" ] && echo "  - build.sh missing"
    FAILED=1
fi

echo ""
echo "4. Checking for translation files..."
echo "-----------------------------------------"
if [ -f "languages/product-roadmap.pot" ]; then
    echo "✅ PASS: POT file included"
else
    echo "❌ FAIL: POT file missing"
    FAILED=1
fi

echo ""
echo "5. Checking for required WordPress files..."
echo "-----------------------------------------"
MISSING_FILES=""
[ ! -f "product-roadmap.php" ] && MISSING_FILES="$MISSING_FILES product-roadmap.php"
[ ! -f "readme.txt" ] && MISSING_FILES="$MISSING_FILES readme.txt"

if [ -z "$MISSING_FILES" ]; then
    echo "✅ PASS: All required files present"
else
    echo "❌ FAIL: Required files missing: $MISSING_FILES"
    FAILED=1
fi

echo ""
echo "6. Checking for minified files with sources..."
echo "-----------------------------------------"
MINIFIED_JS=$(find assets/js -name "*.min.js" 2>/dev/null)
MINIFIED_CSS=$(find assets/css -name "*.min.css" 2>/dev/null)

if [ -z "$MINIFIED_JS" ] && [ -z "$MINIFIED_CSS" ]; then
    echo "✅ PASS: No minified files without sources"
else
    echo "⚠️  WARNING: Minified files found (ensure sources are included):"
    [ ! -z "$MINIFIED_JS" ] && echo "$MINIFIED_JS"
    [ ! -z "$MINIFIED_CSS" ] && echo "$MINIFIED_CSS"
fi

echo ""
echo "7. Checking JavaScript source mapping..."
echo "-----------------------------------------"
if [ -f "assets/js/roadmap-app.js" ] && [ -d "vue-app/src" ]; then
    echo "✅ PASS: Compiled JS has corresponding source in vue-app/src"
else
    echo "⚠️  WARNING: Check that all compiled JS has source files"
fi

echo ""
echo "8. Checking for unwanted files..."
echo "-----------------------------------------"
UNWANTED=$(find . -name ".DS_Store" -o -name "Thumbs.db" -o -name "*.log" 2>/dev/null)
if [ -z "$UNWANTED" ]; then
    echo "✅ PASS: No unwanted system files"
else
    echo "⚠️  WARNING: Unwanted files found (should be removed):"
    echo "$UNWANTED"
fi

echo ""
echo "========================================="
if [ -z "$FAILED" ]; then
    echo "✅ COMPLIANCE CHECK: PASSED"
    echo "========================================="
    echo "The plugin package meets WordPress.org requirements!"
else
    echo "❌ COMPLIANCE CHECK: FAILED"
    echo "========================================="
    echo "Please fix the issues above before submitting to WordPress.org"
fi
echo ""

# Cleanup
cd /
rm -rf "$TEMP_DIR"

[ -z "$FAILED" ] && exit 0 || exit 1