# Plugin: Product Roadmap (free)

> **READ FIRST:** [`audit/manifest.json`](audit/manifest.json) is the generated inventory, and [`audit/AUDIT_SUMMARY.md`](audit/AUDIT_SUMMARY.md) holds the 2026-07-11 onboarding audit findings (bug baseline + free/pro contract). Ground-truth counts (verified by grep, NOT the generator): **13 REST routes, 3 CPTs, 4 taxonomies, 2 shortcodes, 8 AJAX actions, ~13 admin menu/submenu pages, 1 custom table, 25 `roadmap_*` hooks fired.**
>
> ⚠️ `manifest_refresh: agent-enumeration-only` — the deterministic generator (`write-manifest.mjs`) reports `rest:0 / cpt:0 / shortcodes:0` because this plugin registers via wrapper/registry methods the tracer can't follow. Trust the ground-truth counts above and grep, not the manifest's REST/CPT/shortcode sections.

## Quick reference
- **Main file**: `product-roadmap.php`
- **Version**: `1.6.0` (readme Stable tag: 1.6.0 ✓)
- **Namespace**: none (class prefix `Roadmap_*`, main class `RoadmapPlugin`)
- **Text domain**: `product-roadmap`
- **Constant**: `ROADMAP_VERSION` (Pro detects Free via this constant / `RoadmapPlugin` class)
- **Extends**: null (this is the free base; `roadmap-pro-addon` extends it)

## Key entry points
- Main class: `product-roadmap.php` → `RoadmapPlugin`
- REST API: `includes/class-roadmap-api.php` (namespace `roadmap/v1`)
- CPTs/taxonomies: `includes/class-roadmap-cpt.php` (roadmap_item, roadmap_product, roadmap_changelog)
- Voting: `includes/class-roadmap-voting.php` + AJAX `roadmap_vote` (+ `nopriv` guest path)
- Notifications: `includes/class-roadmap-notifications.php` (fires `roadmap_status_changed`)
- Settings/admin: `includes/class-roadmap-settings.php`, `class-roadmap-admin.php`
- Frontend: `vue-app/` (Vue SPA) + `templates/`

## Free → Pro extension contract
Free fires 25 `roadmap_*` hooks that Pro consumes. **See `audit/AUDIT_SUMMARY.md` for 11 dead-listener / naming-drift breaks** where Pro listens for a hook Free never fires (e.g. Pro's analytics listens for `roadmap_item_voted` but Free fires `roadmap_vote_submitted`). When renaming or adding a `roadmap_*` `do_action`/`apply_filters`, check the Pro side first.

## Onboarding audit (2026-07-11) — headline issues
See `audit/AUDIT_SUMMARY.md`. Top real items: AJAX/nonce+cap on `roadmap_vote` (incl. guest nopriv path), no `uninstall.php` cleanup, missing `sanitize_callback` + 3× direct `$_POST`→`update_option`, no `load_plugin_textdomain()`, a11y (unlabeled inputs, `outline:none` w/o `:focus-visible`), `alert()`/`confirm()` in JS, enum drift on `type`. ~4,900 PHPCS errors are cosmetic/auto-fixable (`phpcbf`).

## CI / tooling state
- `.github/workflows/ci.yml` present (PHP Lint + PHPStan + WPCS).
- No local `phpstan.neon` / `phpcs.xml` / `docs/qa/` yet. Not onboarded to the full local-CI + journey gate.

## Refresh
Re-run `/wp-plugin-onboard --refresh` after non-trivial changes. Because of the registry blind spot, verify REST/CPT/shortcode counts by grep, not the generator.
