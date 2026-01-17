# Drag & Drop Management

> **Pro Feature** - This feature requires the [Product Roadmap Pro](https://wbcomdesigns.com/downloads/product-roadmap-pro/) addon.

Move roadmap items between statuses by dragging them. A visual, intuitive way to manage your workflow.

---

## What is Drag & Drop?

Instead of editing each item to change its status, simply drag it:

1. See all items in a kanban board
2. Grab an item card
3. Drag to a different status column
4. Drop to update

Changes save automatically. No page refresh needed.

---

## Enabling Drag & Drop

### Turn On the Feature

1. Go to **Roadmap > Settings > Pro Features**
2. Enable "Drag & Drop"
3. Save changes

### Who Can Drag?

By default, only administrators can drag items. Configure permissions:

1. Go to **Roadmap > Settings > Pro Features**
2. Find "Drag & Drop Permissions"
3. Select allowed user roles
4. Save changes

| Role | Default |
|------|---------|
| Administrator | Enabled |
| Editor | Disabled |
| Author | Disabled |
| Subscriber | Disabled |

---

## Using Drag & Drop

### On the Frontend

1. View your roadmap page with kanban template
2. Log in as a user with drag permissions
3. Hover over an item card
4. Click and hold to grab
5. Drag to the target status column
6. Release to drop

### Visual Feedback

- **Grabbing**: Cursor changes to grab hand
- **Dragging**: Card follows cursor, column highlights
- **Valid drop**: Column shows acceptance indicator
- **Invalid drop**: Returns to original position

### Auto-Save

When you drop an item:
1. Status updates immediately in the database
2. Card stays in new position
3. No save button needed
4. Activity logged for audit

---

## Kanban Template

Drag & drop works best with the kanban template:

```
[roadmap template="kanban"]
```

### Column Layout

Each status becomes a column:
- Under Review | Planned | In Progress | Complete

Items appear as cards in their status column.

### With Filtering

Combine with filters:

```
[roadmap template="kanban" product="mobile-app"]
```

Drag & drop still works on filtered views.

---

## Reordering Items

### Within a Column

Drag items to reorder within the same status:
1. Grab an item
2. Move up or down within the column
3. Drop to set new position

Order is preserved and saved.

### Custom Sort Order

Reordering sets a custom sort order that:
- Persists across page loads
- Works alongside other sorting
- Can be reset in settings

---

## Permissions

### Role-Based Access

Control who can drag items:

| Permission Level | Who Can Drag |
|------------------|--------------|
| Administrators only | Site admins |
| Editors and above | Editors + admins |
| Authors and above | Authors + editors + admins |
| All logged-in users | Anyone logged in |

### Frontend vs Admin

- **Frontend**: Controlled by permission settings
- **Admin area**: Always available to admins

### Why Restrict?

- Prevent accidental changes
- Maintain workflow integrity
- Limit to responsible team members
- Audit trail accuracy

---

## Mobile Support

Drag & drop works on touch devices:

### Touch Gestures

1. **Tap and hold** to grab an item
2. **Drag with finger** to move
3. **Release** to drop

### Mobile Considerations

- Larger touch targets for easy grabbing
- Smooth scrolling while dragging
- Visual feedback on touch
- Works on tablets and phones

---

## Shortcode Parameters

### Enable Drag & Drop

```
[roadmap template="kanban" draggable="true"]
```

### Full Example

```
[roadmap
    template="kanban"
    draggable="true"
    status="under-review,planned,in-progress,complete"
    product="mobile-app"
]
```

### Parameters

| Parameter | Options | Default |
|-----------|---------|---------|
| `draggable` | true, false | true (if enabled in settings) |
| `template` | kanban | (required for drag) |

---

## Use Cases

### Sprint Management

**Scenario**: Daily standup, moving items as work progresses.

1. Display kanban filtered to current sprint
2. During standup, drag completed items to "Done"
3. Drag new items into "In Progress"
4. Team sees updates in real-time

### Client Reviews

**Scenario**: Client meeting to review and prioritize.

1. Give client appropriate permissions
2. Share roadmap page
3. Client drags items to approve/prioritize
4. Changes save automatically

### Personal Task Management

**Scenario**: Individual tracking their work.

```
[roadmap template="kanban" assignee="current_user"]
```

Drag personal tasks through workflow stages.

---

## Activity Logging

### What's Tracked

Each drag action logs:
- Item ID
- Old status
- New status
- User who made change
- Timestamp

### Viewing Activity

Activity shows in:
- Analytics activity feed (Pro)
- WordPress activity log (if using logging plugin)
- Item revision history

---

## Best Practices

### Clear Workflow

- Use distinct, meaningful status names
- Limit to 4-6 statuses for easy dragging
- Left-to-right flow (new → complete)

### Team Training

- Explain what dragging does
- Set clear guidelines for status changes
- Review changes periodically

### Combine with Comments

When moving items:
- Add a comment explaining why
- Helps team understand context
- Creates better history

---

## Troubleshooting

### Can't drag items

**Check permissions:**
- Are you logged in?
- Does your role have drag access?
- Is drag enabled in settings?

**Check template:**
- Using kanban template?
- Shortcode has `draggable="true"`?

### Drag doesn't save

- Check browser console for JavaScript errors
- Verify REST API is accessible
- Try disabling caching plugins
- Check for plugin conflicts

### Mobile drag not working

- Ensure touch events are supported
- Try tap and hold (not quick tap)
- Check if device has touch support
- Try different browser

### Items snap back

- JavaScript error occurred
- REST API returned error
- Check error messages in console
- Verify item still exists

---

## Get Drag & Drop

Drag & Drop is available with Product Roadmap Pro.

**Included in Pro:**
- Visual status management
- Sortable.js integration
- Smooth animations
- Permission controls
- REST API updates
- Mobile touch support

[Get Product Roadmap Pro →](https://wbcomdesigns.com/downloads/product-roadmap-pro/)

---

## Next Steps

| Goal | Guide |
|------|-------|
| Track engagement | [Analytics](analytics.md) | Pro |
| Visual timeline | [Timeline View](timeline-view.md) | Pro |
| Bulk data management | [Import & Export](import-export.md) | Pro |
