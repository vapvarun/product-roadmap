#!/bin/bash

# Build script for both Roadmap plugins

echo "========================================="
echo "Building All Roadmap Plugins..."
echo "========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Store current directory
CURRENT_DIR=$(pwd)

# Build main plugin first
echo "1. Building Product Roadmap Plugin..."
echo "-------------------------------------"
if [ -f "build.sh" ]; then
    if ! ./build.sh; then
        echo -e "${RED}✗ Error: Free plugin build failed${NC}"
        exit 1
    fi
    echo -e "${GREEN}✓ Free plugin build successful${NC}"
else
    echo -e "${RED}✗ Error: build.sh not found in current directory${NC}"
    exit 1
fi

echo ""
echo ""

# Build pro addon if it exists
PRO_ADDON_PATH="../roadmap-pro-addon"
if [ -d "$PRO_ADDON_PATH" ]; then
    echo "2. Building Roadmap Pro Addon..."
    echo "-------------------------------------"
    cd "$PRO_ADDON_PATH"
    if [ -f "build.sh" ]; then
        if ! ./build.sh; then
            echo -e "${RED}✗ Error: Pro addon build failed${NC}"
            echo -e "${YELLOW}Cannot continue - fix pro build errors first${NC}"
            cd "$CURRENT_DIR"
            exit 1
        fi
        echo -e "${GREEN}✓ Pro addon build successful${NC}"
    else
        echo -e "${RED}✗ Warning: build.sh not found in pro addon directory${NC}"
        cd "$CURRENT_DIR"
        exit 1
    fi
    cd "$CURRENT_DIR"
else
    echo "2. Roadmap Pro Addon not found - skipping"
    echo -e "${YELLOW}  (This is OK if you only want to build the free version)${NC}"
fi

echo ""
echo "========================================="
echo -e "${GREEN}✓ Build process completed successfully!${NC}"
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