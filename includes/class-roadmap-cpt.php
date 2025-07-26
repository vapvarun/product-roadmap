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
        add_action('init', [$this, 'register_block_meta']);
        add_action('add_meta_boxes', [$this, 'add_meta_boxes']);
        add_action('save_post_roadmap_item', [$this, 'save_meta_boxes'], 10, 2);
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_block_editor_assets']);
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
        $basic_fields = array_filter($fields, function($field) {
            return empty($field['pro']) || !$field['pro'];
        });
        $pro_fields = array_filter($fields, function($field) {
            return !empty($field['pro']) && $field['pro'];
        });
        
        // Basic Fields Section
        if (!empty($basic_fields)) {
            ?>
            <div class="roadmap-meta-section">
                <?php foreach ($basic_fields as $field) : 
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
                <?php endforeach; ?>
            </div>
            <?php
        }
        
        // Pro Fields Section
        if (!empty($pro_fields)) {
            ?>
            <div class="roadmap-meta-section">
                <h3><?php _e('Pro Features', 'roadmap-plugin'); ?></h3>
                <?php foreach ($pro_fields as $field) : 
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
                <?php endforeach; ?>
            </div>
            <?php
        }
    }
    
    private function get_meta_fields() {
        $fields = [
            [
                'id' => '_roadmap_priority',
                'label' => __('Priority', 'roadmap-plugin'),
                'type' => 'select',
                'options' => [
                    '' => __('-- Select Priority --', 'roadmap-plugin'),
                    'low' => __('Low', 'roadmap-plugin'),
                    'medium' => __('Medium', 'roadmap-plugin'),
                    'high' => __('High', 'roadmap-plugin'),
                    'critical' => __('Critical', 'roadmap-plugin'),
                ],
                'description' => __('Set the priority level for this item.', 'roadmap-plugin'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_progress',
                'label' => __('Progress', 'roadmap-plugin'),
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'step' => 5,
                'default' => 0,
                'description' => __('Current completion percentage (0-100%)', 'roadmap-plugin'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_target_date',
                'label' => __('Target Date', 'roadmap-plugin'),
                'type' => 'date',
                'description' => __('Expected completion date', 'roadmap-plugin'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_start_date',
                'label' => __('Start Date', 'roadmap-plugin'),
                'type' => 'date',
                'description' => __('When work begins on this item', 'roadmap-plugin'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_end_date',
                'label' => __('End Date', 'roadmap-plugin'),
                'type' => 'date',
                'description' => __('When work was completed', 'roadmap-plugin'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_effort_estimate',
                'label' => __('Effort Estimate', 'roadmap-plugin'),
                'type' => 'select',
                'options' => [
                    '' => __('-- Select Effort --', 'roadmap-plugin'),
                    'xs' => __('XS - Very Small (1-2 days)', 'roadmap-plugin'),
                    's' => __('S - Small (3-5 days)', 'roadmap-plugin'),
                    'm' => __('M - Medium (1-2 weeks)', 'roadmap-plugin'),
                    'l' => __('L - Large (2-4 weeks)', 'roadmap-plugin'),
                    'xl' => __('XL - Extra Large (1-2 months)', 'roadmap-plugin'),
                    'xxl' => __('XXL - Huge (2+ months)', 'roadmap-plugin'),
                ],
                'description' => __('Estimated effort required', 'roadmap-plugin'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_github_issue',
                'label' => __('GitHub Issue URL', 'roadmap-plugin'),
                'type' => 'url',
                'placeholder' => '',
                'description' => __('Link to related GitHub issue', 'roadmap-plugin'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_external_link',
                'label' => __('External Link', 'roadmap-plugin'),
                'type' => 'url',
                'placeholder' => '',
                'description' => __('Link to external documentation or reference', 'roadmap-plugin'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_vote_count',
                'label' => __('Vote Count', 'roadmap-plugin'),
                'type' => 'number',
                'readonly' => true,
                'description' => __('Total number of votes (automatically calculated)', 'roadmap-plugin'),
                'free' => true,
            ],
        ];
        
        return apply_filters('roadmap_item_meta_fields', $fields);
    }
    
    private function render_field($field, $value) {
        $readonly = isset($field['readonly']) && $field['readonly'] ? 'readonly' : '';
        $is_pro = isset($field['pro']) && $field['pro'];
        $is_disabled = $is_pro && !defined('ROADMAP_PRO_VERSION');
        $placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
        
        // Check if field should be disabled for free version
        if ($is_disabled) {
            ?>
            <div class="roadmap-pro-field-wrapper">
                <input type="text" 
                       value="<?php _e('Pro Feature', 'roadmap-plugin'); ?>" 
                       class="widefat" 
                       disabled>
                <span class="roadmap-pro-badge">PRO</span>
            </div>
            <?php
            return;
        }
        
        switch ($field['type']) {
            case 'text':
            case 'date':
            case 'number':
            case 'url':
                ?>
                <input type="<?php echo esc_attr($field['type']); ?>" 
                       id="<?php echo esc_attr($field['id']); ?>" 
                       name="<?php echo esc_attr($field['id']); ?>" 
                       value="<?php echo esc_attr($value); ?>" 
                       placeholder="<?php echo esc_attr($placeholder); ?>"
                       class="widefat"
                       <?php echo $readonly; ?>>
                <?php
                break;
                
            case 'range':
                $min = isset($field['min']) ? $field['min'] : 0;
                $max = isset($field['max']) ? $field['max'] : 100;
                $step = isset($field['step']) ? $field['step'] : 1;
                ?>
                <div class="roadmap-range-wrapper">
                    <input type="range" 
                           id="<?php echo esc_attr($field['id']); ?>" 
                           name="<?php echo esc_attr($field['id']); ?>" 
                           value="<?php echo esc_attr($value); ?>" 
                           min="<?php echo esc_attr($min); ?>"
                           max="<?php echo esc_attr($max); ?>"
                           step="<?php echo esc_attr($step); ?>"
                           class="roadmap-range-input"
                           <?php echo $readonly; ?>>
                    <span class="roadmap-range-value"><?php echo esc_html($value); ?>%</span>
                </div>
                <script>
                    jQuery(document).ready(function($) {
                        $('#<?php echo esc_js($field['id']); ?>').on('input', function() {
                            $(this).next('.roadmap-range-value').text($(this).val() + '%');
                        });
                    });
                </script>
                <?php
                break;
                
            case 'select':
                ?>
                <select id="<?php echo esc_attr($field['id']); ?>" 
                        name="<?php echo esc_attr($field['id']); ?>" 
                        class="widefat"
                        <?php echo $readonly; ?>>
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
    
    /**
     * Register block editor meta fields
     */
    public function register_block_meta() {
        $fields = $this->get_meta_fields();
        
        foreach ($fields as $field) {
            // Skip readonly fields
            if (isset($field['readonly']) && $field['readonly']) {
                continue;
            }
            
            // Register meta field for block editor
            register_post_meta('roadmap_item', $field['id'], [
                'show_in_rest' => true,
                'single' => true,
                'type' => $this->get_field_rest_type($field),
                'auth_callback' => function() {
                    return current_user_can('edit_posts');
                }
            ]);
        }
    }
    
    /**
     * Get REST API type for field
     */
    private function get_field_rest_type($field) {
        switch ($field['type']) {
            case 'number':
            case 'range':
                return 'integer';
            case 'date':
            case 'text':
            case 'select':
            case 'url':
            case 'textarea':
            default:
                return 'string';
        }
    }
    
    /**
     * Enqueue block editor assets
     */
    public function enqueue_block_editor_assets() {
        if (!function_exists('get_current_screen')) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'roadmap_item') {
            return;
        }
        
        // Enqueue block editor scripts
        wp_enqueue_script(
            'roadmap-block-editor',
            ROADMAP_PLUGIN_URL . 'assets/js/block-editor.js',
            ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data'],
            ROADMAP_VERSION,
            true
        );
        
        // Enqueue meta box styles
        wp_enqueue_style(
            'roadmap-meta-box',
            ROADMAP_PLUGIN_URL . 'assets/css/meta-box.css',
            [],
            ROADMAP_VERSION
        );
        
        // Enqueue RTL styles if needed
        if (is_rtl() && file_exists(ROADMAP_PLUGIN_PATH . 'assets/css/meta-box-rtl.css')) {
            wp_enqueue_style(
                'roadmap-meta-box-rtl',
                ROADMAP_PLUGIN_URL . 'assets/css/meta-box-rtl.css',
                ['roadmap-meta-box'],
                ROADMAP_VERSION
            );
        }
        
        // Localize script with field data
        wp_localize_script('roadmap-block-editor', 'roadmapMeta', [
            'fields' => $this->get_meta_fields(),
            'nonce' => wp_create_nonce('roadmap_meta'),
            'isPro' => defined('ROADMAP_PRO_VERSION')
        ]);
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