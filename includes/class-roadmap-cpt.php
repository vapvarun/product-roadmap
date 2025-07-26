<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_CPT {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'register_post_types']);
        add_action('init', [$this, 'register_taxonomies']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_roadmap_item', [$this, 'save_meta_boxes'], 10, 2);
        add_action('wp_insert_post', [$this, 'set_default_product_on_create'], 10, 3);
        
        // Custom permalink structure
        add_filter('post_type_link', [$this, 'custom_roadmap_permalinks'], 10, 2);
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('pre_get_posts', [$this, 'filter_roadmap_by_product']);
        
        // Custom template
        add_filter('template_include', [$this, 'custom_template']);
        
        // Pro hooks
        add_filter('roadmap_item_meta_fields', [$this, 'filter_meta_fields']);
    }
    
    public function register_post_types() {
        $labels = [
            'name'               => __('Roadmap Items', 'roadmap-plugin'),
            'singular_name'      => __('Roadmap Item', 'roadmap-plugin'),
            'menu_name'          => __('Roadmap', 'roadmap-plugin'),
            'add_new'            => __('Add New', 'roadmap-plugin'),
            'add_new_item'       => __('Add New Roadmap Item', 'roadmap-plugin'),
            'edit_item'          => __('Edit Roadmap Item', 'roadmap-plugin'),
            'new_item'           => __('New Roadmap Item', 'roadmap-plugin'),
            'view_item'          => __('View Roadmap Item', 'roadmap-plugin'),
            'search_items'       => __('Search Roadmap Items', 'roadmap-plugin'),
            'not_found'          => __('No roadmap items found', 'roadmap-plugin'),
            'not_found_in_trash' => __('No roadmap items found in trash', 'roadmap-plugin'),
        ];
        
        $args = [
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => false, // We'll handle this with custom rules
            'capability_type'     => 'post',
            'has_archive'         => false, // Archive will be product-specific
            'hierarchical'        => false,
            'menu_position'       => 25,
            'menu_icon'           => 'dashicons-chart-line',
            'supports'            => ['title', 'editor', 'author', 'comments'],
            'show_in_rest'        => true,
            'rest_base'           => 'roadmap-items',
        ];
        
        $args = apply_filters('roadmap_item_post_type_args', $args);
        
        register_post_type('roadmap_item', $args);
    }
    
    public function register_taxonomies() {
        // Status taxonomy
        $status_labels = [
            'name'              => __('Statuses', 'roadmap-plugin'),
            'singular_name'     => __('Status', 'roadmap-plugin'),
            'search_items'      => __('Search Statuses', 'roadmap-plugin'),
            'all_items'         => __('All Statuses', 'roadmap-plugin'),
            'edit_item'         => __('Edit Status', 'roadmap-plugin'),
            'update_item'       => __('Update Status', 'roadmap-plugin'),
            'add_new_item'      => __('Add New Status', 'roadmap-plugin'),
            'new_item_name'     => __('New Status Name', 'roadmap-plugin'),
            'menu_name'         => __('Statuses', 'roadmap-plugin'),
        ];
        
        $status_args = [
            'hierarchical'      => true,
            'labels'            => $status_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'roadmap-status'],
            'show_in_rest'      => true,
        ];
        
        register_taxonomy('roadmap_status', ['roadmap_item'], $status_args);
        
        // Category taxonomy
        $category_labels = [
            'name'              => __('Categories', 'roadmap-plugin'),
            'singular_name'     => __('Category', 'roadmap-plugin'),
            'search_items'      => __('Search Categories', 'roadmap-plugin'),
            'all_items'         => __('All Categories', 'roadmap-plugin'),
            'edit_item'         => __('Edit Category', 'roadmap-plugin'),
            'update_item'       => __('Update Category', 'roadmap-plugin'),
            'add_new_item'      => __('Add New Category', 'roadmap-plugin'),
            'new_item_name'     => __('New Category Name', 'roadmap-plugin'),
            'menu_name'         => __('Categories', 'roadmap-plugin'),
        ];
        
        $category_args = [
            'hierarchical'      => true,
            'labels'            => $category_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'roadmap-category'],
            'show_in_rest'      => true,
        ];
        
        register_taxonomy('roadmap_category', ['roadmap_item'], $category_args);
        
        // Product taxonomy (even in free version, but limited to one)
        $product_labels = [
            'name'              => __('Products', 'roadmap-plugin'),
            'singular_name'     => __('Product', 'roadmap-plugin'),
            'search_items'      => __('Search Products', 'roadmap-plugin'),
            'all_items'         => __('All Products', 'roadmap-plugin'),
            'edit_item'         => __('Edit Product', 'roadmap-plugin'),
            'update_item'       => __('Update Product', 'roadmap-plugin'),
            'add_new_item'      => __('Add New Product', 'roadmap-plugin'),
            'new_item_name'     => __('New Product Name', 'roadmap-plugin'),
            'menu_name'         => __('Products', 'roadmap-plugin'),
        ];
        
        $product_args = [
            'hierarchical'      => true,
            'labels'            => $product_labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'roadmap-product'],
            'show_in_rest'      => true,
            'capabilities'      => [
                'manage_terms' => (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE) ? 'manage_categories' : 'do_not_allow',
                'edit_terms'   => (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE) ? 'manage_categories' : 'do_not_allow',
                'delete_terms' => (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE) ? 'manage_categories' : 'do_not_allow',
                'assign_terms' => 'edit_posts',
            ],
        ];
        
        register_taxonomy('roadmap_product', ['roadmap_item'], $product_args);
        
        // Insert default statuses on activation
        $this->insert_default_terms();
    }
    
    private function insert_default_terms() {
        $default_statuses = [
            'planned' => __('Planned', 'roadmap-plugin'),
            'in-progress' => __('In Progress', 'roadmap-plugin'),
            'completed' => __('Completed', 'roadmap-plugin'),
            'on-hold' => __('On Hold', 'roadmap-plugin'),
            'under-review' => __('Under Review', 'roadmap-plugin'),
        ];
        
        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'roadmap_status')) {
                wp_insert_term($name, 'roadmap_status', ['slug' => $slug]);
            }
        }
        
        // Insert default product term
        $default_product = get_option('roadmap_default_product', 'main');
        if (!term_exists($default_product, 'roadmap_product')) {
            $term = wp_insert_term(
                __('Main Product', 'roadmap-plugin'),
                'roadmap_product',
                ['slug' => $default_product]
            );
            
            if (!is_wp_error($term)) {
                update_option('roadmap_default_product_id', $term['term_id']);
            }
        }
    }
    
    public function add_meta_boxes() {
        add_meta_box(
            'roadmap_item_details',
            __('Roadmap Item Details', 'roadmap-plugin'),
            [$this, 'render_meta_box'],
            'roadmap_item',
            'normal',
            'high'
        );
    }
    
    public function render_meta_box($post) {
        wp_nonce_field('roadmap_item_meta_box', 'roadmap_item_meta_box_nonce');
        
        $fields = $this->get_meta_fields();
        
        foreach ($fields as $field) {
            $value = get_post_meta($post->ID, $field['id'], true);
            
            // Use default value if empty
            if (empty($value) && isset($field['default'])) {
                $value = $field['default'];
            }
            ?>
            <p>
                <label for="<?php echo esc_attr($field['id']); ?>">
                    <?php echo esc_html($field['label']); ?>:
                </label>
                <br>
                <?php $this->render_field($field, $value); ?>
                <?php if (!empty($field['description'])) : ?>
                    <span class="description"><?php echo esc_html($field['description']); ?></span>
                <?php endif; ?>
            </p>
            <?php
        }
    }
    
    private function get_meta_fields() {
        $fields = [
            [
                'id' => '_roadmap_target_date',
                'label' => __('Target Date', 'roadmap-plugin'),
                'type' => 'date',
                'free' => true,
            ],
            [
                'id' => '_roadmap_votes',
                'label' => __('Votes', 'roadmap-plugin'),
                'type' => 'number',
                'readonly' => true,
                'free' => true,
            ],
            [
                'id' => '_roadmap_priority',
                'label' => __('Priority', 'roadmap-plugin'),
                'type' => 'select',
                'options' => [
                    'low' => __('Low', 'roadmap-plugin'),
                    'medium' => __('Medium', 'roadmap-plugin'),
                    'high' => __('High', 'roadmap-plugin'),
                    'critical' => __('Critical', 'roadmap-plugin'),
                ],
                'free' => true,
            ],
        ];
        
        return apply_filters('roadmap_item_meta_fields', $fields);
    }
    
    private function render_field($field, $value) {
        $readonly = isset($field['readonly']) && $field['readonly'] ? 'readonly' : '';
        
        switch ($field['type']) {
            case 'text':
            case 'date':
            case 'number':
                ?>
                <input type="<?php echo esc_attr($field['type']); ?>" 
                       id="<?php echo esc_attr($field['id']); ?>" 
                       name="<?php echo esc_attr($field['id']); ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       class="widefat"
                       <?php echo $readonly; ?>>
                <?php
                break;
                
            case 'select':
                ?>
                <select id="<?php echo esc_attr($field['id']); ?>" 
                        name="<?php echo esc_attr($field['id']); ?>" 
                        class="widefat">
                    <option value=""><?php _e('Select...', 'roadmap-plugin'); ?></option>
                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                        <option value="<?php echo esc_attr($option_value); ?>" 
                                <?php selected($value, $option_value); ?>>
                            <?php echo esc_html($option_label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php
                break;
                
            case 'textarea':
                ?>
                <textarea id="<?php echo esc_attr($field['id']); ?>" 
                          name="<?php echo esc_attr($field['id']); ?>" 
                          rows="4" 
                          class="widefat"><?php echo esc_textarea($value); ?></textarea>
                <?php
                break;
        }
    }
    
    public function save_meta_boxes($post_id, $post) {
        if (!isset($_POST['roadmap_item_meta_box_nonce']) || 
            !wp_verify_nonce($_POST['roadmap_item_meta_box_nonce'], 'roadmap_item_meta_box')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        $fields = $this->get_meta_fields();
        
        foreach ($fields as $field) {
            if (isset($field['readonly']) && $field['readonly']) {
                continue;
            }
            
            if (isset($_POST[$field['id']])) {
                $value = sanitize_text_field($_POST[$field['id']]);
                update_post_meta($post_id, $field['id'], $value);
            }
        }
    }
    
    public function filter_meta_fields($fields) {
        // This filter allows pro addon to add additional fields
        return $fields;
    }
    
    public function set_default_product_on_create($post_id, $post, $update) {
        // Only for new roadmap items
        if ($update || $post->post_type !== 'roadmap_item') {
            return;
        }
        
        // Set default product term if not already set
        $product_terms = wp_get_object_terms($post_id, 'roadmap_product');
        if (empty($product_terms)) {
            $default_product_id = get_option('roadmap_default_product_id');
            if (!$default_product_id) {
                // Try to get the default product term
                $default_product = get_option('roadmap_default_product', 'main');
                $term = get_term_by('slug', $default_product, 'roadmap_product');
                if ($term) {
                    $default_product_id = $term->term_id;
                }
            }
            
            if ($default_product_id) {
                wp_set_object_terms($post_id, (int)$default_product_id, 'roadmap_product');
            }
        }
    }
    
    /**
     * Add custom rewrite rules for product-specific URLs
     */
    public function add_rewrite_rules() {
        // Single roadmap item: /roadmap/{product}/{item-slug}/
        add_rewrite_rule(
            '^roadmap/([^/]+)/([^/]+)/?$',
            'index.php?roadmap_item=$matches[2]&roadmap_product=$matches[1]',
            'top'
        );
        
        // Product archive: /roadmap/{product}/
        add_rewrite_rule(
            '^roadmap/([^/]+)/?$',
            'index.php?roadmap_product=$matches[1]&post_type=roadmap_item',
            'top'
        );
    }
    
    /**
     * Add custom query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'roadmap_product';
        return $vars;
    }
    
    /**
     * Customize permalinks for roadmap items
     */
    public function custom_roadmap_permalinks($permalink, $post) {
        if ($post->post_type !== 'roadmap_item') {
            return $permalink;
        }
        
        // Get the product term
        $product_terms = wp_get_object_terms($post->ID, 'roadmap_product');
        $product_slug = !empty($product_terms) ? $product_terms[0]->slug : 'main';
        
        return home_url('/roadmap/' . $product_slug . '/' . $post->post_name . '/');
    }
    
    /**
     * Filter roadmap items by product in single views
     */
    public function filter_roadmap_by_product($query) {
        // Only on frontend single roadmap item pages
        if (!is_admin() && $query->is_main_query() && is_singular('roadmap_item')) {
            $product_slug = get_query_var('roadmap_product');
            
            if ($product_slug) {
                // Verify the item belongs to this product
                $post_id = $query->get('p');
                if (!$post_id) {
                    $post_name = $query->get('roadmap_item');
                    if ($post_name) {
                        $post = get_page_by_path($post_name, OBJECT, 'roadmap_item');
                        if ($post) {
                            $post_id = $post->ID;
                        }
                    }
                }
                
                if ($post_id) {
                    $item_products = wp_get_object_terms($post_id, 'roadmap_product', ['fields' => 'slugs']);
                    if (!in_array($product_slug, $item_products)) {
                        // Item doesn't belong to this product, show 404
                        $query->set_404();
                    }
                }
            }
        }
    }
    
    /**
     * Load custom templates
     */
    public function custom_template($template) {
        // Single roadmap item template
        if (is_singular('roadmap_item')) {
            $custom_template = ROADMAP_PLUGIN_PATH . 'templates/single-roadmap-item.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        // Product archive template
        if (get_query_var('roadmap_product') && !is_singular()) {
            $custom_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap-product.php';
            if (file_exists($custom_template)) {
                return $custom_template;
            }
        }
        
        return $template;
    }
}