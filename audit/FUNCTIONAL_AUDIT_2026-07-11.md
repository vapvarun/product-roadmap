# Product Roadmap (Free + Pro) — Functional Audit @ 1000-Site-Owner Expectation

**Date:** 2026-07-11 · **Versions:** free `product-roadmap` 1.6.0 + pro `roadmap-pro-addon` 1.6.0 (both freshly pulled to origin/main; audited on branch `1.7.0`).
**Method:** code trace (both plugins) + live site `http://roadmap.local` seeded to **5 products, 1,011 items, 4,105 votes** + REST timing + browser render at scale. Covers: what exists, what works, what's broken, the multi-product flow, and gaps a scaled owner will hit.

> Companion docs: `AUDIT_SUMMARY.md` (bug baseline + contract), scratchpad traces `cap-inventory.md`, `multiproduct-flow.md`, `scale-audit.md`.

---

## 1. Executive summary

The **free plugin is solid** — the public board/list/cards views render correctly, the hot read path (`get_items`) is textbook (paginated, cache-primed, batched vote lookup), and voting works. **Most Pro value-adds work** (webhooks, email templates, digest, timeline UI, export, merge, custom fields, user profiles, guest suggestions).

But three classes of defect will bite a scaled owner:
1. **Pro Analytics is dead on arrival** — the flagship "insights" feature records nothing (hook-name drift + a second REST param-name mismatch). Every chart renders empty.
2. **The multi-product REST layer drifted** — Pro's `/timeline` and `/export` product scoping, `/products`, and advanced server filters silently return empty/unfiltered because they were wired to a **disabled** product-CPT model while the live model is a taxonomy.
3. **Frontend has no pagination UI** — a product with >100 items shows only the first 100 with no "load more"/pager and no "100 of 200" hint. Items 101+ are unreachable in the UI (the REST supports paging; the Vue board just never asks).

Plus portfolio hygiene: no `uninstall.php`, `load_plugin_textdomain()` never called (untranslatable), no spam defense, and Pro's `readme.txt` Stable tag (1.5.1) ≠ header (1.6.0) — a release blocker.

---

## 2. What we have (capability matrix)

