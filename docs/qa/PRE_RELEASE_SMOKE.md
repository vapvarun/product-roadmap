# Product Roadmap — Pre-Release Smoke Checklist

> **Run this before every tagged release. Every row must pass.**
> Any failure → file a Basecamp card in Bugs and **halt the release**.
> Target time: 90 minutes end-to-end.

**Matrix:** 3 personas × 3 browsers × 2 viewports × 2 theme modes.

- Personas: Anonymous visitor, Member (qa_member_owner), Admin (qa_admin)
- Browsers: Chrome Desktop, Firefox Desktop, Safari iOS (sim or real)
- Viewports: 1440px desktop, 390px mobile
- Theme modes: Light, Dark (BuddyX's own toggle)

**Environment:**
- Clean Local site with `product-roadmap` **already on the previous stable version** for the upgrade test
- A second clean Local site for the fresh-install test
- Access to `wp-content/debug.log` and DevTools Network tab
- Mailpit open for email rows (this site's Mailpit web UI: check `lsof -iTCP -sTCP:LISTEN | grep mailpit` next to the SMTP port from `wp eval 'echo ini_get("sendmail_path");'`)

---

## A — Fresh install (10 min)

- [ ] Activate `product-roadmap` → no fatal, no PHP warning in `debug.log`
- [ ] `wp db tables --all-tables | grep wp_roadmap_votes` — table created
- [ ] `wp option get roadmap_version` equals `ROADMAP_VERSION`
- [ ] `/board-view/` loads as the very first request after activation → HTTP 200 (no manual Permalinks re-save needed)
- [ ] Deactivate → reactivate → no duplicate tables, no re-run migrations
- [ ] Activate `roadmap-pro-addon` → no fatal, Pro tables created, both version constants agree

## B — Upgrade from previous release (5 min)

- [ ] Drop the new zip → update via WP → no fatal
- [ ] `wp option get roadmap_version` updates to new constant value
- [ ] Pre-existing roadmap items, votes, and changelog entries still render correctly
- [ ] Demo/seed vote counts survive the upgrade unchanged (card F-05 regression — see D below)
- [ ] No new warnings in debug.log

## C — Core user flows (25 min)

### C1 — Anonymous visitor
- [ ] `/board-view/` and the other 5 views render, no console errors
- [ ] Voting and suggestion form correctly gate or allow guests per the Settings toggle
- [ ] Auth-gated actions redirect to login (never silent 403)

### C2 — Member (qa_member_owner)
- [ ] Log in via `?autologin=qa_member_owner`
- [ ] Submit a suggestion → item created, Network 2xx, appears on the board
- [ ] Vote + comment on an item → count/comment updates on every board view showing it
- [ ] Mobile 390px: every flow still usable, no horizontal overflow

### C3 — Admin (qa_admin)
- [ ] Settings, Dashboard, Analytics admin pages render without PHP warnings
- [ ] Settings save flow: change one setting, save, reload — persists, and untouched settings are unchanged (merge, not overwrite)
- [ ] Product/status/type taxonomy CRUD works
- [ ] Merge Duplicates (Pro) works from both block and classic editors on a 1000+-item product

### C4 — Moderator (qa_elevated, editor)
- [ ] Suggestion queue renders
- [ ] Approve / Reject / Start Over fire without AJAX errors and with **no native browser dialog**
- [ ] A rejected/approved status change is visible immediately on every board view

## D — Known-regression guards (15 min)

Seeded from the 1.8.0 Ready-for-Testing QA pass (Basecamp project 43291050) — every PASS verdict from that run is a candidate row here. Coordinator to backfill the specific card ids + repro fixtures before first use; placeholder rows below name the shape.

- [ ] (placeholder) High: old Free + new Pro white-screen (card 10344548089): install Pro 1.8.0 on Free 1.7.x → site stays up, red "Pro features paused" notice shows, no HTTP 500
- [ ] (placeholder) F-05 demo votes (card 10092395732): a real vote + a simulated plugin-update recount → stored vote count unchanged except for the real vote
- [ ] (placeholder) V-19 local sort (card 10092398618): board loads on the owner's configured sort, not a client-side override

**Rule:** every customer-visible fix that ships after this document adds a new row here in the same PR.

## E — Extensions / addons / premium features

Pro-only features — see `roadmap-pro-addon/docs/qa/AGENT_SMOKE_RUNBOOK.md` Section E for full contracts. At minimum:

- [ ] Merge Duplicates, CSV import, Analytics dashboard, Digest emails, Custom fields, Webhooks, User profiles, Feedback widget, Email templates — each has a working admin surface
- [ ] Each Pro feature's primary AJAX/REST endpoint returns 2xx to `qa_admin`
- [ ] License gate: invalid license disables Pro-only features cleanly, never fatals, never disables Free

## F — Cross-browser quick pass (10 min)

Run these 5 pages on **Chrome + Firefox + Safari iOS**:

1. `/board-view/` — landing page
2. `/list-view/` — primary content view
3. `/board-view/` (suggestion form) — creation form
4. `wp-admin/admin.php?page=roadmap-dashboard` — plugin admin page
5. `wp-admin/admin.php?page=roadmap-settings` — plugin settings

Expectations: no JS errors, no layout breaks, interactive elements work.

## G — Post-release verification (first 24h)

- [ ] `wp-content/debug.log` clean of new warnings/notices/fatals
- [ ] `wp cron event list | grep roadmap` — expected events scheduled, no orphans
- [ ] Zoho Desk / Slack #support — no "broke after update" tickets in first 24h
- [ ] Analytics dashboard continues showing activity (no "zero events" sign of breakage)

---

## Failure protocol

1. **Stop.** Do not merge the release branch.
2. File a Basecamp card in **Bugs** (project 43291050) with the failed row verbatim, environment, browser, user persona.
3. Fix + push to the release branch.
4. Re-walk the failed row AND the section that contains it.
5. Resume only after the failure is resolved.

## Version-specific additions

Append a section below for every release with the specific regression guards added that cycle. After 2 clean releases of a row → graduate it into the main flow.

### 1.8.0
First release on this scaffold. D rows pending coordinator backfill from the 19-card RFT QA pass (2026-09-27).
