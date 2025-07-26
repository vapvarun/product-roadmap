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

# Create minified versions (optional, for better performance)
if command -v terser &> /dev/null; then
    echo "Creating minified JavaScript..."
    terser ../assets/js/roadmap-app.js -o ../assets/js/roadmap-app.min.js -c -m
    echo "✓ Minified JavaScript created"
fi

if command -v cssnano &> /dev/null; then
    echo "Creating minified CSS..."
    cssnano ../assets/css/roadmap-app.css ../assets/css/roadmap-app.min.css
    echo "✓ Minified CSS created"
fi

echo ""
echo "========================================="
echo "✓ Product Roadmap build completed!"
echo "========================================="
echo ""
echo "Production assets are ready in:"
echo "  - assets/js/roadmap-app.js"
echo "  - assets/css/roadmap-app.css"
echo ""