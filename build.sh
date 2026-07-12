#!/bin/bash

# Build script for Product Roadmap Plugin
# This script builds the Vue app, generates RTL CSS files, creates translation files, and cleans up

echo "========================================="
echo "Building Product Roadmap Plugin..."
echo "========================================="

# Step 1: Clean up backup files and unwanted files
echo ""
echo "Step 1: Cleaning up backup and temporary files..."
find . -name "*.bak" -type f -delete 2>/dev/null
find . -name "*~" -type f -delete 2>/dev/null
find . -name "*.backup" -type f -delete 2>/dev/null
find . -name "*.tmp" -type f -delete 2>/dev/null
find . -name ".DS_Store" -type f -delete 2>/dev/null
echo "✓ Backup and temporary files removed"

# Navigate to Vue app directory
cd vue-app

# Install dependencies
echo "Installing dependencies..."
npm install

# Build Vue app
echo "Building Vue app for production..."
npm run build

# IIFE-wrap the JS bundle so its top-level declarations (Vue's internal
# `const wp`, etc.) stay function-scoped instead of leaking into the page's
# shared global lexical scope. Without this, a theme that also declares a
# top-level `const wp` in a classic script (e.g. Reign) collides with
# "Identifier 'wp' has already been declared", which fatally aborts whichever
# script parses second and leaves the board stuck on "Loading roadmap...".
# Done as a post-build wrap (not a Vite iife format) so CSS stays a separate
# file. dev mode also loads dist/assets/index.js, so both paths get the fix.
if [ -f "dist/assets/index.js" ]; then
    printf ';(function(){\n' > dist/assets/index.js.wrapped
    cat dist/assets/index.js >> dist/assets/index.js.wrapped
    printf '\n})();\n' >> dist/assets/index.js.wrapped
    mv dist/assets/index.js.wrapped dist/assets/index.js
    echo "✓ Bundle IIFE-wrapped (global-scope isolation)"
fi

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
echo "Step 2: Generating RTL CSS for static files..."
if [ -f "build-rtl.js" ]; then
    node build-rtl.js
else
    echo "✗ Warning: build-rtl.js not found, skipping RTL generation for static files"
fi

# Generate POT file for translations
echo ""
echo "Step 3: Generating translation template (POT file)..."
if command -v wp &> /dev/null; then
    # Use WP-CLI if available
    wp i18n make-pot . languages/product-roadmap.pot --domain=product-roadmap --exclude=node_modules,vue-app/node_modules,tests,docs
    echo "✓ POT file generated using WP-CLI"
elif [ -f "languages/product-roadmap.pot" ]; then
    echo "✓ POT file already exists"
else
    # Create a basic POT file if WP-CLI is not available
    cat > languages/product-roadmap.pot << 'EOF'
# Product Roadmap Plugin
# Copyright (C) 2024 WBComDesigns
# This file is distributed under the GPLv2 or later.
msgid ""
msgstr ""
"Project-Id-Version: Product Roadmap 1.0.1\n"
"Report-Msgid-Bugs-To: https://wordpress.org/support/plugin/product-roadmap\n"
"POT-Creation-Date: 2024-09-24 00:00:00+00:00\n"
"MIME-Version: 1.0\n"
"Content-Type: text/plain; charset=UTF-8\n"
"Content-Transfer-Encoding: 8bit\n"
"PO-Revision-Date: YEAR-MO-DA HO:MI+ZONE\n"
"Last-Translator: FULL NAME <EMAIL@ADDRESS>\n"
"Language-Team: LANGUAGE <LL@li.org>\n"
EOF
    echo "✓ Basic POT file created"
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