# Product Roadmap — Role Matrix

> Expected allow/deny per role, per surface. Oracle for the role ladder.
> **Derived from code:** `includes/class-roadmap-permissions.php:44-82` (grants)
> and `:145-176` + `includes/class-roadmap-api.php:1268-1300` (checks).
> Re-derive when either file changes.

Legend: **Y** allowed · **N** denied · **O** own content only · **–** n/a · **?** unverified (BLOCKED, not Y)

## Capability grants — from `class-roadmap-permissions.php:44-82`

| Capability | anonymous | subscriber | contributor | author | editor | administrator |
|---|---|---|---|---|---|---|
| `roadmap_vote` | N | Y | Y | Y | Y | Y |
| `roadmap_comment` | N | Y | Y | Y | Y | Y |
| `roadmap_suggest` | N | Y | Y | Y | Y | Y |
| `roadmap_edit_own_items` | N | N | N | Y | – | – |
| `roadmap_edit_items` | N | N | N | N | Y | Y |
| `roadmap_delete_items` | N | N | N | N | Y | Y |
| `roadmap_manage_settings` | N | N | N | N | N | Y |
| `roadmap_view_analytics` | N | N | N | N | N | Y |

Guest voting/suggestions are settings-gated, not capability-gated:
`allow_guest_voting` (default **on**) and `enable_guest_suggestions` widen the
anonymous row at runtime — see `class-roadmap-api.php:1268-1279`.

## Surfaces

`member-other` is a second account with the **same role** as `member-owner`.

| Surface | Route / endpoint | anonymous | member-owner | member-other | contributor | elevated | admin |
|---|---|---|---|---|---|---|---|
| Board list | `GET /roadmap/v1/items` | Y | Y | Y | Y | Y | Y |
| Vote | `POST /roadmap/v1/vote` | Y *(when `allow_guest_voting`)* | Y | Y | Y | Y | Y |
| Comment | `POST /roadmap/v1/comment` | N | Y | Y | Y | Y | Y |
| Suggest | `POST /roadmap/v1/suggest` | Y *(when guest suggestions on)* | Y | Y | Y | Y | Y |
| Create item | `POST /roadmap/v1/items` | N | N | N | **N** | Y | Y |
| Update item | `PUT /roadmap/v1/items/<id>` | N | N | N | **N** | Y (roadmap items only) | Y (roadmap items only) |
| Delete item | `DELETE /roadmap/v1/items/<id>` | N | N | N | **N** | Y (roadmap items only) | Y (roadmap items only) |
| Settings screen | `admin.php?page=roadmap-settings` | N | N | N | N | N | Y |
| Products screen | `admin.php?page=roadmap-products` | N | N | N | N | N | Y |

## Known bypasses

| Bypass | Applies to | Why it is correct |
|---|---|---|
| Administrator | all capability + ownership gates | WP convention; `manage_options` holder owns the site |

## Deliberate denials

| Surface | Role | Denied because |
|---|---|---|
| Comment | anonymous | `can_comment()` requires a logged-in user — there is no guest-comment setting |
| Notification email | guest voter | `get_users_to_notify()` JOINs `wp_users`; a guest vote is a count, never a follow-up |
