# Product Roadmap — Role Matrix

> Expected allow/deny per role, per surface. Oracle for the role ladder.
> **Derived from code:** `includes/class-roadmap-permissions.php:44-95` (grants)
> and `:191-246` (`can_edit_item()`/`can_delete_item()`) +
> `includes/class-roadmap-api.php` REST permission callbacks.
> Re-derive when any of these change.

Legend: **Y** allowed · **N** denied · **O** own content only · **–** n/a · **?** unverified (BLOCKED, not Y)

## Capability grants — from `class-roadmap-permissions.php:44-95`

| Capability | anonymous | subscriber | contributor | author | editor | administrator |
|---|---|---|---|---|---|---|
| `roadmap_vote` | N | Y | Y | Y | Y | Y |
| `roadmap_comment` | N | Y | Y | Y | Y | Y |
| `roadmap_suggest` | N | Y | Y | Y | Y | Y |
| `roadmap_edit_own_items` | N | Y | N | Y | – | – |
| `roadmap_delete_own_items` | N | Y | N | Y | – | – |
| `roadmap_edit_items` | N | N | N | N | Y | Y |
| `roadmap_delete_items` | N | N | N | N | Y | Y |
| `roadmap_manage_settings` | N | N | N | N | N | Y |
| `roadmap_view_analytics` | N | N | N | N | N | Y |

`roadmap_edit_own_items`/`roadmap_delete_own_items` only grant access to items the
user owns (`post_author` match) - `can_edit_item()`/`can_delete_item()` check the
capability AND ownership together, never capability alone. As of 1.8.0 these
route through the plugin's own check rather than WordPress's native
`edit_post`/`delete_post` meta-cap, which required the base `edit_posts`
capability before ownership was even considered - a Subscriber never holds
`edit_posts`, so this grant was unreachable dead code before the fix.

Guest voting/suggestions are settings-gated, not capability-gated:
`allow_guest_voting` (default **on**) and `enable_guest_suggestions` widen the
anonymous row at runtime — see `class-roadmap-api.php`'s guest-path checks.

## Surfaces

`member-other` is a second account with the **same role** as `member-owner`.

| Surface | Route / endpoint | anonymous | member-owner | member-other | contributor | elevated | admin |
|---|---|---|---|---|---|---|---|
| Board list | `GET /roadmap/v1/items` | Y | Y | Y | Y | Y | Y |
| Vote | `POST /roadmap/v1/vote` | Y *(when `allow_guest_voting`)* | Y | Y | Y | Y | Y |
| Comment | `POST /roadmap/v1/comment` | N | Y | Y | Y | Y | Y |
| Suggest | `POST /roadmap/v1/suggest` | Y *(when guest suggestions on)* | Y | Y | Y | Y | Y |
| Create item | `POST /roadmap/v1/items` | N | N | N | **N** | Y | Y |
| Update own item | `PUT /roadmap/v1/items/<id>` (own) | N | **O (Y)** | – | N | Y | Y |
| Update other's item | `PUT /roadmap/v1/items/<id>` (not own) | N | – | **O (N)** | N | Y | Y |
| Delete own item | `DELETE /roadmap/v1/items/<id>` (own) | N | **O (Y)** | – | N | Y | Y |
| Delete other's item | `DELETE /roadmap/v1/items/<id>` (not own) | N | – | **O (N)** | N | Y | Y |
| Settings screen | `admin.php?page=roadmap-settings` | N | N | N | N | N | Y |
| Products screen | `admin.php?page=roadmap-products` | N | N | N | N | N | Y |

Contributor is denied on item create/update/delete because it holds none of
`roadmap_edit_items`/`roadmap_edit_own_items`/`roadmap_delete_items`/
`roadmap_delete_own_items` - only `roadmap_vote`/`roadmap_comment`/`roadmap_suggest`.
A Contributor's items reach the board through the suggestion queue instead.

## Known bypasses

| Bypass | Applies to | Why it is correct |
|---|---|---|
| Administrator | all capability + ownership gates | WP convention; `manage_options` holder owns the site |
| Editor | ownership check on edit/delete | holds the site-wide `roadmap_edit_items`/`roadmap_delete_items`, checked before the own-item path |

## Deliberate denials

| Surface | Role | Denied because |
|---|---|---|
| Comment | anonymous | `can_comment()` requires a logged-in user — there is no guest-comment setting |
| Notification email | guest voter | `get_users_to_notify()` JOINs `wp_users`; a guest vote is a count, never a follow-up |
