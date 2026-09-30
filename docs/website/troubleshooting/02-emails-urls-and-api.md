# Emails, URLs and REST API Problems

## Status-change emails do not arrive immediately

- **Symptom:** Voters do not get an email as soon as you change an item's status.
- **Cause:** Since 1.8.0 status-change emails are queued as WordPress cron events and sent in batches, one batch per minute, rather than inside the save request. They depend on WP-Cron running and on your server sending mail.
- **Fix:** Wait a few minutes. If nothing arrives, check that WP-Cron is not disabled (`DISABLE_WP_CRON`) without a real cron job, and test that your site sends mail at all with an SMTP plugin. Admin alerts use the addresses on the [Notifications page](../settings/02-notifications.md); status emails go to voters and commenters and skip anyone who unsubscribed.

## Admin notifications go to the wrong address

- **Symptom:** Alerts reach the site admin instead of your team.
- **Cause:** **Notification Email Addresses** is empty, so the site admin email is used.
- **Fix:** Enter one address per line under **Roadmap > Settings > Notifications**.

## /roadmap/ shows the wrong page or a 404

- **Symptom:** The roadmap archive, a product page or an item returns a 404, or a WordPress Page at `/roadmap/` conflicts with the plugin.
- **Cause:** Rewrite rules are stale. If a published Page owns the `/roadmap/` slug, the plugin stands down its own `/roadmap/` and `/roadmap/{product}/` rules for that URL, and rules rebuild on the next request after such a Page changes.
- **Fix:** Go to **Settings > Permalinks** and click **Save Changes** to flush rules. If you want the plugin's archive at `/roadmap/`, rename or unpublish the Page. Single items always resolve at `/roadmap/{product}/{item-slug}/`.

## Changelog URLs or slugs look wrong

- **Symptom:** A changelog entry's URL contains `general` or the wrong product.
- **Cause:** Entries live at `/changelog/{product-slug}/{entry-slug}/`. An entry with no product assigned uses `general`. Before 1.8.0, an entry published in the block editor under a non-default product could keep a `general-` slug prefix.
- **Fix:** Assign the product on the entry, update the slug if it still shows the old prefix, then save permalinks. See [Changelog](../admin-guide/04-changelog.md).

## REST API returns 403 for non-admins

- **Symptom:** A request to a write endpoint returns 401 or 403.
- **Cause:** Write endpoints check capabilities. Settings updates require `manage_options` (administrators only). Creating items requires an edit capability such as `roadmap_edit_items` or `roadmap_edit_own_items`; editing or deleting requires rights on that specific item. Voting requires **Enable Voting** and, for guests, guest voting; commenting and suggesting require the matching capability and feature setting. Browser requests also need a valid REST nonce.
- **Fix:** Authenticate with cookie plus nonce, or an Application Password, as a user with the needed capability, and enable the matching feature in Settings. See the [REST API reference](../developer-guide/03-rest-api-reference.md) and [Architecture Overview](../developer-guide/01-architecture-overview.md).

## Members cannot edit their own submitted items

- **Symptom:** A member cannot edit or delete an item they submitted.
- **Cause:** On sites that installed before 1.8.0, the role capabilities may not have been refreshed.
- **Fix:** Update to 1.8.0 or later; capabilities (`roadmap_edit_own_items`, `roadmap_delete_own_items` for Author and Subscriber) are re-applied automatically after a version change.
