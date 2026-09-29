# WordPress Product Roadmap Plugin - Developer Guide

**Version:** 1.7.0

## Table of Contents
- [Architecture Overview](#architecture-overview)
- [What's New in v1.7.0](#whats-new-in-v170)
- [Hooks Reference](#hooks-reference)
  - [Action Hooks](#action-hooks)
  - [Filter Hooks](#filter-hooks)
- [REST API Endpoints](#rest-api-endpoints)
  - [Roadmap Items API](#roadmap-items-api)
  - [Changelog API](#changelog-api-v130)
- [JavaScript Integration](#javascript-integration)
- [Database Schema](#database-schema)
- [Custom Post Types](#custom-post-types)
- [Extending the Plugin](#extending-the-plugin)
- [Code Examples](#code-examples)
- [Best Practices](#best-practices)

## Architecture Overview

### Plugin Structure
```
product-roadmap/
├── includes/
│   ├── class-roadmap-admin.php       # Admin interface
│   ├── class-roadmap-api.php         # REST API endpoints
│   ├── class-roadmap-comments.php    # Comment integration
│   ├── class-roadmap-cpt.php         # Custom post types
│   ├── class-roadmap-migration.php   # Database migrations
│   ├── class-roadmap-notifications.php # Email notifications
│   ├── class-roadmap-permissions.php # Access control
│   ├── class-roadmap-settings.php    # Settings management
│   ├── class-roadmap-shortcode.php   # Shortcode handler
│   ├── class-roadmap-suggestions.php # Feature suggestions
│   ├── class-roadmap-template.php    # Template loader
│   └── class-roadmap-voting.php      # Voting system
├── vue-app/                          # Vue.js application
├── assets/                           # CSS and JS files
├── templates/                        # PHP templates
└── product-roadmap.php              # Main plugin file
```

### Key Classes
- **Roadmap_Plugin**: Main plugin class
- **Roadmap_CPT**: Handles custom post type registration
- **Roadmap_API**: REST API implementation
- **Roadmap_Shortcode**: Shortcode rendering
- **Roadmap_Voting**: Vote management

## What's New in v1.7.0

This release focuses on theme integration, spam defense, and data-layer hardening.

**Highlights for developers:**
- **Theme tokens and dark mode.** The frontend is now token-driven and adopts the active theme's accent color via `--wp--preset--color--primary` (falling back to the plugin blue). Dark mode is driven only by the theme's own light/dark toggle, not the visitor's OS preference. This applies to the board, the changelog (shortcode, single, and archive), and all other surfaces.
- **Voting.** A persistent "Voted" state (checkmark + pill) survives themes that restyle buttons. Guest voting by IP is gated behind a setting.
- **Spam defense (automatic).** Votes and suggestions are rate-limited (per user, or per IP for guests), and the suggestion form carries a honeypot field. No configuration required.
- **Notifications.** Status-change emails to voters and commenters include an unsubscribe link, and the per-type notification toggles (vote/comment/suggestion) are now honored.
- **Data layer.** The votes table is indexed and queries are bounded for large roadmaps. An `uninstall.php` now cleans up plugin data, strings are wrapped for translation via `load_plugin_textdomain()`, and frontend assets cache-bust on rebuild.
- **Layouts.** The free version ships the `minimal` layout only; `board`, `list`, `cards`, `timeline`, and `ideabox` are Pro and fall back to `minimal` on free installs.

## What's New in v1.3.1

This release includes security fixes, performance optimizations, and new extension points.

### Security Fixes

**IP Spoofing Prevention**
Guest voting now uses only `REMOTE_ADDR` for IP tracking. Previously, the code checked `HTTP_X_FORWARDED_FOR` and other headers which can be spoofed by malicious users.

```php
// Before (vulnerable)
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

// After (secure)
$ip = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
```

### Performance Optimizations

**N+1 Query Fix**
The REST API now batch-fetches vote counts for all items in a single query instead of querying each item individually.

```php
// Before: One query per item
foreach ($items as $item) {
    $votes = get_post_meta($item->ID, '_roadmap_votes', true);
}

// After: Single query for all items
$item_ids = wp_list_pluck($items, 'ID');
$votes = $wpdb->get_results(
    "SELECT item_id, COUNT(*) as count
     FROM {$wpdb->prefix}roadmap_votes
     WHERE item_id IN (" . implode(',', $item_ids) . ")
     GROUP BY item_id"
);
```

**Dashboard Query Optimization**
Stats now use `SELECT COUNT(*)` instead of loading all posts and counting in PHP.

**Initialization Optimization**
Role capabilities and default taxonomy terms are now only inserted on plugin activation or version upgrade, not on every page load.

### New Filter Hooks

**`roadmap_changelog_shortcode_data`**
Filter changelog data before rendering. Used by Pro addon for product filtering.

```php
$changelog_data = apply_filters('roadmap_changelog_shortcode_data', $changelog_data, $atts);
```

**`roadmap_api_changelog_args`**
Filter WP_Query args for changelog REST API requests.

```php
$args = apply_filters('roadmap_api_changelog_args', $args, $request);
```

### Pro Addon Integration

These hooks allow the Pro addon to extend changelog functionality:

```php
// Pro addon hooks into changelog shortcode
add_filter('roadmap_changelog_shortcode_data', function($data, $atts) {
    if (!empty($atts['product'])) {
        // Filter by product
    }
    return $data;
}, 10, 2);

// Pro addon hooks into changelog API
add_filter('roadmap_api_changelog_args', function($args, $request) {
    $product = $request->get_param('product');
    if ($product) {
        $args['tax_query'][] = [
            'taxonomy' => 'roadmap_product',
            'field' => 'slug',
            'terms' => $product,
        ];
    }
    return $args;
}, 10, 2);
```

## Hooks Reference

### Action Hooks

#### Plugin Lifecycle Hooks

**`roadmap_plugin_activated`**
```php
do_action('roadmap_plugin_activated');
```
Fired when the plugin is activated. Use for initial setup.

**`roadmap_plugin_deactivated`**
```php
do_action('roadmap_plugin_deactivated');
```
Fired when the plugin is deactivated.

**`roadmap_plugin_loaded`**
```php
do_action('roadmap_plugin_loaded');
```
Fired after the plugin is fully loaded.

#### Item Management Hooks

**`roadmap_before_save_item`**
```php
do_action('roadmap_before_save_item', $item_id, $item_data);
```
Fired before a roadmap item is saved.

**`roadmap_after_save_item`**
```php
do_action('roadmap_after_save_item', $item_id, $item_data);
```
Fired after a roadmap item is saved.

**`roadmap_before_delete_item`**
```php
do_action('roadmap_before_delete_item', $item_id);
```
Fired before a roadmap item is deleted.

**`roadmap_after_delete_item`**
```php
do_action('roadmap_after_delete_item', $item_id);
```
Fired after a roadmap item is deleted.

#### Voting Hooks

**`roadmap_before_vote`**
```php
do_action('roadmap_before_vote', $item_id, $user_id, $vote_value);
```
Fired before a vote is recorded.

**`roadmap_after_vote`**
```php
do_action('roadmap_after_vote', $item_id, $user_id, $vote_value);
```
Fired after a vote is recorded.

**`roadmap_vote_removed`**
```php
do_action('roadmap_vote_removed', $item_id, $user_id);
```
Fired when a vote is removed.

#### Suggestion Hooks

**`roadmap_suggestion_submitted`**
```php
do_action('roadmap_suggestion_submitted', $suggestion_data);
```
Fired when a new suggestion is submitted.

**`roadmap_suggestion_approved`**
```php
do_action('roadmap_suggestion_approved', $item_id);
```
Fired when a suggestion is approved.

#### Display Hooks

**`roadmap_before_shortcode_output`**
```php
do_action('roadmap_before_shortcode_output', $atts);
```
Fired before shortcode output is rendered.

**`roadmap_after_shortcode_output`**
```php
do_action('roadmap_after_shortcode_output', $atts);
```
Fired after shortcode output is rendered.

**`roadmap_before_item_display`**
```php
do_action('roadmap_before_item_display', $item);
```
Fired before an individual item is displayed.

**`roadmap_after_item_display`**
```php
do_action('roadmap_after_item_display', $item);
```
Fired after an individual item is displayed.

### Filter Hooks

#### Permission Filters

**`roadmap_can_vote`**
```php
$can_vote = apply_filters('roadmap_can_vote', $can_vote, $user_id, $item_id);
```
Filter whether a user can vote on an item.

**`roadmap_can_suggest`**
```php
$can_suggest = apply_filters('roadmap_can_suggest', $can_suggest, $user_id);
```
Filter whether a user can submit suggestions.

**`roadmap_can_edit_item`**
```php
$can_edit = apply_filters('roadmap_can_edit_item', $can_edit, $item_id, $user_id);
```
Filter whether a user can edit an item.

#### Display Filters

**`roadmap_item_classes`**
```php
$classes = apply_filters('roadmap_item_classes', $classes, $item);
```
Filter CSS classes for roadmap items.

**`roadmap_shortcode_attributes`**
```php
$atts = apply_filters('roadmap_shortcode_attributes', $atts);
```
Filter shortcode attributes before processing.

**`roadmap_template_path`**
```php
$template = apply_filters('roadmap_template_path', $template, $template_name);
```
Filter template file paths.

**`roadmap_board_columns`**
```php
$columns = apply_filters('roadmap_board_columns', $columns);
```
Filter board view columns.

#### Data Filters

**`roadmap_item_data`**
```php
$item_data = apply_filters('roadmap_item_data', $item_data, $item_id);
```
Filter item data before saving.

**`roadmap_default_statuses`**
```php
$statuses = apply_filters('roadmap_default_statuses', $statuses);
```
Filter default status terms.

**`roadmap_default_categories`**
```php
$categories = apply_filters('roadmap_default_categories', $categories);
```
Filter default category terms.

**`roadmap_vote_count`**
```php
$count = apply_filters('roadmap_vote_count', $count, $item_id);
```
Filter vote count display.

#### Settings Filters

**`roadmap_settings_tabs`**
```php
$tabs = apply_filters('roadmap_settings_tabs', $tabs);
```
Add custom settings tabs.

**`roadmap_settings_fields`**
```php
$fields = apply_filters('roadmap_settings_fields', $fields, $tab);
```
Add custom settings fields.

**`roadmap_settings_defaults`**
```php
$defaults = apply_filters('roadmap_settings_defaults', $defaults);
```
Filter default settings values.

#### API Filters

**`roadmap_api_item_response`**
```php
$response = apply_filters('roadmap_api_item_response', $response, $item, $request);
```
Filter REST API item response.

**`roadmap_api_permissions`**
```php
$allowed = apply_filters('roadmap_api_permissions', $allowed, $request);
```
Filter REST API permissions.

## REST API Endpoints

### Authentication
All endpoints require WordPress REST API authentication using nonces.

### Endpoints

#### Get Items
```
GET /wp-json/roadmap/v1/items
```
Parameters:
- `product`: Filter by product slug
- `status`: Filter by status slug
- `category`: Filter by category slug
- `page`: Page number
- `per_page`: Items per page

#### Get Single Item
```
GET /wp-json/roadmap/v1/items/{id}
```

#### Create Item
```
POST /wp-json/roadmap/v1/items
```
Required capabilities: `edit_roadmap_items`

Body:
```json
{
  "title": "Item Title",
  "content": "Description",
  "status": "planned",
  "categories": ["feature"],
  "priority": "high",
  "progress": 0
}
```

#### Update Item
```
PUT /wp-json/roadmap/v1/items/{id}
```
Required capabilities: `edit_roadmap_items`

#### Delete Item
```
DELETE /wp-json/roadmap/v1/items/{id}
```
Required capabilities: `delete_roadmap_items`

#### Submit Vote
```
POST /wp-json/roadmap/v1/vote
```
Body:
```json
{
  "item_id": 123,
  "vote_value": 1
}
```

#### Get Settings
```
GET /wp-json/roadmap/v1/settings
```
Returns statuses, categories, and configuration.

### Changelog API (v1.3.0)

The Changelog API provides endpoints for managing release notes and version history.

#### Get Changelog Entries
```
GET /wp-json/roadmap/v1/changelog
```

Parameters:
- `product`: Filter by product slug (Pro addon required for multi-product)
- `type`: Filter by changelog type (feature, fix, security, etc.)
- `per_page`: Items per page (default: 10)
- `page`: Page number

Response:
```json
{
  "items": [
    {
      "id": 123,
      "title": "Version 2.0 Release",
      "version": "2.0.0",
      "release_date": "2025-01-15",
      "release_type": "major",
      "content": "Full release notes...",
      "changes": [
        {"type": "new", "description": "Added dark mode"},
        {"type": "fix", "description": "Fixed login bug"}
      ],
      "products": ["main"]
    }
  ],
  "total": 15,
  "pages": 2
}
```

#### Get Single Changelog Entry
```
GET /wp-json/roadmap/v1/changelog/{id}
```

#### Get Changelog Types
```
GET /wp-json/roadmap/v1/changelog/types
```

Returns available changelog types:
```json
[
  {"slug": "new", "name": "New", "color": "#22c55e"},
  {"slug": "improved", "name": "Improved", "color": "#3b82f6"},
  {"slug": "fixed", "name": "Fixed", "color": "#f97316"},
  {"slug": "changed", "name": "Changed", "color": "#8b5cf6"},
  {"slug": "deprecated", "name": "Deprecated", "color": "#eab308"},
  {"slug": "removed", "name": "Removed", "color": "#ef4444"},
  {"slug": "security", "name": "Security", "color": "#991b1b"}
]
```

#### Create Changelog Entry
```
POST /wp-json/roadmap/v1/changelog
```
Required capabilities: `edit_posts`

Body:
```json
{
  "title": "Version 2.1 Release",
  "version": "2.1.0",
  "release_date": "2025-01-20",
  "release_type": "minor",
  "content": "This release includes...",
  "changes": [
    {"type": "new", "description": "Added export feature"},
    {"type": "fix", "description": "Fixed performance issue"}
  ],
  "products": ["main"]
}
```

#### Update Changelog Entry
```
PUT /wp-json/roadmap/v1/changelog/{id}
```
Required capabilities: `edit_posts`

#### Delete Changelog Entry
```
DELETE /wp-json/roadmap/v1/changelog/{id}
```
Required capabilities: `delete_posts`

### Pro Addon Changelog Endpoints

When the Pro addon is active, additional endpoints become available:

#### Get Changelog by Product (Pro)
```
GET /wp-json/roadmap/v1/changelog/by-product/{product_slug}
```

Parameters:
- `limit`: Max entries to return (default: 10)
- `page`: Page number for pagination (v1.3.1+)

#### Get Products with Changelogs (Pro)
```
GET /wp-json/roadmap/v1/changelog/products
```

Returns products that have changelog entries:
```json
[
  {"id": 1, "slug": "main", "name": "Main Product", "count": 5},
  {"id": 2, "slug": "mobile-app", "name": "Mobile App", "count": 3}
]
```

## JavaScript Integration

### Global JavaScript Object
```javascript
// Available on pages with roadmap
window.roadmapData = {
  api_url: 'https://site.com/wp-json/roadmap/v1',
  nonce: 'security_nonce',
  user: {
    id: 1,
    can_vote: true,
    can_edit: false,
    can_suggest: true
  },
  settings: {
    enable_voting: true,
    enable_comments: true
  }
};
```

### Custom Events

**`roadmap:vote:submitted`**
```javascript
document.addEventListener('roadmap:vote:submitted', (e) => {
  console.log('Vote submitted for item:', e.detail.itemId);
});
```

**`roadmap:item:updated`**
```javascript
document.addEventListener('roadmap:item:updated', (e) => {
  console.log('Item updated:', e.detail.item);
});
```

### Vue.js Component Access
```javascript
// Access Vue app instance
const app = document.querySelector('#roadmap-app').__vue_app__;

// Access component methods
app.config.globalProperties.$roadmap.refresh();
```

## Database Schema

### Custom Tables

#### wp_roadmap_votes
```sql
CREATE TABLE wp_roadmap_votes (
  id bigint(20) NOT NULL AUTO_INCREMENT,
  item_id bigint(20) NOT NULL,
  user_id bigint(20) NOT NULL,
  vote_value tinyint(1) DEFAULT 1,
  voted_at datetime DEFAULT CURRENT_TIMESTAMP,
  ip_address varchar(45),
  PRIMARY KEY (id),
  UNIQUE KEY user_item (user_id, item_id),
  KEY item_id (item_id),
  KEY user_id (user_id)
);
```

### Post Meta Keys
- `_roadmap_priority`: Item priority (low, medium, high, critical)
- `_roadmap_progress`: Completion percentage (0-100)
- `_roadmap_target_date`: Target completion date
- `_roadmap_start_date`: Start date
- `_roadmap_end_date`: End date
- `_roadmap_vote_count`: Cached vote count
- `_roadmap_github_issue`: Associated GitHub issue URL
- `_roadmap_external_link`: External reference URL

## Custom Post Types

### roadmap_item
```php
$args = array(
  'labels' => array(
    'name' => 'Roadmap Items',
    'singular_name' => 'Roadmap Item'
  ),
  'public' => true,
  'has_archive' => true,
  'supports' => array('title', 'editor', 'excerpt', 'comments'),
  'menu_icon' => 'dashicons-chart-line',
  'show_in_rest' => true,
  'capability_type' => 'roadmap_item',
  'map_meta_cap' => true
);
```

### Taxonomies

#### roadmap_status
- Hierarchical: No
- Public: Yes
- Show in REST: Yes

#### roadmap_category
- Hierarchical: Yes
- Public: Yes
- Show in REST: Yes

#### roadmap_product
- Hierarchical: No
- Public: Yes
- Show in REST: Yes

## Extending the Plugin

### Adding Custom Templates
```php
// Add custom template
add_filter('roadmap_template_path', function($template, $template_name) {
  if ($template_name === 'custom-view') {
    return plugin_dir_path(__FILE__) . 'templates/custom-view.php';
  }
  return $template;
}, 10, 2);
```

### Creating Custom Status
```php
// Add custom status programmatically
add_filter('roadmap_default_statuses', function($statuses) {
  $statuses[] = array(
    'name' => 'Beta Testing',
    'slug' => 'beta-testing',
    'description' => 'In beta testing phase'
  );
  return $statuses;
});
```

A status whose slug isn't in the built-in list shows as a neutral grey chip. To give it a colour, see Colours and Theming below.

### Colours and Theming

Every frontend colour comes from one stylesheet, `assets/css/roadmap-tokens.css`, registered as the `roadmap-tokens` style handle. The board, the item and changelog templates, and Pro's stylesheets all load it as a dependency.

- **Accent:** follows the theme's `--wp--preset--color--primary` and falls back to the plugin blue.
- **Dark mode:** follows the theme's own light/dark toggle (BuddyX, BuddyX Pro, Reign), never the visitor's OS setting. Light/dark is a swap of the same custom properties.
- **Overriding:** redefine any token in your theme's CSS. Also redefine it under your theme's dark selector if the colour should change in dark mode too.

```css
:root {
  --rm-planned: #0b5cad;
  --rm-planned-tint: #e3effb;
}
```

- **Status and priority colours** are mapped once. Any element with a `roadmap-status-{slug}` class gets `--rm-status` / `--rm-status-tint`, and chips are `<span class="roadmap-badge roadmap-status-{slug}">`. Priority works the same way with `roadmap-priority-{low|medium|high|critical}`, which sets `--rm-priority-color` / `--rm-priority-tint`. To colour a custom status:

```css
.roadmap-status-beta-testing {
  --rm-status: var(--rm-review);
  --rm-status-tint: var(--rm-review-tint);
}
```

- **Contributing:** `bin/check-raw-colors.sh` (run in CI) fails when a raw colour value appears in a frontend source outside the tokens file. Use a token, or add one to the tokens file with a light and a dark value.

### Adding Meta Fields
```php
// Add custom meta box
add_action('add_meta_boxes', function() {
  add_meta_box(
    'roadmap_custom_field',
    'Custom Field',
    'render_custom_field',
    'roadmap_item',
    'side'
  );
});

// Save custom meta
add_action('save_post_roadmap_item', function($post_id) {
  if (isset($_POST['custom_field'])) {
    update_post_meta($post_id, '_roadmap_custom_field', 
      sanitize_text_field($_POST['custom_field']));
  }
});
```

### Custom Voting Logic
```php
// Modify voting permissions
add_filter('roadmap_can_vote', function($can_vote, $user_id, $item_id) {
  // Only allow voting on items in certain status
  $status = wp_get_post_terms($item_id, 'roadmap_status', 
    array('fields' => 'slugs'));
  
  if (in_array('completed', $status)) {
    return false;
  }
  
  return $can_vote;
}, 10, 3);
```

## Code Examples

### Display Roadmap Programmatically
```php
// In theme template
<?php echo do_shortcode('[roadmap product="main" template="board"]'); ?>

// With custom attributes
<?php
$atts = array(
  'product' => 'main',
  'template' => 'board',
  'items_per_page' => 20
);
echo Roadmap_Shortcode::render($atts);
?>
```

### Get Roadmap Items
```php
// Query roadmap items
$args = array(
  'post_type' => 'roadmap_item',
  'posts_per_page' => 10,
  'tax_query' => array(
    array(
      'taxonomy' => 'roadmap_status',
      'field' => 'slug',
      'terms' => 'in-progress'
    )
  ),
  'meta_key' => '_roadmap_priority',
  'orderby' => 'meta_value',
  'order' => 'DESC'
);
$items = new WP_Query($args);
```

### Custom API Endpoint
```php
// Register custom endpoint
add_action('rest_api_init', function() {
  register_rest_route('roadmap/v1', '/stats', array(
    'methods' => 'GET',
    'callback' => 'get_roadmap_stats',
    'permission_callback' => '__return_true'
  ));
});

function get_roadmap_stats($request) {
  global $wpdb;
  
  $stats = array(
    'total_items' => wp_count_posts('roadmap_item')->publish,
    'total_votes' => $wpdb->get_var(
      "SELECT COUNT(*) FROM {$wpdb->prefix}roadmap_votes"
    ),
    'active_voters' => $wpdb->get_var(
      "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}roadmap_votes"
    )
  );
  
  return rest_ensure_response($stats);
}
```

### Email Notification
```php
// Send custom notification
add_action('roadmap_after_vote', function($item_id, $user_id, $vote_value) {
  $item = get_post($item_id);
  $user = get_userdata($user_id);
  
  $subject = sprintf('New vote on: %s', $item->post_title);
  $message = sprintf(
    'User %s voted on roadmap item "%s"',
    $user->display_name,
    $item->post_title
  );
  
  wp_mail(get_option('admin_email'), $subject, $message);
}, 10, 3);
```

## Best Practices

### Security
1. Always validate and sanitize input
2. Check capabilities before operations
3. Use nonces for form submissions
4. Escape output appropriately

### Performance
1. Cache expensive queries
2. Use transients for vote counts
3. Minimize database queries
4. Implement pagination

### Code Organization
1. Follow WordPress coding standards
2. Use meaningful hook names
3. Document your extensions
4. Prefix function names

### Testing
1. Test with different user roles
2. Verify permission checks
3. Test edge cases
4. Check for conflicts

## Migration Guide

### From Version 1.x to 2.x
```php
// Handle migration
register_activation_hook(__FILE__, function() {
  $version = get_option('roadmap_plugin_version');
  
  if (version_compare($version, '2.0.0', '<')) {
    // Run migration
    require_once 'includes/class-roadmap-migration.php';
    Roadmap_Migration::migrate_to_v2();
  }
});
```

## Troubleshooting

### Debug Mode
```php
// Enable debug logging
define('ROADMAP_DEBUG', true);

// Log debug information
if (defined('ROADMAP_DEBUG') && ROADMAP_DEBUG) {
  error_log('Roadmap Debug: ' . print_r($data, true));
}
```

### Common Issues
1. **REST API not working**: Check permalinks settings
2. **Votes not recording**: Verify database tables exist
3. **Templates not loading**: Check template hierarchy
4. **Permissions issues**: Review capability mapping

## Support

For developer support:
1. GitHub repository for bug reports
2. Developer documentation
3. Code examples repository
4. WordPress.org support forum

Remember to always test your customizations thoroughly and follow WordPress best practices for plugin development.