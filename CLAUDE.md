# Plugin: Product Roadmap (free)

> **READ FIRST:** [`audit/manifest.json`](audit/manifest.json) is the generated inventory, and [`audit/AUDIT_SUMMARY.md`](audit/AUDIT_SUMMARY.md) holds the 2026-07-11 onboarding audit findings (bug baseline + free/pro contract). Ground-truth counts as of the 1.8.0 refresh, 2026-09-29 (verified by grep against `includes/*.php`): **12 REST routes, 2 CPTs (`roadmap_item`, `roadmap_changelog`), 4 taxonomies (`roadmap_status`, `roadmap_category`, `roadmap_product`, `changelog_type`), 2 shortcodes, 5 AJAX actions, 13 admin menu/submenu pages, 1 custom table (`roadmap_votes`), 33 `roadmap_*` hooks fired.**
>
> `manifest_refresh: generator-verified` — as of this refresh, `write-manifest.mjs` correctly finds rest/ajax/shortcodes/hooks (previously it reported `rest:0 / cpt:0 / shortcodes:0` due to a wrapper/registry blind spot; that's resolved). One known generator quirk: it also emits a spurious single-letter table name alongside the real `roadmap_votes` - the manifest has been manually corrected for this. Re-verify counts by grep after any structural change rather than trusting either source blindly.

## 1.7.0 status (resolved)
The 2026-07-11 onboarding-audit "headline issues" below were **fixed in 1.7.0**:
free/pro analytics hook contract, multi-product taxonomy consolidation, votes-table
indexes + bounded queries, security hardening (nonce/cap, sanitize, rate-limit +
suggestion honeypot), `uninstall.php`, `load_plugin_textdomain`, a11y labels, enum
consolidation, and the notification per-type toggles. The whole frontend is now
theme-token-driven and dark-mode-aware (theme toggle only). PHPStan (level 5) and
the contract audit both pass. Treat the older ⚠️ warnings as historical.

## Quick reference
- **Main file**: `product-roadmap.php`
- **Version**: `1.7.0` (readme Stable tag: 1.7.0 ✓)
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

## Version pairing with Pro
`ROADMAP_MIN_PRO_VERSION` (`product-roadmap.php`) is the Pro release paired with this Free; an older Pro keeps running and admins see an "update Product Roadmap Pro" notice. Pro's own `ROADMAP_PRO_MIN_FREE_VERSION` pauses Pro on an older Free. When a Free change breaks something an older Pro relies on, raise `ROADMAP_MIN_PRO_VERSION` in that commit.

## Free → Pro extension contract
Free fired 25 `roadmap_*` hooks at the 2026-07-11 audit (now 33, after 1.8.0's fixes and additions - see the READ FIRST line above for the current count). **See `audit/AUDIT_SUMMARY.md` for the 11 dead-listener / naming-drift breaks found at that audit** where Pro listened for a hook Free never fired (e.g. Pro's analytics listened for `roadmap_item_voted` but Free fired `roadmap_vote_submitted`) - fixed in 1.8.0, see the "1.7.0 status (resolved)" section above. When renaming or adding a `roadmap_*` `do_action`/`apply_filters`, check the Pro side first.

## Onboarding audit (2026-07-11) — headline issues
See `audit/AUDIT_SUMMARY.md`. Top real items: AJAX/nonce+cap on `roadmap_vote` (incl. guest nopriv path), no `uninstall.php` cleanup, missing `sanitize_callback` + 3× direct `$_POST`→`update_option`, no `load_plugin_textdomain()`, a11y (unlabeled inputs, `outline:none` w/o `:focus-visible`), `alert()`/`confirm()` in JS, enum drift on `type`. ~4,900 PHPCS errors are cosmetic/auto-fixable (`phpcbf`).

## CI / tooling state
- `.github/workflows/ci.yml` present (PHP Lint + PHPStan + WPCS).
- No local `phpstan.neon` / `phpcs.xml` / `docs/qa/` yet. Not onboarded to the full local-CI + journey gate.

## Refresh
Re-run `/wp-plugin-onboard --refresh` after non-trivial changes. Because of the registry blind spot, verify REST/CPT/shortcode counts by grep, not the generator.
