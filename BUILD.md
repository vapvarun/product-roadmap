# Product Roadmap - Build Guide

## Quick Start

To create a release build for both free and pro plugins:

```bash
./build-release.sh
```

This single command:
- Builds the free plugin (Vue app + RTL CSS + POT file)
- Builds the pro addon (RTL CSS + minified assets)
- Creates distribution ZIPs in `dist/` folders
- Verifies all required assets are present

## Build Output

After running `build-release.sh`:

```
product-roadmap/dist/product-roadmap-1.1.1.zip     (WordPress.org compatible)
roadmap-pro-addon/dist/roadmap-pro-addon-1.1.1.zip (Requires free version)
```

## Development Build

To build only the free plugin during development:

```bash
./build.sh
```

This builds:
- Vue app (production bundle)
- RTL CSS files
- Translation template (POT file)

## Requirements

- Node.js 14+
- npm
- WP-CLI (for POT file generation)

## Troubleshooting

### Permission Errors

If you see `EACCES: permission denied` errors:

```bash
chmod -R u+w assets/ vue-app/
```

### Build Fails

The build script has comprehensive error checking and will:
- Stop if any step fails
- Show clear error messages
- Verify all assets before creating ZIPs

If the build fails, read the error output carefully - it will tell you exactly what went wrong.

### Missing Assets

The build process automatically verifies that all required CSS/JS files exist before creating distribution ZIPs. If any are missing, the build will fail with a clear error message.

## Distribution

Both ZIPs are ready for distribution:
- Free version: Upload to WordPress.org or install directly
- Pro addon: Requires free version to be installed first

The ZIPs are tracked in git (in `dist/` folders) for easy access.

---

**Last Updated:** November 3, 2025
