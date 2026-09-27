# Product Roadmap (Free + Pro) — Surface Map

**Why this exists.** A behaviour here lives in several places at once (products are written by 9 code paths, status is styled in 7 components, settings are read 38 times). A fix that lands on the one place QA noticed leaves the others broken, and the same bug comes back. This map lists every place each behaviour lives.

**Rule (in `wp-plugin-development` and `wp-card-qa`):**
1. Before fixing, find the behaviour's row and read every surface in it.
2. Before moving a card to Ready for Testing, tick every surface in that row (fixed, or checked and unaffected), and say so on the card.
3. Adding a new surface (component, template, route, screen)? Add it to its rows in the same commit.

**Keeping it true.** Each row ends with the search that produced it. Re-run it from `wp-content/plugins/` and update the row if the files or lines changed. Built 2026-09-27 against product-roadmap `1471671` and roadmap-pro-addon `296f825` (branch 1.8.0). `F:` = product-roadmap, `P:` = roadmap-pro-addon.

The single-file companion for Pro is `roadmap-pro-addon/docs/qa/SURFACES.md`, which points here: one map for the pair, because every row crosses both plugins.

---

## Surface index

| Group | Surfaces |
|---|---|
| **Frontend board (Vue)** | `F:vue-app/src/App.vue` (shell, toasts, modals) and views `RoadmapBoard`, `RoadmapCards`, `RoadmapList`, `RoadmapTimeline`, `RoadmapMinimal`, `RoadmapIdeaBox`; item card `RoadmapItem`; modals `ItemDetailModal`, `ItemModal`, `CommentModal`, `SuggestionModal`; `RoadmapHeader`, `RoadmapFilters`, `RoadmapFooter`, `LoadingSpinner`. (`RoadmapChangelog.vue` is in the tree but never imported: dead code, see P3.) |
| **Frontend PHP templates** | `F:templates/archive-roadmap_item.php`, `single-roadmap_item.php`, `archive-roadmap_changelog.php`, `single-roadmap_changelog.php`, `archive-roadmap-product.php` |
| **Entry points to the board** | shortcode `[roadmap]` `F:includes/class-roadmap-shortcode.php:19`; shortcode `[roadmap_changelog]` `:20`; block `roadmap-pro/roadmap` `P:includes/class-roadmap-pro-blocks.php:49`; shortcode `[roadmap_user_profile]` `P:includes/class-roadmap-pro-user-profiles.php:35`; Pro feedback widget `P:includes/class-roadmap-pro-feedback-widget.php` + `P:assets/js/feedback-widget.js` |
| **Admin: editors** | block editor: Free "Roadmap Details" panel `F:assets/js/block-editor.js`, Pro single-product panel `P:assets/js/single-product-selector.js`; classic editor meta boxes `F:includes/class-roadmap-cpt.php`, `F:includes/class-roadmap-changelog.php`, Pro radio product box `P:includes/class-roadmap-pro-products.php:184` |
| **Admin: lists** | Items/Changelog list tables incl. **Quick Edit / Bulk Edit** (core, `roadmap_product` has `show_in_quick_edit=true`); Pro product column + filter `P:includes/class-roadmap-pro-products.php:19-27`; Pro changelog bulk product actions `P:includes/class-roadmap-pro-changelog.php:39,215` |
| **Admin: screens** | Roadmap menu `F:includes/class-roadmap-settings.php:41-110` (dashboard, settings, etc.); Changelog menu `F:includes/class-roadmap-changelog.php:67-96`; Setup Wizard `F:includes/class-roadmap-setup-wizard.php:91`; Pro Guest Suggestions `P:includes/class-roadmap-pro-guest-suggestions.php:111`; Pro Analytics `P:roadmap-pro-addon.php:662` |
| **REST** | Free `roadmap/v1` (12 routes) `F:includes/class-roadmap-api.php`; core `wp/v2/roadmap-items`, `wp/v2/changelogs`; Pro `roadmap/v1` (27 routes) in `class-roadmap-pro-api.php` (12), `-guest-suggestions` (4), `-merge` (3), `-webhooks` (3), `-changelog` (2), `-user-profiles` (2), `-custom-fields` (1) |
| **Data in/out** | Pro CSV import/export `P:includes/class-roadmap-pro-export.php`; Free demo import `F:includes/class-roadmap-demo-import.php`; Free migration `F:includes/class-roadmap-migration.php` |
| **Email** | Free status-change notifications `F:includes/class-roadmap-notifications.php`; Pro digest `P:includes/class-roadmap-pro-digest.php`; Pro email templates `P:includes/class-roadmap-pro-email-templates.php`; Pro guest-suggestion mails `P:includes/class-roadmap-pro-guest-suggestions.php:702,754` |

