# Product Roadmap (Free) — Capabilities

Buyer-level roll-up of what the free plugin does, verified against code. Status:
`YES` shipped · `PARTIAL` works with a limit · `NO` not in free (often Pro).

_Reviewed 2026-11-11 (final code freeze) for the 1.8.2 release gate: no capability status changed. 1.8.2 refined
already-YES surfaces (status-email recipients, keyboard and dialog accessibility, theme radius
tokens, block-theme and classic-theme page wrappers) and removed one setting that did nothing._

## Collect feedback

| Can it… | Status | Delivered by |
|---|---|---|
| Show a public roadmap with custom status columns | YES | `roadmap_item` CPT + `roadmap_status` taxonomy; `[roadmap]` shortcode |
| Let visitors upvote items | YES | `wp_roadmap_votes` table, one vote/user toggle (`class-roadmap-api.php`) |
| Let logged-out visitors vote | YES | guest voting by IP (`allow_guest_voting` setting) |
| Let users comment on items | YES | WP comments on roadmap items |
| Let users suggest new items | YES | `/suggest` REST endpoint, product-scoped, moderation queue (pending review) |
| Throttle vote/suggestion spam | YES | per-user/IP rate limit + suggestion honeypot |

## Present the roadmap

| Can it… | Status | Delivered by |
|---|---|---|
| Filter by status / category, sort, paginate | YES | Vue SPA filters + load-more |
| Multiple board layouts | PARTIAL | free ships the minimal layout; board/cards/list/timeline are Pro |
| Adopt the active theme's accent + dark mode | YES | token-driven, theme-toggle dark (1.7.0) |
| Embed anywhere via shortcode | YES | `[roadmap]`, `[roadmap_changelog]` |
| Use items and dialogs by keyboard and screen reader | YES | cards open on Enter/Space, dialogs trap focus, close on Escape and return focus (1.8.2) |
| Gutenberg block | NO | Pro |

## Changelog / release notes

| Can it… | Status | Delivered by |
|---|---|---|
| Publish a changelog / release notes | YES | `roadmap_changelog` CPT, single + archive templates, `[roadmap_changelog]` |
| Configurable release-type badge colours | YES | Settings → Changelog Types (live preview, AJAX save) |
| Per-product changelog | NO | Pro |

## Notify & manage

| Can it… | Status | Delivered by |
|---|---|---|
| Email voters/commenters when an item's status changes | YES | `class-roadmap-notifications.php`; live voters and approved commenters, once per real status change, honours the master switch; per-type toggles + unsubscribe link |
| Manage items in wp-admin (statuses, categories, drag-drop) | YES | admin menu + board manage mode |
| Roles / capability control | YES | `class-roadmap-permissions.php` |
| Setup wizard + demo import | YES | `class-roadmap-setup-wizard.php`, `class-roadmap-demo-import.php` |

## Platform

| Can it… | Status | Delivered by |
|---|---|---|
| REST API | YES | `roadmap/v1` namespace |
| Extensible via hooks/filters | YES | 25 `roadmap_*` hooks (see free/pro contract) |
| Uninstall cleanup + translation-ready | YES | `uninstall.php` + text domain loaded |
| Scale to thousands of items | YES | indexed votes table, bounded queries, pagination |

## Not in free (Pro)

Analytics · advanced filters (priority/progress/assignee/date) · board/cards/list/timeline
layouts · multi-product · CSV export · item merge · webhooks · custom fields · email
digest · feedback widget · guest suggestions · user-profile shortcode · Gutenberg block.

## Known gaps (post-1.7.0)

Private/gated boards · GDPR export/erase · "notify me when shipped" email capture ·
server-side search · global all-products view · bulk status actions · native Slack.
See `plan/1.7.0-readiness-checklist.md`.
