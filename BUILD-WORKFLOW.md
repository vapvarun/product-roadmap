# Roadmap Plugins - Build & Release Workflow

## Overview

There are **TWO** build systems available:

1. **Centralized (RECOMMENDED)** - Build both free and pro from the free plugin directory
2. **Standalone** - Build pro addon separately from its own directory

---

## Centralized Build System (RECOMMENDED)

**Use this for official releases.** Ensures both plugins are built with the same process and validated together.

### Location
```
wp-content/plugins/product-roadmap/
├── build-all.sh          # Builds both plugins
└── build-release.sh      # Creates distribution ZIPs for both
```

### Quick Start

```bash
# Navigate to FREE plugin directory
cd wp-content/plugins/product-roadmap

# Build both plugins and create distribution ZIPs
./build-release.sh
```

This will:
1. ✅ Build free plugin (Vue app, RTL CSS, etc.)
2. ✅ Build pro addon (RTL CSS, minified files)
3. ✅ Verify all builds succeeded
4. ✅ Create `../releases/product-roadmap-VERSION.zip`
5. ✅ Create `../releases/roadmap-pro-addon-VERSION.zip`
6. ✅ Verify all required assets exist in ZIPs
7. ✅ Check for unwanted files (node_modules, etc.)

### Output Location
```
wp-content/plugins/releases/
├── product-roadmap-1.1.1.zip
└── roadmap-pro-addon-1.1.1.zip
```

### Just Building (No ZIP)

If you only want to build without creating ZIPs:

```bash
cd wp-content/plugins/product-roadmap
./build-all.sh
```

This runs the build process for both plugins without packaging.

---

## Standalone Build System

**Use this only for development/testing** of the pro addon in isolation.

### Location
```
wp-content/plugins/roadmap-pro-addon/
├── build.sh              # Builds pro addon only
└── build-release.sh      # Creates pro ZIP only
```

### Usage

```bash
# Navigate to PRO plugin directory
cd wp-content/plugins/roadmap-pro-addon

# Build and create ZIP
./build-release.sh
```

This creates: `roadmap-pro-addon-VERSION.zip` in the current directory.

**Note:** This is useful for quick pro-only development but **not recommended for official releases**.

---

## Comparison

| Feature | Centralized | Standalone |
|---------|-------------|------------|
| **Builds free plugin** | ✅ Yes | ❌ No |
| **Builds pro addon** | ✅ Yes | ✅ Yes |
| **Creates both ZIPs** | ✅ Yes | ❌ No (pro only) |
| **Verifies both builds** | ✅ Yes | ⚠️ Pro only |
| **Single command** | ✅ Yes | ⚠️ Pro only |
| **Recommended for releases** | ✅ **YES** | ❌ No |
| **Good for pro dev** | ⚠️ Overkill | ✅ Yes |

---

## Recommended Workflow

### For Official Releases

```bash
# 1. Update version numbers in plugin files
# - product-roadmap/product-roadmap.php
# - roadmap-pro-addon/roadmap-pro-addon.php

# 2. Navigate to free plugin directory
cd wp-content/plugins/product-roadmap

# 3. Build and package both plugins
./build-release.sh

# 4. Test the ZIPs
# - Install on fresh WordPress
# - Verify all features work
# - Check for errors

# 5. Distribute
# - Upload to WordPress.org (free)
# - Upload to customer portal (pro)
```

### For Pro Development

```bash
# Quick iteration during pro development
cd wp-content/plugins/roadmap-pro-addon
./build.sh  # Just build, no ZIP

# Test changes locally
# Make more changes
# Repeat
```

### For Complete Testing

```bash
# Build everything from centralized location
cd wp-content/plugins/product-roadmap
./build-all.sh

# Then test both plugins together
```

---

## Build Process Details

### What Each Script Does

#### `product-roadmap/build.sh`
1. Installs npm dependencies (if needed)
2. Builds Vue app (npm run build)
3. Copies built assets to plugin
4. Generates RTL CSS
5. Creates minified versions (if tools available)

#### `roadmap-pro-addon/build.sh`
1. Installs npm dependencies (if needed)
2. Generates RTL CSS for pro stylesheets
3. Creates minified CSS files
4. Creates minified JS files
5. **NEW:** Verifies all files were created successfully
6. **NEW:** Exits with error if any step failed

#### `product-roadmap/build-all.sh`
1. Runs `product-roadmap/build.sh`
2. **NEW:** Checks if free build succeeded
3. Runs `roadmap-pro-addon/build.sh`
4. **NEW:** Checks if pro build succeeded
5. **NEW:** Exits with error if either failed

