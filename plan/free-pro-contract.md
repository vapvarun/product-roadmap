# Product Roadmap — Free ↔ Pro Contract

**Free:** `product-roadmap` 1.7.0 · **Pro:** `roadmap-pro-addon` 1.7.0
**Last verified against code:** 2026-07-12 (branch `1.7.0`). Regenerate + re-audit on any change to the hooks, product model, or tables below.

This is the authoritative contract governing how the two plugins interact. Pro extends Free **only** through the documented hooks; Free has no runtime dependency on Pro.

---

## 1. Extension hooks (the contract surface)

Free fires these; Pro consumes them. **Verified: every Pro `roadmap_*` listener has a firer — 0 orphans.** When renaming/removing any hook below, update the other side in the same change (this is the bug class that broke analytics in 1.6.0).

### Actions Free fires → Pro listens

| Hook | Args (firer signature) | Fired at | Pro consumer |
|---|---|---|---|
| `roadmap_vote_submitted` | `$item_id, $user_id, $vote_value` | api.php:607 (on vote) | analytics `track_vote` |
| `roadmap_status_changed` | `$item_id, $new_status, $old_status` | notifications.php:234 | analytics `track_status_change` |
| `roadmap_comment_submitted` | `$comment_id, $item_id, $user_id` | api.php:1032 | analytics `track_comment` |
| `roadmap_item_viewed` | `$item_id` | template.php:35 (single view) | analytics `track_view` |
| `roadmap_suggestion_submitted` | `$post_id` | api.php (suggest) | pro listeners |
| `roadmap_after_main_content` | `$atts` | shortcode.php (after app) | templates `add_analytics_tab` |
| `roadmap_register_api_routes` | `$namespace` | api.php:300 | pro-api registers routes |
| `roadmap_enqueue_assets` | — | shortcode | pro asset enqueue |
| `roadmap_dashboard_after_sections` | — | admin dashboard | pro dashboard analytics |
| `roadmap_render_advanced_settings` / `_license_tab` / `_templates_settings` | — | settings render | pro settings tabs |
| `roadmap_settings_tabs` / `_group` / `_tab_content` / `_form_tabs` | — | settings render | pro settings UI |

### Filters Free applies → Pro modifies

| Filter | Args | Applied at | Pro consumer |
|---|---|---|---|
| `roadmap_api_query_args` | `$args, $params` | api.php (before WP_Query) | filters `add_advanced_filters` (priority/progress/assignee/date) |
| `roadmap_api_filter_by_product` | `$args, $product` | api.php:329 | filters `filter_by_multiple_products` |
| `roadmap_api_format_item` | `$item, $post` | api.php (format) | pro-api `add_pro_fields_to_item` |
| `roadmap_api_changelog_args` | `$args` | changelog | pro changelog |
| `roadmap_changelog_shortcode_args` | `$args, $atts` | shortcode:179 | pro changelog multi-product |
| `roadmap_shortcode_data` | `$app_data, $atts` | shortcode:97 | templates `add_template_data` |
| `roadmap_item_meta_fields` | `$fields` | cpt | pro custom fields |

> **Removed in 1.7.0 (do not re-add as Pro listeners without a Free firer):** `roadmap_api_settings`, `roadmap_settings_sections`, `roadmap_shortcode_atts` — these were dead Pro listeners (nothing fired them). `roadmap_shortcode_atts` in particular must NOT be wired to Pro's old `add_template_attribute`, which force-set `template='board'` and would break `template="cards/list/timeline"` selection.

---

## 2. Product model (single source of truth)

**Products are `roadmap_product` TAXONOMY terms** on the `roadmap_item` post type. This is the only linkage.