---

## Behaviours

### 1. Product assignment (writing an item's or changelog's product)

**Rule:** one product per item and changelog (card 10344377039); `roadmap_allow_multiple_products` filter restores multiple.

| Surface | Where | Enforces one product? |
|---|---|---|
| Block editor Products panel | `P:assets/js/single-product-selector.js:57` | yes (radio) |
| Classic editor Products box | `P:includes/class-roadmap-pro-products.php` `single_product_taxonomy_args()` + `render_single_product_meta_box()` | yes (radio) |
| REST `wp/v2/roadmap-items`, `wp/v2/changelogs` | `P:includes/class-roadmap-pro-products.php:34,35` (`rest_pre_insert_*`) | yes (400) |
| Free REST `POST /items` | `F:includes/class-roadmap-api.php:1092` (arg declared string) | yes |
| Free REST `POST /suggest` | `F:includes/class-roadmap-api.php:799` | yes (single string) |
| Pro guest suggestion | `P:includes/class-roadmap-pro-guest-suggestions.php:402` | yes |
| Pro changelog REST | `P:includes/class-roadmap-pro-changelog.php:252` | yes (single slug) |
| Default product on save | `F:includes/class-roadmap-cpt.php:744`, `F:includes/class-roadmap-changelog.php:681` | yes |
| Demo import, migration | `F:includes/class-roadmap-demo-import.php:258`, `F:includes/class-roadmap-migration.php:61` | yes |
| Quick Edit / Bulk Edit | core list table; `show_in_quick_edit => false` in single-product mode (`single_product_taxonomy_args()`) | yes (not offered; P2 10344405404) |
| Bulk "Move to product" (items + changelogs) | `P:includes/class-roadmap-pro-products.php` `add_move_to_product_actions()` / `handle_move_to_product()` | yes (replaces; per-item `edit_post` check) |
| Pro CSV import | `P:includes/class-roadmap-pro-export.php` `resolve_term()` before any insert/update | yes (unknown or comma value -> row rejected and listed; status and category resolved the same way) |

Re-verify: `wp_set_(object|post)_terms\([^;]*roadmap_product|tax_input\[roadmap_product\]|rest_pre_insert_roadmap|show_in_quick_edit|meta_box_cb|editor\.PostTaxonomyType`

### 2. Product scoping (showing only one product's items, product in URLs)

| Surface | Where |
|---|---|
| Board items query | `F:includes/class-roadmap-api.php:346` (product param), `P:includes/class-roadmap-pro-filters.php:20`, `P:includes/class-roadmap-pro-products.php:19` (`roadmap_api_filter_by_product`) |
| Item permalink `/roadmap/{product}/{item}/` | `F:includes/class-roadmap-cpt.php:853-855` (first product) |
| Changelog permalink / slug | `F:includes/class-roadmap-changelog.php:938-940` (permalink), `:989-998` (slug) |
| Shortcode product fallback | `F:includes/class-roadmap-shortcode.php:66-76` |
| Templates | `F:templates/single-roadmap_item.php:65`, `F:templates/single-roadmap_changelog.php:20`, `F:templates/archive-roadmap_changelog.php:170` |
| Pro API, settings, changelog | `P:includes/class-roadmap-pro-api.php:621`, `P:includes/class-roadmap-pro-changelog.php:503`, `P:includes/class-roadmap-pro-settings.php:644,1568` |

