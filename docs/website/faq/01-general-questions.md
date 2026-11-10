# General Questions

Answers to the questions site owners ask most often about Product Roadmap Free 1.8.0.

## What is the difference between Free and Pro?

Free shows one roadmap for one default product using the Minimal layout. Pro adds multiple products, extra layouts (Board, List, Cards, Timeline, Idea Box), analytics, drag-and-drop status management, CSV import/export, a block, and guest suggestions. See [Multi-Product and Pro Features](../admin-guide/05-multi-product-and-pro-features.md) for the full table.

## Who can vote?

It depends on **Who Can Vote** under **Roadmap > Settings**. **Anyone (including guests)** lets visitors vote without an account; **Logged in users only** requires a login. Voting must also be switched on with **Enable Voting**. Guest votes are tracked by IP address, so one vote per item per IP. Logged-in users need the `roadmap_vote` capability, which Administrator, Editor, Author, Contributor and Subscriber roles receive. See [Voting, Comments, and Suggestions](../admin-guide/03-voting-comments-and-suggestions.md).

## How are votes counted?

The vote count is the number of people who voted on an item. Since 1.8.0 it is no longer a net score that downvotes could reduce, and existing counts are recalculated on upgrade, so a board that had received downvotes may show higher numbers than before.

## Where do suggestions go?

A member's suggestion is saved as a pending roadmap item awaiting your review under **Roadmap > All Items > Pending**. If **Auto-approve user suggestions** is on, it publishes at once. Either way it gets the **Default Status for Suggestions**. See [Review Pending Suggestions](../admin-guide/07-reviewing-pending-suggestions.md). On Free, only logged-in members can suggest; guest suggestions need Pro. Suggestions are rate-limited to 5 per hour per user (or per IP).

## How do I moderate votes, comments and suggestions?

Comments use the standard WordPress comment screen. Suggestions are reviewed in **Roadmap > All Items**: publish, edit or trash each one. Votes have no moderation queue; a vote is a single toggle per person per item.

## Shortcode or block?

Free displays the roadmap with the `[roadmap]` shortcode and the changelog with `[roadmap_changelog]`. The Gutenberg block ships with Pro. See [Displaying Your Roadmap](../admin-guide/02-displaying-your-roadmap.md) and the [shortcode reference](../developer-guide/04-shortcode-reference.md).

## How do colors and dark mode work?

The frontend follows your active theme's accent color and its own light/dark toggle; there is no separate dark-mode switch in the plugin. A per-product **Theme Color** setting overrides the accent for that product's board. See [Statuses and Categories](../settings/03-statuses-and-categories.md) and the [theming section](../developer-guide/01-architecture-overview.md#colours-and-theming).

## What happens to my data when I delete the plugin?

Uninstalling removes the plugin's `roadmap_*` options, its transients, the votes table (`roadmap_votes`) and users' notification opt-out settings. Pro's `roadmap_pro_*` options are left for Pro's own uninstall. Roadmap items, products and changelog entries (posts and terms) are intentionally kept so they survive a reinstall. Votes are deleted, so export anything you need first.

## Who receives emails?

Admin alerts (new vote, comment, suggestion) go to the addresses set on the [Notifications page](../settings/02-notifications.md). Voters and commenters on an item are separately emailed when its status changes, with an unsubscribe link.
