# WordPress.org Plugin Directory Compliance

This document outlines how Product Roadmap plugin complies with WordPress.org guidelines.

## ✅ Issues Addressed from Review

### 1. No Backup Files
- Build scripts automatically remove all backup files (*.bak, *~, *.backup)
- Build process includes verification that no backup files exist in distribution
- `.distignore` file configured to exclude backup files

### 2. Source Code Included
Per WordPress Guidelines Section 4 (Human Readable Code):
- **All Vue.js source files included**: `/vue-app/src/`
- **Build configuration included**: `package.json`, `vite.config.js`
- **Build scripts included**: `build.sh`, `build-all.sh`, `build-rtl.js`
- **Documentation included**: `README.md`, `readme.txt`

### 3. No Inline CSS/JavaScript
All inline styles and scripts have been moved to properly enqueued files:
- `assets/css/roadmap-admin.css` - Admin styles
- `assets/js/roadmap-admin.js` - Admin scripts
- `assets/css/roadmap-single.css` - Single template styles
- `assets/css/roadmap-archive.css` - Archive template styles
- `assets/js/roadmap-meta-box.js` - Meta box functionality

### 4. Proper Script/Style Enqueueing
All scripts and styles are enqueued using WordPress functions:
- `wp_enqueue_script()`
- `wp_enqueue_style()`
- `wp_localize_script()` for AJAX data
- Proper dependencies and version numbers

### 5. Translation Support
- POT file template included: `languages/product-roadmap.pot`
- Build process generates POT file automatically
- All strings use proper localization functions

## Build Process

### Prerequisites
```bash
npm install
cd vue-app && npm install && cd ..
```

### Building for Distribution
```bash
# Full build with all checks
./build.sh

# Create release package
./build-release.sh
```

### What the Build Does
1. **Cleans backup files** - Removes all *.bak, *~, *.backup files
2. **Builds Vue app** - Compiles source to production assets
3. **Generates RTL CSS** - Creates right-to-left stylesheets
4. **Creates POT file** - Generates translation template
5. **Verifies compliance** - Ensures no backup files in distribution

## File Structure

```
product-roadmap/
├── assets/
│   ├── js/           # Compiled JavaScript (with sources in vue-app/src)
│   └── css/          # Stylesheets
├── includes/         # PHP classes
├── templates/        # Template files
├── languages/        # Translation files
│   └── product-roadmap.pot
├── vue-app/
│   ├── src/         # Vue source files (INCLUDED)
│   ├── dist/        # Built files
│   └── package.json # Dependencies
├── build.sh         # Build script (INCLUDED)
├── package.json     # Dependencies (INCLUDED)
└── readme.txt       # WordPress readme with build docs
```

## Verification Checklist

Before submitting to WordPress.org:

- [ ] Run `./build.sh` to build all assets
- [ ] Run `./build-release.sh` to create distribution package
- [ ] Verify no backup files: `find . -name "*.bak" -o -name "*~"`
- [ ] Verify source files included: Check `/vue-app/src/` exists
- [ ] Test with WP_DEBUG enabled
- [ ] Verify all scripts/styles properly enqueued
- [ ] Check POT file exists in `/languages/`
- [ ] Test on clean WordPress installation

## WordPress Guidelines References

- [Section 4: Code must be (mostly) human readable](https://developer.wordpress.org/plugins/wordpress-org/detailed-plugin-guidelines/#4-code-must-be-mostly-human-readable)
- [Plugin Developer FAQ](https://developer.wordpress.org/plugins/wordpress-org/plugin-developer-faq/)

## Support

For questions about compliance:
- GitHub Issues: https://github.com/wbcomdesigns/product-roadmap
- WordPress Support: https://wordpress.org/support/plugin/product-roadmap