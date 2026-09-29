# Architecture Overview

## Plugin structure

```
product-roadmap/
├── includes/
│   ├── class-roadmap-admin.php          Admin interface
│   ├── class-roadmap-api.php            REST API endpoints
│   ├── class-roadmap-changelog.php      Changelog post type, meta, and shortcode
│   ├── class-roadmap-cpt.php            Custom post types, taxonomies, item meta box
│   ├── class-roadmap-demo-import.php    Setup wizard demo content
│   ├── class-roadmap-migration.php      Database migrations / schema repair
│   ├── class-roadmap-notifications.php  Email notifications, status-change emails
│   ├── class-roadmap-permissions.php    Capabilities and access control
│   ├── class-roadmap-settings.php       Settings screens and options
│   ├── class-roadmap-setup-wizard.php   First-run setup wizard
│   ├── class-roadmap-shortcode.php      [roadmap] and [roadmap_changelog] shortcodes
│   └── class-roadmap-template.php       Template loader
├── vue-app/          Vue.js frontend application (board, item, changelog views)
├── assets/           Compiled CSS and JS
├── templates/        PHP templates (single item, archive, changelog)
└── product-roadmap.php   Main plugin file
```

No PHP namespace is used; classes are prefixed `Roadmap_*` (main class `RoadmapPlugin`). Text domain: `product-roadmap`.

## Custom post types

| Post type | Purpose |
|---|---|
| `roadmap_item` | A roadmap feature/task |
| `roadmap_product` | A product (taxonomy-backed; see below) |
| `roadmap_changelog` | A release notes entry |

## Taxonomies

All three are registered hierarchical and REST-exposed:

| Taxonomy | Attached to | Purpose |
|---|---|---|
| `roadmap_status` | `roadmap_item` | Workflow stage (Planned, In Progress, etc.) |
| `roadmap_category` | `roadmap_item` | Feature grouping |
| `roadmap_product` | `roadmap_item`, `roadmap_changelog` | Which product an item or changelog entry belongs to |

## Database

A custom `{$wpdb->prefix}roadmap_votes` table stores individual votes, with indexes on `item_id`, `user_id`, `created_at`, and `item_created` for fast lookups at scale. Everything else (item data, settings, changelog entries) uses WordPress's own posts, postmeta, and options tables.

## Capabilities

Custom capabilities are granted to roles on activation (and re-checked on version upgrade, not on every page load):

| Capability | Administrator | Editor | Author | Contributor | Subscriber |
|---|:---:|:---:|:---:|:---:|:---:|
| `roadmap_vote` | Yes | Yes | Yes | Yes | Yes |
| `roadmap_comment` | Yes | Yes | Yes | Yes | Yes |
| `roadmap_suggest` | Yes | Yes | Yes | Yes | Yes |
| `roadmap_edit_items` / `roadmap_delete_items` | Yes | Yes | - | - | - |
| `roadmap_edit_own_items` / `roadmap_delete_own_items` | - | - | Yes | - | Yes |
| `roadmap_manage_settings` | Yes | - | - | - | - |
| `roadmap_view_analytics` | Yes | - | - | - | - |

`roadmap_edit_own_items` and `roadmap_delete_own_items` are what let a Subscriber (or Author) edit and delete their own submitted items - by design, they can't touch anyone else's.

Disabling a feature (voting, comments, or suggestions) in Settings revokes the matching capability, so `current_user_can()` reflects the current setting immediately rather than only affecting the UI.

## Colours and Theming

Every frontend colour comes from one stylesheet, `assets/css/roadmap-tokens.css`, registered as the `roadmap-tokens` style handle. The board, the item and changelog templates, and Pro's stylesheets all load it as a dependency.

- **Accent colour** follows the active theme's `--wp--preset--color--primary`, falling back to the plugin's own blue if the theme sets none.
- **Dark mode** follows the theme's own light/dark toggle (BuddyX, BuddyX Pro, Reign) - never the visitor's OS setting. Light and dark are just two values of the same custom properties.
- **Overriding a token:** redefine it in your theme's CSS. Redefine it again under your theme's dark-mode selector if it should differ in dark mode too.

```css
:root {
    --rm-planned: #0b5cad;
    --rm-planned-tint: #e3effb;
}
```

- **Status and priority colours** are mapped through CSS classes, not an admin color picker. Any element with a `roadmap-status-{slug}` class gets `--rm-status` / `--rm-status-tint`; a status whose slug isn't one of the built-in five falls back to a neutral grey chip until you add a token for it. Priority works the same way with `roadmap-priority-{low|medium|high|critical}`.

```css
.roadmap-status-beta-testing {
    --rm-status: var(--rm-review);
    --rm-status-tint: var(--rm-review-tint);
}
```

- **Contributing:** `bin/check-raw-colors.sh` runs in CI and fails when a raw colour value appears in frontend source outside the tokens file. Add a token (with a light and dark value) instead of hardcoding a colour.

## Next steps

- [Hooks Reference](02-hooks-reference.md)
- [REST API Reference](03-rest-api-reference.md)
- [Shortcode Reference](04-shortcode-reference.md)
