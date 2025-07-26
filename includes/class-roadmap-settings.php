<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Settings {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', [$this, 'add_menu_pages'], 99);
        add_action('admin_menu', [$this, 'reorganize_menu'], 100);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_ajax_roadmap_save_product_settings', [$this, 'ajax_save_product_settings']);
        add_action('wp_ajax_roadmap_get_product_settings', [$this, 'ajax_get_product_settings']);
        
        // Add shortcode column to product taxonomy
        add_filter('manage_edit-roadmap_product_columns', [$this, 'add_product_columns']);
        add_filter('manage_roadmap_product_custom_column', [$this, 'fill_product_columns'], 10, 3);
        
        // Menu highlighting for taxonomies
        add_filter('parent_file', [$this, 'set_taxonomy_parent_menu']);
        add_filter('submenu_file', [$this, 'set_taxonomy_submenu_active']);
    }
    
    public function add_menu_pages() {
        // Add main Roadmap menu
        add_menu_page(
            __('Roadmap', 'roadmap-plugin'),
            __('Roadmap', 'roadmap-plugin'),
            'edit_posts',
            'roadmap-dashboard',
            [$this, 'render_dashboard_page'],
            'dashicons-chart-line',
            25
        );
        
        // Add Dashboard submenu (rename the first item)
        add_submenu_page(
            'roadmap-dashboard',
            __('Dashboard', 'roadmap-plugin'),
            __('Dashboard', 'roadmap-plugin'),
            'edit_posts',
            'roadmap-dashboard',
            [$this, 'render_dashboard_page']
        );
        
        // Add Products submenu only if Pro is active
        if (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE) {
            add_submenu_page(
                'roadmap-dashboard',
                __('Products', 'roadmap-plugin'),
                __('Products', 'roadmap-plugin'),
                'manage_options',
                'roadmap-products',
                [$this, 'render_products_page']
            );
        }
        
        // Add All Items submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('All Items', 'roadmap-plugin'),
            __('All Items', 'roadmap-plugin'),
            'edit_posts',
            'edit.php?post_type=roadmap_item'
        );
        
        // Add New Item submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Add New Item', 'roadmap-plugin'),
            __('Add New Item', 'roadmap-plugin'),
            'edit_posts',
            'post-new.php?post_type=roadmap_item'
        );
        
        // Add Statuses submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Statuses', 'roadmap-plugin'),
            __('Statuses', 'roadmap-plugin'),
            'manage_options',
            'edit-tags.php?taxonomy=roadmap_status&post_type=roadmap_item'
        );
        
        // Add Categories submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Categories', 'roadmap-plugin'),
            __('Categories', 'roadmap-plugin'),
            'manage_options',
            'edit-tags.php?taxonomy=roadmap_category&post_type=roadmap_item'
        );
        
        // Add Settings submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Settings', 'roadmap-plugin'),
            __('Settings', 'roadmap-plugin'),
            'manage_options',
            'roadmap-settings',
            [$this, 'render_settings_page']
        );
    }
    
    public function reorganize_menu() {
        global $menu, $submenu;
        
        // Remove the default roadmap_item menu
        remove_menu_page('edit.php?post_type=roadmap_item');
        
        // Remove default taxonomy submenus if they still exist
        if (isset($submenu['edit.php?post_type=roadmap_item'])) {
            unset($submenu['edit.php?post_type=roadmap_item']);
        }
    }
    
    public function register_settings() {
        // Global settings
        register_setting('roadmap_global', 'roadmap_global_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_global_settings'],
            'default' => $this->get_default_global_settings()
        ]);
        
        // Product-specific settings
        register_setting('roadmap_products', 'roadmap_product_settings', [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitize_product_settings'],
            'default' => []
        ]);
    }
    
    private function get_default_global_settings() {
        return [
            'default_status' => 'planned',
            'items_per_page' => 20,
            'enable_voting' => true,
            'enable_comments' => true,
            'enable_suggestions' => true,
            'suggestion_requires_login' => true,
            'suggestion_default_status' => 'under-review',
            'enable_notifications' => true,
            'notification_email' => get_option('admin_email'),
            'notify_on_vote' => false,
            'notify_on_comment' => true,
            'notify_on_suggestion' => true,
        ];
    }
    
    private function get_default_product_settings() {
        return [
            'enable_voting' => true,
            'enable_comments' => true,
            'enable_suggestions' => true,
            'default_status' => 'planned',
            'items_per_page' => 20,
            'show_in_nav_menu' => true,
            'custom_slug' => '',
            'theme_color' => '#2271b1',
            'dark_mode' => false,
        ];
    }
    
    public function render_dashboard_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }
        
        $products = get_terms(['taxonomy' => 'roadmap_product', 'hide_empty' => false]);
        $total_items = wp_count_posts('roadmap_item');
        $recent_items = get_posts([
            'post_type' => 'roadmap_item',
            'posts_per_page' => 5,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        ?>
        <div class="wrap">
            <h1><?php _e('Roadmap Dashboard', 'roadmap-plugin'); ?></h1>
            
            <div class="roadmap-dashboard-grid">
                <!-- Stats Cards -->
                <div class="roadmap-stat-card">
                    <h3><?php _e('Total Items', 'roadmap-plugin'); ?></h3>
                    <p class="roadmap-stat-number"><?php echo $total_items->publish; ?></p>
                </div>
                
                <div class="roadmap-stat-card">
                    <h3><?php _e('Products', 'roadmap-plugin'); ?></h3>
                    <p class="roadmap-stat-number"><?php echo count($products); ?></p>
                    <?php if (!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE): ?>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            <?php _e('Pro feature', 'roadmap-plugin'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="roadmap-stat-card">
                    <h3><?php _e('In Progress', 'roadmap-plugin'); ?></h3>
                    <p class="roadmap-stat-number">
                        <?php 
                        $in_progress = get_posts([
                            'post_type' => 'roadmap_item',
                            'posts_per_page' => -1,
                            'tax_query' => [
                                [
                                    'taxonomy' => 'roadmap_status',
                                    'field' => 'slug',
                                    'terms' => 'in-progress'
                                ]
                            ]
                        ]);
                        echo count($in_progress);
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="roadmap-dashboard-sections">
                <div class="roadmap-dashboard-section">
                    <h2><?php _e('Recent Items', 'roadmap-plugin'); ?></h2>
                    <?php if ($recent_items): ?>
                        <ul class="roadmap-recent-list">
                            <?php foreach ($recent_items as $item): ?>
                                <li>
                                    <a href="<?php echo get_edit_post_link($item->ID); ?>">
                                        <?php echo esc_html($item->post_title); ?>
                                    </a>
                                    <span class="roadmap-item-date">
                                        <?php echo human_time_diff(strtotime($item->post_date), current_time('timestamp')) . ' ago'; ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?php _e('No items yet.', 'roadmap-plugin'); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="roadmap-dashboard-section">
                    <h2><?php _e('Quick Actions', 'roadmap-plugin'); ?></h2>
                    <p>
                        <a href="<?php echo admin_url('post-new.php?post_type=roadmap_item'); ?>" 
                           class="button button-primary"><?php _e('Add New Item', 'roadmap-plugin'); ?></a>
                        <?php if (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE): ?>
                            <a href="<?php echo admin_url('admin.php?page=roadmap-products'); ?>" 
                               class="button"><?php _e('Manage Products', 'roadmap-plugin'); ?></a>
                        <?php else: ?>
                            <a href="#" class="button" disabled style="opacity: 0.6; cursor: not-allowed;" 
                               title="<?php _e('Upgrade to Pro to manage multiple products', 'roadmap-plugin'); ?>">
                                <?php _e('Manage Products (Pro)', 'roadmap-plugin'); ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo admin_url('admin.php?page=roadmap-settings'); ?>" 
                           class="button"><?php _e('Settings', 'roadmap-plugin'); ?></a>
                    </p>
                </div>
            </div>
            
            <?php
            // Allow pro addon to add content to dashboard
            do_action('roadmap_dashboard_after_sections');
            ?>
        </div>
        
        <style>
        .roadmap-dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .roadmap-stat-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            text-align: center;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .roadmap-stat-card h3 {
            margin: 0 0 10px;
            font-size: 14px;
            color: #666;
        }
        
        .roadmap-stat-number {
            font-size: 32px;
            font-weight: 600;
            margin: 0;
            color: #2271b1;
        }
        
        .roadmap-dashboard-sections {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 30px;
        }
        
        .roadmap-dashboard-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .roadmap-dashboard-section h2 {
            margin-top: 0;
        }
        
        .roadmap-recent-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }
        
        .roadmap-recent-list li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .roadmap-recent-list li:last-child {
            border-bottom: none;
        }
        
        .roadmap-item-date {
            font-size: 12px;
            color: #666;
        }
        
        @media (max-width: 782px) {
            .roadmap-dashboard-sections {
                grid-template-columns: 1fr;
            }
        }
        </style>
        <?php
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'general';
        $global_settings = get_option('roadmap_global_settings', $this->get_default_global_settings());
        ?>
        <div class="wrap">
            <h1><?php _e('Roadmap Settings', 'roadmap-plugin'); ?></h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=roadmap-settings&tab=general" 
                   class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('General', 'roadmap-plugin'); ?>
                </a>
                <a href="?page=roadmap-settings&tab=notifications" 
                   class="nav-tab <?php echo $active_tab === 'notifications' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('Notifications', 'roadmap-plugin'); ?>
                </a>
                <?php
                // Allow pro addon to add tabs
                $tabs = apply_filters('roadmap_settings_tabs', []);
                foreach ($tabs as $tab_key => $tab_label) {
                    ?>
                    <a href="?page=roadmap-settings&tab=<?php echo esc_attr($tab_key); ?>" 
                       class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html($tab_label); ?>
                    </a>
                    <?php
                }
                ?>
            </h2>
            
            <form method="post" action="options.php">
                <?php settings_fields('roadmap_global'); ?>
                
                <?php
                switch ($active_tab) {
                    case 'general':
                        $this->render_general_settings($global_settings);
                        break;
                    case 'notifications':
                        $this->render_notifications_settings($global_settings);
                        break;
                    default:
                        // Allow pro addon to render custom tabs
                        do_action('roadmap_settings_tab_content', $active_tab);
                        break;
                }
                ?>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    private function render_general_settings($settings) {
        $statuses = get_terms(['taxonomy' => 'roadmap_status', 'hide_empty' => false]);
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="default_status"><?php _e('Default Status for New Items', 'roadmap-plugin'); ?></label>
                </th>
                <td>
                    <select name="roadmap_global_settings[default_status]" id="default_status">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo esc_attr($status->slug); ?>" 
                                <?php selected($settings['default_status'], $status->slug); ?>>
                                <?php echo esc_html($status->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('New roadmap items will be created with this status.', 'roadmap-plugin'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="items_per_page"><?php _e('Items Per Page', 'roadmap-plugin'); ?></label>
                </th>
                <td>
                    <input type="number" name="roadmap_global_settings[items_per_page]" 
                           id="items_per_page" value="<?php echo esc_attr($settings['items_per_page']); ?>" 
                           min="5" max="100" />
                    <p class="description"><?php _e('Number of items to show per page in the roadmap.', 'roadmap-plugin'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Features', 'roadmap-plugin'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="roadmap_global_settings[enable_voting]" value="1" 
                                   <?php checked($settings['enable_voting'], true); ?> />
                            <?php _e('Enable Voting', 'roadmap-plugin'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="roadmap_global_settings[enable_comments]" value="1" 
                                   <?php checked($settings['enable_comments'], true); ?> />
                            <?php _e('Enable Comments', 'roadmap-plugin'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="roadmap_global_settings[enable_suggestions]" value="1" 
                                   <?php checked($settings['enable_suggestions'], true); ?> />
                            <?php _e('Enable User Suggestions', 'roadmap-plugin'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            
            <?php if ($settings['enable_suggestions']): ?>
            <tr>
                <th scope="row">
                    <label for="suggestion_default_status"><?php _e('Default Status for Suggestions', 'roadmap-plugin'); ?></label>
                </th>
                <td>
                    <select name="roadmap_global_settings[suggestion_default_status]" id="suggestion_default_status">
                        <?php foreach ($statuses as $status): ?>
                            <option value="<?php echo esc_attr($status->slug); ?>" 
                                <?php selected($settings['suggestion_default_status'] ?? 'under-review', $status->slug); ?>>
                                <?php echo esc_html($status->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e('User suggestions will be created with this status.', 'roadmap-plugin'); ?></p>
                </td>
            </tr>
            <?php endif; ?>
        </table>
        <?php
    }
    
    private function render_notifications_settings($settings) {
        ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="enable_notifications"><?php _e('Enable Email Notifications', 'roadmap-plugin'); ?></label>
                </th>
                <td>
                    <input type="checkbox" name="roadmap_global_settings[enable_notifications]" 
                           id="enable_notifications" value="1" 
                           <?php checked($settings['enable_notifications'], true); ?> />
                    <p class="description"><?php _e('Send email notifications for roadmap activity.', 'roadmap-plugin'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="notification_email"><?php _e('Notification Email', 'roadmap-plugin'); ?></label>
                </th>
                <td>
                    <input type="email" name="roadmap_global_settings[notification_email]" 
                           id="notification_email" value="<?php echo esc_attr($settings['notification_email']); ?>" 
                           class="regular-text" />
                    <p class="description"><?php _e('Email address to receive notifications.', 'roadmap-plugin'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row"><?php _e('Notify On', 'roadmap-plugin'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" name="roadmap_global_settings[notify_on_vote]" value="1" 
                                   <?php checked($settings['notify_on_vote'] ?? false, true); ?> />
                            <?php _e('New Votes', 'roadmap-plugin'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="roadmap_global_settings[notify_on_comment]" value="1" 
                                   <?php checked($settings['notify_on_comment'], true); ?> />
                            <?php _e('New Comments', 'roadmap-plugin'); ?>
                        </label><br>
                        
                        <label>
                            <input type="checkbox" name="roadmap_global_settings[notify_on_suggestion]" value="1" 
                                   <?php checked($settings['notify_on_suggestion'], true); ?> />
                            <?php _e('New Suggestions', 'roadmap-plugin'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        </table>
        <?php
    }
    
    public function render_products_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $products = get_terms(['taxonomy' => 'roadmap_product', 'hide_empty' => false]);
        $selected_product = isset($_GET['product']) ? $_GET['product'] : '';
        ?>
        <div class="wrap">
            <h1>
                <?php _e('Roadmap Products', 'roadmap-plugin'); ?>
                <a href="<?php echo admin_url('edit-tags.php?taxonomy=roadmap_product&post_type=roadmap_item'); ?>" 
                   class="page-title-action"><?php _e('Manage Products', 'roadmap-plugin'); ?></a>
            </h1>
            
            <?php if (empty($products)): ?>
                <div class="notice notice-info">
                    <p><?php _e('No products found. Create your first product to get started.', 'roadmap-plugin'); ?></p>
                    <p>
                        <a href="<?php echo admin_url('edit-tags.php?taxonomy=roadmap_product&post_type=roadmap_item'); ?>" 
                           class="button button-primary"><?php _e('Create Product', 'roadmap-plugin'); ?></a>
                    </p>
                </div>
            <?php else: ?>
                <div class="roadmap-products-grid">
                    <?php foreach ($products as $product): 
                        $product_settings = $this->get_product_settings($product->term_id);
                        $item_count = $product->count;
                    ?>
                        <div class="roadmap-product-card">
                            <h3><?php echo esc_html($product->name); ?></h3>
                            <p class="description"><?php echo esc_html($product->description); ?></p>
                            
                            <div class="product-stats">
                                <span class="stat-item">
                                    <strong><?php echo $item_count; ?></strong> 
                                    <?php echo _n('Item', 'Items', $item_count, 'roadmap-plugin'); ?>
                                </span>
                            </div>
                            
                            <div class="product-shortcode">
                                <code>[roadmap product="<?php echo esc_attr($product->slug); ?>"]</code>
                            </div>
                            
                            <div class="product-actions">
                                <a href="#" class="button" onclick="editProductSettings('<?php echo esc_attr($product->term_id); ?>')">
                                    <?php _e('Settings', 'roadmap-plugin'); ?>
                                </a>
                                <a href="<?php echo admin_url('edit.php?post_type=roadmap_item&roadmap_product=' . $product->slug); ?>" 
                                   class="button">
                                    <?php _e('View Items', 'roadmap-plugin'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Product Settings Modal -->
                <div id="product-settings-modal" class="roadmap-modal" style="display: none;">
                    <div class="roadmap-modal-content">
                        <span class="roadmap-modal-close">&times;</span>
                        <h2><?php _e('Product Settings', 'roadmap-plugin'); ?></h2>
                        <form id="product-settings-form">
                            <input type="hidden" id="product_id" name="product_id" value="">
                            <?php wp_nonce_field('roadmap_product_settings', 'roadmap_nonce'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="product_default_status"><?php _e('Default Status', 'roadmap-plugin'); ?></label>
                                    </th>
                                    <td>
                                        <select name="default_status" id="product_default_status">
                                            <?php 
                                            $statuses = get_terms(['taxonomy' => 'roadmap_status', 'hide_empty' => false]);
                                            foreach ($statuses as $status): ?>
                                                <option value="<?php echo esc_attr($status->slug); ?>">
                                                    <?php echo esc_html($status->name); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row"><?php _e('Features', 'roadmap-plugin'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="enable_voting" value="1" checked>
                                            <?php _e('Enable Voting', 'roadmap-plugin'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="enable_comments" value="1" checked>
                                            <?php _e('Enable Comments', 'roadmap-plugin'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="enable_suggestions" value="1" checked>
                                            <?php _e('Enable Suggestions', 'roadmap-plugin'); ?>
                                        </label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="product_theme_color"><?php _e('Theme Color', 'roadmap-plugin'); ?></label>
                                    </th>
                                    <td>
                                        <input type="color" name="theme_color" id="product_theme_color" value="#2271b1">
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <button type="submit" class="button button-primary"><?php _e('Save Settings', 'roadmap-plugin'); ?></button>
                            </p>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
        .roadmap-products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .roadmap-product-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 20px;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
        }
        
        .roadmap-product-card h3 {
            margin-top: 0;
        }
        
        .product-stats {
            margin: 15px 0;
            padding: 10px 0;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
        }
        
        .product-shortcode {
            margin: 15px 0;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .product-actions {
            display: flex;
            gap: 10px;
        }
        
        .roadmap-modal {
            display: none;
            position: fixed;
            z-index: 100000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .roadmap-modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }
        
        .roadmap-modal-close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .roadmap-modal-close:hover,
        .roadmap-modal-close:focus {
            color: black;
        }
        </style>
        
        <script>
        function editProductSettings(productId) {
            document.getElementById('product_id').value = productId;
            document.getElementById('product-settings-modal').style.display = 'block';
            
            // Load existing settings
            jQuery.post(ajaxurl, {
                action: 'roadmap_get_product_settings',
                product_id: productId,
                nonce: '<?php echo wp_create_nonce('roadmap_product_settings'); ?>'
            }, function(response) {
                if (response.success) {
                    // Populate form with existing settings
                    var settings = response.data;
                    jQuery('#product_default_status').val(settings.default_status);
                    jQuery('input[name="enable_voting"]').prop('checked', settings.enable_voting);
                    jQuery('input[name="enable_comments"]').prop('checked', settings.enable_comments);
                    jQuery('input[name="enable_suggestions"]').prop('checked', settings.enable_suggestions);
                    jQuery('#product_theme_color').val(settings.theme_color);
                }
            });
        }
        
        jQuery(document).ready(function($) {
            $('.roadmap-modal-close').click(function() {
                $('#product-settings-modal').hide();
            });
            
            $('#product-settings-form').submit(function(e) {
                e.preventDefault();
                
                var formData = $(this).serialize();
                formData += '&action=roadmap_save_product_settings';
                
                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        alert('Settings saved successfully!');
                        $('#product-settings-modal').hide();
                    } else {
                        alert('Error saving settings: ' + response.data);
                    }
                });
            });
        });
        </script>
        <?php
    }
    
    public function ajax_save_product_settings() {
        check_ajax_referer('roadmap_product_settings', 'roadmap_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $product_id = intval($_POST['product_id']);
        $settings = [
            'default_status' => sanitize_text_field($_POST['default_status']),
            'enable_voting' => !empty($_POST['enable_voting']),
            'enable_comments' => !empty($_POST['enable_comments']),
            'enable_suggestions' => !empty($_POST['enable_suggestions']),
            'theme_color' => sanitize_hex_color($_POST['theme_color']),
        ];
        
        update_term_meta($product_id, 'roadmap_product_settings', $settings);
        
        wp_send_json_success();
    }
    
    public function get_product_settings($product_id) {
        $settings = get_term_meta($product_id, 'roadmap_product_settings', true);
        if (empty($settings)) {
            $settings = $this->get_default_product_settings();
        }
        return $settings;
    }
    
    public function ajax_get_product_settings() {
        check_ajax_referer('roadmap_product_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $product_id = intval($_POST['product_id']);
        $settings = $this->get_product_settings($product_id);
        
        wp_send_json_success($settings);
    }
    
    public function sanitize_global_settings($input) {
        $sanitized = [];
        
        $sanitized['default_status'] = sanitize_text_field($input['default_status'] ?? 'planned');
        $sanitized['items_per_page'] = absint($input['items_per_page'] ?? 20);
        $sanitized['enable_voting'] = !empty($input['enable_voting']);
        $sanitized['enable_comments'] = !empty($input['enable_comments']);
        $sanitized['enable_suggestions'] = !empty($input['enable_suggestions']);
        $sanitized['suggestion_requires_login'] = !empty($input['suggestion_requires_login']);
        $sanitized['suggestion_default_status'] = sanitize_text_field($input['suggestion_default_status'] ?? 'under-review');
        $sanitized['enable_notifications'] = !empty($input['enable_notifications']);
        $sanitized['notification_email'] = sanitize_email($input['notification_email'] ?? '');
        $sanitized['notify_on_vote'] = !empty($input['notify_on_vote']);
        $sanitized['notify_on_comment'] = !empty($input['notify_on_comment']);
        $sanitized['notify_on_suggestion'] = !empty($input['notify_on_suggestion']);
        
        return $sanitized;
    }
    
    public function sanitize_product_settings($input) {
        // Sanitize product-specific settings
        return $input;
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'roadmap') === false) {
            return;
        }
        
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
    
    public function add_product_columns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Add shortcode column after name
            if ($key === 'name') {
                $new_columns['shortcode'] = __('Shortcode', 'roadmap-plugin');
            }
        }
        
        return $new_columns;
    }
    
    public function fill_product_columns($content, $column_name, $term_id) {
        if ($column_name === 'shortcode') {
            $term = get_term($term_id, 'roadmap_product');
            if ($term && !is_wp_error($term)) {
                $shortcode = '[roadmap product="' . esc_attr($term->slug) . '"]';
                $content = '<code style="user-select: all; cursor: pointer;" onclick="navigator.clipboard.writeText(this.textContent).then(() => { this.style.background = \'#d4edda\'; setTimeout(() => this.style.background = \'\', 1000); })" title="Click to copy">' . $shortcode . '</code>';
            }
        }
        
        return $content;
    }
    
    public function set_taxonomy_parent_menu($parent_file) {
        global $current_screen;
        
        // Check if we're on a roadmap taxonomy page
        if ($current_screen && isset($current_screen->taxonomy) && isset($current_screen->post_type)) {
            if ($current_screen->post_type === 'roadmap_item') {
                // Force the parent menu to be roadmap-dashboard
                return 'roadmap-dashboard';
            }
        }
        
        return $parent_file;
    }
    
    public function set_taxonomy_submenu_active($submenu_file) {
        global $current_screen, $taxonomy;
        
        // Check if we're on a roadmap taxonomy page
        if ($current_screen && isset($current_screen->taxonomy) && isset($current_screen->post_type)) {
            if ($current_screen->post_type === 'roadmap_item') {
                // Highlight the appropriate submenu based on taxonomy
                switch ($current_screen->taxonomy) {
                    case 'roadmap_status':
                        return 'edit-tags.php?taxonomy=roadmap_status&post_type=roadmap_item';
                    case 'roadmap_category':
                        return 'edit-tags.php?taxonomy=roadmap_category&post_type=roadmap_item';
                    case 'roadmap_product':
                        if (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE) {
                            return 'roadmap-products';
                        }
                        break;
                }
            }
        }
        
        return $submenu_file;
    }
}

// Initialize
Roadmap_Settings::get_instance();