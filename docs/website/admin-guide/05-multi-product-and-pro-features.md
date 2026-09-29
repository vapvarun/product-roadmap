# Multi-Product and Pro Features

Product Roadmap uses a product taxonomy under the hood even in the free version - you just don't see it, because Free is limited to a single product.

## How products work on Free

- A default product (internally slugged "main") is created automatically on activation.
- Every roadmap item, changelog entry, and shortcode implicitly uses this default product when you don't specify one.
- `[roadmap]` with no `product` attribute always shows your configured default product - it isn't hardcoded to a literal "main" string, so if the default product's name or label changes, your existing shortcodes keep working without edits.
- There's no **Products** admin menu on Free, since there's nothing to manage beyond the one default product.

## What Pro adds

Pro turns the same taxonomy into full multi-product management:

| Capability | Free | Pro |
|---|:---:|:---:|
| One roadmap | Yes | Yes |
| Unlimited products, each with its own roadmap | - | Yes |
| **Products** admin menu (create, edit, delete products) | - | Yes |
| Per-product settings (theme color, default status) | - | Yes |
| `[roadmap product="your-slug"]` targeting a specific product | - | Yes |
| Per-product changelogs | - | Yes |
| Analytics dashboard (votes, views, engagement, CSV export) | - | Yes |
| Board, List, Cards, Timeline, and Idea Box layouts | - | Yes |
| Priority and target-date shortcode filters | - | Yes |
| Drag-and-drop status management | - | Yes |
| CSV import/export | - | Yes |
| Gutenberg block | - | Yes |
| Priority email support | - | Yes |

## When to upgrade

Consider Pro if you:

- Manage more than one product or service and want separate roadmaps for each
- Want to track engagement with an analytics dashboard
- Need a visual layout beyond the single-column Minimal view (a Kanban board, a timeline, a card grid)
- Want editors to manage items with drag-and-drop instead of the admin list
- Need to bulk-import or export items via CSV

Pro is an addon: install and activate the free plugin first, then install and activate Product Roadmap Pro and enter your license key. All existing items, votes, and settings carry over automatically.

For Pro-specific setup and usage, see the Pro addon's own documentation.

[Get Product Roadmap Pro](https://wbcomdesigns.com/downloads/product-roadmap-pro/)