Known gap: changelog slug derivation in the block editor — card 10326920444.
Re-verify: `roadmap_api_filter_by_product|get_the_terms\([^)]*roadmap_product|wp_get_object_terms\([^)]*roadmap_product|product_terms\[0\]|products\[0\]`

### 3. Status display (chip / colour / column per status)

**Source of truth:** `F:assets/css/roadmap-tokens.css`. Any element with `roadmap-status-{slug}` gets `--rm-status` / `--rm-status-tint`, and the chip is `.roadmap-badge.roadmap-status-{slug}`. The solids and tints are the `--rm-{planned|progress|hold|review|done|cancelled}` tokens, with dark values in the same file. Unknown slugs get the neutral hold pair. No view keeps its own slug-to-colour map.

| Surface | Where | Uses the shared mapping? |
|---|---|---|
| Cards | `F:vue-app/src/components/RoadmapCards.vue` | yes |
| List | `F:vue-app/src/components/RoadmapList.vue` | yes |
| Item detail modal | `F:vue-app/src/components/ItemDetailModal.vue` | yes |
| Board columns | `F:vue-app/src/components/RoadmapBoard.vue` | n/a (the status is the plain column heading) |
| Item card | `F:vue-app/src/components/RoadmapItem.vue` | tokens; no status chip |
| Minimal (dots) | `F:vue-app/src/components/RoadmapMinimal.vue` | yes (`var(--rm-status)`) |
| Timeline (stripe + chip) | `F:vue-app/src/components/RoadmapTimeline.vue` | yes |
| IdeaBox tag | `F:vue-app/src/components/RoadmapIdeaBox.vue` | yes |
| Archive template pill | `F:templates/archive-roadmap_item.php` | yes |
| Single item page chip | `F:templates/single-roadmap_item.php` | yes (it used to read `_roadmap_status_color`, which nothing wrote) |
| Pro analytics doughnut | `P:includes/class-roadmap-pro-templates.php` | yes (it reads `--rm-status` through a probe element) |
| Pro legacy timeline bars | `P:assets/css/roadmap-pro.css` | yes, but the rule is unreachable (`.legacy-timeline` is never added; see the timeline.js card) |

Re-verify: `roadmap-status-|--rm-status|statusColors|getStatusStyle|_roadmap_status_color`. The only hit should be the tokens file and the class names in markup.

### 4. Priority display

**Source of truth:** `F:assets/css/roadmap-tokens.css`. `roadmap-priority-{low|medium|high|critical}` sets `--rm-priority-color` / `--rm-priority-tint`; the chip is `.roadmap-badge.roadmap-priority-{slug}`.

| Surface | Where | Shared? |
|---|---|---|
| Item card | `F:vue-app/src/components/RoadmapItem.vue` | yes |
| List | `F:vue-app/src/components/RoadmapList.vue` | yes |
| Item detail modal | `F:vue-app/src/components/ItemDetailModal.vue` | yes |
| Cards | `F:vue-app/src/components/RoadmapCards.vue` | yes |
| IdeaBox | `F:vue-app/src/components/RoadmapIdeaBox.vue` | yes |

Re-verify: `getPriorityClass|roadmap-badge-(success|warning|danger)|roadmap-priority-`. The `getPriorityClass` maps are gone; each level has its own token, so high and critical no longer share one colour.

### 5. Setup / configuration messages (what a visitor could see when something is misconfigured)

**Rule:** visitors always get a working board; setup messages only for people who can edit the page (card 10343990559).

