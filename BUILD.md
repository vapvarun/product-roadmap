# Building Product Roadmap Plugin

## Quick Start

To build the production assets for Product Roadmap, simply run:

```bash
./build.sh
```

To build both the main plugin and pro addon:

```bash
./build-all.sh
```

## What's New

The build process now includes:
- **RTL CSS Generation** - Automatically creates RTL stylesheets for all CSS files
- **Minification** - Creates minified versions of all JS and CSS files
- **Pro Addon Support** - Builds both plugins with a single command

## Requirements

- Node.js 16+ and npm
- Bash shell (Mac/Linux) or Git Bash (Windows)

## Build Commands

### Using Shell Scripts

```bash
# Build main plugin only
./build.sh

# Build both plugins
./build-all.sh
```

### Using npm

```bash
# Build main plugin only
npm run build

# Build both plugins
npm run build:all

# Development mode with hot reload
npm run dev
```

## Build Process Details

The build script will:

1. **Install Dependencies** - Installs npm packages for Vue app
2. **Build Vue Application** - Compiles Vue 3 app with Vite
3. **Generate RTL CSS** - Creates RTL versions using Vite plugin
4. **Copy Assets** - Moves built files to WordPress directories
5. **Generate Static RTL** - Creates RTL for admin styles
6. **Minify Files** - Creates .min versions of all assets

## Output Files

After building, you'll have:

### JavaScript Files
- `assets/js/roadmap-app.js` - Main Vue application
- `assets/js/roadmap-app.min.js` - Minified version

### CSS Files
- `assets/css/roadmap-app.css` - Main styles
- `assets/css/roadmap-app-rtl.css` - RTL styles
- `assets/css/roadmap-app.min.css` - Minified styles
- `assets/css/roadmap-app-rtl.min.css` - Minified RTL
- `assets/css/meta-box-rtl.css` - Admin RTL styles
- `assets/css/meta-box-rtl.min.css` - Minified admin RTL

## Pro Addon Build

If you have the pro addon installed:

```bash
cd ../roadmap-pro-addon
./build.sh
```

This will generate:
- `assets/css/roadmap-pro-rtl.css` - Pro RTL styles
- Minified versions of all pro assets

## Development Mode

For development with hot reload:

```bash
cd vue-app
npm run dev
```

The development server will start at http://localhost:5173

## New Features Usage

### Sorting Options

After building, you can use sorting in shortcodes:

```
[roadmap product="main" sort="votes"]     // Most voted items first
[roadmap product="main" sort="recent"]    // Most recent items first
[roadmap product="main" sort="comments"]  // Most discussed first
[roadmap product="main" sort="priority"]  // Highest priority first
```

### RTL Support

RTL stylesheets are automatically loaded when:
- WordPress site language is RTL (Arabic, Hebrew, Persian, etc.)
- No manual configuration needed
- All UI elements properly mirror for RTL languages

### Translation Ready

All UI strings are now translatable:
- Use standard WordPress translation tools
- POT files can be generated from the plugin
- All JavaScript strings use the i18n system

## Troubleshooting

If the build fails:

1. **Check Node.js version**: `node --version` (requires 16+)
2. **Clear npm cache**: `npm cache clean --force`
3. **Reinstall dependencies**: 
   ```bash
   rm -rf node_modules package-lock.json
   npm install
   ```
4. **Check permissions**: `chmod +x build.sh build-all.sh`
5. **Manual RTL generation**: `node build-rtl.js`

## Production Deployment

The built files in `assets/` directory are ready for production use. No additional build steps are required on the production server.

## Creating Release Packages

To create distribution-ready zip files for both free and pro versions:

```bash
# Create release packages
./build-release.sh
```

This will:
1. Build the plugin with production assets
2. Create a `releases` directory with timestamp
3. Generate zip files for distribution:
   - `product-roadmap-{version}.zip` - Free version only
   - `product-roadmap-pro-{version}.zip` - Complete pro package (includes both plugins)

### Release Build Process

The release script performs these steps:

1. **Production Build**: Runs `./build.sh` to compile all assets
2. **Version Detection**: Reads version from main plugin file
3. **Directory Setup**: Creates timestamped release directory
4. **Free Version Package**:
   - Copies all plugin files
   - Removes development files (node_modules, build scripts, etc.)
   - Excludes source files and documentation
5. **Pro Version Package**:
   - Includes complete free plugin
   - Adds pro addon (if available)
   - Removes all development files

### Release Package Contents

**Free Version Package:**
- All core plugin files
- Production-ready assets (minified JS/CSS)
- Setup wizard and demo import
- No development files
- Ready for WordPress.org repository

**Pro Version Package:**
- Complete free plugin with all features
- Pro addon with premium features
- Production-ready assets for both plugins
- No development files

### Files Excluded from Release:
```
.git/
.gitignore
.env
node_modules/
vue-app/src/
vue-app/public/
vue-app/index.html
vue-app/.gitignore
build*.sh
package*.json
vite.config.js
build-rtl.js
*.log
.DS_Store
Thumbs.db
BUILD.md
DEVELOPER.md
docs/
```

### Manual Packaging

If you need to create packages manually:

```bash
# Free version
zip -r roadmap-plugin.zip roadmap-plugin/ \
  -x "*/node_modules/*" \
  -x "*/vue-app/src/*" \
  -x "*/vue-app/public/*" \
  -x "*/.git/*" \
  -x "*/build*.sh" \
  -x "*/package*.json" \
  -x "*/.*" \
  -x "*/docs/*" \
  -x "*/BUILD.md" \
  -x "*/DEVELOPER.md"

# Pro version (from parent directory)
zip -r product-roadmap-pro.zip product-roadmap/ roadmap-pro-addon/ \
  -x "*/node_modules/*" \
  -x "*/vue-app/src/*" \
  -x "*/.git/*" \
  -x "*/build*.sh" \
  -x "*/.*"
```

### Version Management

The version number is automatically read from the plugin header:
```php
/**
 * Version: 1.0.0
 */
```

To update the version:
1. Update version in `product-roadmap.php` header
2. Update version in `readme.txt` (if using WordPress.org)
3. Run `./build-release.sh` to create new versioned packages

## Contributing

When making CSS changes:
- Only edit source files in `vue-app/src/`
- RTL CSS is auto-generated, don't edit RTL files directly
- Run build process to test RTL output
- Test in both LTR and RTL languages