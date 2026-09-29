# Product Roadmap

Create public product roadmaps to share your vision, collect user feedback, and build products your customers actually want.

**Version:** 1.3.1
**Requires WordPress:** 5.8 or higher
**Requires PHP:** 7.4 or higher
**License:** GPL v2 or later

## 📋 Short Description

Product Roadmap helps you build transparency with your customers by sharing your development plans and letting them vote on features that matter most to them.

## 🚀 What's New in 1.1.1

- **Fixed Submission Workflow**: User submissions now properly appear as pending for admin review
- **Enhanced Permissions**: Subscriber role can now submit roadmap suggestions
- **Guest Voting Improvements**: Fixed database structure for guest voting with IP tracking
- **Security & Compliance**: WordPress security compliance with proper input sanitization
- **Automatic Permalink Refresh**: Flush permalinks on plugin activation and updates
- **Better Admin Experience**: WordPress default styling for pending submission notices
- **Improved Settings**: Clearer Pro feature indicators in free version

## 📖 Long Description

**Build Products Your Customers Love**

Product Roadmap transforms the way you communicate with your customers about upcoming features and improvements. Instead of developing in isolation, create a transparent development process where customers can see what's coming, vote on priorities, and suggest new ideas.

**Why Product Roadmap?**

- **Increase Customer Engagement**: Let customers feel heard by allowing them to vote and comment on upcoming features
- **Reduce Development Waste**: Build features that customers actually want, not what you think they need
- **Build Trust Through Transparency**: Show customers you're actively working on improvements they care about
- **Gather Valuable Insights**: Understand which features resonate most with your user base
- **Reduce Support Tickets**: Customers can see what's already planned, reducing "when will you add X?" questions
- **Boost Customer Retention**: Users stay loyal when they see their feedback shapes your product

## ✨ Features

### Free Version - What You Get

- **Public Roadmap Display**: Share your development plans openly with customers
- **User Voting**: Let customers vote on features they want most
- **Progress Tracking**: Show what's planned, in progress, under review, on hold, or completed
- **Organized Categories**: Group similar features together for easy browsing
- **User Suggestions**: Allow customers to submit their own feature ideas
- **Pending Review System**: Admin review workflow for user-submitted suggestions
- **Comment System**: Enable discussions on each roadmap item
- **Copy to Clipboard** (NEW): Easy shortcode copying throughout admin
- **Setup Wizard**: Get started quickly with our 4-step guided setup
- **Demo Content**: Import sample roadmap items to see how it works
- **Custom Labels**: Personalize item labels (e.g., "Features", "Tasks", "Stories")
- **Product Name**: Brand your roadmap with your product name
- **Quick Setup**: Add to any page with a simple shortcode
- **Mobile Friendly**: Your roadmap looks great on all devices
- **Modern Admin UI**: Enhanced interface with animations and transitions
- **REST API**: Full API support for external integrations

### Pro Version - Scale Your Feedback

- **Gutenberg Block Builder** (NEW): Visual roadmap creation in block editor
- **Multiple Products**: Manage unlimited separate roadmaps
- **5 Professional Templates**: Board, Timeline, List, Cards, and Minimal views
- **Timeline/Gantt View**: Visual timeline with dates
- **Drag & Drop Management**: Easily reorder items
- **Advanced Analytics**: Track engagement and voting patterns
- **CSV Export**: Export data for external analysis
- **Custom Filters**: Save and reuse filter presets
- **Priority Support**: Direct support from our team

## 📦 Installation

### Automatic Installation

1. Go to Plugins → Add New in your WordPress admin
2. Search for "Product Roadmap"
3. Click "Install Now" and then "Activate"
4. Follow the automatic Setup Wizard

### Manual Installation

1. Download the plugin zip file
2. Upload to `/wp-content/plugins/` and extract
3. Activate the plugin through the 'Plugins' menu
4. Navigate to Roadmap → Setup Wizard

## 🎯 Usage

### Basic Usage

Add the roadmap to any page using the shortcode:

```
[roadmap product="main"]
```

**Note:** The `product` parameter is optional. Leave it out to show your default product.

### First Time Setup

1. **Run Setup Wizard**: Click "Run Setup Wizard" from the welcome notice
2. **Configure Product**: Enter your product name and customize labels
3. **Basic Settings**: Configure voting, comments, and suggestions
4. **Import Demo**: Optionally import demo items
5. **Done!**: Your roadmap page is created automatically

### Shortcode Parameters

```
[roadmap
    product="main"        // Optional: leave empty for your default product
    status="planned"      // Filter by one status slug
    category="features"   // Filter by one category slug
    limit="20"             // Number of items to show (0 = all, with Load more)
    sort="votes"           // Sort by: votes, recent, comments, priority, title
    show_voting="hide"     // Hide voting on this board (empty = follow Settings)
    show_comments="hide"   // Hide comments on this board (empty = follow Settings)
    show_filters="true"    // Show/hide the filter bar
    show_search="true"     // Show/hide the search box
]
```

`status` and `category` each take one value. Voting and comments can only be turned off here, never turned on if Settings has them disabled.

### Managing Submissions

1. Go to **Roadmap → Product Roadmap** in admin
2. Click "Pending Review (X)" to see submitted items
3. Use quick approve/reject actions
4. Items are published when approved

### Copy Shortcode Feature

- Click the copy button next to any shortcode
- Visual feedback confirms successful copy
- Works in product listings and taxonomy pages
- Paste anywhere with Ctrl+V (Cmd+V on Mac)

## ⚙️ Configuration

### Settings Structure

Navigate to **Roadmap → Settings** to access:

1. **General Tab**
   - Product configuration
   - Display settings
   - Permissions & access
   - Features toggles
   - Auto-approval settings