| Surface | Where | Visitor-safe? |
|---|---|---|
| `[roadmap]` | `F:includes/class-roadmap-shortcode.php:66-76`, helper `editor_notice()` `:955-963` | yes |
| `[roadmap_changelog]` | `F:includes/class-roadmap-shortcode.php:187-192, 226` | yes |
| Block | `P:includes/class-roadmap-pro-blocks.php:210` (falls back to default product) | yes |
| Block editor placeholder | `P:blocks/roadmap-block/index.js:227` | editor only |
| Board app load error | `F:vue-app/src/App.vue:40` | shows on real API failure (correct) |
| Free REST `/items` without product | `F:includes/class-roadmap-api.php:327` | API clients only (correct) |
| Notice style | `F:vue-app/src/assets/css/main.scss:265` | tokens (since `1471671`) |

Re-verify: `editor_notice|roadmap-error|roadmap-block-error|Placeholder\b|missing_product`

### 6. User feedback: toasts, notices, dialogs

**Source of truth (frontend):** the board toast, `F:vue-app/src/App.vue:237` + `showNotification()`, styled on tokens `:789-840`. Only `success` and `error` are used.

| Surface | Where | Consistent? |
|---|---|---|
| Board toast | `F:vue-app/src/App.vue:237,484-590` | yes |
| Single-item page notice | `F:templates/single-roadmap_item.php:244` (own element) | separate implementation |
| Admin notices (PHP) | `F:product-roadmap.php:48`, `F:includes/class-roadmap-cpt.php:50,1020`, `F:includes/class-roadmap-settings.php:27`, `F:includes/class-roadmap-setup-wizard.php:178`, `F:includes/class-roadmap-demo-import.php:23`, `P:roadmap-pro-addon.php:56,210,1111`, `P:includes/class-roadmap-pro-export.php:24`, `P:includes/class-roadmap-pro-guest-suggestions.php:56`, `P:includes/class-roadmap-pro-merge.php:41` | WordPress standard |
| Admin JS messages | `F:assets/js/roadmap-admin-feedback.js` `roadmapAdminNotice()` (standard dismissible notice, `role=alert` for errors, `status` otherwise): used by `F:assets/js/roadmap-admin.js` (copy, product save) and `P:assets/js/admin.js` (clear cache) | yes |
| Confirmations | Admin: `data-roadmap-confirm` two-step (same file), used on Reject (`F:includes/class-roadmap-cpt.php`), Start Over (`F:includes/class-roadmap-setup-wizard.php`), Merge (`P:includes/class-roadmap-pro-merge.php`), Clear Cache (`P:includes/class-roadmap-pro-settings.php`). Board: two-step delete in `F:vue-app/src/components/RoadmapItem.vue` | yes; no browser `alert()` / `confirm()` anywhere (grep) |

Setup Wizard: no admin notices at all (`Roadmap_Setup_Wizard::suppress_admin_notices()` on its `load-` hook). That covers our welcome call to action and notices from the theme and other plugins; the wizard prints its own messages inline. The welcome notice still shows on the Dashboard.
Re-verify: `showNotification|roadmap-notification|admin_notices|alert\(|confirm\(`

### 7. Forms: submit and reset

**Rule:** keep what the person typed until the server confirms success.

| Surface | Where | Keeps input on failure? |
|---|---|---|
| Suggestion modal | `F:vue-app/src/components/SuggestionModal.vue` `handleSubmit()` (no reset; the modal unmounts on success) | yes |
| Comment modal | `F:vue-app/src/components/CommentModal.vue:51-53` | yes |
| Item modal (manage mode) | `F:vue-app/src/components/ItemModal.vue:225-230` | yes |
| Pro feedback widget | `P:assets/js/feedback-widget.js` (`form.reset()` only on a successful response) | yes |
| Single item comments | `F:templates/single-roadmap_item.php:231` (core `comments_template()`) | core behaviour |

