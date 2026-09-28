# Agent Smoke Runbook - Product Roadmap

**Audience:** a browser-capable agent (Claude Sonnet or equivalent) with Playwright MCP + WP-CLI Bash access, OR a human QA person with the same access. Both should be able to execute every step of this runbook.

## How to read this runbook

Each C and E step describes a **customer contract**: what the feature promises, why it matters, the surfaces it touches, and what "working" looks like in customer terms. It does NOT prescribe the exact Playwright calls, selectors, REST paths, or DB queries. Read the relevant plugin code, pick the right mechanism, and verify the contract.

D (regression guards) stays specific - those are repros of past incidents; the exact fixture IS the contract.

## Global preconditions

- Working directory: `wp-content/plugins/product-roadmap`
- Site URL: `http://roadmap.local`
- WP-CLI: `wp --path="<3 levels up from this plugin dir>" <cmd>`
- Admin auto-login: `?autologin=1` (or `?autologin=<login>` for a specific persona — see `docs/qa/qa-config.json` `personas[]`)
- Playwright: one Chromium session throughout; restart with `browser_close` + `browser_navigate` if it dies.
- Plugin version constant: `ROADMAP_VERSION`
- Front-end routes (no single base slug — six standalone board views): `/board-view/`, `/poll-roadmap/`, `/timeline-view/`, `/list-view/`, `/cards-view/`, `/minimal-view/`
- Admin pages: `wp-admin/admin.php?page=roadmap-settings`, `wp-admin/admin.php?page=roadmap-dashboard`
- Pair plugin: `roadmap-pro-addon`

## Output contract

At the end of the walk, write exactly one JSON file to
`docs/qa/.last-smoke-pass.json` (this is Free's own report path; Pro's `qa-config.json` points its `report_path` here too — one combo-mode walk covers both):

```json
{
  "mode": "free|combo",
  "release_version": "<from ROADMAP_VERSION>",
  "pro_version": "<from ROADMAP_PRO_VERSION, combo mode only>",
  "ran_at": "<ISO 8601 UTC>",
  "sections": {
    "A_fresh_install":     { "pass": N, "fail": N, "skipped": N },
    "B_upgrade":           { "pass": N, "fail": N, "skipped": N },
    "C_core_flows":        { "pass": N, "fail": N, "skipped": N },
    "D_regression_guards": { "pass": N, "fail": N, "skipped": N },
    "E_pro_smoke":         { "pass": N, "fail": N, "skipped": N },
    "E2_environment":      { "pass": N, "fail": N, "skipped": N },
    "F_cross_browser":     { "pass": N, "fail": N, "skipped": N }
  },
  "failures": [
    { "id": "...", "origin": "from|for", "triage_note": "...", "expected": "...", "actual": "...", "url": "...", "screenshot": "..." }
  ],
  "debug_log_issues": [
    { "section": "...", "level": "fatal|warning|notice|deprecated", "line": "...", "file": "..." }
  ],
  "manual_required": [],
  "hard_fails":   [ { "id": "...", "invariant": "no-fatal|save-persists|capability-blocks|privacy-holds|no-data-loss|promise:<id>", "actual": "..." } ],
  "untested":     [ { "id": "...", "reason": "auth wall|missing seed|unreachable role|env: <what>", "needs": "..." } ],
  "coverage":     { "<axis>": "run | na: <reason> | deferred: <reason>" },
  "layer1_delta": []
}
```

**Every authored row you did not run goes in `untested` with a reason.**

Emit a Basecamp draft per failure using project `43291050`.

## Fixture cleanup (before every walk)

```bash
wp eval '
global $wpdb;
$ids = get_posts(["post_type" => ["roadmap_item", "roadmap_changelog"], "s" => "QA-", "posts_per_page" => -1, "fields" => "ids"]);
foreach ($ids as $id) { wp_delete_post($id, true); }
$wpdb->query("DELETE FROM {$wpdb->prefix}roadmap_votes WHERE item_id NOT IN (SELECT ID FROM {$wpdb->posts})");
echo count($ids) . " QA- fixtures cleaned\n";
'
```

