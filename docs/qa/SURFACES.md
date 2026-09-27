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
| **Frontend board (Vue)** | `F:vue-app/src/App.vue` (shell, toasts, modals) and views `RoadmapBoard`, `RoadmapCards`, `RoadmapList`, `RoadmapTimeline`, `RoadmapMinimal`, `RoadmapIdeaBox`; item card `RoadmapItem`; modals `ItemDetailModal`, `ItemModal`, `CommentModal`, `SuggestionModal`; `RoadmapHeader`, `RoadmapFilters`, `RoadmapFooter`, `LoadingSpinner`; changelog `RoadmapChangelog` |
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
| Classic editor Products box | `P:includes/class-roadmap-pro-products.php:171,207` | yes (radio) |
| REST `wp/v2/roadmap-items`, `wp/v2/changelogs` | `P:includes/class-roadmap-pro-products.php:34,35` (`rest_pre_insert_*`) | yes (400) |
| Free REST `POST /items` | `F:includes/class-roadmap-api.php:1092` (arg declared string) | yes |
| Free REST `POST /suggest` | `F:includes/class-roadmap-api.php:799` | yes (single string) |
| Pro guest suggestion | `P:includes/class-roadmap-pro-guest-suggestions.php:402` | yes |
| Pro changelog REST | `P:includes/class-roadmap-pro-changelog.php:252` | yes (single slug) |
| Default product on save | `F:includes/class-roadmap-cpt.php:744`, `F:includes/class-roadmap-changelog.php:681` | yes |
| Demo import, migration | `F:includes/class-roadmap-demo-import.php:258`, `F:includes/class-roadmap-migration.php:61` | yes |
| **Quick Edit / Bulk Edit** | core list table (`show_in_quick_edit=true`) | **no** — P2 10344405404 |
| **Pro CSV import** | `P:includes/class-roadmap-pro-export.php:566,601` | **no** (cell becomes one term; unknown value creates a junk product) — P2 10344405404 |

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

**Source of truth:** `F:vue-app/src/assets/css/main.scss:360-400` — `.roadmap-badge.roadmap-status-{slug}` on the `--rm-{status}` / `--rm-{status}-tint` tokens (light `:62-67`, dark `:504-509`).

| Surface | Where | Uses the shared classes? |
|---|---|---|
| Cards | `F:vue-app/src/components/RoadmapCards.vue:34` | yes |
| List | `F:vue-app/src/components/RoadmapList.vue:71` | yes |
| Item detail modal | `F:vue-app/src/components/ItemDetailModal.vue:23` | yes (since `1471671`) |
| Board columns | `F:vue-app/src/components/RoadmapBoard.vue:5-11` | n/a (status is the plain column heading, no colour) |
| Item card | `F:vue-app/src/components/RoadmapItem.vue:209-210` (Suggested badge), `:393` (progress bar) | tokens; no status chip |
| **Minimal** | `F:vue-app/src/components/RoadmapMinimal.vue:37`, own rules `:455-471` | **own colours** — P3 10344405416 |
| **Timeline** | `F:vue-app/src/components/RoadmapTimeline.vue:40,123`, own rules `:267,284,742-759` | **own colours** — P3 |
| **IdeaBox** | `F:vue-app/src/components/RoadmapIdeaBox.vue:60` | **own colours** — P3 |
| **Archive template** | `F:templates/archive-roadmap_item.php:160,288` | **raw hex** — P3 |

Re-verify: `roadmap-status-|getStatusSlug|getStatusColor|status\[0\]\.name|var\(--rm-(planned|progress|done|cancelled|hold|review)`

### 4. Priority display

**Source of truth:** `F:vue-app/src/assets/css/main.scss:70-73` (tokens) and `:343-349` (chip rules).

| Surface | Where | Shared? |
|---|---|---|
| Item card | `F:vue-app/src/components/RoadmapItem.vue:48,174` | yes |
| List | `F:vue-app/src/components/RoadmapList.vue:78,271` | yes |
| Item detail modal | `F:vue-app/src/components/ItemDetailModal.vue:33,183` | yes |
| **Cards** | `F:vue-app/src/components/RoadmapCards.vue:78`, own rules `:373-383` | **own rules** — P3 |
| **IdeaBox** | `F:vue-app/src/components/RoadmapIdeaBox.vue:72,250` | **own rules** — P3 |

Re-verify: `roadmap-priority|getPriorityClass|priority-(low|medium|high|critical)|var\(--rm-priority`

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
| **Browser `alert()` / `confirm()`** | `F:assets/js/roadmap-admin.js:52,124,128`, `P:assets/js/admin.js:219,224`, `P:blocks/roadmap-block/index.js:215` | **no** — breaks the no-browser-dialogs rule; card 10344407804 |

Known gap: the Setup Wizard shows the welcome notice on itself — card 10336345758.
Re-verify: `showNotification|roadmap-notification|admin_notices|alert\(|confirm\(`

### 7. Forms: submit and reset

**Rule:** keep what the person typed until the server confirms success.

