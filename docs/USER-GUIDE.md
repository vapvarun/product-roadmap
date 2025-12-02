# WordPress Product Roadmap Plugin - User Guide

## Table of Contents
- [Introduction](#introduction)
- [Installation & Setup](#installation--setup)
- [Dashboard Overview](#dashboard-overview)
- [Creating & Managing Roadmap Items](#creating--managing-roadmap-items)
- [Categories & Statuses](#categories--statuses)
- [Product Management](#product-management)
- [Changelog Feature](#changelog-feature-v130) (NEW in 1.3.0)
- [Displaying Roadmaps on Frontend](#displaying-roadmaps-on-frontend)
- [User Engagement Features](#user-engagement-features)
- [Settings Configuration](#settings-configuration)
- [Advanced Features](#advanced-features)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Introduction

The WordPress Product Roadmap Plugin helps you create and manage product roadmaps directly on your WordPress website. Share your product vision, collect user feedback through voting and comments, and keep your community engaged with your development plans.

### What You Can Do

**For Product Owners:**
- Create and organize roadmap items with statuses and categories
- Display beautiful roadmaps on any page using shortcodes
- Collect user votes to prioritize features
- Enable community discussions with comments
- Accept feature suggestions from users
- Track progress with percentage indicators
- Set target dates and timelines
- Manage multiple products (with Pro addon)

**For Users:**
- Vote on features they want
- Comment on roadmap items
- Submit feature suggestions
- Track what's being built
- Stay informed about product direction

## Installation & Setup

### Automatic Installation

1. Log into WordPress admin
2. Go to **Plugins > Add New**
3. Search for "Product Roadmap"
4. Click **Install Now** on the Product Roadmap plugin
5. Click **Activate** once installed

### Manual Installation

1. Download the plugin zip file from WordPress.org
2. Go to **Plugins > Add New > Upload Plugin**
3. Click **Choose File** and select the zip
4. Click **Install Now**
5. Click **Activate Plugin**

### Initial Setup Wizard

After activation, a setup wizard automatically appears to help you configure:

**Step 1: Product Name**
- Enter your product name (e.g., "My Awesome App")
- Customize item labels (default: "Feature" / "Features")
- This appears in titles and throughout the interface

**Step 2: Categories**
- Default categories are created: Feature, Enhancement, Bug Fix, Security, Performance
- You can add custom categories or remove defaults
- Categories help organize different types of items

**Step 3: Statuses**
- Default statuses: Idea, Under Review, Planned, In Progress, Testing, Completed, Cancelled
- Customize your workflow by adding/removing statuses
- Statuses show development stage

**Step 4: Settings**
- Enable voting (let users vote on items)
- Enable comments (let users discuss items)
- Enable suggestions (let users submit feature ideas)
- Configure notification emails

**Step 5: Demo Content (Optional)**
- Import sample roadmap items to see how it works
- Great for testing before creating real items
- Can delete demo items anytime

**Click "Finish Setup"** when done. You can always change these settings later.

### What Gets Created

After setup:
- **Menu**: "Roadmap" appears in WordPress admin sidebar
- **Database tables**: For storing votes and settings
- **Taxonomies**: Categories, Statuses, Products (taxonomy-based)
- **Default product**: "Main" product is created
- **Settings**: Initial configuration is saved

## Dashboard Overview

Access the dashboard at **Roadmap > Dashboard** in WordPress admin.

### Dashboard Widgets

**Quick Stats**
- Total roadmap items
- Items by status (Planned, In Progress, Completed, etc.)
- Total votes received
- Recent activity

**Recent Items**
- Shows latest 5 roadmap items
- Quick links to edit
- Status indicators

**Popular Items**
- Top voted items
- Helps identify community priorities
- Click to view/edit

**Activity Feed**
- Recent votes
- New comments
- Feature suggestions
- Status changes

### Top Navigation

The Roadmap menu includes:
- **Dashboard**: Overview and quick stats
- **All Items**: View and manage all roadmap items
- **Add New Item**: Create a new roadmap item
- **Statuses**: Manage status taxonomy
- **Categories**: Manage category taxonomy
- **Products**: Manage products (shown if Pro addon active)
- **Settings**: Configure plugin options

## Creating & Managing Roadmap Items

### Creating a New Item

1. Go to **Roadmap > Add New Item**

2. **Fill in Item Details:**

   **Title** (Required)
   - Clear, concise feature name
   - Example: "Dark Mode Support"

   **Description** (Content Editor)
   - Detailed explanation of the feature
   - Use formatting, images, videos
   - Explain why this matters
   - Example: "Add a dark color scheme that reduces eye strain..."

   **Excerpt** (Optional)
   - Short summary (1-2 sentences)
   - Shows in board view cards
   - If left empty, auto-generated from content

3. **Assign Taxonomy:**

   **Status** (Right sidebar)
   - Select current development stage
   - Default: "Planned"
   - Choose from: Idea, Under Review, Planned, In Progress, Testing, Completed, Cancelled

   **Category** (Right sidebar)
   - Select item type
   - Choose from: Feature, Enhancement, Bug Fix, Security, Performance
   - Can select multiple categories

   **Product** (Right sidebar)
   - Assign to product (default: "Main")
   - With Pro: Can assign to multiple products

4. **Set Additional Fields:**

   **Priority** (Meta box)
   - Low, Medium, High, Critical
   - Helps internal prioritization

   **Progress** (Meta box)
   - Percentage complete (0-100%)
   - Updates as work progresses
   - Shows progress bar on frontend

   **Target Date** (Meta box)
   - Expected completion date
   - Click calendar icon to select
   - Optional but recommended

   **Start Date** (Meta box)
   - When work begins
   - Useful for timeline planning

   **End Date** (Meta box)
   - Actual completion date
   - Set when item is completed

   **Featured Image** (Right sidebar)
   - Optional image for the item
   - Shows in certain views
   - Recommended size: 1200x630px

5. **Publish**
   - Click **Publish** to make live
   - Or **Save Draft** to work on later
   - **Preview** to see how it looks

### Managing Existing Items

**View All Items**
1. Go to **Roadmap > All Items**
2. See list of all roadmap items with:
   - Title
   - Status
   - Category
   - Votes count
   - Comments count
   - Date

**Filter Items**
- **By Status**: Dropdown at top to filter
- **By Category**: Dropdown at top
- **By Date**: Date filter at top
- **Search**: Search box for keywords

**Bulk Actions**
1. Check boxes next to items
2. Select action from dropdown:
   - **Edit**: Mass edit selected items
   - **Move to Trash**: Delete multiple items
3. Click **Apply**

**Quick Edit**
Hover over item title and click **Quick Edit** to quickly change:
- Title
- Status
- Category
- Priority
- Target date

**Individual Edit**
- Click item title to edit full details
- Change any field
- Add/remove categories
- Update progress
- Click **Update** to save

**Trash/Delete**
- Hover and click **Trash** to move to trash
- Go to **Trash** tab to permanently delete or restore

### Item Meta Boxes

When editing an item, you'll see meta boxes:

**Roadmap Item Details**
- Priority selector (Low/Medium/High/Critical)
- Progress slider (0-100%)
- Target Date picker
- Start Date picker
- End Date picker

**Roadmap Statistics** (Read-only)
- Total votes
- Total comments
- View count
- Last updated date

**Publish Box**
- Save Draft
- Preview
- Status (Published/Draft/Pending)
- Visibility (Public/Private/Password Protected)
- Publish immediately or schedule

## Categories & Statuses

### Understanding Taxonomies

Categories and Statuses are taxonomies that organize your roadmap items.

**Categories** = Type of item (what it is)
**Statuses** = Development stage (where it's at)

### Managing Statuses

Access at **Roadmap > Statuses**

**Default Statuses:**
1. **Idea** - Suggested but not confirmed
2. **Under Review** - Being evaluated by team
3. **Planned** - Approved, scheduled for development
4. **In Progress** - Actively being developed
5. **Testing** - In quality assurance phase
6. **Completed** - Finished and deployed
7. **Cancelled** - Won't be implemented

**Add New Status:**
1. Click **Roadmap > Statuses**
2. Fill in form:
   - **Name**: Display name (e.g., "In Development")
   - **Slug**: URL-friendly (e.g., "in-development")
   - **Description**: Optional explanation
   - **Color**: Pick a color for board view
   - **Icon**: Dashicon class (optional)
   - **Order**: Display order (lower = first)
3. Click **Add New Status**

**Edit Status:**
- Click status name
- Modify fields
- Click **Update**

**Delete Status:**
- Hover over status
- Click **Delete**
- Items assigned to it will become "Uncategorized"
- **Warning**: Deleting affects all items with that status

**Best Practices for Statuses:**
- Keep 5-8 statuses for clarity
- Use clear, action-oriented names
- Order logically (left to right flow)
- Choose distinct colors for visual clarity

### Managing Categories

Access at **Roadmap > Categories**

**Default Categories:**
1. **Feature** - New functionality
2. **Enhancement** - Improvements to existing features
3. **Bug Fix** - Fixing issues
4. **Security** - Security updates
5. **Performance** - Speed/optimization improvements

**Add New Category:**
1. Click **Roadmap > Categories**
2. Fill in form:
   - **Name**: Display name (e.g., "Integration")
   - **Slug**: URL-friendly (e.g., "integration")
   - **Description**: What this category includes
   - **Icon**: Dashicon class (optional)
3. Click **Add New Category**

**Edit/Delete Category:**
- Same process as statuses
- Items keep category even after deletion (stored in database)

**Category Usage Tips:**
- Use categories to filter items
- Items can have multiple categories
- Create categories based on your product areas
- Examples: Mobile, Web, API, Design, Infrastructure

## Product Management

Products let you manage separate roadmaps. The free version includes basic product support via taxonomy.

### Default Product

- **Main** product is created automatically
- Use for single-product roadmaps
- Required parameter in shortcodes

### Viewing Products

Go to **Roadmap > Products** to:
- See all products
- View item count per product
- See shortcode for each product

### Adding Products

1. Go to **Roadmap > Products**
2. Enter product details:
   - **Name**: Product name (e.g., "Mobile App")
   - **Slug**: URL-friendly (e.g., "mobile-app")
   - **Description**: Product description
3. Click **Add New Product**

### Assigning Items to Products

When editing an item:
1. Find **Product** taxonomy box (right sidebar)
2. Check product(s) this item belongs to
3. Can select multiple products
4. Click **Update** to save

### Using Products

Display specific product roadmap:
```
[roadmap product="mobile-app"]
```

Filter items by product in admin:
- Dropdown filter above item list
- Select product to view only its items

## Changelog Feature (v1.3.0)

Document your product releases and updates with the new Changelog feature.

### What is Changelog?

The Changelog lets you document releases, updates, and changes to your product over time. It's separate from the roadmap but complements it by showing what's been completed.

### Creating a Changelog Entry

1. Go to **Roadmap > Changelog > Add New**
2. Fill in details:
   - **Title**: Release name (e.g., "Version 2.0 Release")
   - **Version**: Version number (e.g., "2.0.0")
   - **Release Date**: When it was released
   - **Release Type**: Major, Minor, or Patch
   - **Content**: Detailed release notes

3. Add change items:
   - **New**: New features added
   - **Improved**: Enhancements to existing features
   - **Fixed**: Bug fixes
   - **Changed**: Changes to existing behavior
   - **Deprecated**: Features being phased out
   - **Removed**: Features that were removed
   - **Security**: Security-related updates

4. Assign to **Product** (optional)
5. Click **Publish**

### Displaying Changelog

**Shortcode:**
```
[roadmap_changelog product="main"]
```

**Parameters:**
- `product` - Filter by product slug
- `limit` - Number of entries to show
- `order` - ASC or DESC (default: DESC)

**Example - Show last 5 releases:**
```
[roadmap_changelog product="main" limit="5"]
```

### Changelog Archive

Visit `/changelog/` to see all changelog entries, or `/changelog/product-slug/` for product-specific changelogs.

### Change Type Badges

Each change item displays with a colored badge:
- **New** - Green badge
- **Improved** - Blue badge
- **Fixed** - Orange badge
- **Changed** - Purple badge
- **Deprecated** - Yellow badge
- **Removed** - Red badge
- **Security** - Dark red badge

## Displaying Roadmaps on Frontend

### Basic Shortcode

**Minimum required shortcode:**
```
[roadmap product="main"]
```

The `product` parameter is mandatory. Use "main" for the default product.

### Copy Shortcode Feature (v1.1.0)

**Easy shortcode copying:**
- Look for the copy button (📋) next to shortcodes in admin
- Click to copy instantly to clipboard
- Visual feedback confirms successful copy
- Available in:
  - Product listings
  - Category pages
  - Settings page
  - Quick reference areas

### Shortcode Parameters

**Full example with all parameters:**
```
[roadmap
    product="main"
    template="board"
    status="planned,in-progress"
    category="feature"
    limit="50"
    orderby="votes"
    order="desc"
]
```

**Available Parameters:**

**product** (Required)
- Which product to display
- Value: product slug (e.g., "main", "mobile-app")
- Example: `product="main"`

**template** (Optional)
- Display style
- Default: "board"
- Free version: "board", "ideabox" (NEW in 1.3.0)
- Pro version: "board", "ideabox", "timeline", "list", "cards", "minimal"
- Example: `template="board"`

**Idea Box Template (v1.3.0)**
- Use `template="ideabox"` for a focused idea collection view
- Features prominent upvote buttons and sorting options
- Perfect for collecting user feedback and feature requests

**status** (Optional)
- Filter by status
- Single: `status="planned"`
- Multiple: `status="planned,in-progress,completed"`
- Comma-separated status slugs

**category** (Optional)
- Filter by category
- Single: `category="feature"`
- Multiple: `category="feature,enhancement"`
- Comma-separated category slugs

**limit** (Optional)
- How many items to show
- Default: 100
- Example: `limit="20"`

**orderby** (Optional)
- Sort items by field
- Values: "date", "title", "votes", "modified"
- Default: "date"
- Example: `orderby="votes"`

**order** (Optional)
- Sort direction
- Values: "ASC" (ascending), "DESC" (descending)
- Default: "DESC"
- Example: `order="desc"`

**mode** (Optional)
- Display mode
- Values: "view" (default), "manage" (Pro - enables drag & drop)
- Example: `mode="view"`

### Display Examples

**Show all items from main product:**
```
[roadmap product="main"]
```

**Show only planned features:**
```
[roadmap product="main" status="planned" category="feature"]
```

**Show top voted items:**
```
[roadmap product="main" orderby="votes" order="desc" limit="10"]
```

**Show in-progress and testing items:**
```
[roadmap product="main" status="in-progress,testing"]
```

### Adding to Pages

**Gutenberg Editor:**
1. Add a **Shortcode** block
2. Paste shortcode
3. Preview and publish

**Classic Editor:**
1. Switch to Text mode
2. Paste shortcode where you want roadmap
3. Switch back to Visual mode to see placeholder
4. Publish

**Page Builders:**
- **Elementor**: Use Shortcode widget
- **WPBakery**: Use Raw HTML element
- **Beaver Builder**: Use HTML module
- **Divi**: Use Code module

### Widget Areas

Add roadmap to sidebar or footer:
1. Go to **Appearance > Widgets**
2. Add **Custom HTML** widget
3. Paste shortcode in content
4. Save

### Menu Items

Create menu link to roadmap page:
1. Create page with roadmap shortcode
2. **Appearance > Menus**
3. Add page to menu
4. Save menu

## User Engagement Features

### Voting System

**How Voting Works:**
- Users click vote button on items
- One vote per user per item
- Votes are tracked and displayed
- Helps prioritize features

**Enabling Voting:**
1. Go to **Roadmap > Settings > General**
2. Check **Enable Voting**
3. Choose **Require Login for Voting** (recommended)
4. Save settings

**Vote Display:**
- Vote count shown on item cards
- Items can be sorted by votes
- Admins see who voted (in meta box when editing item)

**Guest Voting:**
- If enabled, anonymous users can vote
- Uses cookies/IP to prevent duplicate votes
- Less reliable than logged-in voting
- Enable in Settings > Advanced

**Removing Votes:**
- Users can click vote button again to unvote
- Admins can manually delete votes from item edit screen

### Comments & Discussions

**Enabling Comments:**
1. Go to **Roadmap > Settings > General**
2. Check **Enable Comments**
3. Configure comment settings:
   - Require login to comment
   - Moderate comments
   - Email notifications for new comments
4. Save settings

**Comment Features:**
- Standard WordPress comments
- Threaded replies
- Moderation tools
- Spam protection (if Akismet active)
- Email notifications

**Managing Comments:**
- View all comments at **Comments** in WordPress admin
- Approve/unapprove comments
- Reply to comments
- Mark as spam or trash

**Comment Display:**
- Shows on single item page
- Comment count on item cards
- Click to view all comments
- Standard WordPress comment form

### Feature Suggestions

Let users submit feature ideas that become roadmap items.

**Enabling Suggestions:**
1. Go to **Roadmap > Settings > General**
2. Check **Enable Feature Suggestions**
3. Configure:
   - **Require Login**: Make users log in to suggest
   - **Auto-approve** (v1.1.0): Automatically publish suggestions without review
   - **Default Status**: Status for new suggestions (default: "Under Review")
   - **Notification Email**: Get notified of new suggestions
4. Save settings

**Auto-Approval Feature (v1.1.0):**
- **Enable**: Toggle "Auto-approve user suggestions" in settings
- **When Enabled**: Suggestions publish immediately
- **Default Status**: Applied automatically (e.g., "Planned" or "Idea")
- **No Manual Review**: Bypasses moderation queue
- **Use Cases**: Trusted communities, internal teams
- **Caution**: Enable only if spam is not a concern

**How It Works:**
1. **Suggest Button** appears on roadmap
2. User clicks and fills form:
   - Title (required)
   - Description (required)
   - Category (optional)
   - Email (if guest submission allowed)
3. Submission creates draft item with "Under Review" status
4. Admin receives email notification

**Managing Suggestions:**
1. Go to **Roadmap > All Items**
2. Filter by "Under Review" status
3. Review each suggestion:
   - **Approve**: Change status to "Planned" or "Idea"
   - **Edit**: Improve title/description
   - **Reject**: Move to trash or "Cancelled"
4. Suggestions are now regular roadmap items

**Suggestion Notifications:**
- Admin gets email for each suggestion
- Email includes title, description, submitter
- Configure email address in settings
- Can disable notifications

**Tips for Suggestions:**
- Review regularly (weekly)
- Thank submitters (comment on item)
- Explain decisions (why accepted/rejected)
- Merge duplicate suggestions
- Keep submitters informed of progress

## Settings Configuration

Access at **Roadmap > Settings**

### General Settings Tab

**Product Information**
- **Product Name**: Your product name (appears in titles)
- **Item Singular Label**: What to call one item (default: "Feature")
- **Item Plural Label**: What to call multiple items (default: "Features")

**Display Options**
- **Items Per Page**: How many items to show (default: 100)
- **Default Status**: Initial status for new items (default: "Planned")
- **Enable Dark Mode**: Toggle dark theme support

**User Interaction**
- **Enable Voting**: Let users vote on items
- **Enable Comments**: Let users comment on items
- **Enable Suggestions**: Let users submit feature ideas
- **Require Login for Suggestions**: Make users log in to suggest

**Notifications**
- **Admin Email**: Where to send notifications
- **Notify on New Vote**: Email when someone votes (can be noisy)
- **Notify on New Comment**: Email on new comments
- **Notify on New Suggestion**: Email on feature suggestions

### Display Settings Tab

**Board View Settings**
- **Status Column Order**: Drag to reorder status columns
- **Items Per Status**: Limit items shown per column
- **Show Vote Count**: Display vote numbers
- **Show Comment Count**: Display comment numbers
- **Show Progress Bar**: Display completion percentage

**Item Card Display**
- **Show Excerpt**: Display item excerpt on cards
- **Show Category Badge**: Show category label
- **Show Target Date**: Display target date on cards
- **Show Priority Badge**: Display priority level
- **Card Height**: Auto or fixed height

**Colors & Styling**
- **Primary Color**: Main theme color (buttons, links)
- **Status Colors**: Color for each status column
- **Custom CSS**: Add your own CSS for advanced styling

### Advanced Settings Tab

**Performance**
- **Cache Duration**: How long to cache roadmap data (seconds)
- **Enable Object Cache**: Use WordPress object cache if available
- **Lazy Load Images**: Load images as user scrolls

**Permissions**
- **Who Can View Roadmap**: Everyone, Logged-in users, or Specific roles
- **Who Can Vote**: Everyone, Logged-in users, or Specific roles
- **Who Can Comment**: Based on WordPress comment settings
- **Who Can Submit Suggestions**: Everyone or Logged-in users

**Guest Voting**
- **Allow Guest Voting**: Let non-logged-in users vote
- **Guest Vote Tracking**: Cookie or IP address
- **Guest Vote Expiry**: How long guest votes last (days)

**Developer Options**
- **REST API Access**: Enable/disable REST API endpoints
- **Webhook URL**: Send data to external services (Pro feature)
- **Debug Mode**: Show debug information (for troubleshooting)

### Notifications Tab

**Email Templates**
Customize notification emails:

**New Suggestion Email**
- **Subject**: Email subject line
- **Body**: Email content (supports variables)
- **Variables**: {title}, {description}, {submitter}, {date}

**New Comment Email**
- **Subject**: Email subject line
- **Body**: Email content
- **Variables**: {item_title}, {commenter}, {comment}, {link}

**New Vote Email** (if enabled)
- **Subject**: Email subject line
- **Body**: Email content
- **Variables**: {item_title}, {voter}, {total_votes}

**Email Settings**
- **From Name**: Sender name (default: site name)
- **From Email**: Sender email address
- **Reply-To**: Where replies go

### Product Settings Tab

**Per-Product Configuration** (Pro Feature)
- Configure settings for each product separately
- Different statuses per product
- Different categories per product
- Product-specific colors and styling

**Free Version:**
- Uses global settings for all products
- One set of statuses/categories for all

## Advanced Features

### Progress Tracking

**Setting Progress:**
1. Edit roadmap item
2. Find **Progress** field in meta box
3. Drag slider or enter percentage (0-100%)
4. Click **Update**

**Progress Display:**
- Shows as progress bar on item cards
- Green bar fills based on percentage
- Helps users see what's nearly done

**Progress Tips:**
- Update regularly (weekly or at milestones)
- 0% = Not started
- 25% = Started, early stage
- 50% = Half done
- 75% = Mostly done
- 100% = Completed (change status to "Completed")

### Target Dates

**Setting Target Date:**
1. Edit roadmap item
2. Click **Target Date** field
3. Select date from calendar
4. Click **Update**

**Date Display:**
- Shows on item cards if enabled
- Format: Based on WordPress settings
- Past dates highlighted in red
- Upcoming dates in green

**Date Tips:**
- Set realistic dates
- Update if timeline changes
- Use ranges (Q1 2024, etc.) for flexibility
- Communicate delays to users

### Priority Levels

**Setting Priority:**
1. Edit roadmap item
2. Select from **Priority** dropdown:
   - **Low**: Nice to have
   - **Medium**: Important but not urgent
   - **High**: Critical for users
   - **Critical**: Must have, blocking
3. Click **Update**

**Priority Use:**
- Internal prioritization
- Can show priority badges on frontend (if enabled in settings)
- Sort items by priority
- Different colors per priority level

### Custom Fields

**Adding Custom Data:**
- Use **Custom Fields** meta box when editing item
- Click **Add New Custom Field**
- Enter name and value
- Examples: estimated_hours, team_member, release_version

**Displaying Custom Fields:**
- Requires custom code in theme
- Access via `get_post_meta($post_id, 'field_name', true)`
- Can display in custom templates

### Single Item Pages

Each roadmap item has its own page with:
- Full description
- All metadata
- Vote button
- Comment section
- Share buttons (if enabled)
- Related items (if configured)

**StackOverflow-Style Voting (v1.3.0)**

Single item pages now feature a redesigned voting UI:
- Voting section prominently displayed on the left side
- Large vote count with "votes" label
- Up arrow button to add votes (green hover)
- Down arrow button to remove existing votes (orange hover)
- Breadcrumb navigation showing product context
- Status and category badges
- Author and date metadata

**Vote Button Behavior:**
- **Up arrow**: Click to add +1 vote, click again to remove
- **Down arrow**: Only active if you've already voted (removes your vote)
- Visual feedback with color changes on hover
- Mobile responsive - voting moves to horizontal layout on small screens

**Customizing Single Pages:**
- Create template in theme: `single-roadmap_item.php`
- Copy from plugin: `templates/single-roadmap-item.php`
- Modify HTML/styling as needed

### REST API Access

**Endpoints Available:**
- `/wp-json/roadmap/v1/items` - Get roadmap items
- `/wp-json/roadmap/v1/items/{id}` - Get single item
- `/wp-json/roadmap/v1/vote` - Submit vote
- `/wp-json/roadmap/v1/settings` - Get settings

**Using the API:**
- Requires authentication for write operations
- Use for mobile apps, integrations
- See DEVELOPER-GUIDE.md for details

**Enable/Disable:**
- Settings > Advanced > REST API Access
- Toggle on/off
- Secure with nonces and capabilities

## Best Practices

### Content Writing

**Item Titles:**
- Keep under 60 characters
- Be specific and clear
- Use action verbs
- Examples:
  - Good: "Add dark mode support"
  - Bad: "Dark mode"

**Item Descriptions:**
- Explain the "why" not just "what"
- Include benefits to users
- Add mockups or screenshots if possible
- Use formatting (headings, lists, bold)
- 2-3 paragraphs minimum

**Excerpts:**
- Write custom excerpts (don't rely on auto-generation)
- 1-2 sentences max
- Highlight key benefit
- Ends with compelling call-to-action

### Organization

**Status Workflow:**
1. **Idea** → User suggestion or team idea
2. **Under Review** → Team evaluating feasibility
3. **Planned** → Approved, on roadmap
4. **In Progress** → Development started
5. **Testing** → In QA phase
6. **Completed** → Live and deployed

Move items left to right through statuses.

**Category Strategy:**
- Create 5-10 categories max
- Based on product areas or types
- Keep names short
- Use consistently

**Regular Maintenance:**
- Update item statuses weekly
- Archive completed items after 3 months
- Merge duplicate suggestions
- Respond to comments within 48 hours
- Update progress percentages at milestones

### Engagement Tips

**Encourage Voting:**
- Add call-to-action: "Vote for features you want!"
- Promote in newsletters
- Respond to top-voted items
- Show votes influence decisions

**Foster Discussion:**
- Reply to all comments
- Ask for feedback
- Update items based on comments
- Thank contributors

**Feature Suggestions:**
- Review within 1 week
- Give feedback (accept/reject with reason)
- Merge similar suggestions
- Credit submitters when implemented

**Transparency:**
- Explain delays
- Show progress updates
- Communicate changes
- Share wins when items complete

### Performance Optimization

**For Large Roadmaps (100+ items):**
- Set reasonable **Items Per Page** (50-100)
- Enable caching (Settings > Advanced)
- Use status/category filters
- Archive old items
- Optimize images in descriptions

**Caching:**
- Enable object cache if supported by host
- Set cache duration to 3600 seconds (1 hour)
- Clear cache after major updates
- Use caching plugin (WP Rocket, W3 Total Cache)

**Database:**
- Regular WordPress database optimization
- Delete spam comments
- Remove auto-drafts and revisions
- Use database cleanup plugins

## Troubleshooting

### Roadmap Not Displaying

**Problem**: Shortcode shows but no roadmap appears

**Solutions:**
1. **Check shortcode syntax:**
   - Must have `product` parameter
   - Correct: `[roadmap product="main"]`
   - Wrong: `[roadmap]`

2. **Verify items exist:**
   - Go to Roadmap > All Items
   - Check items are Published (not Draft)
   - Items must be assigned to the product

3. **Check for JavaScript errors:**
   - Open browser console (F12)
   - Look for red errors
   - Disable other plugins to test conflicts

4. **Clear cache:**
   - If using caching plugin, clear cache
   - Clear browser cache
   - Try incognito/private mode

5. **Theme compatibility:**
   - Switch to default WordPress theme (Twenty Twenty-Four)
   - Test if roadmap appears
   - Report theme conflict to theme author

### Voting Not Working

**Problem**: Vote button doesn't respond

**Solutions:**
1. **Check settings:**
   - Roadmap > Settings > General
   - Ensure "Enable Voting" is checked
   - Save settings

2. **Login requirement:**
   - If "Require Login" is enabled, user must log in
   - Test with logged-in account
   - Check guest voting settings in Advanced tab

3. **JavaScript conflicts:**
   - Disable other plugins
   - Test with default theme
   - Check browser console for errors

4. **Vote already cast:**
   - Users can only vote once per item
   - Check if vote shows as already voted (blue button)
   - Try different item

5. **Database permissions:**
   - Voting requires database writes
   - Check with hosting provider
   - Check error logs

### Comments Not Showing

**Problem**: Comment section missing

**Solutions:**
1. **Enable comments:**
   - Roadmap > Settings > General
   - Check "Enable Comments"
   - Save settings

2. **Item-level comments:**
   - Edit specific roadmap item
   - Check **Discussion** meta box
   - Ensure "Allow comments" is checked

3. **WordPress comment settings:**
   - Settings > Discussion
   - Check "Allow people to submit comments"
   - Verify comment settings

4. **Theme support:**
   - Some themes hide comments
   - Check theme documentation
   - Try default WordPress theme

### Styles Not Loading

**Problem**: Roadmap looks broken or unstyled

**Solutions:**
1. **Clear cache:**
   - Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
   - Clear WordPress cache
   - Clear CDN cache if using

2. **Check for CSS conflicts:**
   - Inspect element with browser dev tools
   - Look for overriding styles
   - Add custom CSS to fix conflicts

3. **Plugin conflict:**
   - Deactivate all plugins except Roadmap
   - Test if styles load
   - Reactivate one by one to find conflict

4. **File permissions:**
   - Check CSS files can be accessed
   - Visit yoursite.com/wp-content/plugins/product-roadmap/assets/css/roadmap.css
   - Should display CSS code, not 404

### Feature Suggestions Not Creating Items

**Problem**: Submission succeeds but no item appears

**Solutions:**
1. **Check draft items:**
   - Roadmap > All Items
   - Click **All** tab at top
   - Filter by "Under Review" status
   - Suggestions may be in Draft status

2. **Email notification:**
   - Check admin email for notification
   - Contains link to item
   - Verify email settings

3. **Settings verification:**
   - Roadmap > Settings > General
   - Ensure "Enable Feature Suggestions" is checked
   - Verify default status is set

4. **Permission issues:**
   - Check user permissions
   - Verify database write access
   - Check error logs

### Slow Performance

**Problem**: Roadmap takes long to load

**Solutions:**
1. **Enable caching:**
   - Settings > Advanced
   - Enable object cache
   - Set cache duration to 3600

2. **Reduce items per page:**
   - Settings > General
   - Lower "Items Per Page" to 50
   - Use status filters to show fewer items

3. **Optimize images:**
   - Compress images in descriptions
   - Use image optimization plugins
   - Lazy load images (Advanced settings)

4. **Upgrade hosting:**
   - Consider better hosting plan
   - Use CDN for assets
   - Enable server-side caching

### Pro Addon Issues

**Problem**: Pro features not appearing after activation

**Solutions:**
1. **License activation:**
   - Roadmap > Settings > License
   - Enter license key
   - Click Activate
   - Verify "Active" status

2. **Free plugin required:**
   - Pro addon requires free plugin
   - Install and activate free version first
   - Then activate Pro addon

3. **Version compatibility:**
   - Update both free and Pro to latest versions
   - Ensure PHP version is 7.4 or higher
   - Ensure WordPress is 5.8 or higher

## Getting Help

### Documentation

- **User Guide**: This document
- **Developer Guide**: /docs/DEVELOPER-GUIDE.md
- **Settings Guide**: /docs/SETTINGS-GUIDE.md
- **Product System**: /docs/PRODUCT-SYSTEM.md

### Support Channels

**Free Version:**
- WordPress.org support forum
- GitHub issues (for bug reports)
- Documentation at plugin website

**Pro Version:**
- Priority email support: support@wbcomdesigns.com
- Include license key in requests
- Response within 24 hours
- Direct developer access

### Before Requesting Support

1. **Check documentation** - Read relevant guides
2. **Search support forum** - Issue might be solved already
3. **Test with default theme** - Rule out theme conflicts
4. **Disable plugins** - Test for plugin conflicts
5. **Check browser console** - Look for JavaScript errors
6. **Gather information:**
   - WordPress version
   - Plugin version
   - PHP version
   - Active theme
   - Active plugins list
   - Error messages (exact text)
   - Steps to reproduce issue

## Conclusion

The WordPress Product Roadmap Plugin gives you everything needed to share your product vision, engage your community, and gather valuable feedback. By following this guide and best practices, you'll create an effective roadmap that keeps users informed and excited about your product's future.

### Next Steps

1. **Complete setup wizard** if you haven't
2. **Create 3-5 roadmap items** as examples
3. **Add shortcode to a page** to display roadmap
4. **Configure settings** to match your needs
5. **Share roadmap link** with your community
6. **Monitor votes and comments** regularly
7. **Update statuses** as you make progress

### Upgrade to Pro

For advanced features, consider upgrading to Pro:
- **Analytics Dashboard** with charts and metrics
- **Multi-Product Management** for separate roadmaps
- **Timeline View** with interactive Gantt charts
- **Drag & Drop** for easy status updates
- **Import/Export** in CSV format
- **Advanced Filtering** and sorting options
- **Priority Support** with faster response times

Visit [your website] to learn more about Pro features.

Happy roadmapping! 🚀