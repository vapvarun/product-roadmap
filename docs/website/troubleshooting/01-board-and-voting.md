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

## A suggestion was rejected with a "too often" message

- **Symptom:** The form reports "You are doing that too often."
- **Cause:** Suggestions are limited to 5 per hour per user (per IP for guests).
- **Fix:** Wait for the window to pass. The submitted text stays in the form when a submission fails.
