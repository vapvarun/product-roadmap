# Board and Voting Problems

## The board shows nothing or the wrong product

- **Symptom:** `[roadmap]` renders empty or shows a different product than expected.
- **Cause:** No published items exist for that product, the `status` or `category` attribute filters everything out (each takes exactly one slug), or `product` names a slug that does not exist. Without a `product` attribute, the board uses the default product set in Settings.
- **Fix:** Check that items are published under **Roadmap > All Items** and assigned to the product. Remove the `status`, `category` and `product` attributes to test, then add them back one at a time. See the [shortcode reference](../developer-guide/04-shortcode-reference.md).

## Votes are not saving

- **Symptom:** The vote button does nothing, or the count does not change.
- **Cause:** **Enable Voting** is off, the visitor is a guest and **Who Can Vote** is set to logged-in users, the shortcode uses `show_voting="hide"`, or the visitor hit the rate limit (30 votes per minute per user or IP).
- **Fix:** Turn on **Enable Voting** in **Roadmap > Settings**, confirm who may vote, and wait a minute if rate-limited. Also check the browser console for JavaScript errors and rule out a plugin conflict by deactivating other plugins temporarily.

## Guests cannot vote

- **Symptom:** Logged-out visitors are asked to log in or get an error.
- **Cause:** **Who Can Vote** is set to **Logged in users only**. The setting is honored on both the board and the single item page.
- **Fix:** Choose **Anyone (including guests)** in **Roadmap > Settings**. Guest votes are matched by IP, so visitors behind one shared IP share one vote per item. If a guest voted earlier and guest voting was later turned off, their vote is no longer recognized.

## The vote count looks higher than before

- **Symptom:** After updating to 1.8.0, counts increased.
- **Cause:** Votes now count voters, not a net score. Downvotes used to reduce the number, and counts are recalculated on upgrade.
- **Fix:** None needed; the new number matches the actual number of voters.

## The suggestion form does not appear

- **Symptom:** No "Suggest a feature" button on the board.
- **Cause:** **User Suggestions** is off, or the visitor is not logged in (Free requires login).
- **Fix:** Enable **User Suggestions** in Settings and log in to test. The button is part of `[roadmap]`; there is no separate shortcode.

## An item shows as "Unknown" or sits under "Future - Later"

- **Symptom:** A new item has an "Unknown" status tab, or appears in the "Future - Later" group when you expected "Next".
- **Cause:** An item added in the admin has no status until you tick one. Items with a custom status also go to "Future - Later", because the Minimal layout groups only In Progress, Planned and a few similar slugs.
- **Fix:** Open the item, tick one status in the **Statuses** panel and click **Update**. See [Move an Item to a New Status](../admin-guide/06-changing-an-items-status.md).

## Voters did not get a status change email

- **Symptom:** You moved an item, such as "Dark theme", and voters report no email.
- **Cause:** **Enable Email Notifications** is off. Or the voters voted as guests, who have no account to email. Or they clicked the unsubscribe link in an earlier email. Or WP-Cron has not run yet, because emails send in the background, 50 at a time.
- **Fix:** Turn on **Enable Email Notifications** in **Roadmap > Settings > Notifications**. Check that your site gets regular visits so WP-Cron runs. See [Move an Item to a New Status](../admin-guide/06-changing-an-items-status.md#emails-sent-when-a-status-changes).

## A suggestion is not on the board

- **Symptom:** A member says they suggested "Support SSO with Okta" but visitors cannot see it.
- **Cause:** Suggestions stay Pending until you approve them, unless **Auto-approve user suggestions** is on.
- **Fix:** Go to **Roadmap > All Items**, click **Pending** and approve it. See [Review Pending Suggestions](../admin-guide/07-reviewing-pending-suggestions.md).

## A suggestion was rejected with a "too often" message

- **Symptom:** The form reports "You are doing that too often."
- **Cause:** Suggestions are limited to 5 per hour per user (per IP for guests).
- **Fix:** Wait for the window to pass. The submitted text stays in the form when a submission fails.