2. **Notifications Tab**
   - Email configuration
   - Notification events
   - Alert preferences

### User Permissions

- **Voting**: Anyone (default) or logged-in users only
- **Suggestions**: Anyone or logged-in users only
- **Comments**: WordPress discussion settings apply
- **Auto-Approval**: Enable/disable automatic publishing

### Managing Suggestions

**Finding Pending Suggestions:**
1. Go to Product Roadmap in admin
2. Click "Pending Review (X)" at the top
3. Review and approve/reject suggestions
4. Use quick action links for faster moderation

**Pending Review Workflow:**
- User suggestions require admin approval
- Clear notification of pending submissions
- Quick approve/reject actions available
- Automatic status assignment upon approval

## 🔌 REST API Endpoints

The plugin provides full REST API support:

- `GET /wp-json/roadmap/v1/items` - Get roadmap items
- `POST /wp-json/roadmap/v1/items/{id}/vote` - Submit vote
- `POST /wp-json/roadmap/v1/items/{id}/comment` - Add comment
- `POST /wp-json/roadmap/v1/suggestions` - Submit suggestion

## 🎨 Customization

### CSS Classes

Key classes for custom styling:

```css
.roadmap-container          /* Main container */
.roadmap-board             /* Board view container */
.roadmap-column            /* Status columns */
.roadmap-item              /* Individual items */
.roadmap-vote-button       /* Voting buttons */
.roadmap-filters           /* Filter section */
.roadmap-suggestion-form   /* Suggestion form */
```

### Hooks & Filters

```php
// Modify item output
add_filter('roadmap_item_content', 'your_function', 10, 2);

// Add custom fields
add_action('roadmap_item_meta', 'your_function', 10, 1);

// Customize vote behavior
add_filter('roadmap_can_vote', 'your_function', 10, 2);

// Modify suggestion approval
add_filter('roadmap_suggestion_status', 'your_function', 10, 1);
```

## 🧑‍💻 Developer Documentation

### File Structure

```
product-roadmap/
├── assets/
│   ├── css/
│   │   ├── roadmap-admin.css    # Admin styles
│   │   ├── roadmap-app.css      # Frontend styles
│   │   └── roadmap-single.css   # Single item styles
│   └── js/
│       ├── roadmap-admin.js     # Admin JavaScript
│       └── roadmap-app.js       # Frontend JavaScript
├── includes/
│   ├── class-roadmap-api.php         # REST API
│   ├── class-roadmap-settings.php    # Settings page
│   ├── class-roadmap-shortcode.php   # Shortcode handler
│   ├── class-roadmap-cpt.php         # Custom post type
│   └── class-roadmap-notifications.php # Email notifications
├── templates/
│   └── roadmap-template.php     # Main template
└── product-roadmap.php          # Main plugin file
```

### Database Structure

**Custom Post Type:** `roadmap_item`
**Taxonomies:**
- `roadmap_status` - Item status (planned, in-progress, etc.)
- `roadmap_category` - Item categories
- `roadmap_product` - Products (Pro feature, defaults to 'main')

**Post Meta:**
- `_roadmap_votes` - Vote count
- `_roadmap_priority` - Item priority
- `_roadmap_user_votes` - User vote tracking

## 📊 Pro Version Features

### Gutenberg Block (NEW in 1.1.0)

The Pro version includes a visual Gutenberg block:

1. Add "Roadmap" block from block inserter
2. Configure all settings visually
3. Live preview in editor
4. No shortcode memorization needed
5. Copy generated shortcode if needed

### Multiple Products

Create unlimited product roadmaps:

```
[roadmap product="mobile-app"]
[roadmap product="web-platform"]
[roadmap product="api"]
```

### Professional Templates

- **Board View**: Kanban-style columns
- **Timeline View**: Gantt chart visualization
- **List View**: Compact list format
- **Cards View**: Modern card layout
- **Minimal View**: Clean, simple display

## 🐛 Troubleshooting

### Common Issues

**Suggestions Not Appearing:**
- Check "Pending Review" filter in admin
- Verify auto-approval settings
- Ensure user has permission to suggest

**Shortcode Not Working:**
- If you set a `product`, check the slug is correct
- Verify plugin is activated

**Styling Issues:**
- Clear browser cache
- Check for theme conflicts
- Verify CSS is loading

## 📝 Changelog

### Version 1.1.1
- Fix: User submissions now properly appear as pending for admin review
- Fix: Subscriber role permissions to allow roadmap suggestions
- Fix: Guest voting system with proper IP tracking and database structure
- Fix: WordPress security compliance with proper input sanitization and output escaping
- Fix: Automatic permalink flushing on plugin activation, deactivation, and updates
- Enhancement: Quick approve/reject actions for pending submissions
- Enhancement: Better support for non-English websites with improved translations
- Enhancement: WordPress default styling for pending submissions notice
- Update: Version tracking system to detect plugin updates and refresh rewrite rules

### Version 1.1.0
- New: Auto-approval option for suggestions
- New: Copy to clipboard functionality
- New: Enhanced admin UI
- New: Better responsive design
- Fix: CSS loading conditions
- Fix: JavaScript optimizations

### Version 1.0.2
- Fix: Settings navigation
- Enhancement: Visual improvements
- Fix: Pro compatibility

### Version 1.0.1
- Fix: Items per page limit
- Enhancement: Configurable pagination

### Version 1.0.0
- Initial release

## 🤝 Support

- **Documentation**: [GitHub Wiki](https://github.com/wbcomdesigns/product-roadmap/wiki)
- **Support Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/product-roadmap/)
- **Pro Support**: Available with Pro license

## 👥 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 License

This plugin is licensed under GPL v2 or later.

---

**Built with ❤️ by WBComDesigns**