Re-verify: `emit\(\s*'(submit|save)'|@submit|handleSubmit|onSubmit|addEventListener\(\s*'submit'`

### 8. Colours and dark mode

**Source of truth:** `F:assets/css/roadmap-tokens.css`, the only file with colour values, registered as the `roadmap-tokens` style handle in `F:product-roadmap.php` `init()`. Every frontend stylesheet depends on it.
- Tokens are declared on `:root`.
- Dark values sit on the theme's dark selectors plus `.roadmap-dark`, and follow the theme toggle only.
- The derived accent tokens are recomputed on `.roadmap-app`, `.roadmap-changelog*` and `.roadmap-fw`, so a per-product Theme Color, or the feedback-widget colour set on that root, reaches every shade.
- Guard: `F:bin/check-raw-colors.sh` and `P:bin/check-raw-colors.sh`, run in CI, fail on any raw colour outside the tokens file. The exception is a line marked `raw-color-ok`, used for owner option defaults.

| Surface | Stylesheet | Depends on `roadmap-tokens` |
|---|---|---|
| Board app, all views + modals | `assets/css/roadmap-app.css` (built from `vue-app/src`) | `F:includes/class-roadmap-shortcode.php` enqueue_assets |
| `[roadmap_changelog]` shortcode | inline `roadmap-changelog` handle | same file, enqueue_changelog_assets |
| Changelog single / archive, item archive | `assets/css/changelog-frontend.css` | `F:includes/class-roadmap-changelog.php`, `F:templates/archive-roadmap_item.php` |
| Single item page | `assets/css/roadmap-single.css` | `F:templates/single-roadmap_item.php` |
| Product archive | `assets/css/roadmap-archive.css` | `F:templates/archive-roadmap-product.php` |
| Pro board extras | `P:assets/css/roadmap-pro.css` | via `roadmap-vue-app` |
| Pro profile + analytics tab | `P:assets/css/roadmap-pro-frontend.css` | `P:class-roadmap-pro-user-profiles.php`, `P:class-roadmap-pro-templates.php` |
| Pro feedback widget | `P:assets/css/feedback-widget.css` | `P:class-roadmap-pro-feedback-widget.php` |

**Not covered:**
- Admin screens use the wp-admin colour scheme and are out of scope.
- Changelog type colours are owner data (term meta) and are output as saved.
- WordPress's own comment form on the single item page is styled by the theme.

Re-verify: `bash bin/check-raw-colors.sh`, then the contrast audit (every text node and control border at >= 4.5:1, or 3:1 for large text and borders) in light + dark on BuddyX and Twenty Twenty-Five.

### 9. Voting

| Surface | Where |
|---|---|
| Vote API (REST + guest) | `F:includes/class-roadmap-api.php:73` (route). Visitor vote read: `get_user_vote()` (one item) and `get_user_votes_batch()` (list), by user ID or by IP for guests |
| Permissions | `F:includes/class-roadmap-permissions.php:47-98` |
| Board vote buttons | `F:vue-app/src/components/RoadmapItem.vue:71-80`, `RoadmapIdeaBox.vue:27-36,226-233`, `ItemDetailModal.vue:129-131`, `RoadmapCards.vue:180`, `RoadmapList.vue:178`, `RoadmapTimeline.vue:370`; state in `App.vue:540-560` |
| Single item page | `F:templates/single-roadmap_item.php` reads the visitor's vote through `Roadmap_API::get_user_vote()`, the same read as the REST item response (no SQL in templates) |
| Settings (enable / guest) | see row 13 |
| Pro: analytics, merge, profiles, webhooks, digest, export | `P:includes/class-roadmap-pro-analytics.php:37`, `-merge.php:292-408`, `-user-profiles.php:138-450`, `-webhooks.php:53,179`, `-digest.php:131`, `-export.php:234` |

Re-verify: `roadmap_vote|/vote|submitVote|handleVote|user_voted|allow_guest_voting|enable_voting`

