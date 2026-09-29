# REST API Reference

All endpoints are under the `roadmap/v1` namespace: `https://yoursite.com/wp-json/roadmap/v1/`.

Read endpoints (marked **Public**) require no authentication. Write endpoints check a WordPress capability or a specific permission callback (voting, commenting, and suggesting check the matching `roadmap_vote` / `roadmap_comment` / `roadmap_suggest` capability described in [Architecture Overview](01-architecture-overview.md)); authenticate them the same way you'd authenticate any WordPress REST request (cookie + nonce for browser JS, or Application Passwords for external clients).

## Items

### List items

```
GET /items
```

| Param | Type | Notes |
|---|---|---|
| `product` | string | **Required.** Product slug to filter by. |
| `status` | string | Optional status slug filter. |
| `category` | string | Optional category slug filter. |
| `search` | string | Free-text search across title and content. |
| `sort` | string | One of `votes`, `recent`, `comments`, `priority`, `title`. |
| `page` | integer | Default `1`. |
| `per_page` | integer | Default `100`. Capped by the `roadmap_max_per_page` filter (default ceiling: 200) - this route is public, so page size can't be raised without limit by the caller. |

### Get a single item

```
GET /items/{id}
```

### Create an item

```
POST /items
```

Requires the capability to create roadmap items. Body includes `title`, `content`, `status`, `categories`, `priority`, `progress`, and the other [item detail fields](../admin-guide/01-managing-roadmap-items.md#roadmap-item-details-fields).

### Update an item

```
PUT /items/{id}
```

Permission is checked against that specific post (via `Roadmap_Permissions::can_edit_item()`), not a blanket `edit_posts` capability - this is what lets a member edit their own submitted item without being able to edit anyone else's.

### Delete an item

```
DELETE /items/{id}
```

Same per-post permission check, via `Roadmap_Permissions::can_delete_item()`. Deleting moves the item to the trash rather than deleting it permanently.

## Voting

```
POST /vote
```

| Param | Type | Notes |
|---|---|---|
| `item_id` | integer | **Required.** |
| `vote_value` | integer | `1` to vote, `-1` or `0` depending on context to remove/adjust. Default `1`. |

Permission is checked by `can_vote`, which respects the **Who Can Vote** setting (including guest voting by IP).

## Comments

```
POST /comment
```

| Param | Type | Notes |
|---|---|---|
| `item_id` | integer | **Required.** |
| `content` | string | **Required.** |

### Get an item's comments

```
GET /items/{id}/comments
```

## Suggestions

```
POST /suggest
```

| Param | Type | Notes |
|---|---|---|
| `title` | string | **Required.** |
| `description` | string | **Required.** |
| `category` | string | Optional category slug. |

Permission is checked by `can_suggest`, which requires login on Free (guest suggestions are a Pro feature).

## Settings

```
GET /settings
POST /settings
```

`GET` is public and returns the statuses, categories, and configuration the frontend needs to render. `POST` requires the `roadmap_manage_settings` capability.

## Stats

```
GET /stats
```

Public. Returns aggregate counts (e.g. total items, total votes) used by the admin dashboard.

## Changelog

```
GET /changelog
GET /changelog/{id}
GET /changelog-types
```

`GET /changelog` accepts `product` (Pro, for multi-product filtering) and standard pagination params, and is filterable via the `roadmap_api_changelog_args` hook.

## Extending the API

Register your own routes on the same nonce/capability model using the `roadmap_register_api_routes` action, which fires after Product Roadmap's own routes are registered:

```php
add_action( 'roadmap_register_api_routes', function ( $namespace ) {
    register_rest_route( $namespace, '/my-endpoint', array(
        'methods'             => 'GET',
        'callback'            => 'my_endpoint_callback',
        'permission_callback' => '__return_true',
    ) );
} );
```

## Next steps

- [Hooks Reference](02-hooks-reference.md)
- [Shortcode Reference](04-shortcode-reference.md)
