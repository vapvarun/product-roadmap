#!/bin/bash

# Build script for both Roadmap plugins

echo "========================================="
echo "Building All Roadmap Plugins..."
echo "========================================="
echo ""

# Store current directory
CURRENT_DIR=$(pwd)

# Build main plugin first
echo "1. Building Product Roadmap Plugin..."
echo "-------------------------------------"
if [ -f "build.sh" ]; then
    ./build.sh
else
    echo "✗ Error: build.sh not found in current directory"
    exit 1
fi

echo ""
echo ""

# Build pro addon if it exists
PRO_ADDON_PATH="../../roadmap-pro-addon"
if [ -d "$PRO_ADDON_PATH" ]; then
    echo "2. Building Roadmap Pro Addon..."
    echo "-------------------------------------"
    cd "$PRO_ADDON_PATH"
    if [ -f "build.sh" ]; then
        ./build.sh
    else
        echo "✗ Warning: build.sh not found in pro addon directory"
    fi
    cd "$CURRENT_DIR"
else
    echo "2. Roadmap Pro Addon not found - skipping"
fi

echo ""
echo "========================================="
echo "✓ Build process completed!"
echo "========================================="
echo ""
echo "Both plugins are now ready with:"
echo "  - Production JavaScript and CSS"
echo "  - RTL stylesheets for all languages"
echo "  - Minified versions (if tools available)"
echo ""
echo "New features added:"
echo "  - RTL support (auto-loads for RTL languages)"
echo "  - Sort by: Most Voted, Recent, Discussed, Priority"
echo "  - All strings are translation-ready"
echo ""
echo "Example usage with sorting:"
echo '  [roadmap product="main" sort="votes"]'
echo ""