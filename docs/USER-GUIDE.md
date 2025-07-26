# WordPress Product Roadmap Plugin - User Guide

## Table of Contents
- [Introduction](#introduction)
- [Installation](#installation)
- [Getting Started](#getting-started)
- [Managing Roadmap Items](#managing-roadmap-items)
- [Using Categories and Statuses](#using-categories-and-statuses)
- [Product Management](#product-management)
- [Displaying Roadmaps](#displaying-roadmaps)
- [User Interactions](#user-interactions)
- [Settings Configuration](#settings-configuration)
- [Frequently Asked Questions](#frequently-asked-questions)

## Introduction

The WordPress Product Roadmap Plugin allows you to create, manage, and display product roadmaps on your WordPress website. It provides an intuitive interface for managing roadmap items and enables user engagement through voting and comments.

### Key Features (Free Version)
- Create and manage roadmap items
- Organize items with categories and statuses
- Display roadmaps using shortcodes
- User voting system
- Comment integration
- Responsive design
- Dark mode support

## Installation

### Automatic Installation
1. Navigate to **Plugins > Add New** in your WordPress admin
2. Search for "Product Roadmap"
3. Click **Install Now** and then **Activate**

### Manual Installation
1. Download the plugin zip file
2. Navigate to **Plugins > Add New > Upload Plugin**
3. Select the zip file and click **Install Now**
4. Activate the plugin

### Initial Setup
After activation, the plugin will:
- Create necessary database tables
- Set up default statuses and categories
- Configure initial permissions

## Getting Started

### Accessing the Plugin
After activation, you'll find the **Roadmap** menu in your WordPress admin sidebar with the following options:
- **All Items**: View and manage all roadmap items
- **Add New**: Create new roadmap items
- **Categories**: Manage item categories
- **Statuses**: Manage item statuses
- **Products**: Manage different products
- **Settings**: Configure plugin settings

### Creating Your First Roadmap Item
1. Navigate to **Roadmap > Add New**
2. Enter the item details:
   - **Title**: Clear, descriptive title
   - **Description**: Detailed explanation of the feature/improvement
   - **Status**: Current development status
   - **Category**: Type of item (Feature, Bug Fix, etc.)
   - **Priority**: Item priority level
   - **Target Date**: Expected completion date (optional)
3. Click **Publish** to save

## Managing Roadmap Items

### Item Fields
- **Title**: The main heading for your roadmap item
- **Content**: Detailed description using the WordPress editor
- **Excerpt**: Short summary for display in roadmap views
- **Status**: Development stage (Planned, In Progress, Completed, etc.)
- **Category**: Classification (Feature, Enhancement, Bug Fix)
- **Priority**: Importance level (Low, Medium, High, Critical)
- **Progress**: Completion percentage (0-100%)
- **Target Date**: Expected delivery date
- **Start Date**: When work begins
- **End Date**: When work completes

### Bulk Actions
1. Select multiple items using checkboxes
2. Choose an action from the **Bulk Actions** dropdown:
   - Edit
   - Move to Trash
3. Click **Apply**

### Quick Edit
Hover over an item and click **Quick Edit** to rapidly update:
- Title
- Status
- Category
- Priority

## Using Categories and Statuses

### Default Categories
- **Feature**: New functionality
- **Enhancement**: Improvements to existing features
- **Bug Fix**: Resolving issues
- **Security**: Security-related updates
- **Performance**: Speed and optimization

### Default Statuses
- **Idea**: Suggested but not confirmed
- **Under Review**: Being evaluated
- **Planned**: Approved for development
- **In Progress**: Currently being worked on
- **Testing**: In quality assurance
- **Completed**: Finished and deployed
- **Cancelled**: Not proceeding

### Creating Custom Categories/Statuses
1. Navigate to **Roadmap > Categories** or **Statuses**
2. Fill in:
   - **Name**: Display name
   - **Slug**: URL-friendly version
   - **Description**: Optional details
3. Click **Add New Category/Status**

## Product Management

### Understanding Products
Products allow you to maintain separate roadmaps for different offerings. The free version includes basic product support.

### Default Product
- **Main**: The primary product roadmap
- Automatically created during plugin activation

### Managing Products
1. Navigate to **Roadmap > Products**
2. View all products and their associated items
3. Edit product details as needed

## Displaying Roadmaps

### Basic Shortcode
```
[roadmap]
```
Displays the default roadmap board view.

### Shortcode Parameters
```
[roadmap product="main" template="board"]
```

**Available Parameters:**
- `product`: Which product to display (default: "main")
- `template`: Display template (default: "board")
  - `board`: Kanban-style board (free version)

### Widget Support
Add roadmaps to widget areas:
1. Navigate to **Appearance > Widgets**
2. Add a **Custom HTML** widget
3. Insert the roadmap shortcode

### Page Builder Compatibility
The shortcode works with popular page builders:
- Elementor: Use the Shortcode widget
- Gutenberg: Use the Shortcode block
- WPBakery: Use the Raw HTML element

## User Interactions

### Voting System
Users can vote on roadmap items to show interest:
1. **Logged-in Users**: Click the vote button on any item
2. **Vote Tracking**: One vote per user per item
3. **Vote Display**: Shows total vote count

### Comments
If enabled, users can comment on individual items:
1. Click the comment icon or **View Details**
2. Use the standard WordPress comment form
3. Comments appear on the single item page

### Feature Suggestions
When enabled in settings:
1. Users see a **Suggest a Feature** button
2. Fill out the suggestion form
3. Submissions create items with "Under Review" status

## Settings Configuration

### General Settings
Navigate to **Roadmap > Settings** to configure:

**Display Options:**
- Items per page
- Default sort order
- Date format
- Show/hide vote counts
- Show/hide comment counts

**User Permissions:**
- Enable public voting
- Enable comments
- Enable feature suggestions
- Restrict voting to logged-in users

**Email Notifications:**
- Admin notifications for new suggestions
- Admin notifications for new votes
- Email templates customization

### Styling Options
- Color scheme selection
- Custom CSS support
- Dark mode toggle

## Frequently Asked Questions

### How do I change the roadmap appearance?
The plugin includes several built-in styles. You can add custom CSS in the settings or your theme.

### Can users vote without logging in?
By default, voting requires login. This prevents duplicate votes and spam.

### How do I moderate feature suggestions?
All suggestions appear as "Under Review" items. Admins can then approve, modify, or delete them.

### Can I export roadmap data?
Currently, you can export via WordPress's standard export tools under **Tools > Export**.

### Is the plugin multisite compatible?
Yes, the plugin works on multisite installations. Each site maintains its own roadmap data.

### How do I backup my roadmap data?
1. Use WordPress export (**Tools > Export > Roadmap Items**)
2. Regular database backups include roadmap tables
3. The plugin data is stored in custom post types and taxonomies

### Can I translate the plugin?
Yes, the plugin is translation-ready:
1. POT file included in `/languages`
2. Compatible with WPML and Polylang
3. Supports WordPress translation system

## Troubleshooting

### Common Issues

**Roadmap not displaying:**
- Check shortcode syntax
- Verify items exist with visible status
- Clear cache if using caching plugin

**Voting not working:**
- Ensure voting is enabled in settings
- Check user permissions
- Verify JavaScript is not blocked

**Styles not loading:**
- Clear browser cache
- Check for theme conflicts
- Ensure no CSS optimization plugins are blocking

### Getting Support
1. Check documentation at [plugin website]
2. WordPress.org support forum
3. Submit bug reports on GitHub

## Best Practices

### Content Guidelines
- Write clear, concise titles
- Provide detailed descriptions
- Set realistic target dates
- Update statuses regularly
- Respond to user comments

### Organization Tips
- Use consistent categories
- Maintain status workflow
- Archive completed items periodically
- Regular progress updates
- Monitor user engagement

### Performance Optimization
- Limit items per page for large roadmaps
- Use caching plugins
- Optimize images in descriptions
- Regular database maintenance

## Conclusion

The WordPress Product Roadmap Plugin provides a comprehensive solution for managing and displaying product development plans. With its intuitive interface and flexible display options, you can keep your users informed and engaged with your product's future.

For advanced features like timeline views, multiple products, and enhanced analytics, consider upgrading to the Pro version.