### 10. Comments

| Surface | Where |
|---|---|
| Comment API | `F:includes/class-roadmap-api.php:100` (route), `:293` |
| Permission + `comments_open` filter (applies the setting to the single page) | `F:includes/class-roadmap-permissions.php:21,101,131,173-180` |
| Board | `F:vue-app/src/components/CommentModal.vue`, `ItemDetailModal.vue:180`, counts in `RoadmapItem.vue:162-166`, `RoadmapCards.vue:181`, `RoadmapList.vue:179`, `RoadmapTimeline.vue:371`; `App.vue:577-579` |
| Single item page | `F:templates/single-roadmap_item.php:231` (core comments) |
| Pro merge | `P:includes/class-roadmap-pro-merge.php:152` |

Re-verify: `/comment|submitComment|enable_comments|comments_open|comment_form`

### 11. Suggestions

| Surface | Where |
|---|---|
| Member suggest | `F:vue-app/src/components/SuggestionModal.vue`, `App.vue:195-230`, REST `F:includes/class-roadmap-api.php:121` (honeypot + rate limit `:744-753`) |
| Guest suggest (Pro) | REST `P:includes/class-roadmap-pro-guest-suggestions.php` (4 routes), admin screen `:111`, emails `:702,754` |
| Pro feedback widget | `P:includes/class-roadmap-pro-feedback-widget.php:128,169`, `P:assets/js/feedback-widget.js:61,73` |
| Moderation (pending items) | Roadmap admin (`F:includes/class-roadmap-cpt.php` pending view), Pro guest screen |
| Webhooks | `P:includes/class-roadmap-pro-webhooks.php:56,247,273,568` |

Re-verify: `/suggest|/guest-suggest|submitSuggestion|enable_suggestions|guest_suggestion|SuggestionModal`

### 12. Changelog

| Surface | Where |
|---|---|
| CPT, meta boxes, slug, permalink, admin menu | `F:includes/class-roadmap-changelog.php` (menu `:67-96`, slug `:952-1010`, permalink `:938-946`) |
| Shortcode `[roadmap_changelog]` | `F:includes/class-roadmap-shortcode.php:163-330` |
| Vue changelog | `F:vue-app/src/components/RoadmapChangelog.vue`: never imported, not rendered (dead code) |
| Templates | `F:templates/archive-roadmap_changelog.php`, `single-roadmap_changelog.php` |
| REST | Free `F:includes/class-roadmap-api.php:253,272,282`; core `wp/v2/changelogs`; Pro `P:includes/class-roadmap-pro-changelog.php` (2 routes, bulk actions `:39-40,215`) |
| Settings | `F:includes/class-roadmap-settings.php:1432-1522` |

Slug rule (`auto_generate_slug()`): `{product}-v{version}`, derived on the first save that has a version, in both editors. In the block editor that's the meta-box save after the REST publish. An entry that already has a stored version, or a hand-edited slug, keeps its URL. Free's default product counts as the product (`free_default_product_id()`, shared with `save_meta_boxes()`). Two renderers are live (the shortcode PHP and the PHP templates), and both read the P3 tokens.
Re-verify: `roadmap_changelog|RoadmapChangelog|changelog-frontend|/changelog`

### 13. Settings reads (all `roadmap_global_settings` keys)

**Source of truth:** `Roadmap_Settings::get( $key )` (`F:includes/class-roadmap-settings.php`), which merges the stored array over `Roadmap_Settings::defaults()`, plus `Roadmap_Settings::notification_recipients()` for the one-per-line recipient list (card 10092400992). The pre-1.8 standalone options are moved into the array once by `Roadmap_Settings::migrate_legacy_options()` (called from the version-upgrade routine in `F:product-roadmap.php`) and then deleted.

