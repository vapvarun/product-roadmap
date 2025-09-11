# Developer Documentation

This guide provides technical documentation for developers working with the Product Roadmap plugin.

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Core Components](#core-components)
3. [Setup Wizard](#setup-wizard)
4. [Demo Import System](#demo-import-system)
5. [Build System](#build-system)
6. [Hooks and Filters](#hooks-and-filters)
7. [REST API](#rest-api)
8. [Database Schema](#database-schema)
9. [Frontend Architecture](#frontend-architecture)

## Architecture Overview

The Product Roadmap plugin follows WordPress best practices with a modular architecture:

```
product-roadmap/
├── includes/           # PHP classes and core functionality
├── vue-app/           # Vue.js frontend application
├── assets/            # CSS, JS, and images
├── admin/             # Admin-specific functionality
├── templates/         # PHP templates
├── languages/         # Translation files
└── build scripts      # Build and release automation
```

## Core Components

### 1. Custom Post Type (CPT)

**File**: `includes/class-roadmap-cpt.php`

The plugin registers a custom post type `roadmap_item` with three taxonomies:

- **roadmap_status**: Item status (planned, in-progress, completed, on-hold, under-review)
- **roadmap_category**: Item categories for grouping
- **roadmap_product**: Product assignment (limited to 'main' in free version)

Key features:
- Custom permalinks: `/roadmap/{product}/{item-slug}/`
- Block editor support with custom meta fields
- Automatic product assignment for new items

### 2. Shortcode System

**File**: `includes/class-roadmap-shortcode.php`

The `[roadmap]` shortcode accepts these parameters:

```php
[roadmap 
    product="main"           // REQUIRED - specify product to display
    mode="view"              // view or manage
    allow_frontend_edit="false" 
    class=""                 // custom CSS classes
    template="board"         // board, timeline, list (pro)
    sort=""                  // votes, recent, comments, priority
]
```

**Important:** The `product` parameter is mandatory. Always specify which product roadmap to display.

### 3. REST API

**File**: `includes/class-roadmap-rest-api.php`

Endpoints:
- `GET /wp-json/roadmap/v1/items` - List roadmap items
- `GET /wp-json/roadmap/v1/items/{id}` - Get single item
- `POST /wp-json/roadmap/v1/items` - Create item
- `PUT /wp-json/roadmap/v1/items/{id}` - Update item
- `DELETE /wp-json/roadmap/v1/items/{id}` - Delete item
- `POST /wp-json/roadmap/v1/items/{id}/vote` - Vote on item
- `POST /wp-json/roadmap/v1/items/{id}/comment` - Add comment

## Setup Wizard

**File**: `includes/class-roadmap-setup-wizard.php`

The setup wizard provides a 4-step onboarding process:

### Step 1: Product Configuration
```php
// Options saved:
update_option('roadmap_product_name', $product_name);
update_option('roadmap_item_singular_label', $singular);
update_option('roadmap_item_plural_label', $plural);
```

### Step 2: Basic Settings
```php
// Options saved:
update_option('roadmap_enable_voting', 'yes'|'no');
update_option('roadmap_allow_guest_voting', 'yes'|'no');
update_option('roadmap_enable_comments', 'yes'|'no');
```

### Step 3: Demo Import
- Imports 11 demo items across all status types
- Creates categories and assigns taxonomies
- Automatically creates a roadmap page

### Step 4: Completion
- Marks wizard as completed
- Provides links to view roadmap and manage items

## Demo Import System

**File**: `includes/class-roadmap-demo-import.php`

The demo import system:
1. Ensures taxonomies exist before import
2. Creates default product term ('main')
3. Creates status terms if missing
4. Imports items with proper taxonomy assignments
5. Sets meta fields (priority, votes, progress)

### Demo Content Structure
```php
$demo_items = [
    [
        'title' => 'Feature Title',
        'content' => 'Description',
        'status' => 'planned|in-progress|completed|on-hold|under-review',
        'priority' => 'low|medium|high|critical',
        'category' => 'Features|Improvements|Bug Fixes|Infrastructure',
        'votes' => 0-100,
        'target_date' => 'Y-m-d' // optional
    ]
];
```

## Build System

### Development Build
```bash
./build.sh
```

### Release Build
```bash
./build-release.sh
```

The release build script:
1. Runs production build
2. Creates release directory structure
3. Copies all plugin files
4. Removes development files
5. Generates two zip packages:
   - Free version: `product-roadmap-{version}.zip`
   - Pro version: `product-roadmap-pro-{version}.zip`

### Files excluded from release:
- `.git`, `.gitignore`
- `node_modules`
- `vue-app/src`, `vue-app/public`
- Build scripts
- Development documentation

## Hooks and Filters

### Actions

```php
// After roadmap item is created
do_action('roadmap_item_created', $post_id, $post);

// After vote is cast
do_action('roadmap_vote_cast', $item_id, $user_id);

// After comment is added
do_action('roadmap_comment_added', $item_id, $comment_id);

// When importing demo content
do_action('roadmap_before_demo_import');
do_action('roadmap_after_demo_import');
```

### Filters

```php
// Modify CPT arguments
add_filter('roadmap_item_post_type_args', function($args) {
    return $args;
});

// Modify meta fields
add_filter('roadmap_item_meta_fields', function($fields) {
    return $fields;
});

// Modify shortcode output data
add_filter('roadmap_shortcode_data', function($data, $atts) {
    return $data;
}, 10, 2);

// Modify REST API response
add_filter('roadmap_api_item_data', function($data, $item) {
    return $data;
}, 10, 2);
```

## Database Schema

### Custom Tables

**wp_roadmap_votes**
```sql
CREATE TABLE wp_roadmap_votes (
    id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    item_id bigint(20) unsigned NOT NULL,
    user_id bigint(20) unsigned DEFAULT NULL,
    ip_address varchar(45) DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_item_id (item_id),
    KEY idx_user_id (user_id)
);
```

### Post Meta

- `_roadmap_priority`: Item priority (low, medium, high, critical)
- `_roadmap_progress`: Completion percentage (0-100)
- `_roadmap_target_date`: Expected completion date
- `_roadmap_vote_count`: Cached vote count
- `_roadmap_external_link`: External reference URL

### Options

- `roadmap_product_name`: Custom product name
- `roadmap_item_singular_label`: Singular item label
- `roadmap_item_plural_label`: Plural item label
- `roadmap_enable_voting`: Enable voting feature
- `roadmap_enable_comments`: Enable comments
- `roadmap_allow_guest_voting`: Allow anonymous votes
- `roadmap_wizard_completed`: Setup wizard status
- `roadmap_demo_imported`: Demo import status
- `roadmap_page_id`: Created roadmap page ID

## Frontend Architecture

The frontend is built with Vue 3 and uses:

- **Vue Router**: For SPA navigation
- **Pinia**: State management
- **Axios**: API requests
- **Tailwind CSS**: Styling

### Key Components

```
vue-app/src/
├── components/
│   ├── RoadmapBoard.vue      # Board layout
│   ├── RoadmapItem.vue       # Individual item
│   ├── VoteButton.vue        # Voting interface
│   └── CommentSection.vue    # Comments
├── stores/
│   ├── roadmap.js            # Main data store
│   └── user.js               # User state
└── api/
    └── roadmap.js            # API service layer
```

### Building Frontend

Development:
```bash
cd vue-app
npm install
npm run dev
```

Production:
```bash
npm run build
```

## Testing

### PHP Unit Tests
```bash
composer test
```

### JavaScript Tests
```bash
cd vue-app
npm test
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add/update tests
5. Update documentation
6. Submit a pull request

### Coding Standards

- Follow WordPress Coding Standards for PHP
- Use ESLint configuration for JavaScript
- Add proper documentation blocks
- Include filters/actions for extensibility

## Security Considerations

- All user inputs are sanitized and validated
- Nonces are used for all form submissions
- Capabilities are checked before any data modification
- SQL queries use prepared statements
- REST API requires authentication for write operations

## Performance Optimization

- Vote counts are cached in post meta
- Database queries are optimized with proper indexes
- Assets are minified in production
- Lazy loading for comments
- Efficient REST API pagination

## Troubleshooting

### Common Issues

1. **Taxonomies not appearing**: Run `flush_rewrite_rules()` or save permalinks
2. **Demo import fails**: Ensure CPT is registered before import
3. **Shortcode not displaying**: Check if product term exists
4. **REST API 404**: Ensure pretty permalinks are enabled

### Debug Mode

Enable debug logging:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('ROADMAP_DEBUG', true);
```

## License

GPL v2 or later