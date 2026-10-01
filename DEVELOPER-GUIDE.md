# Product Roadmap Developer Guide

## What's New in v1.1.1

- **Enhanced Submission API**: Fixed `/suggest` endpoint to properly create pending items for admin review
- **Permission System**: Added `roadmap_suggest` capability to subscriber role
- **Database Updates**: Enhanced guest voting with IP tracking and nullable user_id column
- **Security Improvements**: WordPress coding standards compliance with proper escaping
- **Admin Quick Actions**: Added approve/reject functionality for pending submissions
- **Version Management**: Automatic permalink flushing on plugin updates

## Table of Contents
1. [Architecture Overview](#architecture-overview)
2. [File Structure](#file-structure)
3. [Core Classes](#core-classes)
4. [Database Schema](#database-schema)
5. [REST API](#rest-api)
6. [Hooks & Filters](#hooks--filters)
7. [JavaScript Architecture](#javascript-architecture)
8. [CSS Structure](#css-structure)
9. [Testing](#testing)
10. [Contributing](#contributing)

## Architecture Overview

Product Roadmap follows WordPress coding standards and modern development practices:

- **Object-Oriented PHP**: Singleton pattern for main classes
- **REST API**: Modern endpoints for frontend interactions
- **Vue.js**: Interactive frontend components
- **Custom Post Type**: Leverages WordPress CPT for roadmap items
- **Taxonomies**: Status, categories, and products organization

## File Structure

```
product-roadmap/
├── assets/
│   ├── css/
│   │   ├── roadmap-admin.css      # Admin styles (enhanced v1.1.0)
│   │   ├── roadmap-app.css        # Frontend application
│   │   └── roadmap-single.css     # Single item view
│   └── js/
│       ├── roadmap-admin.js       # Admin JavaScript (v1.1.0 updates)
│       └── roadmap-app.js         # Frontend Vue app
├── includes/
│   ├── class-roadmap-api.php           # REST API endpoints
│   ├── class-roadmap-cpt.php           # Custom post type
│   ├── class-roadmap-settings.php      # Settings management
│   ├── class-roadmap-shortcode.php     # Shortcode handler
│   ├── class-roadmap-permissions.php   # Access control
│   ├── class-roadmap-notifications.php # Email notifications
│   ├── class-roadmap-voting.php        # Voting system
│   ├── class-roadmap-comments.php      # Comments handler
│   ├── class-roadmap-suggestions.php   # User suggestions
│   ├── class-roadmap-template.php      # Template loader
│   ├── class-roadmap-admin.php         # Admin interface
│   ├── class-roadmap-migration.php     # Database migrations
│   ├── class-roadmap-demo-import.php   # Demo content
│   └── class-roadmap-setup-wizard.php  # Setup wizard
├── templates/
│   └── roadmap-template.php       # Main template file
├── vue-app/                       # Vue.js source files
│   └── src/
│       ├── components/
│       ├── composables/
│       └── main.js
├── languages/                     # Translation files
├── product-roadmap.php            # Main plugin file
└── README.md                       # User documentation
```

## Core Classes

### Main Plugin Class

```php
class RoadmapPlugin {
    // Singleton instance
    private static $instance = null;

    // Core initialization
    public static function get_instance()
    private function load_dependencies()
    private function define_hooks()

    // Activation/Deactivation
    public function activate()
    public function deactivate()
}
```

### Roadmap_CPT Class

Manages the custom post type and taxonomies:

```php
// Registration
public function register_post_type()
public function register_taxonomies()

// Taxonomies:
- roadmap_status (planned, in-progress, completed, etc.)
- roadmap_category (feature organization)
- roadmap_product (multi-product support)
```

### Roadmap_API Class

REST API endpoints (v1.1.0 enhanced):

```php
// Endpoints
GET  /wp-json/roadmap/v1/items
GET  /wp-json/roadmap/v1/items/{id}
POST /wp-json/roadmap/v1/items/{id}/vote
POST /wp-json/roadmap/v1/suggestions
GET  /wp-json/roadmap/v1/stats

// New in v1.1.0: Auto-approval logic
public function create_suggestion($request) {
    $auto_approve = get_option('roadmap_auto_approve_suggestions');
    $post_status = $auto_approve ? 'publish' : 'pending';
}
```

### Roadmap_Settings Class

Settings management (v1.1.0 enhanced):

```php
// Settings structure
- General Tab
  - Product configuration
  - Display settings
  - Auto-approval (NEW v1.1.0)
  - Copy buttons (NEW v1.1.0)
- Notifications Tab
  - Email settings
  - Event triggers
```

## Database Schema

### Custom Tables

```sql
-- Votes table
CREATE TABLE {prefix}_roadmap_votes (
    id bigint(20) UNSIGNED AUTO_INCREMENT,
    user_id bigint(20) UNSIGNED NOT NULL,
    item_id bigint(20) UNSIGNED NOT NULL,
    vote_value int(1) DEFAULT 1,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY user_item (user_id, item_id),
    KEY item_id (item_id)
);
```

### Post Meta

```php
// Item metadata
_roadmap_votes        // Vote count
_roadmap_priority     // Priority level
_roadmap_user_votes   // Serialized user vote data
_roadmap_timeline     // Timeline dates (Pro feature)
```

## REST API

### Authentication

```php
// Permission callbacks
public function vote_permission_check($request) {
    $allow_guest = get_option('roadmap_allow_guest_voting');
    return $allow_guest === 'yes' || is_user_logged_in();
}
```

### Request/Response Format

```javascript
// Get items
GET /wp-json/roadmap/v1/items?product=main&status=planned

// Response
{
    "items": [{
        "id": 123,
        "title": "Feature Name",
        "content": "Description",
        "status": "planned",
        "category": ["enhancement"],
        "votes": 42,
        "user_voted": false
    }],
    "total": 10,
    "pages": 1
}

// Submit vote
POST /wp-json/roadmap/v1/items/123/vote
{
    "vote_value": 1
}
```

## Hooks & Filters

### Actions

```php
// Item lifecycle
do_action('roadmap_before_item_save', $post_id, $post);
do_action('roadmap_after_item_save', $post_id, $post);
do_action('roadmap_item_status_changed', $post_id, $old_status, $new_status);

// Voting
do_action('roadmap_vote_cast', $item_id, $user_id, $vote_value);
do_action('roadmap_vote_removed', $item_id, $user_id);

// Suggestions (v1.1.0)
do_action('roadmap_suggestion_submitted', $post_id, $data);
do_action('roadmap_suggestion_auto_approved', $post_id); // NEW
```

### Filters

```php
// Display customization
add_filter('roadmap_item_content', $content, $post_id);
add_filter('roadmap_item_classes', $classes, $post_id);

// Permissions
add_filter('roadmap_can_vote', $can_vote, $user_id, $item_id);
add_filter('roadmap_can_suggest', $can_suggest, $user_id);

// Auto-approval (v1.1.0)
add_filter('roadmap_suggestion_status', $status, $data); // NEW
add_filter('roadmap_auto_approve_enabled', $enabled); // NEW

// Shortcode
add_filter('roadmap_shortcode_output', $output, $atts);
```

## JavaScript Architecture

### Admin JavaScript (v1.1.0)

```javascript
// Copy to clipboard functionality (NEW)
jQuery(document).ready(function($) {
    // Copy shortcode buttons
    $(document).on('click', '.copy-shortcode', function(e) {
        e.preventDefault();
        const shortcode = $(this).data('shortcode');
        navigator.clipboard.writeText(shortcode).then(() => {
            $(this).addClass('copied');
            // Visual feedback
        });
    });
});
```

### Frontend Vue App

```javascript
// Main components
- RoadmapBoard.vue      // Main board view
- RoadmapItem.vue       // Individual item
- RoadmapFilters.vue    // Filter controls
- VotingButton.vue      // Vote interaction
- SuggestionForm.vue    // User suggestions

// State management
const roadmapStore = {
    items: [],
    filters: {},
    loading: false,

    // Methods
    fetchItems(),
    submitVote(),
    submitSuggestion()
};
```

## CSS Structure

### Admin Styles (v1.1.0 Enhanced)

```css
/* Main containers */
.roadmap-admin-wrap { /* Admin wrapper */ }
.roadmap-settings-header { /* Settings header */ }

/* Copy functionality (NEW) */
.shortcode-wrapper {
    display: inline-flex;
    align-items: center;
    background: #f6f7f7;
    border: 1px solid #ddd;
}

.copy-shortcode {
    transition: all 0.3s ease;
}

.copy-shortcode.copied {
    background-color: #00a32a;
    color: white;
}

/* Animations (NEW) */
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
```

### Frontend Styles

```css
/* Board layout */
.roadmap-board { /* Kanban board */ }
.roadmap-column { /* Status columns */ }
.roadmap-item { /* Item cards */ }

/* Responsive design */
@media (max-width: 768px) {
    .roadmap-board { /* Mobile layout */ }
}
```

## Testing

### PHP Unit Tests

```bash
# Run tests
composer test

# Test structure
tests/
├── test-roadmap-cpt.php
├── test-roadmap-api.php
├── test-roadmap-voting.php
└── test-roadmap-permissions.php
```

### JavaScript Tests

```bash
# Vue component tests
npm run test:unit

# E2E tests
npm run test:e2e
```

### Manual Testing Checklist

#### v1.1.0 Features
- [ ] Auto-approval toggles correctly
- [ ] Suggestions publish immediately when enabled
- [ ] Copy buttons work across all browsers
- [ ] Clipboard feedback displays correctly
- [ ] Settings save without errors

#### Core Features
- [ ] Shortcode displays roadmap
- [ ] Voting increments correctly
- [ ] Guest voting respects settings
- [ ] Suggestions submit successfully
- [ ] Email notifications send
- [ ] Status filters work
- [ ] Category filters work
- [ ] Search functionality works

## Contributing

### Development Setup

```bash
# Clone repository
git clone https://github.com/wbcomdesigns/product-roadmap.git

# Install dependencies
composer install
npm install

# Build assets
npm run build

# Watch for changes
npm run dev
```

### Code Standards

- Follow WordPress Coding Standards
- Use PHP 8.1+ features
- Document all functions with PHPDoc
- Test on latest 3 WordPress versions
- Ensure PHP 8.0+ compatibility

### Pull Request Process

1. Fork the repository
2. Create feature branch (`feature/amazing-feature`)
3. Commit changes with clear messages
4. Add/update tests as needed
5. Update documentation
6. Submit pull request

### Version Numbering

We use Semantic Versioning:
- MAJOR.MINOR.PATCH (e.g., 1.1.0)
- MAJOR: Breaking changes
- MINOR: New features (backward compatible)
- PATCH: Bug fixes

## Security

### Data Validation

```php
// Always sanitize input
$title = sanitize_text_field($_POST['title']);
$content = wp_kses_post($_POST['content']);

// Verify nonces
wp_verify_nonce($_POST['roadmap_nonce'], 'roadmap_action');

// Check capabilities
current_user_can('edit_posts');
```

### SQL Queries

```php
// Use prepared statements
$wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}roadmap_votes
     WHERE item_id = %d AND user_id = %d",
    $item_id,
    $user_id
);
```

## Performance

### Caching

```php
// Transient caching
set_transient('roadmap_stats_' . $product, $stats, HOUR_IN_SECONDS);
get_transient('roadmap_stats_' . $product);
```

### Query Optimization

```php
// Efficient meta queries
'meta_query' => [
    'relation' => 'AND',
    ['key' => '_roadmap_votes', 'type' => 'NUMERIC']
]
```

## Migration Guide

### From 1.0.x to 1.1.0

New features:
- Auto-approval setting in General tab
- Copy to clipboard for shortcodes
- Enhanced admin UI with animations

Database changes:
- No schema changes required

Code changes:
- Updated admin CSS classes
- New JavaScript handlers for copy functionality

## Support

- **Documentation**: [GitHub Wiki](https://github.com/wbcomdesigns/product-roadmap/wiki)
- **Issues**: [GitHub Issues](https://github.com/wbcomdesigns/product-roadmap/issues)
- **Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/product-roadmap/)

## License

GPL v2 or later - see LICENSE file for details.