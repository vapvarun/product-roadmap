# Roadmap Plugin Product System

## Overview

The Roadmap Plugin uses a product-based architecture where roadmap items are organized by products. This allows you to have separate roadmaps for different products, services, or projects.

## Free vs Pro Version

### Free Version (Base Plugin)
- **Single Product**: Limited to the default "main" product
- **Shortcode**: `[roadmap]` or `[roadmap product="main"]`
- **Product Taxonomy**: Registered but not editable
- **Admin Menu**: No Products menu item

### Pro Version (With Pro Addon)
- **Multiple Products**: Create unlimited products
- **Shortcode**: `[roadmap product="your-product"]`
- **Product Management**: Full access to add/edit/delete products
- **Admin Menu**: Products menu item visible
- **Product Settings**: Each product can have custom settings

## How It Works

### 1. Default Product
When the plugin is activated, it automatically creates a default product called "main". This ensures the plugin works out of the box.

### 2. Shortcode Behavior

**Free Version:**
```
[roadmap]                    → Defaults to product="main"
[roadmap product="main"]     → Works normally
[roadmap product="mobile"]   → Shows error (Pro required)
```

**Pro Version:**
```
[roadmap product="main"]     → Shows main product roadmap
[roadmap product="mobile"]   → Shows mobile product roadmap
[roadmap product="desktop"]  → Shows desktop product roadmap
```

### 3. Product Taxonomy Column
When viewing the Products taxonomy in the admin (Pro only), you'll see a "Shortcode" column that displays the exact shortcode to use for each product. Click to copy!

### 4. Admin Menu Structure

**Free Version:**
```
Roadmap
├── Dashboard
├── All Items
├── Add New Item
├── Statuses
├── Categories
└── Settings
```

**Pro Version:**
```
Roadmap
├── Dashboard
├── Products (Pro only)
├── All Items
├── Add New Item
├── Statuses
├── Categories
└── Settings
```

### 5. Product Capabilities

The product taxonomy uses WordPress capabilities to control access:
- **Free Version**: Can assign products to items but cannot create/edit/delete products
- **Pro Version**: Full management capabilities for products

### 6. URL Structure

Each roadmap item has a product-specific URL:
```
/roadmap/{product-slug}/{item-slug}/
```

Examples:
- `/roadmap/main/user-authentication/`
- `/roadmap/mobile/offline-mode/`
- `/roadmap/desktop/dark-theme/`

## Implementation Details

### Product Registration
```php
// Products are registered with conditional capabilities
'capabilities' => [
    'manage_terms' => ROADMAP_PRO_ACTIVE ? 'manage_categories' : 'do_not_allow',
    'edit_terms'   => ROADMAP_PRO_ACTIVE ? 'manage_categories' : 'do_not_allow',
    'delete_terms' => ROADMAP_PRO_ACTIVE ? 'manage_categories' : 'do_not_allow',
    'assign_terms' => 'edit_posts',
],
```

### Default Status Integration
When creating new items:
1. Global default status is used (configurable in Settings)
2. Product-specific default status overrides global (Pro feature)
3. All items are created with post_status = 'publish'

## Benefits

1. **Organization**: Keep different product roadmaps separate
2. **Scalability**: Add new products as your business grows
3. **Flexibility**: Each product can have its own settings (Pro)
4. **User Experience**: Visitors only see relevant roadmap items
5. **SEO**: Product-specific URLs help with organization

## Migration Path

Starting with Free version:
1. All items use the "main" product
2. Shortcodes work with or without product parameter

Upgrading to Pro:
1. Existing items remain in "main" product
2. Create new products via Products menu
3. Move items to appropriate products
4. Update shortcodes to use specific products

## Best Practices

1. **Product Naming**: Use descriptive, URL-friendly names (e.g., "mobile-app", "web-platform")
2. **Organization**: Create products for major offerings, not minor features
3. **Documentation**: Document which shortcode to use for each product
4. **Testing**: Test shortcodes after creating new products