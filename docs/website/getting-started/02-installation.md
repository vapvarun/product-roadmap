# Installation

## Automatic installation

1. Log into your WordPress admin.
2. Go to **Plugins > Add New**.
3. Search for "Product Roadmap".
4. Click **Install Now** on the plugin, then click **Activate**.

## Manual installation

1. Download the plugin ZIP file from WordPress.org.
2. Go to **Plugins > Add New > Upload Plugin**.
3. Choose the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

After activation, a **Roadmap** menu appears in your WordPress admin sidebar, and the setup wizard opens automatically.

## What activation creates

- The **roadmap_item**, **roadmap_product**, and **roadmap_changelog** post types
- The **roadmap_status**, **roadmap_category**, and **roadmap_product** taxonomies, plus a default "main" product
- A custom database table for votes
- Five default statuses: Planned, In Progress, Completed, On Hold, and Under Review

## Updating from an earlier version

Update through your WordPress dashboard as usual. Database repairs and schema updates run automatically on the first page view after updating, whether that page view happens in wp-admin or on the front end - there are no manual migration steps.

If you run the Pro addon alongside Free, keep both updated together. An outdated Pro shows an admin notice naming both versions with a link to update, but keeps running rather than being disabled.

## Next step

[Walk through the setup wizard](03-setup-wizard.md)
