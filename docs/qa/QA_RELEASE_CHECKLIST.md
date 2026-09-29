# Product Roadmap — QA Release Checklist

> **The final gate before tagging a release.** Every row must pass, no exceptions.
> This is the backend counterpart to `PRE_RELEASE_SMOKE.md` (frontend/browser).
> Together they guarantee: code quality + feature behavior + safe packaging.

**Target time:** 45 minutes end-to-end (plus the 90-min browser smoke).

---

## 0 — Branch hygiene

- [ ] On a named release branch (`release/<the version being tagged, e.g. 1.8.1>` or equivalent), NOT on `main`
- [ ] `git status` clean — no uncommitted changes
- [ ] `git pull` on the branch — up to date with origin
- [ ] `main` merged into release branch (or rebased) — no stale base
- [ ] No `.DS_Store`, `.idea/`, `.vscode/`, `node_modules/`, `vendor/` staged for commit

```bash
cd wp-content/plugins/product-roadmap
git status
git fetch origin
git log --oneline origin/main..HEAD | head -20   # what's shipping
```

## 1 — Version triangulation

Product Roadmap keeps the version in multiple places. Every place must match.

- [ ] `product-roadmap.php` header `Version:` comment equals `<the version being tagged, e.g. 1.8.1>`
- [ ] `product-roadmap.php` `define( 'ROADMAP_VERSION', '<the version being tagged, e.g. 1.8.1>' );` matches
- [ ] `readme.txt` `Stable tag: <the version being tagged, e.g. 1.8.1>` matches (if .org plugin)
- [ ] `package.json` `version` matches (if present)
- [ ] `composer.json` `version` matches (if present)
- [ ] `CHANGELOG.md` has a `## <the version being tagged, e.g. 1.8.1> — YYYY-MM-DD` entry with real release notes

- [ ] roadmap-pro-addon version also equals `<the version being tagged, e.g. 1.8.1>` (lockstep)
- [ ] roadmap-pro-addon version constant matches free version at runtime (use a small wp-cli shell script or REST probe — both constants must print `<the version being tagged, e.g. 1.8.1>`)


Fast check:
```bash
grep -rE "Version:|define.*ROADMAP_VERSION|Stable tag" wp-content/plugins/product-roadmap \
  | grep -v vendor | grep -v node_modules
```

Every printed line should show `<the version being tagged, e.g. 1.8.1>`.

## 2 — Static analysis

Run from `wp-content/plugins/product-roadmap`.

### WPCS (WordPress Coding Standards)

- [ ] `composer phpcs` (or `vendor/bin/phpcs`) — 0 errors, 0 warnings on changed files
- [ ] No new suppressions (`// phpcs:ignore`) added this release without a comment explaining why

```bash
vendor/bin/phpcs --standard=phpcs.xml.dist --report=summary
```

### PHPStan

- [ ] `composer phpstan` — level 5 clean, or only entries in the baseline
- [ ] Baseline not grown this release (or the diff is documented in CHANGELOG)

```bash
vendor/bin/phpstan analyse --memory-limit=2G
diff <(git show HEAD:phpstan-baseline.neon 2>/dev/null || echo "") phpstan-baseline.neon
```

### PHP lint (syntax)

```bash
find wp-content/plugins/product-roadmap -name "*.php" \
  -not -path "*/vendor/*" -not -path "*/node_modules/*" \
  -exec php -l {} \; 2>&1 | grep -v "No syntax errors"
```

- [ ] No output (means no syntax errors in any PHP file)

## 3 — Tests

### PHPUnit

- [ ] `composer test` — all tests pass
- [ ] PHP matrix covers the declared floor (`Requires PHP: 7.4`) AND current stable (8.3 / 8.4 at minimum)
- [ ] WP matrix covers declared `Requires at least:` AND current stable AND `latest`

```bash
composer test
# or full matrix:
phpenv local 8.1 && composer test && \
phpenv local 8.2 && composer test && \
phpenv local 8.3 && composer test && \
phpenv local 8.4 && composer test
```

### Jest / JS tests (if present)

- [ ] `npm test` — all pass
- [ ] No `.only` / `.skip` left in test files

```bash
grep -rE "\.only\(|\.skip\(" wp-content/plugins/product-roadmap/tests/ wp-content/plugins/product-roadmap/assets/ 2>/dev/null
```

### Code coverage (if tracked)

- [ ] Coverage for files touched this release ≥ project floor (document if below)

## 4 — Security sweep

