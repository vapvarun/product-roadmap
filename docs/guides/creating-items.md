# Creating Roadmap Items

**Time to read: 8 minutes**

Learn how to create, edit, and organize roadmap items effectively.

## What You'll Learn

- Creating a roadmap item
- Understanding all fields
- Quick editing items
- Organizing with categories and statuses
- Setting priorities and progress

## Creating a New Item

### Start Creating

1. Go to **Roadmap > Add New Item**
2. You'll see the standard WordPress editor

### Required Fields

**Title** (Required)
- Keep under 60 characters
- Be specific and clear
- Use action verbs

Examples:
- ✅ Good: "Add dark mode support"
- ✅ Good: "Improve search speed"
- ❌ Bad: "Dark mode"
- ❌ Bad: "Search"

**Description** (Content Editor)
- Explain **why** this matters
- Describe the benefit to users
- 2-3 paragraphs minimum
- Use formatting (bold, lists, headings)

Example:
```
## Why This Feature?

Many users work late at night and find the bright interface
straining on their eyes. Adding a dark mode will:

- Reduce eye strain during night usage
- Save battery on mobile devices
- Match system preferences on modern devices

## What We'll Build

- System-aware dark mode (follows OS setting)
- Manual toggle in user preferences
- Optimized color palette for readability
```

### Taxonomy Fields

**Status** (Right Sidebar)

Select where this item is in your workflow:
- **Idea** - Just a thought, not confirmed
- **Under Review** - Team is evaluating
- **Planned** - Approved for development
- **In Progress** - Currently being built
- **Testing** - In quality assurance
- **Completed** - Done and deployed
- **Cancelled** - Won't be implemented

Default: "Planned"

**Category** (Right Sidebar)

What type of item is this:
- **Feature** - New functionality
- **Enhancement** - Improvement to existing
- **Bug Fix** - Fixing issues
- **Security** - Security updates
- **Performance** - Speed/optimization

You can select multiple categories.

**Product** (Right Sidebar)

Which product this belongs to:
- Free version: Only "Main" available
- Pro version: Multiple products

### Detail Fields

**Priority** (Meta Box)

How important is this:
- **Critical** - Must have, blocking other work
- **High** - Very important to users
- **Medium** - Important but not urgent
- **Low** - Nice to have

Tip: Don't make everything "Critical"!

**Progress** (Meta Box)

Completion percentage:
- Drag slider or type number (0-100%)
- Updates as work progresses
- Shows progress bar to users

Guidelines:
- 0% = Not started
- 25% = Early stage
- 50% = Half done
- 75% = Almost complete
- 100% = Finished (should be "Completed" status)

**Target Date** (Meta Box)

When you expect to finish:
- Click calendar icon
- Select date
- Optional but recommended
- Shows on item cards

Tips:
- Set realistic dates
- Add buffer time
- Update if timeline changes
- Use quarters (Q1 2024) for flexibility

**Start Date** (Meta Box)

When work begins:
- Useful for timeline planning
- Optional field

**End Date** (Meta Box)

Actual completion date:
- Set when item is done
- Helps track velocity
- Optional field

**Excerpt** (Below Content Editor)

Short summary (1-2 sentences):
- Shows on item cards
- If empty, auto-generated
- Write custom for better clarity

Example:
```
Enable dark color scheme that reduces eye strain and saves
battery life on mobile devices.
```

### Publishing

**Save Draft**
- Saves without publishing
- Useful for incomplete items

**Preview**
- See how it looks before publishing
- Opens in new tab

**Publish**
- Makes item visible on roadmap
- Sends to selected status

**Visibility**
- Public (default)
- Password protected
- Private (admins only)

## Editing Existing Items

### Quick Edit

Fast way to update basic fields:

1. Go to **Roadmap > All Items**
2. Hover over item title
3. Click **Quick Edit**

You can change:
- Title
- Status
- Category
- Priority
- Target date

Changes save immediately.

### Full Edit

For detailed changes:

1. Click item title
2. Edit any field
3. Click **Update** to save

## Managing Multiple Items

### Bulk Actions

Update many items at once:

