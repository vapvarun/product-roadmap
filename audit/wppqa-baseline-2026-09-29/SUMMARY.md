# wppqa baseline - 2026-09-29 (1.8.0 manifest refresh)

Run before regenerating `audit/manifest.json`, per `/wp-plugin-onboard` Phase 0.
`wppqa_check_plugin_dev_rules` is no longer available in the installed wp-plugin-qa
MCP server (not in its current tool list); the two other Phase 0 checks ran.

## wppqa_check_rest_js_contract - 24 failed, all false positives

Every finding is the same shape: a property access "near route /suggest" in
`assets/js/roadmap-app.js` that isn't in `/suggest`'s response shape
(`class-roadmap-api.php:119` returns only `[success, id]`).

**Root cause, verified:** `roadmap-app.js` is a single minified Vite/Vue IIFE
bundle (the whole frontend app in one file, one-letter variable names). The
checker's "50-line proximity" heuristic assumes unminified source where
physical proximity implies logical relatedness; on a minified bundle it
instead flags whatever property reads happen to land within 50 lines of any
route-string literal, regardless of which function or component they're
actually in.

Spot-verified one flagged group (`priority`, `progress`, `target_date`,
`categories`) against the actual PHP: these are real keys in
`class-roadmap-api.php`'s `format_item()` (the `/items` response shape,
~line 465-475), not `/suggest`. The JS is reading them correctly, from the
correct endpoint - the checker just attributed the read to the wrong nearby
route string in the minified soup. One finding (`offsetHeight`) is a DOM
property read, not a REST response key at all - a tell that "proximity" here
is purely textual, not logical.

**Classification: heuristic-driven false positive, not a real contract bug.**
This check is a good fit for unbundled admin.js-style files (its intended
use case) and a poor fit for a Vite-bundled SPA. No code change made.

## wppqa_check_wiring_completeness - 0 pass / 0 fail / 1 skipped

Scans `includes/admin/` for setting keys; this plugin keeps its settings
renderer at `includes/class-roadmap-settings.php` directly (no `admin/`
subdirectory), so the check found nothing to scan. Not a finding either way -
a structural mismatch between the check's assumed layout and this plugin's.

## Net result

No real bugs surfaced by either check on this pass. Proceeding to the
manifest refresh (Phase 1-2) with this documented rather than silently
discarding the run.
