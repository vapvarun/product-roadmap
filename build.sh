#!/bin/bash

# Build script for Product Roadmap Plugin

echo "Building Product Roadmap Plugin..."

# Navigate to Vue app directory
cd vue-app

# Install dependencies
echo "Installing dependencies..."
npm install

# Build Vue app
echo "Building Vue app for production..."
npm run build

# Copy built assets to plugin assets directory
echo "Copying built assets..."
mkdir -p ../assets/js
mkdir -p ../assets/css

# Copy and rename files for WordPress
if [ -f "dist/assets/index.js" ]; then
    cp dist/assets/index.js ../assets/js/roadmap-app.js
    echo "✓ JavaScript bundle copied"
else
    echo "✗ Error: JavaScript bundle not found"
    exit 1
fi

if [ -f "dist/assets/index.css" ]; then
    cp dist/assets/index.css ../assets/css/roadmap-app.css
    echo "✓ CSS bundle copied"
else
    echo "✗ Error: CSS bundle not found"
    exit 1
fi

# Copy RTL CSS if generated
if [ -f "dist/assets/index-rtl.css" ]; then
    cp dist/assets/index-rtl.css ../assets/css/roadmap-app-rtl.css
    echo "✓ RTL CSS bundle copied"
fi

# Go back to plugin root
cd ..

# Generate RTL CSS for static files
echo ""
echo "Generating RTL CSS for static files..."
if [ -f "build-rtl.js" ]; then
    node build-rtl.js
else
    echo "✗ Warning: build-rtl.js not found, skipping RTL generation for static files"
fi

# Create minified versions (optional, for better performance)
if command -v terser &> /dev/null; then
    echo ""
    echo "Creating minified JavaScript..."
    terser assets/js/roadmap-app.js -o assets/js/roadmap-app.min.js -c -m
    echo "✓ Minified JavaScript created"
fi

if command -v cssnano &> /dev/null; then
    echo ""
    echo "Creating minified CSS..."
    cssnano assets/css/roadmap-app.css assets/css/roadmap-app.min.css
    echo "✓ Minified CSS created"
    
    # Minify RTL CSS if it exists
    if [ -f "assets/css/roadmap-app-rtl.css" ]; then
        cssnano assets/css/roadmap-app-rtl.css assets/css/roadmap-app-rtl.min.css
        echo "✓ Minified RTL CSS created"
    fi
    
    if [ -f "assets/css/meta-box-rtl.css" ]; then
        cssnano assets/css/meta-box-rtl.css assets/css/meta-box-rtl.min.css
        echo "✓ Minified Meta Box RTL CSS created"
    fi
fi

echo ""
echo "========================================="
echo "✓ Product Roadmap build completed!"
echo "========================================="
echo ""
echo "Production assets are ready in:"
echo "  - assets/js/roadmap-app.js"
echo "  - assets/css/roadmap-app.css"
if [ -f "assets/css/roadmap-app-rtl.css" ]; then
    echo "  - assets/css/roadmap-app-rtl.css"
fi
if [ -f "assets/css/meta-box-rtl.css" ]; then
    echo "  - assets/css/meta-box-rtl.css"
fi
echo ""
echo "To use the plugin with sorting:"
echo '  [roadmap product="main" sort="votes"]'
echo ""