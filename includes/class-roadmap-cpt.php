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

        // Admin post status views
        add_filter('views_edit-roadmap_item', [$this, 'add_pending_post_status_view']);

        // Quick approve/reject actions
        add_filter('post_row_actions', [$this, 'add_quick_approve_actions'], 10, 2);
        add_action('admin_action_approve_roadmap_item', [$this, 'handle_approve_action']);
        add_action('admin_action_reject_roadmap_item', [$this, 'handle_reject_action']);
        add_action('admin_notices', [$this, 'show_action_notices']);
    }
    
    public function register_post_types() {
        // Get custom labels
        $item_singular = get_option('roadmap_item_singular_label', 'Feature');
        $item_plural = get_option('roadmap_item_plural_label', 'Features');
        $product_name = get_option('roadmap_product_name', 'Product');
        
        $labels = [
            'name'               => sprintf(
                /* translators: %s: Product name */
                __('%s Roadmap', 'product-roadmap'),
                $product_name
            ),
            'singular_name'      => $item_singular,
            'menu_name'          => sprintf(
                /* translators: %s: Product name */
                __('%s Roadmap', 'product-roadmap'),
                $product_name
            ),
            'add_new'            => __('Add New', 'product-roadmap'),
            'add_new_item'       => sprintf(
                /* translators: %s: Singular item label */
                __('Add New %s', 'product-roadmap'),
                $item_singular
            ),
            'edit_item'          => sprintf(
                /* translators: %s: Singular item label */
                __('Edit %s', 'product-roadmap'),
                $item_singular
            ),
            'new_item'           => sprintf(
                /* translators: %s: Singular item label */
                __('New %s', 'product-roadmap'),
                $item_singular
            ),
            'view_item'          => sprintf(
                /* translators: %s: Singular item label */
                __('View %s', 'product-roadmap'),
                $item_singular
            ),
            'search_items'       => sprintf(
                /* translators: %s: Plural item label */
                __('Search %s', 'product-roadmap'),
                $item_plural
            ),
            'not_found'          => sprintf(
                /* translators: %s: Plural item label (lowercase) */
                __('No %s found', 'product-roadmap'),
                strtolower($item_plural)
            ),
            'not_found_in_trash' => sprintf(
                /* translators: %s: Plural item label (lowercase) */
                __('No %s found in trash', 'product-roadmap'),
                strtolower($item_plural)
            ),
            'all_items'          => sprintf(
                /* translators: %s: Plural item label */
                __('All %s', 'product-roadmap'),
                $item_plural
            ),
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
            'name'              => __('Statuses', 'product-roadmap'),
            'singular_name'     => __('Status', 'product-roadmap'),
            'search_items'      => __('Search Statuses', 'product-roadmap'),
            'all_items'         => __('All Statuses', 'product-roadmap'),
            'edit_item'         => __('Edit Status', 'product-roadmap'),
            'update_item'       => __('Update Status', 'product-roadmap'),
            'add_new_item'      => __('Add New Status', 'product-roadmap'),
            'new_item_name'     => __('New Status Name', 'product-roadmap'),
            'menu_name'         => __('Statuses', 'product-roadmap'),
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
            'name'              => __('Categories', 'product-roadmap'),
            'singular_name'     => __('Category', 'product-roadmap'),
            'search_items'      => __('Search Categories', 'product-roadmap'),
            'all_items'         => __('All Categories', 'product-roadmap'),
            'edit_item'         => __('Edit Category', 'product-roadmap'),
            'update_item'       => __('Update Category', 'product-roadmap'),
            'add_new_item'      => __('Add New Category', 'product-roadmap'),
            'new_item_name'     => __('New Category Name', 'product-roadmap'),
            'menu_name'         => __('Categories', 'product-roadmap'),
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
            'name'              => __('Products', 'product-roadmap'),
            'singular_name'     => __('Product', 'product-roadmap'),
            'search_items'      => __('Search Products', 'product-roadmap'),
            'all_items'         => __('All Products', 'product-roadmap'),
            'edit_item'         => __('Edit Product', 'product-roadmap'),
            'update_item'       => __('Update Product', 'product-roadmap'),
            'add_new_item'      => __('Add New Product', 'product-roadmap'),
            'new_item_name'     => __('New Product Name', 'product-roadmap'),
            'menu_name'         => __('Products', 'product-roadmap'),
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
            'planned' => __('Planned', 'product-roadmap'),
            'in-progress' => __('In Progress', 'product-roadmap'),
            'completed' => __('Completed', 'product-roadmap'),
            'on-hold' => __('On Hold', 'product-roadmap'),
            'under-review' => __('Under Review', 'product-roadmap'),
        ];
        
        foreach ($default_statuses as $slug => $name) {
            if (!term_exists($slug, 'roadmap_status')) {
                wp_insert_term($name, 'roadmap_status', ['slug' => $slug]);
            }
        }
        
        // Insert default product term
        $default_product = get_option('roadmap_default_product', 'main');
        if (!term_exists($default_product, 'roadmap_product')) {
            // Use the product name from settings if available
            $product_name = get_option('roadmap_product_name', __('Main Product', 'product-roadmap'));
            $term = wp_insert_term(
                $product_name,
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
            __('Roadmap Item Details', 'product-roadmap'),
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
                <h3><?php esc_html_e('Pro Features', 'product-roadmap'); ?></h3>
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
                'label' => __('Priority', 'product-roadmap'),
                'type' => 'select',
                'options' => [
                    '' => __('-- Select Priority --', 'product-roadmap'),
                    'low' => __('Low', 'product-roadmap'),
                    'medium' => __('Medium', 'product-roadmap'),
                    'high' => __('High', 'product-roadmap'),
                    'critical' => __('Critical', 'product-roadmap'),
                ],
                'description' => __('Set the priority level for this item.', 'product-roadmap'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_progress',
                'label' => __('Progress', 'product-roadmap'),
                'type' => 'range',
                'min' => 0,
                'max' => 100,
                'step' => 5,
                'default' => 0,
                'description' => __('Current completion percentage (0-100%)', 'product-roadmap'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_target_date',
                'label' => __('Target Date', 'product-roadmap'),
                'type' => 'date',
                'description' => __('Expected completion date', 'product-roadmap'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_start_date',
                'label' => __('Start Date', 'product-roadmap'),
                'type' => 'date',
                'description' => __('When work begins on this item', 'product-roadmap'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_end_date',
                'label' => __('End Date', 'product-roadmap'),
                'type' => 'date',
                'description' => __('When work was completed', 'product-roadmap'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_effort_estimate',
                'label' => __('Effort Estimate', 'product-roadmap'),
                'type' => 'select',
                'options' => [
                    '' => __('-- Select Effort --', 'product-roadmap'),
                    'xs' => __('XS - Very Small (1-2 days)', 'product-roadmap'),
                    's' => __('S - Small (3-5 days)', 'product-roadmap'),
                    'm' => __('M - Medium (1-2 weeks)', 'product-roadmap'),
                    'l' => __('L - Large (2-4 weeks)', 'product-roadmap'),
                    'xl' => __('XL - Extra Large (1-2 months)', 'product-roadmap'),
                    'xxl' => __('XXL - Huge (2+ months)', 'product-roadmap'),
                ],
                'description' => __('Estimated effort required', 'product-roadmap'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_github_issue',
                'label' => __('GitHub Issue URL', 'product-roadmap'),
                'type' => 'url',
                'placeholder' => '',
                'description' => __('Link to related GitHub issue', 'product-roadmap'),
                'pro' => true,
            ],
            [
                'id' => '_roadmap_external_link',
                'label' => __('External Link', 'product-roadmap'),
                'type' => 'url',
                'placeholder' => '',
                'description' => __('Link to external documentation or reference', 'product-roadmap'),
                'free' => true,
            ],
            [
                'id' => '_roadmap_vote_count',
                'label' => __('Vote Count', 'product-roadmap'),
                'type' => 'number',
                'readonly' => true,
                'description' => __('Total number of votes (automatically calculated)', 'product-roadmap'),
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
                       value="<?php esc_attr_e('Pro Feature', 'product-roadmap'); ?>" 
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
                       <?php echo esc_attr($readonly); ?>>
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
                           <?php echo esc_attr($readonly); ?>>
                    <span class="roadmap-range-value"><?php echo esc_html($value); ?>%</span>
                </div>
                <?php
                break;
                
            case 'select':
                $is_multiple = isset($field['multiple']) && $field['multiple'];
                $name_attr = $is_multiple ? $field['id'] . '[]' : $field['id'];
                $selected_values = $is_multiple && is_array($value) ? $value : [$value];
                ?>
                <select id="<?php echo esc_attr($field['id']); ?>"
                        name="<?php echo esc_attr($name_attr); ?>"
                        class="widefat"
                        <?php echo $is_multiple ? 'multiple' : ''; ?>
                        <?php echo esc_attr($readonly); ?>>
                    <?php foreach ($field['options'] as $option_value => $option_label) : ?>
                        <option value="<?php echo esc_attr($option_value); ?>"
                                <?php echo in_array($option_value, $selected_values, true) ? 'selected' : ''; ?>>
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
            !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['roadmap_item_meta_box_nonce'])), 'roadmap_item_meta_box')) {
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
                // Handle array values for multiple select fields
                if (is_array($_POST[$field['id']])) {
                    $value = array_map('sanitize_text_field', wp_unslash($_POST[$field['id']]));
                } else {
                    $value = sanitize_text_field(wp_unslash($_POST[$field['id']]));
                }
                update_post_meta($post_id, $field['id'], $value);
            } else {
                // Clear meta if field not in POST (e.g., unchecked multiselect)
                $is_multiple = isset($field['multiple']) && $field['multiple'];
                if ($is_multiple) {
                    update_post_meta($post_id, $field['id'], []);
                }
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
        
        // Enqueue meta box scripts and styles
        wp_enqueue_script(
            'roadmap-meta-box',
            ROADMAP_PLUGIN_URL . 'assets/js/roadmap-meta-box.js',
            ['jquery'],
            ROADMAP_VERSION,
            true
        );

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
     * Add pending post status view in admin
     */
    public function add_pending_post_status_view($views) {
        global $wpdb;

        $post_type = 'roadmap_item';
        $pending_count = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'pending'",
                $post_type
            )
        );

        if ($pending_count > 0) {
            $class = (isset($_GET['post_status']) && $_GET['post_status'] === 'pending') ? ' class="current"' : '';
            $pending_url = admin_url('edit.php?post_status=pending&post_type=' . $post_type);

            $views['pending'] = sprintf(
                '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                esc_url($pending_url),
                $class,
                __('Pending', 'product-roadmap'),
                $pending_count
            );
        }

        return $views;
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

    /**
     * Add quick approve/reject actions to pending items
     */
    public function add_quick_approve_actions($actions, $post) {
        if ($post->post_type !== 'roadmap_item' || $post->post_status !== 'pending') {
            return $actions;
        }

        if (!current_user_can('publish_posts')) {
            return $actions;
        }

        $approve_url = wp_nonce_url(
            admin_url('admin.php?action=approve_roadmap_item&post=' . $post->ID),
            'approve_roadmap_item_' . $post->ID
        );

        $reject_url = wp_nonce_url(
            admin_url('admin.php?action=reject_roadmap_item&post=' . $post->ID),
            'reject_roadmap_item_' . $post->ID
        );

        // Add approve action (with green color)
        $actions['approve'] = sprintf(
            '<a href="%s" style="color: #46b450;">%s</a>',
            esc_url($approve_url),
            __('Approve', 'product-roadmap')
        );

        // Add reject action (with red color)
        $actions['reject'] = sprintf(
            '<a href="%s" style="color: #dc3232;" onclick="return confirm(\'%s\');">%s</a>',
            esc_url($reject_url),
            esc_js(__('Are you sure you want to reject this submission?', 'product-roadmap')),
            __('Reject', 'product-roadmap')
        );

        return $actions;
    }

    /**
     * Handle approve action
     */
    public function handle_approve_action() {
        if (!isset($_GET['post'])) {
            wp_die(esc_html__('No submission specified', 'product-roadmap'));
        }

        $post_id = intval($_GET['post']);

        // Security check
        check_admin_referer('approve_roadmap_item_' . $post_id);

        if (!current_user_can('publish_posts')) {
            wp_die(esc_html__('You do not have permission to approve submissions', 'product-roadmap'));
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'roadmap_item') {
            wp_die(esc_html__('Invalid submission', 'product-roadmap'));
        }

        // Update post status to publish
        wp_update_post([
            'ID' => $post_id,
            'post_status' => 'publish'
        ]);

        // Set a transient for the success message
        set_transient('roadmap_action_notice_' . get_current_user_id(), [
            'type' => 'success',
            /* translators: %s: Post title */
            'message' => sprintf(__('"%s" has been approved and published!', 'product-roadmap'), $post->post_title)
        ], 30);

        // Redirect back to the list
        wp_redirect(admin_url('edit.php?post_type=roadmap_item&approved=1'));
        exit;
    }

    /**
     * Handle reject action
     */
    public function handle_reject_action() {
        if (!isset($_GET['post'])) {
            wp_die(esc_html__('No submission specified', 'product-roadmap'));
        }

        $post_id = intval($_GET['post']);

        // Security check
        check_admin_referer('reject_roadmap_item_' . $post_id);

        if (!current_user_can('delete_posts')) {
            wp_die(esc_html__('You do not have permission to reject submissions', 'product-roadmap'));
        }

        $post = get_post($post_id);
        if (!$post || $post->post_type !== 'roadmap_item') {
            wp_die(esc_html__('Invalid submission', 'product-roadmap'));
        }

        // Move to trash
        wp_trash_post($post_id);

        // Set a transient for the success message
        set_transient('roadmap_action_notice_' . get_current_user_id(), [
            'type' => 'updated',
            /* translators: %s: Post title */
            'message' => sprintf(__('"%s" has been rejected and moved to trash.', 'product-roadmap'), $post->post_title)
        ], 30);

        // Redirect back to the list
        wp_redirect(admin_url('edit.php?post_type=roadmap_item&rejected=1'));
        exit;
    }

    /**
     * Show admin notices after approve/reject actions
     */
    public function show_action_notices() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'roadmap_item') {
            return;
        }

        // Check for transient message
        $notice = get_transient('roadmap_action_notice_' . get_current_user_id());
        if ($notice) {
            printf(
                '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                esc_attr($notice['type']),
                esc_html($notice['message'])
            );
            delete_transient('roadmap_action_notice_' . get_current_user_id());
        }
    }
}