# Setup Wizard Guide

The Product Roadmap plugin includes a comprehensive setup wizard to help you get started quickly. This guide explains each step of the wizard and how to customize your roadmap.

## Accessing the Setup Wizard

There are three ways to access the setup wizard:

1. **Automatic Redirect**: After activating the plugin for the first time, you'll be automatically redirected to the wizard
2. **Welcome Notice**: Click "Run Setup Wizard" in the admin notice that appears on roadmap pages
3. **Admin Menu**: Navigate to **Roadmap → Setup Wizard** at any time

## Wizard Steps

### Step 1: Product Configuration

Configure your product name and customize item labels to match your terminology.

**Fields:**

- **Product Name**: The name of your product or project (e.g., "My App", "WordPress Plugin")
  - This appears in menu titles and page headings
  - Used as the default roadmap page title

- **Item Labels**: Customize what you call roadmap items
  - **Singular**: How you refer to one item (e.g., "Feature", "Task", "Story", "Ticket")
  - **Plural**: How you refer to multiple items (e.g., "Features", "Tasks", "Stories", "Tickets")
  - These labels appear throughout the interface

**Pro Tip**: Choose labels that resonate with your users. Software teams might use "Features", while agencies might prefer "Deliverables".

### Step 2: Basic Settings

Configure how users can interact with your roadmap.

**Options:**

- **Enable Voting**: Allow users to vote on roadmap items
  - Helps prioritize development based on user demand
  - Shows vote counts on each item

- **Guest Voting**: Allow non-logged-in users to vote
  - Tracks votes by IP address for guests
  - Prevents duplicate voting from same IP

- **Enable Comments**: Allow users to comment on roadmap items
  - Facilitates discussion and feedback
  - Uses WordPress native comment system

### Step 3: Import Demo Content

Optionally import sample roadmap items to see how everything works.

**Demo Content Includes:**
- 11 sample roadmap items
- Items distributed across all 5 status types:
  - Planned (3 items)
  - In Progress (3 items)
  - Completed (2 items)
  - Under Review (2 items)
  - On Hold (2 items)
- 4 categories: Features, Improvements, Bug Fixes, Infrastructure
- Various priority levels and vote counts
- Sample comments on popular items

**Should You Import Demo Content?**
- ✅ **Yes** if you want to see how the roadmap looks with content
- ✅ **Yes** if you're testing or demonstrating the plugin
- ❌ **No** if you're ready to add your real roadmap items immediately

### Step 4: Finish

The wizard completes by:
1. Creating a roadmap page with the `[roadmap product="main"]` shortcode
2. Saving all your configuration
3. Providing quick links to:
   - View your roadmap page
   - Edit the roadmap page
   - Add your first real item
   - Access advanced settings

## What Happens Behind the Scenes

### Page Creation
- Creates a new page titled "{Your Product} Roadmap"
- Adds the `[roadmap product="main"]` shortcode automatically
- Sets page status to published
- Prevents duplicate pages if run multiple times

### Data Storage
All settings are stored as WordPress options:
- `roadmap_product_name`
- `roadmap_item_singular_label`
- `roadmap_item_plural_label`
- `roadmap_enable_voting`
- `roadmap_allow_guest_voting`
- `roadmap_enable_comments`
- `roadmap_wizard_completed`
- `roadmap_demo_imported`
- `roadmap_page_id`

### Taxonomy Setup
- Creates default product term "main" (free version limitation)
- Ensures all status terms exist
- Sets up categories if demo content is imported

## Re-running the Wizard

You can access the wizard again at any time:

1. Go to **Roadmap → Setup Wizard**
2. If already completed, you'll see a "Start Over" button
3. Starting over will:
   - Reset wizard completion flag
   - Allow you to reconfigure settings
   - NOT delete existing roadmap items
   - NOT create duplicate pages

## Customizing After Setup

After completing the wizard, you can:

### Change Settings
- Go to **Roadmap → Settings** for advanced options
- Modify voting, comments, and display settings
- Configure items per page and other options

### Manage Taxonomies
- **Categories**: Add/edit at **Roadmap → Categories**
- **Statuses**: Managed automatically (planned, in-progress, completed, on-hold, under-review)
- **Products**: Limited to one in free version

### Customize the Roadmap Page
1. Edit the created page
2. Add content before/after the shortcode
3. Change the page template if needed
4. Modify shortcode parameters:
   ```
   [roadmap product="main" sort="votes" template="board"]
   ```

## Troubleshooting

### Wizard Not Appearing
- Clear browser cache
- Deactivate and reactivate the plugin
- Check for JavaScript errors in console

### Demo Content Not Importing
- Ensure you're logged in as administrator
- Check PHP error logs
- Verify database write permissions

### Page Not Created
- Check if a page with the shortcode already exists
- Verify you have permission to create pages
- Look for the page in trash

### Settings Not Saving
- Ensure proper file permissions
- Check for plugin conflicts
- Verify database connection

## Pro Version Differences

In the Pro version, the wizard includes:
- Multiple product configuration
- Advanced template selection
- Custom branding options
- Email notification setup
- Import/export settings

## Developer Notes

### Hooks Available

```php
// Before wizard step processing
do_action('roadmap_before_wizard_step_{step_number}');

// After wizard step processing
do_action('roadmap_after_wizard_step_{step_number}');

// After wizard completion
do_action('roadmap_wizard_completed');
```

### Customizing Wizard Steps

```php
// Add custom step
add_filter('roadmap_wizard_steps', function($steps) {
    $steps[] = [
        'title' => 'Custom Step',
        'callback' => 'my_custom_step_callback'
    ];
    return $steps;
});
```

## Next Steps

After completing the wizard:

1. **Add Real Content**: Start adding your actual roadmap items
2. **Customize Statuses**: Set appropriate statuses for your workflow
3. **Engage Users**: Announce the roadmap to your users
4. **Monitor Feedback**: Track votes and comments to guide development
5. **Keep Updated**: Regularly update item statuses to maintain trust