1. **Roadmap > All Items**
2. Check boxes next to items
3. Select action from dropdown:
   - Edit (change status, category)
   - Move to Trash
4. Click **Apply**

Example: Move 5 completed items to "Completed" status

### Filtering Items

Find specific items:

**By Status**: Dropdown at top
- Shows only items in that status
- Example: See all "In Progress" items

**By Category**: Dropdown at top
- Filter by category
- Example: See all "Features"

**By Date**: Date filter
- Items from specific month

**Search**: Search box
- Find by keyword in title/content

### Sorting Items

Click column headers to sort:
- **Title** - Alphabetical
- **Status** - By status type
- **Category** - By category
- **Date** - By publish date
- **Votes** - By vote count (if enabled)

## Item Meta Information

When editing an item, you'll see statistics:

**Roadmap Statistics** (Meta Box)
- Total votes received
- Total comments
- View count
- Last updated date

This is read-only information.

## Best Practices

### Writing Titles

**Do:**
- Use action verbs
- Be specific
- Keep it short
- User-focused

**Don't:**
- Use jargon
- Be vague
- Make it too long
- Use internal code names

### Writing Descriptions

**Do:**
- Explain the "why"
- List benefits
- Use formatting
- Add mockups/screenshots
- Keep it scannable

**Don't:**
- Just describe technical details
- Write walls of text
- Assume user knowledge
- Skip the value proposition

### Setting Progress

**Update regularly:**
- Weekly for active items
- At major milestones
- When status changes

**Be honest:**
- Don't inflate progress
- Update if blocked
- Explain delays

### Managing Status

**Keep moving items:**
- Weekly status reviews
- Left to right progression
- Update on real changes
- Communicate status changes

**Status workflow:**
1. Idea → Under Review (evaluation)
2. Under Review → Planned (approved)
3. Planned → In Progress (started)
4. In Progress → Testing (in QA)
5. Testing → Completed (deployed)

Or skip directly to Cancelled if not proceeding.

## Common Workflows

### New Feature Request

1. Create item with Status: "Idea"
2. Gather votes and comments
3. Review with team
4. Move to "Under Review"
5. If approved → "Planned"
6. Add to sprint → "In Progress"
7. When done → "Testing"
8. After QA → "Completed"

### Bug Fix

1. Create item with Category: "Bug Fix"
2. Set Priority based on severity
3. Status: "Planned" (if prioritized)
4. Move to "In Progress" when fixing
5. Set Progress as you work
6. "Testing" when ready
7. "Completed" when verified

### User Suggestion

1. Submitted via suggestion form
2. Appears as Status: "Under Review"
3. Team evaluates feasibility
4. If accepted → edit and move to "Planned"
5. If rejected → "Cancelled" with explanation

## Deleting Items

### Move to Trash

1. Hover over item
2. Click **Trash**
3. Item goes to trash (not deleted)

### Restore from Trash

1. Click **Trash** tab at top
2. Hover over item
3. Click **Restore**

### Permanently Delete

1. Click **Trash** tab
2. Hover over item
3. Click **Delete Permanently**
4. **Warning**: Cannot be undone!

## Custom Fields

Want to add more data?

### Using Custom Fields

1. Edit item
2. Scroll to **Custom Fields** meta box
3. Click **Enter new**
4. Add name and value

Examples:
- `estimated_hours`: "40"
- `team_member`: "John"
- `release_version`: "2.5.0"

Note: Custom fields don't show on frontend without custom code.

## Keyboard Shortcuts

Speed up your workflow:

- **Ctrl/Cmd + S** - Save draft
- **Ctrl/Cmd + Enter** - Publish
- **Tab** - Move between fields
- **Shift + Alt + D** - Delete item

## Next Steps

- **[Manage Statuses & Categories](statuses-categories.md)** - Customize workflow
- **[Display Your Roadmap](displaying-roadmap.md)** - Show items to users
- **[Enable User Engagement](voting-comments.md)** - Get feedback

## Need Help?

- Can't publish? Check permissions
- Missing fields? Check screen options (top right)
- Item not showing? Check status and visibility
- Lost changes? Check revisions

---

← [Getting Started](getting-started.md) | **[Statuses & Categories](statuses-categories.md)** →