Legend: ✅ works · 🅿️ works, Pro-only · ⚠️ partial · ❌ broken (code exists, doesn't function) · — missing

| Concern | Capability | State | Evidence |
|---|---|---|---|
| **Collect** | Logged-in suggestions (moderated, pending by default) | ✅ | free api.php:107/626/630 |
| | Guest suggestions + rate limit | 🅿️ | pro guest-suggestions.php:192/337 |
| | Feedback widget | 🅿️ | pro feedback-widget.php |
| | Spam defense (Akismet/honeypot/captcha) | — | none |
| **Vote** | Upvote, guest vote (IP dedup), denormalized totals | ✅ | free voting.php:19, api.php:467/609 |
| | Drag-drop reprioritize | 🅿️ | pro api.php:79 `/items/reorder` |
| | Per-user vote budget / downvote | — | none |
| **Display** | Status board / list / cards / minimal views | ✅ | free cpt.php:157 + Vue |
| | Client filter/sort | ✅ | RoadmapFilters.vue |
| | Search | ⚠️ | client-side only, current page; no REST search param |
| | Timeline view | 🅿️ | pro timeline.php |
| | **Advanced server filters** (priority/progress/assignee/date) | ❌ | pro filters.php:23 — dead hook |
| **Multi-product** | Multiple products, per-product board, switch/filter | 🅿️ ⚠️ | taxonomy-based; see §4 |
| | Per-product / per-board access control | — | all boards public |
| **Changelog** | CPT + shortcode + REST, per-product | ✅ | free changelog.php:227 |
| **Comments** | Comments + admin email notifications | ✅ | free api.php:1076, notifications.php:19 |
| | Pro email templates + digest | 🅿️ | pro email-templates.php, digest.php (correct hooks) |
| **Admin** | Settings, setup wizard, demo import | ✅ | free settings.php, setup-wizard.php |
| | Uninstall cleanup | — | no uninstall.php in either plugin |
| | Settings validation | ⚠️ | direct `$_POST`→update_option (pro settings.php:1362) |
| **Pro insight** | **Analytics (vote/view/status/comment)** | ❌ | pro analytics.php:34-37 + :83 (see §3) |
| | Webhooks | 🅿️ ✅ | pro webhooks.php:52 (correct hooks) |
| | CSV export / import, item merge, custom fields, user profiles, license | 🅿️ | one class each |
| | Export formats beyond CSV | — | CSV only despite `format` param |
| **Dev** | REST (13 free / 27 pro), 2 free + 1 pro shortcodes | ✅ | api.php |
| | Gutenberg display block | 🅿️ | pro blocks.php:49 (no block.json); free has none |
| | i18n | ❌ | no `load_plugin_textdomain()` in either |
| | GDPR export/erase | — | stores voter IPs + PII, no hooks |
| | API pagination hygiene | ⚠️ | no max `per_page` clamp, no total/count headers |

---

## 3. What's NOT working (verified broken)

### 3.1 Pro Analytics — records nothing (flagship feature, doubly broken)
`class-roadmap-pro-analytics.php` wires four trackers to hooks **Free never fires**:
| Listener (analytics.php) | Waits for | Free actually fires |
|---|---|---|
| :34 `track_vote` | `roadmap_item_voted` | `roadmap_vote_submitted` (api.php:598) |
| :36 `track_status_change` | `roadmap_item_status_changed` | `roadmap_status_changed` (notifications.php:234) |
| :37 `track_view` | `roadmap_item_viewed` | *(never fired anywhere)* |
| — comment | `roadmap_item_comment_added` | `roadmap_comment_submitted` |

And the **REST fallback is also broken**: `analytics.php:83` reads `event`/`item_id`/`metadata` but `roadmap-pro.js:153` posts `event_type`/`data`. So `/analytics/track` writes garbage, and only `item_reordered` is ever sent. **Every analytics dashboard/chart renders but reads an empty events table.** Confirmed: `/analytics/overview` returns 401 for anon and empty data when authed.

### 3.2 Multi-product REST scoping — silent empty results (see §4)
`_roadmap_product_id` (numeric CPT id) is read by Pro REST `/timeline`, `/export`, `/products` (pro-api.php:311/344/541/662) but **written by nothing**, and resolved against the **disabled** product CPT. On a multi-product site these return empty/unscoped.

### 3.3 Advanced server filters — dead hook
Pro registers priority/progress/assignee/date filters on `roadmap_api_query_args` (filters.php:23) — Free never fires that filter. Advanced filtering silently no-ops.

### 3.4 Frontend pagination — items 101+ unreachable
Live test: product `alpha-app` has 200 items; board renders exactly **100** (`roadmap-minimal-item-meta` ×100) with **no pager, no load-more, no "100 of 200"**. The REST supports paging (`per_page=200`→200, `page=2` works, body has `total:200`) — the **Vue board never requests page 2 or exposes a control.** Default `per_page=100`.

### 3.5 `roadmap_product` CPT not registered
`register_post_type('roadmap_product')` collides with the same-named taxonomy and **does not take effect** (runtime: `post_type_exists('roadmap_product') === false`; taxonomy is registered). Any code/UI expecting the product CPT is dead.

---

## 4. Multi-product roadmap flow (end-to-end)

```
Create product ──▶ Assign item ──▶ Display board ──▶ Switch/filter ──▶ Changelog
 (taxonomy term)   (taxonomy)       (shortcode→Vue    (product param)   (per product)
                                     →/items?product=)
```

| Step | State | Detail |
|---|---|---|
| 1. Create product | ⚠️ | Works as bare `roadmap_product` **taxonomy** terms. Pro's product module (color/icon/visibility, `products.php:19-22`) is **commented out** → those settings are dead. |
| 2. Assign item→product | ✅ | Both free item-create (api.php:788) and Pro meta box (`tax_input[roadmap_product][]`, products.php:100) write the **taxonomy** — they agree. |
| 3. Display a product board | ✅ | `[roadmap product="slug"]` → Vue → `/items?product=slug` (mandatory param) → `tax_query`. Renders. Verified live (main + alpha-app). |
| 4. Switch / filter | ⚠️ | Product switch works (taxonomy). **Advanced filters dead** (§3.3). Pro timeline/export product scoping broken (§3.2). |
| 5. Changelog per product | ✅ | Rides the live `roadmap_api_changelog_args` hook + taxonomy. |

**Root cause of the drift:** three competing item→product keys —
- **A. taxonomy `roadmap_product` (slug)** = canonical, used by everything that works;
- **B. meta `_roadmap_product` (slug)** = legacy, migrated into A (migration.php:25/61) — reconciled;
- **C. meta `_roadmap_product_id` (CPT post id)** = **dead**, read only by Pro REST, written by nothing, resolved against the disabled CPT.
The Free CPT, Pro meta box, Pro filters and Pro changelog all correctly share taxonomy **A**. **Only the Pro REST API layer drifted onto model C** — that's precisely where multi-product silently breaks.

**Design constraint:** `/items` **requires** a `product` param — there is **no "all products" aggregate view** via the API. A scaled owner wanting a global board across products cannot get one today.

---

## 5. Scale / 1000-site-owner readiness

Seeded and measured. The **free hot path is safe**; the **Pro aggregation/export paths are not.**

### ✅ Already correct (do not regress)
- Free `get_items` (api.php:304-390): paginated, primes postmeta+term cache, **batches all user votes in one `IN()`** (api.php:488-528). `/items?product=alpha-app` (200 items) = **0.069s**.
- Vote totals denormalized to `_roadmap_votes` meta, updated on write (api.php:609) — O(1) reads.
- User-profiles voted-items: paginated single JOIN.

### ❌ Breaks at scale (ranked)
| # | Sev | Issue | Location | At 2000+ |
|---|---|---|---|---|
| 1 | CRIT | CSV export `posts_per_page=-1` + `wp_count_comments()` per post + 2× term fetch/post | export.php:124,196-233 | ~8-10k queries + OOM |
| 2 | CRIT | Status chart: `WP_Query(-1)` once **per status term** to COUNT | analytics.php:603-620,685-710 | full scans × N statuses |
| 3 | HIGH | **`wp_roadmap_votes` has no `created_at` index** → every date-window vote query full-scans | schema product-roadmap.php:216; analytics.php:658; digest.php:139/149 | full scan at 100k votes |
| 4 | HIGH | `get_progress_data` loads all items + per-item meta to average one number | analytics.php:108-142 | replace with one AVG SQL |
| 5 | HIGH | Timeline REST + export REST unbounded (`-1`, no paging) | pro-api.php:326-337, 527-535 | OOM |
| 6 | HIGH | Analytics aggregations uncached (only overview is transient-cached) | analytics.php trends/charts/popular | recompute every request |
| 7 | MED | `get_product_item_count(-1)` **inside** products loop(`-1`) | pro-api.php:308-317,247-258 | 500 products = 500 scans |
| 8 | MED | Frontend renders all (≤100) items, no pager (§3.4) | Vue board | heavy DOM, hidden items |
| 9 | MED | Merge vote migration N+1 (SELECT per source vote) | merge.php:298-357 | slow merges |
| 10 | MED | Sync webhooks + WP-Cron delivery (no Action Scheduler) | webhooks.php, digest cron | blocks requests / missed runs |

---

## 6. Gaps a scaled owner will expect (missing entirely)

1. **Spam defense** — no Akismet/honeypot/captcha; only guest-suggest is rate-limited (logged-in submit + all voting unthrottled).
2. **Private / internal roadmaps** — every board is public; no visibility control.
3. **Per-product / per-board access control** — no role/segment/SSO gating.
4. **Global "all products" view** — API forces a single product (§4).
5. **Server-side search** — `/items` has no search param; UI search is client-side/page-only.
6. **API pagination hygiene** — no `per_page` max clamp (unbounded query risk), no `X-WP-Total` headers.
7. **GDPR export/erase** — stores voter IPs + PII, no privacy hooks.
8. **Uninstall cleanup** — no `uninstall.php`; custom table(s) + options orphaned on delete.
9. **i18n** — `load_plugin_textdomain()` never called → untranslatable.
10. **Bulk admin actions** — no bulk status/assign/merge/delete; triage at 2000 items impractical (merge is one pair at a time).
11. **Export formats** — CSV only despite a `format` param.
12. **Analytics that actually work** (§3.1).

---

## 7. Prioritized fix plan (suggested for 1.7.0)

**P0 — functional correctness (ship-blockers for a "1.6/1.7 is done" claim)**
1. Fix the Free↔Pro hook contract: align analytics/comment listeners to the fired names (or fire the missing events, incl. `roadmap_item_viewed`), and fire `roadmap_api_query_args`/`roadmap_api_settings` so Pro filters apply. *(paired Free+Pro change)*
2. Fix `/analytics/track` param names (`event_type`/`data` ↔ `event`/`item_id`/`metadata`).
3. Fix multi-product REST scoping: retire `_roadmap_product_id`/product-CPT path, route Pro `/timeline` `/export` `/products` through the **taxonomy** (model A).
4. Pro `readme.txt` Stable tag → 1.6.0 (then bump both to 1.7.0).

**P1 — scale (1000-site-owner)**
5. Add `KEY created_at` + `KEY item_created(item_id,created_at)` to `wp_roadmap_votes` (versioned dbDelta).
6. Batch/stream CSV export; grouped COUNT for status chart; AVG SQL for progress; cache analytics with write-invalidation; paginate timeline/export REST.
7. Frontend: expose pagination / load-more on the board (API already supports it); clamp `per_page`, emit total headers.

**P2 — hygiene / trust**
8. `uninstall.php` (both); `load_plugin_textdomain()` (both); nonce+capability + sanitize on the direct `$_POST` settings paths; a11y (labels, focus rings); replace `alert()/confirm()`.
9. `phpcbf` pass to clear the ~4,900 (free) / ~14,300 (pro) cosmetic PHPCS errors.

**P3 — product gaps (roadmap items)**
10. Spam defense, private boards, per-product access control, global view, server search, GDPR, bulk actions, Action-Scheduler-backed webhooks/digest.

---

*Live audit state left on the site: Pro activated, license valid, 5 seeded products + 1,000 seeded items (meta `_seeded=1`) + 4,100 votes, plus a "Scale Test Alpha" page. Run `wp post delete $(wp post list --post_type=roadmap_item --meta_key=_seeded --meta_value=1 --format=ids) --force` (and the product terms/page) to tear down.*
