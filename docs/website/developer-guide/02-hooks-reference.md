# Hooks Reference

Every hook Product Roadmap fires is prefixed `roadmap_`. This is the current, code-verified list for version 1.8.0 - it replaces any hook list from older documentation, some of which described hooks that were never actually implemented.

## Action hooks

| Hook | Fires when | Arguments |
|---|---|---|
| `roadmap_plugin_loaded` | The plugin has finished loading | - |
| `roadmap_register_api_routes` | REST routes are being registered | `$namespace` |
| `roadmap_vote_submitted` | A vote is recorded | `$item_id, $user_id, $vote_value` |
| `roadmap_comment_submitted` | A comment is posted through the REST comment endpoint | `$comment_id, $item_id, $user_id` |
| `roadmap_suggestion_submitted` | A feature suggestion is submitted | `$post_id, $user_id` |
| `roadmap_status_changed` | An item's status taxonomy term changes | `$item_id, $new_status_name, $old_status_name` |
| `roadmap_item_viewed` | A single roadmap item page is viewed | `$post_id` |
| `roadmap_settings_updated` | Settings are saved via the REST settings endpoint | `$settings` |
| `roadmap_after_main_content` | After the shortcode's main content renders | `$atts` |
| `roadmap_enqueue_assets` | When frontend assets are enqueued | - |
| `roadmap_dashboard_after_sections` | After the admin dashboard's built-in sections render | - |
| `roadmap_settings_tab_content` | A non-form settings tab is rendering | `$tab` |
| `roadmap_render_license_tab` | The License settings tab renders (Pro adds this tab) | - |
| `roadmap_render_templates_settings` | The Templates settings section renders | `$settings` |
| `roadmap_render_advanced_settings` | The Advanced settings section renders | `$settings` |

## Filter hooks

| Hook | Purpose | Arguments |
|---|---|---|
| `roadmap_item_post_type_args` | Modify the `roadmap_item` post type registration args | `$args` |
| `roadmap_changelog_post_type_args` | Modify the `roadmap_changelog` post type registration args | `$args` |
| `roadmap_item_meta_fields` | Add or modify the Roadmap Item Details meta box fields | `$fields` |
| `roadmap_api_query_args` | Modify the `WP_Query` args used by the items REST endpoint | `$args, $params` |
| `roadmap_api_filter_by_product` | Modify the product filter applied to an items query | `$args, $product_filter` |
| `roadmap_api_format_item` | Modify a single item's REST API response shape | `$item, $post` |
| `roadmap_api_changelog_args` | Modify the `WP_Query` args used by the changelog REST endpoint | `$args, $request` |
| `roadmap_max_per_page` | Change the ceiling on how many items a single REST request can ask for (default 200) | `$max` |
| `roadmap_guest_user_ip` | Modify the IP address used to identify a guest for voting/rate-limiting | `$ip` |
| `roadmap_shortcode_data` | Modify the data passed to the Vue app that renders `[roadmap]` | `$app_data, $atts` |
| `roadmap_changelog_shortcode_args` | Modify the query args used by `[roadmap_changelog]` | `$query_args, $atts` |
| `roadmap_available_templates` | Register additional `[roadmap]` layouts (used by Pro) | `$templates` |
| `roadmap_changelog_enqueue_frontend_css` | Return `false` to stop the plugin enqueuing its own changelog frontend CSS | `true` |
| `roadmap_changelog_frontend_css_url` | Change the URL used for changelog frontend CSS (e.g. to point at a theme override) | `$css_url` |
| `roadmap_settings_tabs` | Add settings tabs (used by Pro for Templates, Advanced, License) | `$tabs` |
| `roadmap_settings_form_tabs` | Add tabs that should render inside the settings `<form>` wrapper | `$form_tabs` |
| `roadmap_settings_group` | Change the `settings_fields()` group for a given tab | `$settings_group, $active_tab` |
| `roadmap_tab_setting_keys` | Register which option keys a custom settings tab owns, so saving one tab doesn't wipe another's values | `$tab_setting_keys` |

## Example: adding a custom item meta field

```php
add_filter( 'roadmap_item_meta_fields', function ( $fields ) {
    $fields[] = array(
        'id'    => '_roadmap_customer_name',
        'label' => __( 'Requested by (customer)', 'my-integration' ),
        'type'  => 'text',
        'free'  => true,
    );
    return $fields;
} );
```

## Example: reacting to a status change

```php
add_action( 'roadmap_status_changed', function ( $item_id, $new_status, $old_status ) {
    if ( 'completed' === $new_status ) {
        // Notify an external system that this item shipped.
    }
}, 10, 3 );
```

## Next steps

- [REST API Reference](03-rest-api-reference.md)
- [Shortcode Reference](04-shortcode-reference.md)