### Nonce + capability hot-check

- [ ] Every new REST route registered this release calls `current_user_can()` in its `permission_callback`
- [ ] Every new AJAX action checks `check_ajax_referer()` AND `current_user_can()`
- [ ] Every new admin form calls `wp_verify_nonce()` + `current_user_can()` on POST handler
- [ ] Every new form output includes `wp_nonce_field()`

Hot-check:
```bash
# New REST routes this release
git diff origin/main...HEAD -- '*.php' | grep -E "^\+.*register_rest_route" | head -20
# …then audit each for permission_callback
```

### Escape on output

- [ ] Every echoed variable passes through an escape function (`esc_html`, `esc_attr`, `esc_url`, `wp_kses_post`, etc.)
- [ ] No `echo $variable` without escape in templates/* added this release
- [ ] Translations via `esc_html__`, `esc_attr__`, `esc_html_e`, etc. (not bare `__` in output context)

```bash
git diff origin/main...HEAD -- '*.php' | grep -E "^\+.*echo \\\$" | grep -v "esc_"
```

### SQL

- [ ] No string concatenation in `$wpdb` queries — all use `$wpdb->prepare()`
- [ ] Table names use `$wpdb->prefix` — no hardcoded `wp_`

```bash
git diff origin/main...HEAD -- '*.php' | grep -E "\\\$wpdb->(query|get_)" | grep -v "prepare"
```

### File operations

- [ ] No `file_get_contents` on user-supplied paths
- [ ] No dynamic-code execution functions called with user-supplied data (grep for forbidden patterns listed in `.phpcs.xml.dist` — project policy)
- [ ] Uploads use `wp_handle_upload()` with proper MIME/size validation

## 5 — Translations (i18n)

- [ ] `.pot` file regenerated and matches current strings
- [ ] No em-dashes (`—`) inside any `__()`, `_e()`, `_x()`, `_n()`, `esc_html__()` (reads as AI-generated)
- [ ] Text domain consistent across all files (`product-roadmap`)
- [ ] `_n()` used for pluralizable strings (not runtime `if ($count === 1)`)

```bash
# Em-dash check
grep -rE "(__|_e|_x|_n|esc_html__|esc_attr__|esc_html_e|esc_attr_e)\\([^)]*—" \
  wp-content/plugins/product-roadmap | grep -v vendor
```

Regenerate pot:
```bash
wp i18n make-pot wp-content/plugins/product-roadmap wp-content/plugins/product-roadmap/languages/product-roadmap.pot
```

## 6 — Readme + Docs

### WordPress.org readme (if applicable)

- [ ] `readme.txt` validates at https://wordpress.org/plugins/developers/readme-validator/
- [ ] `Requires at least`, `Tested up to`, `Requires PHP` current
- [ ] `Stable tag` matches `<the version being tagged, e.g. 1.8.1>`
- [ ] Changelog entry written for `<the version being tagged, e.g. 1.8.1>`
- [ ] Upgrade notice written for `<the version being tagged, e.g. 1.8.1>` if behavior changes

### Internal docs

- [ ] `CHANGELOG.md` updated (human-readable, customer-facing)
- [ ] `docs/qa/AGENT_SMOKE_RUNBOOK.md` section D updated with any new regression guards from this release
- [ ] `docs/architecture/` updated if architecture changed
- [ ] Any new public hooks documented in `docs/hooks.md` (or equivalent)

## 7 — Browser smoke gate (external dependency)

- [ ] `wp-content/plugins/product-roadmap/docs/qa/.last-smoke-pass.json` exists
- [ ] Report `release_version` equals `<the version being tagged, e.g. 1.8.1>`
- [ ] Report `ran_at` within the last 24 hours
- [ ] `failures[]` is empty
- [ ] `debug_log_issues[]` is empty (no fatals/warnings during walk)
- [ ] `manual_required[]` reviewed — Firefox / Safari iOS flows verified separately by a human

If the report is missing or stale, run the `product-roadmap-smoke` skill.

## 8 — Packaging dry-run

- [ ] `bin/build-release.sh --output /tmp --dry-run` (or equivalent) succeeds end-to-end
- [ ] Resulting zip has NO: `.git/`, `node_modules/`, `tests/`, `.github/`, `bin/`, `phpunit.xml.dist`, `phpcs.xml.dist`, `composer.json` (unless required), `composer.lock`, `package.json`, `package-lock.json`, `.DS_Store`
- [ ] Resulting zip HAS: `product-roadmap.php`, `readme.txt`, `languages/*.pot`, `includes/`, `assets/`, `templates/`, `vendor/` (if runtime deps)
- [ ] Zip extracts to a folder named exactly `product-roadmap/` (not `product-roadmap-<the version being tagged, e.g. 1.8.1>/` or similar)
- [ ] Zip size reasonable (flag if >2× previous release)

```bash
cd wp-content/plugins/product-roadmap
bin/build-release.sh --output /tmp
unzip -l /tmp/product-roadmap-<the version being tagged, e.g. 1.8.1>.zip | head -50
ls -lh /tmp/product-roadmap-<the version being tagged, e.g. 1.8.1>.zip
```

## 9 — Install-in-anger

On a **second clean** Local site (not the development site):

- [ ] Install the generated zip via `wp plugin install /tmp/product-roadmap-<the version being tagged, e.g. 1.8.1>.zip --activate`
- [ ] Activation succeeds — no fatal, no PHP warning in debug.log
- [ ] Front-end landing route (first request after activation) returns HTTP 200
- [ ] DB tables created (see Section A2 in the agent runbook)

- [ ] Install `roadmap-pro-addon` zip — activates, no fatal, Pro-specific features appear
- [ ] Deactivate `product-roadmap` → `roadmap-pro-addon` shows the "requires product-roadmap" notice, doesn't fatal


## 10 — Upgrade-in-anger

On a **third clean** site with the **previous stable version** installed + real data:

- [ ] Upload the new zip via "Replace plugin" or WP admin update flow
- [ ] Upgrade succeeds — no fatal
- [ ] DB version option updates (`wp option get roadmap_version`)
- [ ] Pre-existing data still renders on every surface (don't just check one page)
- [ ] Settings preserved (no defaults overwritten)
- [ ] No new `debug.log` entries during the upgrade request
- [ ] Cron events re-registered cleanly (`wp cron event list | grep roadmap`)

## 11 — Release metadata

- [ ] Git tag created: `v<the version being tagged, e.g. 1.8.1>` (annotated, not lightweight — `git tag -a v<the version being tagged, e.g. 1.8.1> -m "..."`)
- [ ] Tag points at the release-branch commit (not `main` yet)
- [ ] GitHub Release drafted with changelog copied from `CHANGELOG.md`
- [ ] Release zip attached to GitHub Release

- [ ] Matching tag on `roadmap-pro-addon` repo with same `v<the version being tagged, e.g. 1.8.1>`


## 12 — Post-tag checks (first push)

- [ ] CI on the tag is green (PHPUnit matrix, PHPStan, WPCS, Lint)
- [ ] Release branch merged back to `main` (fast-forward or merge commit per repo convention)
- [ ] `main` branch protection rules intact (no accidental direct push)

## 13 — Customer-facing publish

Only once sections 0–12 are ticked:

- [ ] Wbcom store product page updated with new version + changelog
- [ ] Docs website synced (via `mcp__wbcom-docs__publish_product_docs`, `sync_to_live: true`)
- [ ] Customer update email drafted (with the real changelog, not marketing fluff) — optional per release
- [ ] Internal Slack post to `#releases` with zip link + changelog link + smoke report link

## 14 — Post-release monitor (first 24h)

- [ ] `wp-content/debug.log` on your own production site clean of new warnings/notices/fatals
- [ ] Zoho Desk / Crisp — no "broke after update" tickets in first 24h
- [ ] Basecamp Bugs column — no new cards matching the release
- [ ] Analytics / activity signal continues (no "zero events" sign of breakage)

If any post-release signal is red → open a `<the version being tagged, e.g. 1.8.1>.1` patch cycle immediately.

---

## Failure protocol

If ANY row in sections 0–11 fails:

1. **Stop.** Do not tag or publish.
2. Fix in the release branch.
3. Re-run from Section 0 (branch hygiene) — a fix can regress earlier sections.
4. Only tag after the entire checklist is green in one continuous run.

## Emergency patch

For a genuinely emergency patch (security CVE, dataloss bug reaching production):

- The `--skip-browser-smoke` flag on `build-release.sh` is allowed
- But `QA_RELEASE_CHECKLIST.md` sections 0–6 and 8–11 are still non-negotiable
- Document the skipped browser smoke in the release notes with a reason

## Version-specific additions

Append a section below for every release with the specific extra checks added that cycle. After 2 clean releases of a row, graduate it into the main checklist above.