## Debug log protocol

Enable WP_DEBUG + WP_DEBUG_LOG + WP_DEBUG_DISPLAY=false before Section A. Baseline `wp-content/debug.log` byte count. After every section, diff new lines into `debug_log_issues[]` classified by level. Any new fatal or warning is a failure unless explicitly whitelisted.

---

## A - Fresh install

### A1 - Activation and first-request routing
**What to verify:** after a clean activation, `/board-view/` and the other five front-end views return HTTP 200 without a manual Permalinks re-save.
**Acceptance:** 200 on all six routes; rewrite rules contain `roadmap_item`/`board-view` patterns.

### A2 - Database schema is in place
**What to verify:** `wp_roadmap_votes` table exists; `roadmap_version` option matches `ROADMAP_VERSION`.

### A3 - Pro pairs cleanly
**What to verify:** activating `roadmap-pro-addon` on top of `product-roadmap` does not fatal; Pro-only tables are created; `ROADMAP_VERSION` and `ROADMAP_PRO_VERSION` agree (lockstep, see card 10344548089's version-compat notices).

---

## B - Upgrade from previous version

### B1 - Migration is silent, data is intact
**What to verify:** upgrading from the prior stable tag completes with no debug.log entries during the activation request; existing roadmap items, votes, and changelog entries still render; vote counts stay accurate (see D.demo-votes below — this is the exact regression that card F-05 covered).

---

## C - Core customer flows

Persona ladder: Anonymous > Member (qa_member_owner / qa_member_other) > Editor (qa_elevated, moderates suggestions) > Admin (qa_admin). Exercise both desktop 1440px and mobile 390px where the UI differs. Check both light and dark (BuddyX's own toggle).

### C.anon.home
**What to verify:** `/board-view/` renders for a logged-out visitor with real roadmap items, status columns, and a clear voting/suggest entry point.

### C.anon.primary-views
**What to verify:** all six board views (`/board-view/`, `/poll-roadmap/`, `/timeline-view/`, `/list-view/`, `/cards-view/`, `/minimal-view/`) render for an anonymous visitor without fatal; each reflects the same underlying items consistently.

### C.member.create-flow
**What to verify:** a logged-in member can submit a suggestion end-to-end: the item is persisted as `roadmap_item` in a "suggested"/pending state, appears in the relevant view, and (if enabled) notifies admins.

### C.member.edit-and-delete
**What to verify:** a member can edit/delete their own suggestion while it's still pending; cannot edit another member's.

### C.member.interactions
**What to verify:** voting (up/down or +1, whichever the config uses) and commenting work from the member's side and the count/comment reflects immediately on every board view showing that item. Guest voting (both guest-voting-on and guest-voting-off) per card 10344407815.

### C.member.mobile
**What to verify:** board views, voting, and the suggestion form are usable at 390px: touch targets reachable, no horizontal overflow.

### C.mod.queue-and-actions
**What to verify:** an editor sees pending suggestions and can Approve / Reject / Start Over (per card 10344407804's dialog-free flow); each action updates the item status and propagates to every board view. No native browser `alert()`/`confirm()` fires anywhere in this flow.

### C.admin.plugin-pages
**What to verify:** Settings, Dashboard, Analytics, and the Setup Wizard (first-run only, per card 10336345758) all render without PHP Notice/Warning/Fatal; every tab loads; AJAX actions (Clear Cache, etc.) return expected JSON with no native dialogs.

### C.admin.crud
**What to verify:** products, statuses, and types (the `roadmap_product`/status/type taxonomy terms) can be created, edited, reordered, deleted. Settings edits MERGE with existing values — editing one key never drops others (this is the exact shape of card 10092400992, legacy options vs global settings).

### C.admin.user-management
**Not applicable** — the plugin has no per-user roles/trust-level admin surface beyond WP's own capabilities. Mark `coverage.user_management: "na: no per-user admin surface"`.

### C.notifications
**What to verify:** status-change notifications reach the item's author (check Mailpit at the site's local SMTP port). Per-type toggles in Settings are respected.

### C.cron
**What to verify:** any scheduled recount/maintenance cron event registered by Free is present after activation and absent after deactivation (`wp cron event list`).

---

## D - Known-regression guards

Seeded from the 1.8.0 Ready-for-Testing QA pass (2026-09-27, Basecamp project 43291050). Each row is a bug that was verified fixed at least once — re-verify on every future release, not just this one.

| ID | Bug (card) | Fixture + assertion |
|----|-----|---------------------|
| D.1 | Old Free + new Pro white-screened the site (10344548089) | Install Free 1.7.x with Pro 1.8.0 → site returns 200, red "Pro features are paused" notice, no fatal. Update Free to 1.8.0 → notice clears, Pro resumes with no reactivation. Free 1.8.0 + old Pro → yellow "update Pro" notice, board still works. Free installed under a renamed folder → Pro stays active (no folder-name auto-deactivate). |
| D.2 | Merge Duplicates never ran from the edit screen (10344503930) | Two items in the same product → Merge Into Selected in block AND classic editor → source trashed, votes/comments moved to target, target findable via search past the old 200-item alphabetical cap. |
| D.3 | Board block/shortcode options didn't reach the board (10344405387) | Change every block sidebar option → board updates live in the editor preview; Copy shortcode output renders an identical board. |
| D.4 | Multi-product editing let one item straddle products (10344405404) | Quick Edit has no product checklist; bulk "Move to product" works; CSV import with a bad row is rejected with a listed reason, not silently dropped. |
| D.5 | Colour/dark-mode surfaces were unreadable or inconsistent (10344405416) | Toggle dark mode on every board + admin surface → all readable; a given status keeps the same colour across every view; a product's Theme Color reaches every accent that should carry it. |
| D.6 | Board pointed at a deleted/unknown product fataled or blanked (10343990559) | Point a board shortcode/block at a nonexistent product id → visitors see a working (empty) board, not a fatal or blank page; editors see a config-error notice. |
| D.7 | Board "delete" was destructive with no undo (10344503945) | Manage Mode delete a board feature → item moves to Trash (verify in DB, not just the UI); Restore returns it exactly as it was; message reads "Feature moved to the trash." |
| D.8 | A failed suggestion submit erased what the visitor typed (10343990481) | Submit a suggestion fast enough to hit the rate limit → typed title/description remain in the form after the failed submit. |
| D.9 | A visitor's local sort silently overrode the owner's configured sort (10092398618) | Owner sets a board's sort order → List and IdeaBox views start on the owner's sort with a single sort control; Title (A-Z) works when picked. |
| D.10 | Demo item vote counts were fake numbers, not real votes (10092395732) | Cast a real vote on a demo item, then run the recount/update routine → count reflects the real vote (+1) and is otherwise unchanged by the recount. |
| D.11 | Admin actions used native `alert()`/`confirm()` dialogs (10344407804) | Walk Copy shortcode, product save, Clear Cache, Reject, Start Over, Merge, board delete as admin → zero native browser dialogs; all confirmation/feedback is in-page. |
| D.12 | The single item page computed its own vote state, disagreeing with the board (10344407815) | Vote as a member and as a guest (guest voting on AND off) → the single item page shows the same voted state the board shows, for both. |
| D.13 | **OPEN — not yet fixed.** Block-editor changelog publish under a non-default Pro product still writes a `general-` slug prefix instead of `{product}-v{version}` (Bugs card 10326920444, root cause: `class-roadmap-changelog.php:990-1005`, product term not attached when the slug-derivation filter runs) | Publish a changelog entry in the block editor under a non-default product → slug MUST be `{product}-v{version}`, not `general-...`. **Do not mark this row pass until 10326920444 ships a fix** — leave it in `failures[]`/`hard_fails[]` until then, don't silently skip it. |
| D.14 | Setup Wizard showed unrelated admin notices during first-run setup (10336345758) | Fresh install, open the Setup Wizard → wizard screen shows no admin notices; the Dashboard still shows the normal welcome notice (suppression is scoped to the wizard only). |
| D.15 | Legacy option keys and the new global Settings screen could disagree (10092400992) | Change a setting via the current Settings screen → the board, notification emails, and the digest all read the same value Settings just saved (no stale legacy-key read anywhere in that chain). |
| D.16 | Success/error toast text failed contrast in one colour mode (10343990514) | Trigger a success and an error toast in both light and dark → both meet AA text contrast in both modes. |
| D.17 | Pro shipped with no `.pot` translation template (10344503957) | After a build, `languages/roadmap-pro-addon.pot` exists and contains current (not stale) strings. |
| D.18 | Dead `timeline.js` (18 KB) loaded on every board page regardless of settings (10344436734) | Load any board page with "Enable timeline view" OFF → `timeline.js` is not requested; turning the setting off hides Timeline everywhere it could appear. |
| D.19 | Dead code: `RoadmapChangelog.vue` left in the tree after its replacement shipped (10344482714) | Grep both repos for `RoadmapChangelog.vue` → zero references; the file itself does not exist. |

Rule: every customer-visible fix adds a D row in the same PR. After 2 clean releases, a D row graduates into C.

---

## E2 - Environment (report key `E2_environment`)

| Row | Walk | Pass bar |
|---|---|---|
| E2.no-host | Deactivate any BuddyPress/BuddyX-specific integration hooks; load every board view | No fatal, fallback styling renders |
| E2.block-theme | Activate Twenty Twenty-Five; visit every board view + admin page | Site chrome present; board renders inside the theme, no `[hidden]` bleed |
| E2.no-reset-theme | Activate a theme with no CSS reset (e.g. a default block theme); walk C.member rows | Nothing meant to be hidden is visible; plugin CSS is self-scoped |
| E2.php-latest | Run C rows with `WP_DEBUG` on the newest supported PHP | Zero *from-origin* deprecations |
| E2.cache | Page cache + object cache on; submit a vote past nonce life | Write succeeds or fails honestly, no silent 403 |
| E2.multisite | Network-activate on a multisite scratch | Activation creates per-site artefacts, no cross-site data leak |
| E2.stack | Activate one companion at a time (BuddyX, BuddyX Pro, Reign) | No fatal, no layout break, our assets still load |
| E2.textdomain | `WP_DEBUG` on WP 6.7+ | No `_load_textdomain_just_in_time` notice |

---

## F - Cross-browser, RTL, accessibility

### F.chromium
Already covered by Sections A-E.

### F.firefox-desktop and F.safari-ios
Chromium-only MCP cannot walk these. Populate `manual_required[]`.

### F.rtl
**What to verify:** board views render right-to-left without overflow on an RTL locale.

### F.a11y
**What to verify:** voting controls, suggestion form fields, and moderation actions have visible focus rings and `aria-label`s where icon-only.

---

## G - Post-release monitoring (first 24h)

Runs on the production host. Watch for new debug.log entries, orphaned cron events, and support tickets.

---

## Failure protocol

1. Screenshot on every failure: `browser_take_screenshot({ filename: "fail-<id>.png" })`.
2. **Triage: from vs for our plugin.**
3. Record in `failures[]` with `{ id, origin, triage_note, expected, actual, url, screenshot }`.
4. Never halt. Collect all failures in one pass.
5. Emit a Basecamp draft per failure with the origin line populated.

## Step ID format

`<Section>.<persona>.<feature>` e.g. `C.member.create-flow`. D rows: `D.<descriptor>`. E rows: `E.<extension>`.