- Free registers the taxonomy (`class-roadmap-cpt.php`). There is **no `roadmap_product` CPT** (the old disabled CPT + its `_roadmap_product_id` meta path were removed in 1.7.0).
- Item → product: taxonomy term assignment (`wp_set_object_terms(... 'roadmap_product')`). Both Free item-create and Pro's meta box write the taxonomy.
- Pro reads products via `get_terms('roadmap_product')` (`get_products`, `format_product`, `get_product_item_count` via `$term->count`), and scopes timeline/export via `tax_query` on slug.
- Term meta for product presentation: `roadmap_product_color` / `roadmap_product_icon` / `roadmap_product_visibility` (no leading underscore).
- **Verified: 0 references to `_roadmap_product_id` or `get_product_by_slug` (both removed).**

---

## 3. Data ownership

| Table | Owner | Notes |
|---|---|---|
| `{prefix}roadmap_votes` | **Free** | schema in `product-roadmap.php`; indexes `created_at`, `item_created`. Pro analytics reads it. |
| `{prefix}roadmap_analytics` | **Pro** | single schema in `class-roadmap-pro-analytics.php::create_analytics_table()` (also called on activation). |
| `{prefix}roadmap_filter_presets` | **Pro** | `roadmap-pro-addon.php::create_pro_tables()`. |

**Uninstall:** each plugin drops only its own tables + `roadmap_*` (free, excluding `roadmap_pro_*`) / `roadmap_pro_*` (pro) options. Content posts/terms are preserved.

---

## 4. Options / namespaces

- **Option prefixes:** Free owns `roadmap_*` (except `roadmap_pro_*`); Pro owns `roadmap_pro_*`. No cross-writes.
- **REST:** both register under the shared `roadmap/v1` namespace; routes are disjoint (Free: items/vote/suggest/comment/products/stats/changelog; Pro: analytics/timeline/export/webhooks/custom-fields/guest-suggestions/filter-presets/users). Free fires `roadmap_register_api_routes` so Pro attaches its routes.
- **AJAX:** Free `roadmap_*` actions; Pro `roadmap_*` (disjoint). (The no-op `wp_ajax_roadmap_vote` stub was removed in 1.7.0 — voting is REST `/vote`.)

---

## 5. Invariants (enforce on every change)

1. **Free never depends on Pro.** Free must function with `ROADMAP_PRO_ACTIVE` undefined.
2. **Pro extends via the hooks in §1 only** — no source modification, no assuming Pro-only classes in Free.
3. **Pro detects Free** via `defined('ROADMAP_VERSION')` / `class_exists('RoadmapPlugin')` / `is_plugin_active`, and initializes on `plugins_loaded`.
4. **Every Pro `roadmap_*` listener MUST have a Free (or Pro-self) firer** — re-run the orphan scan (§6) on any hook change. 0 orphans is the gate.
5. **One product model** (taxonomy) — never reintroduce a `roadmap_product` CPT or `_roadmap_product_id` meta.
6. **One schema per table** — never duplicate a `CREATE TABLE` across files.
7. **Versions move in lockstep** — Free and Pro share a version; Pro's readme `Requires`/`Compat` names the Free version.

---

## 6. Verification (re-run to audit this contract)

```bash
# 0 orphan listeners: every Pro roadmap_ listener has a firer
comm -23 \
  <(grep -rhoE "(add_action|add_filter)\(\s*'roadmap_[a-z_]*'" roadmap-pro-addon/includes | sed -E "s/.*'(roadmap_[a-z_]*)'.*/\1/" | sort -u) \
  <(grep -rhoE "(do_action|apply_filters)\(\s*'roadmap[a-z_]*'" product-roadmap roadmap-pro-addon | grep -v node_modules | sed -E "s/.*'(roadmap[a-z_]*)'.*/\1/" | sort -u)
# → must print nothing.

# no dead product model
grep -rn "_roadmap_product_id\|get_product_by_slug" */includes   # → nothing

# one analytics schema
grep -rln "CREATE TABLE.*roadmap_analytics" roadmap-pro-addon    # → exactly one file
```

**2026-07-12 status:** all three checks pass. Contract clean.
