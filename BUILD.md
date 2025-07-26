# Building Product Roadmap Plugin

## Quick Start

To build the production assets for Product Roadmap, simply run:

```bash
./build.sh
```

This single command will:
1. Install all npm dependencies
2. Build the Vue.js application
3. Copy assets to the correct WordPress directories
4. Create minified versions (if tools are available)

## Requirements

- Node.js 16+ and npm
- Bash shell (Mac/Linux) or Git Bash (Windows)

## Manual Build Steps

If you prefer to build manually:

```bash
# Navigate to Vue app directory
cd vue-app

# Install dependencies
npm install

# Build for production
npm run build

# Copy assets (from vue-app directory)
cp dist/assets/index.js ../assets/js/roadmap-app.js
cp dist/assets/index.css ../assets/css/roadmap-app.css
```

## Development Mode

For development with hot reload:

```bash
cd vue-app
npm run dev
```

The development server will start at http://localhost:3000

## Build Output

After running the build script, production assets will be available at:
- `assets/js/roadmap-app.js` - Main JavaScript bundle
- `assets/css/roadmap-app.css` - Main CSS bundle

## Troubleshooting

If the build fails:
1. Ensure Node.js 16+ is installed: `node --version`
2. Clear npm cache: `npm cache clean --force`
3. Delete node_modules and reinstall: `rm -rf vue-app/node_modules && cd vue-app && npm install`

## Production Deployment

The built files in `assets/` directory are ready for production use. No additional build steps are required on the production server.