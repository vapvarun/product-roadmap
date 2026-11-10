# Notifications

Go to **Roadmap > Settings > Notifications** to control who gets emailed about roadmap activity, and which activity triggers an email.

## Email configuration

| Field | Description |
|---|---|
| Enable Email Notifications | Master switch for admin notification emails. |
| Notification Email Addresses | A textarea where you enter one email address per line. Every address listed receives notification emails. Leave it empty to fall back to the site's admin email. |

Because this field accepts multiple addresses, you can route notifications to a whole team (for example, one product manager and one support lead) instead of a single inbox.

## Per-type toggles

Three toggles control which events send a notification, and each one is honored independently:

- **Notify on new vote**
- **Notify on new comment**
- **Notify on new suggestion**

Turn off any toggle you don't want to be paged for. Votes can be noisy on an active roadmap, so many site owners leave vote notifications off and keep comment and suggestion notifications on.

## Status-change emails are separate

The **Notification Email Addresses** list above is for admin-facing alerts only. When a roadmap item's status changes, Product Roadmap separately emails the people who voted or commented on that item, letting them know it moved forward. Those emails include a one-click unsubscribe link. They are sent only while **Enable Email Notifications** (above) is on: switching that master switch off stops them too, but the individual toggles below it do not affect them.

## Changelog Types

The Notifications tab sits alongside a **Changelog Types** tab at **Roadmap > Settings > Changelog Types**, where you manage the change-type labels (New, Improved, Fixed, and so on) used when writing changelog entries. See [Changelog](../admin-guide/04-changelog.md) for how those types are used.

## Next steps

- [Statuses and Categories](03-statuses-and-categories.md)
- [Managing Roadmap Items](../admin-guide/01-managing-roadmap-items.md)
