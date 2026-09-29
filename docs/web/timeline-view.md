# Timeline View

> **Pro Feature** - This feature requires the [Product Roadmap Pro](https://wbcomdesigns.com/downloads/product-roadmap-pro/) addon.

Visualize your roadmap as a horizontal timeline. Plan releases and communicate schedules by target date.

---

## What is Timeline View?

Timeline view lays your roadmap items out on a horizontal calendar by their target date, so you can see what's coming and when at a glance.

---

### Timeline scale

**Roadmap > Settings > Templates > Timeline Scale** sets how far ahead the timeline looks from today: **Month** (default), **Quarter** or **Year**. Items whose target date falls inside that window are shown, grouped by status (in progress, then upcoming); items with no target date are always included.

## Enabling Timeline View

### Requirements

1. Pro addon installed and activated
2. Items have a target date set

### Set Item Dates

For items to appear on the timeline:
1. Edit a roadmap item
2. Set the **Target Date** field
3. Update the item

Items without a target date won't appear on the timeline.

---

## Displaying Timeline

![Timeline View](images/frontend-poll-roadmap-timeline.png)

### Shortcode

```
[roadmap template="timeline"]
```

### With Product Filter

```
[roadmap template="timeline" product="mobile-app"]
```

### With Status Filter

```
[roadmap template="timeline" status="in-progress"]
```

### With a Date Range

```
[roadmap template="timeline" date_from="2026-01-01" date_to="2026-06-30"]
```

Shows only items with a target date in that range. `date_from` and `date_to` require the Pro addon.

---

## Timeline Elements

### Item Bars

Each roadmap item appears positioned at its target date, colored by status.

### Status Colors

| Status | Color |
|--------|-------|
| Under Review | Gray |
| Planned | Blue |
| In Progress | Yellow/Orange |
| Complete | Green |

Colors match your status configuration.

---

## Shortcode Parameters

### Full Example

```
[roadmap
    template="timeline"
    product="mobile-app"
    status="planned"
    date_from="2026-01-01"
    date_to="2026-12-31"
]
```

### Parameters

Timeline uses the same options as every other template - see the [full reference](display-options.md#complete-shortcode-reference). The ones you'll use most on a timeline:

| Parameter | Options | Notes |
|-----------|---------|-------|
| `template` | `timeline` | Required for the timeline layout |
| `status` | one status slug | Visitors can still use the filter bar unless you hide it |
| `priority` | `low`, `medium`, `high`, `critical` | Pro |
| `date_from` / `date_to` | `YYYY-MM-DD` | Pro. Narrows the timeline to a date range |

---

## Use Cases

### Release Planning

**Scenario**: Plan your next quarter's releases.

```
[roadmap template="timeline" status="planned" date_from="2026-01-01" date_to="2026-03-31"]
```

Shows planned work with a target date in Q1.

### Product-Specific Timeline

**Scenario**: Show mobile app timeline only.

```
[roadmap template="timeline" product="mobile-app"]
```

Filtered timeline for one product.

---

## Best Practices

### Date Accuracy

- Set realistic target dates
- Update dates when plans change

### Not Too Crowded

- Filter to relevant statuses
- Narrow the range with `date_from`/`date_to`
- Consider multiple timeline pages for different views

### Regular Updates

- Review the timeline weekly
- Update target dates as plans change
- Communicate changes to stakeholders

---

## Troubleshooting

### Items not appearing

- Check that items have a target date set
- Verify the date falls within `date_from`/`date_to` if you set one
- Ensure items are published (not drafts)

### Timeline not rendering

- Check for JavaScript errors in browser console
- Ensure Pro addon is active
- Try disabling other plugins temporarily

### Performance issues

- Narrow the date range with `date_from`/`date_to`
- Filter to fewer statuses

---

## Get Timeline View

Timeline View is available with Product Roadmap Pro.

**Included in Pro:**
- Horizontal timeline layout
- Target-date filtering
- Status colors

[Get Product Roadmap Pro →](https://wbcomdesigns.com/downloads/product-roadmap-pro/)

---

## Next Steps

| Goal | Guide |
|------|-------|
| Track engagement | [Analytics](analytics.md) | Pro |
| Manage multiple products | [Multi-Product](multi-product.md) | Pro |
| Bulk data management | [Import & Export](import-export.md) | Pro |
