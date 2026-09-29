# Shortcode Reference

This is the authoritative, code-verified attribute list for version 1.8.0. Earlier documentation described several attributes (`orderby`, `order`, `tag`, `draggable`, `show_votes`, and various timeline view-mode parameters) that were never implemented - they were removed from the docs and must not be reintroduced.

## `[roadmap]`

| Attribute | Type | Default | Notes |
|---|---|---|---|
| `product` | string | (empty) | Product slug. Leave empty to use your default product. |
| `template` | string | your default layout | `minimal` on Free. `board`, `list`, `cards`, `timeline`, and `ideabox` require Pro and fall back to `minimal` on Free. |
| `status` | string | (empty) | One status slug. Visitors can still change it from the filter bar unless `show_filters="false"`. |
| `category` | string | (empty) | One category slug. |
| `priority` | string | (empty) | **Pro.** One of `low`, `medium`, `high`, `critical`. |
| `date_from` | string (`YYYY-MM-DD`) | (empty) | **Pro.** Only items with a target date on or after this day. |
| `date_to` | string (`YYYY-MM-DD`) | (empty) | **Pro.** Only items with a target date on or before this day. |
| `sort` | string | (empty) | One of `votes`, `recent`, `comments`, `priority`, `title`. Empty uses the default order. |
| `limit` | integer | `0` | `0` shows every item with a Load more button. Accepts `0`-`100`. |
| `show_title` | boolean | `true` | Show the board title. |
| `show_filters` | boolean | `true` | Show the filter bar. |
| `show_search` | boolean | `true` | Show the search box. |
| `show_voting` | string | (empty) | `hide` hides voting on this board only. Empty follows the Settings toggle. Can never turn voting on if it's off in Settings. |
| `show_comments` | string | (empty) | `hide` hides comments on this board only. Same rule as `show_voting`. |
| `mode` | string | `view` | `view` or `manage`. `manage` lets editors add/edit items on the page; visitors always see `view` regardless of this attribute. |
| `allow_frontend_edit` | boolean | `false` | Allow editing items directly on the page. |
| `class` | string | (empty) | Extra CSS class on the container. |

`priority`, `date_from`, and `date_to` require the Pro addon; on Free they have no effect.

### Examples

```
[roadmap]
[roadmap status="planned" sort="votes"]
[roadmap show_title="false" show_filters="false" show_search="false"]
[roadmap show_voting="hide" show_comments="hide"]
[roadmap product="mobile-app" priority="high"]
```

## `[roadmap_changelog]`

| Attribute | Type | Default | Notes |
|---|---|---|---|
| `product` | string | (empty) | Product slug. On Free this always resolves to your single default product. |
| `id` | integer | (empty) | Show a single changelog entry by post ID. |
| `limit` | integer | `10` | Number of entries to show. |
| `show_date` | boolean | `true` | Show the release date. |
| `collapsible` | boolean | `false` | Collapse entries until clicked. |
| `class` | string | (empty) | Extra CSS class. |

Entries are always ordered by release date, newest first.

```
[roadmap_changelog]
[roadmap_changelog limit="5" collapsible="true"]
```

## Feature suggestions

There's no shortcode for the suggestion form. When suggestions are enabled in Settings, a "Suggest a feature" button appears on the `[roadmap]` board itself and opens a modal.

## Next steps

- [Hooks Reference](02-hooks-reference.md)
- [REST API Reference](03-rest-api-reference.md)