#### `product-roadmap/build-release.sh`
1. Gets version from plugin files
2. Runs build-all.sh
3. **NEW:** Checks if builds succeeded
4. Creates clean directory for free plugin
5. Copies only distribution files
6. Removes dev files (node_modules, .sh, etc.)
7. Creates free ZIP
8. Creates clean directory for pro addon
9. Copies only distribution files
10. **NEW:** Verifies required pro assets exist
11. Creates pro ZIP
12. **NEW:** Validates ZIPs contain all required files

---

## Error Handling

All scripts now include comprehensive error checking:

### Build Failures

If `build.sh` fails in either plugin:
```
✗ Build completed with 2 error(s)
⚠ Some assets may not have been generated properly.
⚠ Check file permissions and try again.
```

The script will **exit with error code 1** and stop the process.

### Missing Assets

If required files are missing from ZIP:
```
Verifying pro addon assets...
✗ Missing required pro asset: assets/css/roadmap-pro.css
✗ ERROR: 1 required pro asset(s) missing
The pro build may have failed. Check build output.
```

The script will **delete the incomplete ZIP** and **exit with error**.

### Permission Errors

If files can't be written:
```
Error: EACCES: permission denied, open 'assets/css/roadmap-pro-rtl.css'
✗ Error: roadmap-pro-rtl.css was not created
```

**Solution:**
```bash
# Fix file ownership
sudo chown -R $(whoami):staff wp-content/plugins/roadmap-pro-addon/assets/
```

---

## Troubleshooting

### "Build failed" error

**Cause:** Permission issues or missing dependencies

**Solution:**
1. Check file permissions (see above)
2. Ensure Node.js is installed: `node --version`
3. Delete node_modules and try again:
   ```bash
   rm -rf node_modules
   rm -rf package-lock.json
   npm install
   ```

### "Pro addon not found"

**Cause:** Plugins are not in expected directory structure

**Expected structure:**
```
wp-content/plugins/
├── product-roadmap/
│   ├── build-all.sh
│   └── build-release.sh
└── roadmap-pro-addon/
    ├── build.sh
    └── build-release.sh
```

**Solution:** Ensure both plugins are sibling directories in `wp-content/plugins/`

### "Required assets missing"

**Cause:** Build failed but continued anyway

**Solution:**
1. Check the build output for specific errors
2. Fix permission issues
3. Run build again
4. The new error checking will catch this early

---

## Best Practices

### ✅ DO

- **Always use centralized build for releases**
- Check build output for errors
- Test ZIPs on fresh WordPress before distribution
- Keep build scripts executable: `chmod +x *.sh`
- Commit built assets to version control
- Document version changes

### ❌ DON'T

- Don't manually create ZIPs
- Don't skip build process
- Don't ignore error messages
- Don't distribute without testing
- Don't mix build systems (use one or the other)
- Don't commit node_modules

---

## Version Control

### What to Commit

**Built assets** (yes, commit these):
- `assets/css/*.css` (including RTL and minified)
- `assets/js/*.js` (including minified)
- `assets/js/*.min.js`

**Why?** Ensures distributionn works even if build fails.

**Build scripts** (yes, commit these):
- `*.sh` files
- `package.json`

**Don't commit:**
- `node_modules/`
- `package-lock.json` (optional)
- `*.zip` files
- `releases/` directory

---

## Changes in v1.1.1

### Centralized Build Scripts

✅ Added comprehensive error checking to `build-all.sh`
- Verifies each build succeeds before continuing
- Exits with error if any build fails
- Color-coded output for better visibility

✅ Enhanced `build-release.sh` with asset verification
- Checks if builds succeeded before creating ZIPs
- Verifies required assets exist in final ZIPs
- Fails early if anything is wrong

### Standalone Build Scripts

✅ Enhanced `roadmap-pro-addon/build.sh`
- Checks if each file was created successfully
- Tracks errors and exits with proper code
- Clear error messages

✅ Enhanced `roadmap-pro-addon/build-release.sh`
- Verifies build succeeded before creating ZIP
- Validates required assets in final ZIP
- Won't create broken distributions

### PHP Activation Check

✅ Added asset verification in `roadmap-pro-addon.php`
- Checks for required files on activation
- Auto-deactivates with clear error if files missing
- Prevents broken installations

---

## Summary

**For releases:** Use centralized build from `product-roadmap/`
```bash
cd wp-content/plugins/product-roadmap
./build-release.sh
```

**For pro development:** Use standalone build from `roadmap-pro-addon/`
```bash
cd wp-content/plugins/roadmap-pro-addon
./build.sh
```

**Either way:** The new error checking ensures you can't create broken builds!

---

**Last Updated:** November 3, 2025
**Maintained by:** WBComDesigns Development Team