| Surface | Where | Keeps input on failure? |
|---|---|---|
| **Suggestion modal** | `F:vue-app/src/components/SuggestionModal.vue:140-148` (resets on emit) | **no** — card 10343990481 |
| Comment modal | `F:vue-app/src/components/CommentModal.vue:51-53` | yes |
| Item modal (manage mode) | `F:vue-app/src/components/ItemModal.vue:225-230` | yes |
| Pro feedback widget | `P:assets/js/feedback-widget.js:43` | to verify with the fix for 10343990481 |
| Single item comments | `F:templates/single-roadmap_item.php:231` (core `comments_template()`) | core behaviour |

Re-verify: `emit\(\s*'(submit|save)'|@submit|handleSubmit|onSubmit|addEventListener\(\s*'submit'`

### 8. Colours and dark mode

**Source of truth:** tokens on `.roadmap-app` in `F:vue-app/src/assets/css/main.scss:28-75`, dark swap `@mixin roadmap-dark-theme` `:493-530`, applied by the board toggle `.roadmap-dark` `:572-586`.

| Surface | Raw hex / token uses | Dark mode |
|---|---|---|
| `RoadmapIdeaBox.vue` | 101 / 3 | **own dark CSS** `:583-649` — P3 |
| `RoadmapChangelog.vue` | 40 / 0 | **own `darkMode` switch** `:2,148,455` — P3 |
| `F:assets/css/changelog-frontend.css` | 51 / 26 | partial — P3 |
| `P:assets/css/roadmap-pro.css` | 36 / 0 | none — P3 |
| `F:templates/archive-roadmap_item.php` | 27 / 0 | none — P3 |
| `ItemDetailModal.vue` | 22 / 3 | via tokens (partial) — P3 |
| `RoadmapTimeline`, `RoadmapItem`, `RoadmapMinimal`, `RoadmapCards`, `RoadmapList` | 8, 5, 3, 3, 2 raw | via tokens — P3 |
| `LoadingSpinner`, `ItemModal`, `RoadmapFooter`, `P:class-roadmap-pro-templates.php`, changelog templates | 4, 4, 2, 5, 2+2+1 raw | none — P3 |

Re-verify: count `#[0-9a-f]{3,6}` vs `var\(--rm-|var\(--roadmap-` per file.

### 9. Voting

| Surface | Where |
|---|---|
| Vote API (REST + guest) | `F:includes/class-roadmap-api.php:73` (route), `:451-555` (read/write, guest by IP) |
| Permissions | `F:includes/class-roadmap-permissions.php:47-98` |
| Board vote buttons | `F:vue-app/src/components/RoadmapItem.vue:71-80`, `RoadmapIdeaBox.vue:27-36,226-233`, `ItemDetailModal.vue:129-131`, `RoadmapCards.vue:180`, `RoadmapList.vue:178`, `RoadmapTimeline.vue:370`; state in `App.vue:540-560` |
| **Single item page** | `F:templates/single-roadmap_item.php:19-97` — **its own vote query with `$wpdb` in the template** instead of the API's vote read; card 10344407815 |
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
| Vue changelog | `F:vue-app/src/components/RoadmapChangelog.vue` |
| Templates | `F:templates/archive-roadmap_changelog.php`, `single-roadmap_changelog.php` |
| REST | Free `F:includes/class-roadmap-api.php:253,272,282`; core `wp/v2/changelogs`; Pro `P:includes/class-roadmap-pro-changelog.php` (2 routes, bulk actions `:39-40,215`) |
| Settings | `F:includes/class-roadmap-settings.php:1432-1522` |

Known gap: block-editor slug (card 10326920444). Three renderers (shortcode PHP, Vue component, PHP templates) — P3 unifies their colours.
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

**Rule:** one option schema; the block is a thin wrapper (CLAUDE.md rule 8, card P1 10344405387).

| Surface | Where | Today |
|---|---|---|
| `[roadmap]` accepted options | `F:includes/class-roadmap-shortcode.php:46-62` | mode, allow_frontend_edit, product, class, template, sort, show_filters, show_title |
| Block attributes + shortcode builder | `P:includes/class-roadmap-pro-blocks.php:51-80, 206-250` | **7 options dropped** (status, category, limit, order, show_voting, show_comments, show_search); no sort / show_title |
| Block sidebar + preview | `P:blocks/roadmap-block/index.js:109-200` (controls), `:227-260` (static preview, no real board) | **no real preview**; dead `roadmap_block_preview` nonce `P:includes/class-roadmap-pro-blocks.php:187` |
| Config handed to the board | `F:includes/class-roadmap-shortcode.php:101-124`, filter `roadmap_shortcode_data` `:124` (Pro `P:includes/class-roadmap-pro-templates.php:36`, `P:roadmap-pro-addon.php:187`) | |
| Board reads config | `F:vue-app/src/App.vue:15-20, 293-299, 336, 394, 511` | |
| `[roadmap_user_profile]` | `P:includes/class-roadmap-pro-user-profiles.php:416` | separate, own options |

Re-verify: `shortcode_atts|roadmap_shortcode_data|'attributes'\s*=>|\$shortcode \.=|config\.(show_filters|show_title|default_sort|template|product)`

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
