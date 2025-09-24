#!/bin/bash

# Build and package script for Product Roadmap Plugin
# This script builds the plugin and creates distribution zip files

echo "========================================="
echo "Building Release Packages..."
echo "========================================="
echo ""

# Store current directory
CURRENT_DIR=$(pwd)
PLUGIN_SLUG="product-roadmap"
PRO_SLUG="roadmap-pro-addon"

# Create a releases directory if it doesn't exist
RELEASES_DIR="$CURRENT_DIR/../releases"
mkdir -p "$RELEASES_DIR"

# Get version from plugin file
if [ -f "product-roadmap.php" ]; then
    VERSION=$(grep -i "Version:" product-roadmap.php | sed 's/.*Version: *//' | tr -d '\r\n' | awk '{print $1}')
    echo "Plugin version: $VERSION"
else
    echo "✗ Error: product-roadmap.php not found"
    exit 1
fi

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
cp build.sh "$FREE_DIR/"  # Include build scripts for transparency
cp build-all.sh "$FREE_DIR/" 2>/dev/null || true
cp build-rtl.js "$FREE_DIR/" 2>/dev/null || true

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

# Remove unnecessary markdown files but keep README
rm -f "$FREE_DIR/MARKETING-IMAGES.md" 2>/dev/null || true
rm -f "$FREE_DIR/SNEAK-PEEK.md" 2>/dev/null || true
rm -f "$FREE_DIR/BUILD.md" 2>/dev/null || true
rm -f "$FREE_DIR/DEVELOPER.md" 2>/dev/null || true

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
zip -r "$RELEASES_DIR/${PLUGIN_SLUG}-${VERSION}.zip" "$PLUGIN_SLUG" -q
echo "✓ Free version package created: ${PLUGIN_SLUG}-${VERSION}.zip"

# Build and package pro version if it exists
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
        ./build.sh
    else
        echo "✗ Warning: build.sh not found in pro addon"
    fi
    
    echo ""
    echo "4. Creating Pro Version Package..."
    echo "-------------------------------------"
    
    # Create pro package directory
    PRO_PACKAGE_DIR="$TEMP_DIR/${PLUGIN_SLUG}-pro"
    mkdir -p "$PRO_PACKAGE_DIR/$PLUGIN_SLUG"
    mkdir -p "$PRO_PACKAGE_DIR/$PRO_SLUG"
    
    # Copy free version files to pro package
    echo "Copying free plugin files..."
    cp -r "$FREE_DIR/"* "$PRO_PACKAGE_DIR/$PLUGIN_SLUG/"
    
    # Copy pro addon files
    echo "Copying pro addon files..."
    cp -r assets "$PRO_PACKAGE_DIR/$PRO_SLUG/"
    cp -r includes "$PRO_PACKAGE_DIR/$PRO_SLUG/"
    cp -r languages "$PRO_PACKAGE_DIR/$PRO_SLUG/" 2>/dev/null || true
    cp roadmap-pro-addon.php "$PRO_PACKAGE_DIR/$PRO_SLUG/"
    cp readme.txt "$PRO_PACKAGE_DIR/$PRO_SLUG/"
    cp LICENSE "$PRO_PACKAGE_DIR/$PRO_SLUG/" 2>/dev/null || true
    
    # Clean up pro addon files
    echo "Cleaning up pro addon development files..."
    find "$PRO_PACKAGE_DIR/$PRO_SLUG" -name "*.scss" -delete 2>/dev/null || true
    find "$PRO_PACKAGE_DIR/$PRO_SLUG" -name "*.map" -delete 2>/dev/null || true
    find "$PRO_PACKAGE_DIR/$PRO_SLUG" -name ".DS_Store" -delete 2>/dev/null || true
    rm -f "$PRO_PACKAGE_DIR/$PRO_SLUG/build.sh" 2>/dev/null || true
    rm -f "$PRO_PACKAGE_DIR/$PRO_SLUG/package.json" 2>/dev/null || true
    rm -f "$PRO_PACKAGE_DIR/$PRO_SLUG/package-lock.json" 2>/dev/null || true
    rm -rf "$PRO_PACKAGE_DIR/$PRO_SLUG/node_modules" 2>/dev/null || true
    rm -rf "$PRO_PACKAGE_DIR/$PRO_SLUG/docs" 2>/dev/null || true
    rm -f "$PRO_PACKAGE_DIR/$PRO_SLUG/*.md" 2>/dev/null || true
    
    # Create pro version zip
    echo "Creating pro version zip..."
    cd "$TEMP_DIR"
    zip -r "$RELEASES_DIR/${PLUGIN_SLUG}-pro-${VERSION}.zip" "${PLUGIN_SLUG}-pro" -q
    echo "✓ Pro version package created: ${PLUGIN_SLUG}-pro-${VERSION}.zip"
else
    echo ""
    echo "3. Pro addon not found - skipping pro package"
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
echo "Packages created in: $RELEASES_DIR"
echo ""
echo "Free version:"
echo "  - ${PLUGIN_SLUG}-${VERSION}.zip"
if [ -d "$PRO_ADDON_PATH" ]; then
    echo ""
    echo "Pro version:"
    echo "  - ${PLUGIN_SLUG}-pro-${VERSION}.zip (complete package with both plugins)"
fi
echo ""
echo "These packages are ready for distribution!"
echo ""