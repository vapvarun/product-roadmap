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

## Contributing

When making CSS changes:
- Only edit source files in `vue-app/src/`
- RTL CSS is auto-generated, don't edit RTL files directly
- Run build process to test RTL output
- Test in both LTR and RTL languages