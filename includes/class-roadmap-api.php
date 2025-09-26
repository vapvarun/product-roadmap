<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_API {
    
    private static $instance = null;
    private $namespace = 'roadmap/v1';
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }
    
    public function register_routes() {
        // Get roadmap items
        register_rest_route($this->namespace, '/items', [
            'methods' => 'GET',
            'callback' => [$this, 'get_items'],
            'permission_callback' => '__return_true',
            'args' => [
                'product' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'description' => 'Product slug to filter roadmap items',
                ],
                'status' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'category' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'page' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
                'per_page' => [
                    'default' => 100,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
        
        // Vote on item
        register_rest_route($this->namespace, '/vote', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_vote'],
            'permission_callback' => [$this, 'can_vote'],
            'args' => [
                'item_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
                'vote_value' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
        
        // Submit comment
        register_rest_route($this->namespace, '/comment', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_comment'],
            'permission_callback' => [$this, 'can_comment'],
            'args' => [
                'item_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
                'content' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
            ],
        ]);
        
        // Suggest new item
        register_rest_route($this->namespace, '/suggest', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_suggestion'],
            'permission_callback' => [$this, 'can_suggest'],
            'args' => [
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'description' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'category' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
        
        // Get statistics
        register_rest_route($this->namespace, '/stats', [
            'methods' => 'GET',
            'callback' => [$this, 'get_stats'],
            'permission_callback' => '__return_true',
        ]);
        
        // Frontend management endpoints
        register_rest_route($this->namespace, '/items/(?P<id>\d+)', [
            [
                'methods' => 'PUT',
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'can_edit_items'],
                'args' => [
                    'title' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'content' => [
                        'sanitize_callback' => 'sanitize_textarea_field',
                    ],
                    'status' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'category' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'priority' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                    'target_date' => [
                        'sanitize_callback' => 'sanitize_text_field',
                    ],
                ],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'can_edit_items'],
            ],
        ]);
        
        register_rest_route($this->namespace, '/items', [
            'methods' => 'POST',
            'callback' => [$this, 'create_item'],
            'permission_callback' => [$this, 'can_edit_items'],
            'args' => [
                'title' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'content' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
                'status' => [
                    'default' => 'planned',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'category' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'priority' => [
                    'default' => 'medium',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'target_date' => [
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
        
        // Settings endpoints
        register_rest_route($this->namespace, '/settings', [
            [
                'methods' => 'GET',
                'callback' => [$this, 'get_settings'],
                'permission_callback' => '__return_true',
            ],
            [
                'methods' => 'POST',
                'callback' => [$this, 'update_settings'],
                'permission_callback' => [$this, 'can_manage_settings'],
            ],
        ]);
        
        // Comment endpoints
        register_rest_route($this->namespace, '/comment', [
            'methods' => 'POST',
            'callback' => [$this, 'submit_comment'],
            'permission_callback' => [$this, 'can_comment'],
            'args' => [
                'item_id' => [
                    'required' => true,
                    'sanitize_callback' => 'absint',
                ],
                'content' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field',
                ],
            ],
        ]);
        
        register_rest_route($this->namespace, '/items/(?P<id>\d+)/comments', [
            'methods' => 'GET',
            'callback' => [$this, 'get_item_comments'],
            'permission_callback' => '__return_true',
        ]);
        
        // Allow pro addon to register additional routes
        do_action('roadmap_register_api_routes', $this->namespace);
    }
    
    public function get_items($request) {
        $params = $request->get_params();
        
        $args = [
            'post_type' => 'roadmap_item',
            'posts_per_page' => $params['per_page'],
            'paged' => $params['page'],
            'post_status' => 'publish',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- API endpoint requires filtering
            'meta_query' => [],
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- API endpoint requires filtering
            'tax_query' => [],
        ];
        
        // Filter by product (mandatory)
        $product_filter = $params['product'];
        
        if (!$product_filter) {
            return new WP_Error('missing_product', 'Product parameter is required', ['status' => 400]);
        }
        
        if (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE) {
            // Let pro addon handle product filtering
            $args = apply_filters('roadmap_api_filter_by_product', $args, $product_filter);
        } else {
            // Free version - filter by default product using taxonomy
            $args['tax_query'][] = [
                'taxonomy' => 'roadmap_product',
                'field' => 'slug',
                'terms' => $product_filter,
            ];
        }
        
        // Filter by status
        if (!empty($params['status'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'roadmap_status',
                'field' => 'slug',
                'terms' => $params['status'],
            ];
        }
        
        // Filter by category
        if (!empty($params['category'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'roadmap_category',
                'field' => 'slug',
                'terms' => $params['category'],
            ];
        }
        
        $query = new WP_Query($args);
        $items = [];
        
        foreach ($query->posts as $post) {
            $items[] = $this->format_item($post);
        }
        
        return new WP_REST_Response([
            'items' => $items,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current_page' => $params['page'],
        ], 200);
    }
    
    private function format_item($post) {
        $item = [
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content,
            'excerpt' => wp_trim_words($post->post_content, 20),
            'author' => get_the_author_meta('display_name', $post->post_author),
            'date' => $post->post_date,
            'modified' => $post->post_modified,
            'votes' => (int) get_post_meta($post->ID, '_roadmap_votes', true),
            'comment_count' => $post->comment_count,
            'priority' => get_post_meta($post->ID, '_roadmap_priority', true),
            'progress' => (int) get_post_meta($post->ID, '_roadmap_progress', true),
            'target_date' => get_post_meta($post->ID, '_roadmap_target_date', true),
            'external_link' => get_post_meta($post->ID, '_roadmap_external_link', true),
            'start_date' => get_post_meta($post->ID, '_roadmap_start_date', true),
            'end_date' => get_post_meta($post->ID, '_roadmap_end_date', true),
            'effort_estimate' => get_post_meta($post->ID, '_roadmap_effort_estimate', true),
            'github_issue' => get_post_meta($post->ID, '_roadmap_github_issue', true),
            'status' => $this->get_item_terms($post->ID, 'roadmap_status'),
            'categories' => $this->get_item_terms($post->ID, 'roadmap_category'),
            'user_voted' => $this->has_user_voted($post->ID),
            'product' => $this->get_item_terms($post->ID, 'roadmap_product'),
            'link' => get_permalink($post->ID),
        ];
        
        return apply_filters('roadmap_api_format_item', $item, $post);
    }
    
    private function get_item_terms($post_id, $taxonomy) {
        $terms = wp_get_post_terms($post_id, $taxonomy);
        $formatted = [];
        
        foreach ($terms as $term) {
            $formatted[] = [
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            ];
        }
        
        return $formatted;
    }
    
    private function has_user_voted($item_id) {
        if (!is_user_logged_in()) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';
        $user_id = get_current_user_id();
        
        $vote = $wpdb->get_var($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT vote_value FROM {$table} WHERE user_id = %d AND item_id = %d",
            $user_id,
            $item_id
        )); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, caching not needed for single user vote check
        
        return $vote ? (int) $vote : false;
    }
    
    public function submit_vote($request) {
        $item_id = $request->get_param('item_id');
        $vote_value = $request->get_param('vote_value');
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';
        
        // Check if user already voted
        $existing_vote = $wpdb->get_row($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT * FROM {$table} WHERE user_id = %d AND item_id = %d",
            $user_id,
            $item_id
        )); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, caching not needed for vote submission
        
        if ($existing_vote) {
            // Update existing vote
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table update
            $wpdb->update(
                $table,
                ['vote_value' => $vote_value],
                ['user_id' => $user_id, 'item_id' => $item_id]
            );
        } else {
            // Insert new vote
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table insert
            $wpdb->insert(
                $table,
                [
                    'user_id' => $user_id,
                    'item_id' => $item_id,
                    'vote_value' => $vote_value,
                ]
            );
        }
        
        // Update vote count
        $this->update_vote_count($item_id);
        
        do_action('roadmap_vote_submitted', $item_id, $user_id, $vote_value);
        
        return new WP_REST_Response([
            'success' => true,
            'vote_count' => (int) get_post_meta($item_id, '_roadmap_votes', true),
        ], 200);
    }
    
    private function update_vote_count($item_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';
        
        $vote_count = $wpdb->get_var($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT SUM(vote_value) FROM {$table} WHERE item_id = %d",
            $item_id
        )); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, vote count is cached in post meta
        
        update_post_meta($item_id, '_roadmap_votes', (int) $vote_count);
    }
    
    public function submit_suggestion($request) {
        $title = $request->get_param('title');
        $description = $request->get_param('description');
        $category = $request->get_param('category');
        $user_id = get_current_user_id();

        // Check if auto-approve is enabled
        $global_settings = get_option('roadmap_global_settings', []);
        $auto_approve = !empty($global_settings['auto_approve_suggestions']);
        $post_status = $auto_approve ? 'publish' : 'pending';

        $post_data = [
            'post_title' => $title,
            'post_content' => $description,
            'post_type' => 'roadmap_item',
            'post_status' => $post_status,
            'post_author' => $user_id,
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Set status based on settings
            $default_status = $auto_approve ? ($global_settings['suggestion_default_status'] ?? 'planned') : 'planned';
            wp_set_object_terms($post_id, $default_status, 'roadmap_status');
            
            // Set category if provided
            if ($category) {
                wp_set_object_terms($post_id, $category, 'roadmap_category');
            }
            
            // Set default product
            update_post_meta($post_id, '_roadmap_product', get_option('roadmap_default_product', 'main'));
            
            // Set priority if provided
            $priority = $request->get_param('priority');
            if ($priority) {
                update_post_meta($post_id, '_roadmap_priority', sanitize_text_field($priority));
            }
            
            // Set external link if provided
            $external_link = $request->get_param('external_link');
            if ($external_link) {
                update_post_meta($post_id, '_roadmap_external_link', esc_url_raw($external_link));
            }
            
            do_action('roadmap_suggestion_submitted', $post_id, $user_id);
            
            return new WP_REST_Response([
                'success' => true,
                'message' => __('Thank you for your suggestion!', 'product-roadmap'),
            ], 200);
        }
        
        return new WP_Error('suggestion_failed', __('Failed to submit suggestion', 'product-roadmap'), ['status' => 500]);
    }
    
    public function get_stats($request) {
        $stats = [
            'total_items' => wp_count_posts('roadmap_item')->publish,
            'total_votes' => $this->get_total_votes(),
            'total_comments' => $this->get_total_comments(),
            'items_by_status' => $this->get_items_by_status(),
        ];
        
        return new WP_REST_Response($stats, 200);
    }
    
    private function get_total_votes() {
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Statistics endpoint, caching not needed
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Statistics endpoint, table name is safe
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }
    
    private function get_total_comments() {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Statistics endpoint, caching not needed
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID IN (
                SELECT ID FROM $wpdb->posts WHERE post_type = %s
            )",
            'roadmap_item'
        ));
    }
    
    private function get_items_by_status() {
        $statuses = get_terms([
            'taxonomy' => 'roadmap_status',
            'hide_empty' => false,
        ]);
        
        $counts = [];
        
        foreach ($statuses as $status) {
            $counts[$status->slug] = [
                'name' => $status->name,
                'count' => $status->count,
            ];
        }
        
        return $counts;
    }
    
    public function create_item($request) {
        $post_data = [
            'post_title' => $request->get_param('title'),
            'post_content' => $request->get_param('content'),
            'post_type' => 'roadmap_item',
            'post_status' => 'publish',
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if ($post_id && !is_wp_error($post_id)) {
            // Get default status from settings
            $global_settings = get_option('roadmap_global_settings', []);
            $default_status = $global_settings['default_status'] ?? 'planned';
            
            // Set taxonomies
            $status = $request->get_param('status') ?: $default_status;
            wp_set_object_terms($post_id, $status, 'roadmap_status');
            
            if ($request->get_param('categories')) {
                // Categories come as array of IDs from frontend
                $categories = $request->get_param('categories');
                if (is_array($categories)) {
                    $categories = array_map('intval', $categories);
                    wp_set_object_terms($post_id, $categories, 'roadmap_category');
                }
            }
            
            // Set all meta fields
            $meta_fields = [
                '_roadmap_priority' => $request->get_param('priority'),
                '_roadmap_progress' => $request->get_param('progress'),
                '_roadmap_target_date' => $request->get_param('target_date'),
                '_roadmap_external_link' => $request->get_param('external_link'),
                '_roadmap_start_date' => $request->get_param('start_date'),
                '_roadmap_end_date' => $request->get_param('end_date'),
                '_roadmap_effort_estimate' => $request->get_param('effort_estimate'),
                '_roadmap_github_issue' => $request->get_param('github_issue'),
            ];
            
            foreach ($meta_fields as $key => $value) {
                if ($value !== null) {
                    update_post_meta($post_id, $key, sanitize_text_field($value));
                }
            }
            
            // Set product taxonomy
            $product = $request->get_param('product');
            if ($product) {
                wp_set_object_terms($post_id, $product, 'roadmap_product');
            }
            
            return new WP_REST_Response($this->format_item(get_post($post_id)), 201);
        }
        
        return new WP_Error('create_failed', __('Failed to create item', 'product-roadmap'), ['status' => 500]);
    }
    
    public function update_item($request) {
        $post_id = $request->get_param('id');
        
        $post_data = [
            'ID' => $post_id,
        ];
        
        if ($request->get_param('title')) {
            $post_data['post_title'] = $request->get_param('title');
        }
        
        if ($request->get_param('content')) {
            $post_data['post_content'] = $request->get_param('content');
        }
        
        $updated = wp_update_post($post_data);
        
        if ($updated && !is_wp_error($updated)) {
            // Update taxonomies
            if ($request->get_param('status')) {
                wp_set_object_terms($post_id, $request->get_param('status'), 'roadmap_status');
            }
            if ($request->get_param('categories')) {
                // Categories come as array of IDs from frontend
                $categories = $request->get_param('categories');
                if (is_array($categories)) {
                    $categories = array_map('intval', $categories);
                    wp_set_object_terms($post_id, $categories, 'roadmap_category');
                }
            }
            
            // Update all meta fields
            $meta_fields = [
                '_roadmap_priority' => $request->get_param('priority'),
                '_roadmap_progress' => $request->get_param('progress'),
                '_roadmap_target_date' => $request->get_param('target_date'),
                '_roadmap_external_link' => $request->get_param('external_link'),
                '_roadmap_start_date' => $request->get_param('start_date'),
                '_roadmap_end_date' => $request->get_param('end_date'),
                '_roadmap_effort_estimate' => $request->get_param('effort_estimate'),
                '_roadmap_github_issue' => $request->get_param('github_issue'),
            ];
            
            foreach ($meta_fields as $key => $value) {
                if ($value !== null) {
                    update_post_meta($post_id, $key, sanitize_text_field($value));
                }
            }
            
            return new WP_REST_Response($this->format_item(get_post($post_id)), 200);
        }
        
        return new WP_Error('update_failed', __('Failed to update item', 'product-roadmap'), ['status' => 500]);
    }
    
    public function delete_item($request) {
        $post_id = $request->get_param('id');
        
        if (wp_delete_post($post_id, true)) {
            return new WP_REST_Response(['success' => true], 200);
        }
        
        return new WP_Error('delete_failed', __('Failed to delete item', 'product-roadmap'), ['status' => 500]);
    }
    
    public function get_settings($request) {
        $settings = [
            'enable_voting' => get_option('roadmap_enable_voting', 'yes') === 'yes',
            'allow_guest_voting' => get_option('roadmap_allow_guest_voting', 'yes') === 'yes',
            'enable_comments' => get_option('roadmap_enable_comments', 'yes') === 'yes',
            'enable_suggestions' => get_option('roadmap_enable_suggestions', 'yes') === 'yes',
            'items_per_page' => (int) get_option('roadmap_items_per_page', 100),
            'current_user' => $this->get_current_user_data(),
            'statuses' => $this->get_all_statuses(),
            'categories' => $this->get_all_categories(),
            'products' => $this->get_all_products(),
            'is_pro' => (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE),
        ];
        
        return new WP_REST_Response($settings, 200);
    }
    
    private function get_current_user_data() {
        if (!is_user_logged_in()) {
            return null;
        }
        
        $user = wp_get_current_user();
        
        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'capabilities' => [
                'vote' => $this->can_vote(),
                'comment' => $this->can_comment(),
                'suggest' => $this->can_suggest(),
                'edit_items' => $this->can_edit_items(),
                'manage_settings' => $this->can_manage_settings(),
            ],
        ];
    }
    
    private function get_all_statuses() {
        $statuses = get_terms([
            'taxonomy' => 'roadmap_status',
            'hide_empty' => false,
        ]);
        
        $formatted = [];
        foreach ($statuses as $status) {
            $formatted[] = [
                'id' => $status->term_id,
                'name' => $status->name,
                'slug' => $status->slug,
            ];
        }
        
        return $formatted;
    }
    
    private function get_all_categories() {
        $categories = get_terms([
            'taxonomy' => 'roadmap_category',
            'hide_empty' => false,
        ]);
        
        $formatted = [];
        foreach ($categories as $category) {
            $formatted[] = [
                'id' => $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
            ];
        }
        
        return $formatted;
    }
    
    private function get_all_products() {
        $products = get_terms([
            'taxonomy' => 'roadmap_product',
            'hide_empty' => false,
        ]);
        
        $formatted = [];
        foreach ($products as $product) {
            $formatted[] = [
                'id' => $product->term_id,
                'name' => $product->name,
                'slug' => $product->slug,
            ];
        }
        
        return $formatted;
    }
    
    public function update_settings($request) {
        $settings = $request->get_json_params();
        
        if (isset($settings['enable_voting'])) {
            update_option('roadmap_enable_voting', $settings['enable_voting'] ? 'yes' : 'no');
        }
        
        if (isset($settings['enable_comments'])) {
            update_option('roadmap_enable_comments', $settings['enable_comments'] ? 'yes' : 'no');
        }
        
        if (isset($settings['enable_suggestions'])) {
            update_option('roadmap_enable_suggestions', $settings['enable_suggestions'] ? 'yes' : 'no');
        }
        
        if (isset($settings['items_per_page'])) {
            update_option('roadmap_items_per_page', absint($settings['items_per_page']));
        }
        
        do_action('roadmap_settings_updated', $settings);
        
        return new WP_REST_Response(['success' => true], 200);
    }
    
    // Comment methods
    public function submit_comment($request) {
        $item_id = $request->get_param('item_id');
        $content = $request->get_param('content');
        
        // Verify the roadmap item exists
        $post = get_post($item_id);
        if (!$post || $post->post_type !== 'roadmap_item') {
            return new WP_Error('invalid_item', __('Invalid roadmap item', 'product-roadmap'), ['status' => 404]);
        }
        
        // Create the comment
        $comment_data = [
            'comment_post_ID' => $item_id,
            'comment_content' => $content,
            'comment_type' => '',
            'comment_approved' => 1, // Auto-approve for logged-in users
            'user_id' => get_current_user_id(),
        ];
        
        $comment_id = wp_insert_comment($comment_data);
        
        if (!$comment_id) {
            return new WP_Error('comment_failed', __('Failed to add comment', 'product-roadmap'), ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'success' => true,
            'comment_id' => $comment_id,
            'message' => __('Comment added successfully', 'product-roadmap'),
        ], 201);
    }
    
    public function get_item_comments($request) {
        $item_id = $request->get_param('id');
        
        $comments = get_comments([
            'post_id' => $item_id,
            'status' => 'approve',
            'orderby' => 'comment_date',
            'order' => 'DESC',
        ]);
        
        $formatted_comments = [];
        foreach ($comments as $comment) {
            $formatted_comments[] = [
                'id' => $comment->comment_ID,
                'content' => $comment->comment_content,
                'author' => $comment->comment_author,
                'author_name' => get_comment_author($comment),
                'author_avatar' => get_avatar_url($comment->user_id ?: $comment->comment_author_email),
                'date' => $comment->comment_date,
                'formatted_date' => get_comment_date('M j, Y', $comment),
            ];
        }
        
        return new WP_REST_Response($formatted_comments, 200);
    }
    
    // Permission callbacks
    public function can_vote() {
        $voting_enabled = get_option('roadmap_enable_voting', 'yes') === 'yes';
        $guest_voting_allowed = get_option('roadmap_allow_guest_voting', 'yes') === 'yes';
        
        return $voting_enabled && ($guest_voting_allowed || is_user_logged_in());
    }
    
    public function can_comment() {
        return is_user_logged_in() && get_option('roadmap_enable_comments', 'yes') === 'yes';
    }
    
    public function can_suggest() {
        return is_user_logged_in() && get_option('roadmap_enable_suggestions', 'yes') === 'yes';
    }
    
    public function can_edit_items() {
        return current_user_can('edit_posts');
    }
    
    public function can_manage_settings() {
        return current_user_can('manage_options');
    }
}