| Surface | Where |
|---|---|
| Readers (through the getter) | `F:includes/class-roadmap-shortcode.php`, `-api.php`, `-permissions.php`, `-notifications.php`, `-setup-wizard.php` (pre-fill), `F:templates/single-roadmap_item.php`, `P:includes/class-roadmap-pro-guest-suggestions.php`, `P:includes/class-roadmap-pro-digest.php` |
| Writers (raw option, by design) | Settings screen via `register_setting()` + `sanitize_global_settings()`; REST `update_settings()` (`F:includes/class-roadmap-api.php`); Setup Wizard save; the migration |

**Rule:** read a global setting only through `Roadmap_Settings::get()`. A new setting gets its default in `defaults()` and nowhere else.

Re-verify: `get_option\( *'roadmap_global_settings'` must match only the getter, the migration and the three writers; `get_option\( *'roadmap_(enable_voting|allow_guest_voting|enable_comments|enable_suggestions|items_per_page|notification_email)'` must match nothing.

### 14. Board options (what the shortcode and block let an owner configure)

**Source of truth:** `Roadmap_Shortcode::options()` (`F:includes/class-roadmap-shortcode.php`), one entry per option (type, default, label, help, group, choices; `pro` for options that need Pro's API filters). `normalize_options()` casts and constrains every value; `option_choices()` resolves term-based choice lists only when an editor needs them (card 10344405387).

| Surface | Where | How it uses the schema |
|---|---|---|
| `[roadmap]` shortcode | `F:includes/class-roadmap-shortcode.php` `render_shortcode()` | `shortcode_atts()` defaults from the schema, then `normalize_options()` |
| Board config handed to the app | same, `$app_data` | every option reaches `data-config`; per-board `show_voting`/`show_comments` can only hide (the REST API enforces Settings) |
| Board app | `F:vue-app/src/App.vue` (`selectedStatus`/`selectedCategory` seeded, `pageSize`/`boardLimit`, fixed `priority`/`date_from`/`date_to` request params), `F:vue-app/src/components/RoadmapHeader.vue` (`showSearch`) | reads the config |
| Pro block attributes + render | `P:includes/class-roadmap-pro-blocks.php` (`register_blocks()`, `render_roadmap_block()` -> `Roadmap_Shortcode::render_shortcode()`, legacy names in `LEGACY_ATTRIBUTES`) | attributes generated from the schema; renders through the shortcode renderer |
| Pro block sidebar + live preview | `P:blocks/roadmap-block/index.js` | controls built from the localized schema; preview = `ServerSideRender` + `RoadmapApp.mount()`, inert in the editor |
| Docs | `F:docs/guides/displaying-roadmap.md` (options table generated from the schema) | |
| Parity check | `F:bin/check-board-options.php` (`wp eval-file`) | fails if any schema option does not reach the board from the shortcode and the block, or has no test case |
| `[roadmap_user_profile]` | `P:includes/class-roadmap-pro-user-profiles.php:416` | separate shortcode, own options |

**Rule:** add a board option only in `options()`; it then works in the shortcode, the block sidebar and preview, and the parity check forces a test case for it.

Re-verify: `wp eval-file wp-content/plugins/product-roadmap/bin/check-board-options.php` must print `OK: 0 failure(s)`.

### 15. Email

| Surface | Where |
|---|---|
| Status-change notifications | `F:includes/class-roadmap-notifications.php:255,451` |
| Pro digest | `P:includes/class-roadmap-pro-digest.php` |
| Pro email templates | `P:includes/class-roadmap-pro-email-templates.php:36,197` |
| Pro guest-suggestion mails | `P:includes/class-roadmap-pro-guest-suggestions.php:702,754` |
| Pro email settings | `P:includes/class-roadmap-pro-settings.php:194-474` |

Check with Mailpit on the Local site (see `wp-card-qa` → Email checks), never an email-log row.
Re-verify: `wp_mail\(|roadmap_pro_email|email_template|digest`
