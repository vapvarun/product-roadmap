# Product Roadmap (free) — Onboarding Audit

**Date:** 2026-07-11 · **Version audited:** 1.6.0 (freshly pulled from origin/main; local was 1.0.1 / 50 commits behind) · **Tools:** wppqa_audit_plugin, enum-consistency, free/pro contract scan.

Maturity COMPLETE (70/100) · Code Quality C (62/100). Most of the 4,900+ errors are auto-fixable PHPCS whitespace — the list below is the *real* work.

## Critical / high (verify + fix)
1. **AJAX security — `roadmap_vote` (+ guest `nopriv`).** Guest voting is enabled, so a `wp_ajax_nopriv_roadmap_vote` path exists. Confirm `check_ajax_referer()` + appropriate capability/rate-limit on both the logged-in and nopriv handlers. (wppqa critical GAP + qa-coverage "uncovered AJAX".)
2. **No uninstall cleanup.** No `uninstall.php` / `register_uninstall_hook`. The custom table + 6 options survive deletion. Add cleanup.
3. **Settings input validation.** Missing `sanitize_callback` on `register_setting()` and **3× direct `$_POST` → `update_option`** bypassing the Settings API. Route through register_setting + sanitize.
4. **i18n broken — no `load_plugin_textdomain()`.** Strings do not translate at all. Add the call; add missing `translators:` comment on `_n()`.
5. **Enum drift on `"type"`** — `class-roadmap-cpt.php:477` (custom-field types) vs `class-roadmap-notifications.php:298` (notification types). *Likely two different concepts (false positive)* — verify; if distinct, rename one to remove the collision.

## Accessibility (real WCAG)
- 8 form inputs without labels (settings/meta forms) — add `<label for>` / `aria-label`.
- `outline:none` without `:focus-visible` replacement (7 in a11y, 12 more in ux-guidelines) — restore keyboard focus rings.

## UX / standards
- `alert()` ×10 and `confirm()` ×1 in JS — replace with in-UI notices/modals.
- 6 Dashicons in views instead of the shipped icon runtime.
- Iterating `$_POST`/`$_GET` directly — refactor to explicit sanitized reads.
- Marketing: no WordPress.org banner/icon/screenshots (blocks a clean .org listing).

## Free → Pro contract (this plugin is the firer)
Free fires 25 `roadmap_*` hooks. Pro depends on many. **11 hooks Pro listens for are never fired by Free** — when you touch hook names here, update Pro. Key drifts to fix on the Free side (add/rename the `do_action`) so Pro features work:
- Fire `roadmap_item_viewed` (Pro view analytics has no source event).
- Consider aligning names: Free `roadmap_vote_submitted` vs Pro `roadmap_item_voted`; Free `roadmap_status_changed` vs Pro `roadmap_item_status_changed`; Free `roadmap_comment_submitted` vs Pro `roadmap_item_comment_added`. Either rename on one side or fire both.
- `roadmap_after_main_content`, `roadmap_api_query_args`, `roadmap_api_settings`, `roadmap_settings_fields/_sections`, `roadmap_shortcode_atts`, `roadmap_changelog_shortcode_data` — Pro extension points with no Free firer.

## Heuristic notes / non-issues
- WIRING, REST-JS-CONTRACT, wiring_completeness all **passed** — the generic "cross-layer wiring" GAP is template boilerplate, not evidenced.
- "Directory & Listings" category detection (Map Integration, Listing Submission, Reviews) is **misclassification noise** — this is a roadmap/voting plugin. Disregard.
- ~4,900 PHPCS errors = cosmetic, one `phpcbf` pass.

## Ground-truth inventory (grep-verified)
13 REST routes (`roadmap/v1`) · 3 CPTs (roadmap_item, roadmap_product, roadmap_changelog) · 4 taxonomies · 2 shortcodes (`roadmap`, `roadmap_changelog`) · 8 AJAX actions · ~13 admin menu/submenu · 1 custom table · 25 `roadmap_*` hooks fired.
