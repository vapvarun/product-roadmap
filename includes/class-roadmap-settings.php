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
            __('Roadmap', 'product-roadmap'),
            __('Roadmap', 'product-roadmap'),
            'edit_posts',
            'roadmap-dashboard',
            [$this, 'render_dashboard_page'],
            'dashicons-chart-line',
            25
        );
        
        // Add Dashboard submenu (rename the first item)
        add_submenu_page(
            'roadmap-dashboard',
            __('Dashboard', 'product-roadmap'),
            __('Dashboard', 'product-roadmap'),
            'edit_posts',
            'roadmap-dashboard',
            [$this, 'render_dashboard_page']
        );
        
        // Add Products submenu only if Pro is active
        if (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE) {
            add_submenu_page(
                'roadmap-dashboard',
                __('Products', 'product-roadmap'),
                __('Products', 'product-roadmap'),
                'manage_options',
                'roadmap-products',
                [$this, 'render_products_page']
            );
        }
        
        // Add All Items submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('All Items', 'product-roadmap'),
            __('All Items', 'product-roadmap'),
            'edit_posts',
            'edit.php?post_type=roadmap_item'
        );
        
        // Add New Item submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Add New Item', 'product-roadmap'),
            __('Add New Item', 'product-roadmap'),
            'edit_posts',
            'post-new.php?post_type=roadmap_item'
        );
        
        // Add Statuses submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Statuses', 'product-roadmap'),
            __('Statuses', 'product-roadmap'),
            'manage_options',
            'edit-tags.php?taxonomy=roadmap_status&post_type=roadmap_item'
        );
        
        // Add Categories submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Categories', 'product-roadmap'),
            __('Categories', 'product-roadmap'),
            'manage_options',
            'edit-tags.php?taxonomy=roadmap_category&post_type=roadmap_item'
        );
        
        // Add Settings submenu
        add_submenu_page(
            'roadmap-dashboard',
            __('Settings', 'product-roadmap'),
            __('Settings', 'product-roadmap'),
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
        
        // Product name and labels
        register_setting('roadmap_global', 'roadmap_product_name', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => ''
        ]);
        register_setting('roadmap_global', 'roadmap_item_singular_label', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Feature'
        ]);
        register_setting('roadmap_global', 'roadmap_item_plural_label', [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'Features'
        ]);
        
        // Advanced settings
        register_setting('roadmap_global', 'roadmap_allow_guest_voting', [
            'type' => 'string',
            'sanitize_callback' => [$this, 'sanitize_yes_no'],
            'default' => 'yes'
        ]);
        register_setting('roadmap_global', 'roadmap_cache_expiry', [
            'type' => 'integer',
            'sanitize_callback' => 'absint',
            'default' => 3600
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
            'items_per_page' => 100,
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
            'items_per_page' => 100,
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
            <h1><?php esc_html_e('Roadmap Dashboard', 'product-roadmap'); ?></h1>
            
            <div class="roadmap-dashboard-grid">
                <!-- Stats Cards -->
                <div class="roadmap-stat-card">
                    <h3><?php esc_html_e('Total Items', 'product-roadmap'); ?></h3>
                    <p class="roadmap-stat-number"><?php echo esc_html($total_items->publish); ?></p>
                </div>
                
                <div class="roadmap-stat-card">
                    <h3><?php esc_html_e('Products', 'product-roadmap'); ?></h3>
                    <p class="roadmap-stat-number"><?php echo count($products); ?></p>
                    <?php if (!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE): ?>
                        <p style="font-size: 12px; color: #666; margin-top: 10px;">
                            <?php esc_html_e('Pro feature', 'product-roadmap'); ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <div class="roadmap-stat-card">
                    <h3><?php esc_html_e('In Progress', 'product-roadmap'); ?></h3>
                    <p class="roadmap-stat-number">
                        <?php 
                        $in_progress = get_posts([
                            'post_type' => 'roadmap_item',
                            'posts_per_page' => -1,
                            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Dashboard statistics query
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
                    <h2><?php esc_html_e('Recent Items', 'product-roadmap'); ?></h2>
                    <?php if ($recent_items): ?>
                        <ul class="roadmap-recent-list">
                            <?php foreach ($recent_items as $item): ?>
                                <li>
                                    <a href="<?php echo esc_url(get_edit_post_link($item->ID)); ?>">
                                        <?php echo esc_html($item->post_title); ?>
                                    </a>
                                    <span class="roadmap-item-date">
                                        <?php echo esc_html(human_time_diff(strtotime($item->post_date), current_time('timestamp')) . ' ago'); ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p><?php esc_html_e('No items yet.', 'product-roadmap'); ?></p>
                    <?php endif; ?>
                </div>
                
                <div class="roadmap-dashboard-section">
                    <h2><?php esc_html_e('Quick Actions', 'product-roadmap'); ?></h2>
                    <p>
                        <a href="<?php echo esc_url(admin_url('post-new.php?post_type=roadmap_item')); ?>" 
                           class="button button-primary"><?php esc_html_e('Add New Item', 'product-roadmap'); ?></a>
                        <?php if (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE): ?>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=roadmap-products')); ?>" 
                               class="button"><?php esc_html_e('Manage Products', 'product-roadmap'); ?></a>
                        <?php else: ?>
                            <a href="#" class="button" disabled style="opacity: 0.6; cursor: not-allowed;" 
                               title="<?php esc_html_e('Upgrade to Pro to manage multiple products', 'product-roadmap'); ?>">
                                <?php esc_html_e('Manage Products (Pro)', 'product-roadmap'); ?>
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=roadmap-settings')); ?>" 
                           class="button"><?php esc_html_e('Settings', 'product-roadmap'); ?></a>
                    </p>
                </div>
            </div>
            
            <?php
            // Allow pro addon to add content to dashboard
            do_action('roadmap_dashboard_after_sections');
            ?>
        </div>
        <?php
    }
    
    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading tab parameter for display only
        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general';
        $global_settings = get_option('roadmap_global_settings', $this->get_default_global_settings());
        ?>
        <div class="wrap roadmap-settings-wrap">
            <div class="roadmap-settings-header">
                <h1><?php esc_html_e('Roadmap Settings', 'product-roadmap'); ?></h1>
                <p class="roadmap-settings-description">
                    <?php esc_html_e('Configure your roadmap settings to match your workflow and requirements.', 'product-roadmap'); ?>
                </p>
            </div>
            
            <div class="roadmap-settings-container">
                <div class="roadmap-settings-sidebar">
                    <ul class="roadmap-settings-nav">
                        <li>
                            <a href="?page=roadmap-settings&tab=general" 
                               class="roadmap-nav-item <?php echo esc_attr($active_tab === 'general' ? 'active' : ''); ?>">
                                <i class="dashicons dashicons-admin-settings"></i>
                                <span><?php esc_html_e('General', 'product-roadmap'); ?></span>
                            </a>
                        </li>
                        <li>
                            <a href="?page=roadmap-settings&tab=notifications" 
                               class="roadmap-nav-item <?php echo esc_attr($active_tab === 'notifications' ? 'active' : ''); ?>">
                                <i class="dashicons dashicons-email"></i>
                                <span><?php esc_html_e('Notifications', 'product-roadmap'); ?></span>
                            </a>
                        </li>
                        <?php
                        // Allow pro addon to add tabs
                        $tabs = apply_filters('roadmap_settings_tabs', []);
                        foreach ($tabs as $tab_key => $tab_data) {
                            $icon = isset($tab_data['icon']) ? $tab_data['icon'] : 'dashicons-admin-generic';
                            $label = isset($tab_data['label']) ? $tab_data['label'] : $tab_data;
                            ?>
                            <li>
                                <a href="?page=roadmap-settings&tab=<?php echo esc_attr($tab_key); ?>" 
                                   class="roadmap-nav-item <?php echo esc_attr($active_tab === $tab_key ? 'active' : ''); ?>">
                                    <i class="dashicons <?php echo esc_attr($icon); ?>"></i>
                                    <span><?php echo esc_html($label); ?></span>
                                </a>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                    
                    <?php if (!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE): ?>
                    <div class="roadmap-pro-cta">
                        <h3><?php esc_html_e('Upgrade to Pro', 'product-roadmap'); ?></h3>
                        <p><?php esc_html_e('Unlock advanced features like multiple products, timeline view, and more.', 'product-roadmap'); ?></p>
                        <a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
                            <?php esc_html_e('Get Pro Version', 'product-roadmap'); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="roadmap-settings-content">
                    <?php
                    // Check if this is a tab that needs form wrapper
                    $form_tabs = array('general', 'notifications', 'advanced');
                    $needs_form = in_array($active_tab, $form_tabs);
                    
                    if ($needs_form) {
                        echo '<form method="post" action="options.php" class="roadmap-settings-form">';
                        settings_fields('roadmap_global');
                    }
                    
                    switch ($active_tab) {
                        case 'general':
                            $this->render_general_settings_modern($global_settings);
                            break;
                        case 'notifications':
                            $this->render_notifications_settings_modern($global_settings);
                            break;
                        default:
                            // Allow pro addon to render custom tabs
                            $this->render_pro_tab_content($active_tab, $global_settings);
                            break;
                    }
                    
                    if ($needs_form) {
                        ?>
                        <div class="roadmap-settings-footer">
                            <?php submit_button(__('Save Settings', 'product-roadmap'), 'primary', 'submit', false); ?>
                            <span class="roadmap-settings-saved" style="display: none;">
                                <i class="dashicons dashicons-yes-alt"></i> <?php esc_html_e('Settings saved', 'product-roadmap'); ?>
                            </span>
                        </div>
                        
                        <?php
                        // Settings saved notification handled in roadmap-admin.js
                        ?>
                        </form>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_general_settings($settings) {
        // Legacy method kept for compatibility
        $this->render_general_settings_modern($settings);
    }
    
    private function render_general_settings_modern($settings) {
        $statuses = get_terms(['taxonomy' => 'roadmap_status', 'hide_empty' => false]);
        $product_name = get_option('roadmap_product_name', 'Product');
        $item_singular = get_option('roadmap_item_singular_label', 'Feature');
        $item_plural = get_option('roadmap_item_plural_label', 'Features');
        ?>
        <div class="roadmap-settings-section">
            <h2><?php esc_html_e('General Settings', 'product-roadmap'); ?></h2>
            <p class="section-description">
                <?php esc_html_e('Configure the basic settings for your roadmap.', 'product-roadmap'); ?>
            </p>
            
            <!-- Product Configuration Card -->
            <div class="roadmap-settings-card">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-portfolio"></i> <?php esc_html_e('Product Configuration', 'product-roadmap'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <label for="product_name"><?php esc_html_e('Product Name', 'product-roadmap'); ?></label>
                        <input type="text" name="roadmap_product_name" id="product_name" 
                               value="<?php echo esc_attr($product_name); ?>" class="regular-text" />
                        <p class="description"><?php esc_html_e('The name of your product or project.', 'product-roadmap'); ?></p>
                    </div>
                    
                    <div class="form-row-group">
                        <div class="form-row half">
                            <label for="item_singular"><?php esc_html_e('Item Label (Singular)', 'product-roadmap'); ?></label>
                            <input type="text" name="roadmap_item_singular_label" id="item_singular" 
                                   value="<?php echo esc_attr($item_singular); ?>" />
                            <p class="description"><?php esc_html_e('e.g., Feature, Task, Story', 'product-roadmap'); ?></p>
                        </div>
                        <div class="form-row half">
                            <label for="item_plural"><?php esc_html_e('Item Label (Plural)', 'product-roadmap'); ?></label>
                            <input type="text" name="roadmap_item_plural_label" id="item_plural" 
                                   value="<?php echo esc_attr($item_plural); ?>" />
                            <p class="description"><?php esc_html_e('e.g., Features, Tasks, Stories', 'product-roadmap'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Display Settings Card -->
            <div class="roadmap-settings-card">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-layout"></i> <?php esc_html_e('Display Settings', 'product-roadmap'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="form-row-group">
                        <div class="form-row half">
                            <label for="default_status"><?php esc_html_e('Default Status', 'product-roadmap'); ?></label>
                            <select name="roadmap_global_settings[default_status]" id="default_status">
                                <?php foreach ($statuses as $status): ?>
                                    <option value="<?php echo esc_attr($status->slug); ?>" 
                                        <?php selected($settings['default_status'], $status->slug); ?>>
                                        <?php echo esc_html($status->name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e('Default status for new items.', 'product-roadmap'); ?></p>
                        </div>
                        
                        <div class="form-row half">
                            <label for="items_per_page"><?php esc_html_e('Items Per Page', 'product-roadmap'); ?></label>
                            <input type="number" name="roadmap_global_settings[items_per_page]" 
                                   id="items_per_page" value="<?php echo esc_attr($settings['items_per_page']); ?>" 
                                   min="5" max="100" />
                            <p class="description"><?php esc_html_e('Items to show per page.', 'product-roadmap'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Permissions & Access Card -->
            <div class="roadmap-settings-card">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-admin-users"></i> <?php esc_html_e('Permissions & Access', 'product-roadmap'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <label for="who_can_vote"><?php esc_html_e('Who Can Vote', 'product-roadmap'); ?></label>
                        <select name="roadmap_allow_guest_voting" id="who_can_vote">
                            <option value="yes" <?php selected(get_option('roadmap_allow_guest_voting', 'yes'), 'yes'); ?>>
                                <?php esc_html_e('Anyone (including guests)', 'product-roadmap'); ?>
                            </option>
                            <option value="no" <?php selected(get_option('roadmap_allow_guest_voting', 'yes'), 'no'); ?>>
                                <?php esc_html_e('Logged in users only', 'product-roadmap'); ?>
                            </option>
                        </select>
                        <p class="description"><?php esc_html_e('Control who can vote on roadmap items.', 'product-roadmap'); ?></p>
                    </div>
                    
                    <div class="form-row">
                        <label for="who_can_suggest"><?php esc_html_e('Who Can Suggest Ideas', 'product-roadmap'); ?></label>
                        <select name="roadmap_global_settings[suggestion_requires_login]" id="who_can_suggest">
                            <option value="0" <?php selected($settings['suggestion_requires_login'] ?? true, false); ?>>
                                <?php esc_html_e('Anyone (including guests)', 'product-roadmap'); ?>
                            </option>
                            <option value="1" <?php selected($settings['suggestion_requires_login'] ?? true, true); ?>>
                                <?php esc_html_e('Logged in users only', 'product-roadmap'); ?>
                            </option>
                        </select>
                        <p class="description"><?php esc_html_e('Control who can submit new ideas.', 'product-roadmap'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Features Card -->
            <div class="roadmap-settings-card">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-admin-generic"></i> <?php esc_html_e('Features', 'product-roadmap'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="features-grid">
                        <div class="feature-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="roadmap_global_settings[enable_voting]" value="1" 
                                       <?php checked($settings['enable_voting'], true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="feature-info">
                                <h4><?php esc_html_e('Enable Voting', 'product-roadmap'); ?></h4>
                                <p><?php esc_html_e('Allow users to vote on roadmap items.', 'product-roadmap'); ?></p>
                            </div>
                        </div>
                        
                        <div class="feature-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="roadmap_global_settings[enable_comments]" value="1" 
                                       <?php checked($settings['enable_comments'], true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="feature-info">
                                <h4><?php esc_html_e('Enable Comments', 'product-roadmap'); ?></h4>
                                <p><?php esc_html_e('Allow users to comment on items.', 'product-roadmap'); ?></p>
                            </div>
                        </div>
                        
                        <div class="feature-toggle">
                            <label class="toggle-switch">
                                <input type="checkbox" name="roadmap_global_settings[enable_suggestions]" value="1" 
                                       <?php checked($settings['enable_suggestions'], true); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <div class="feature-info">
                                <h4><?php esc_html_e('User Suggestions', 'product-roadmap'); ?></h4>
                                <p><?php esc_html_e('Let users submit feature ideas.', 'product-roadmap'); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($settings['enable_suggestions']): ?>
                    <div class="form-row" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                        <label for="suggestion_default_status"><?php esc_html_e('Default Status for Suggestions', 'product-roadmap'); ?></label>
                        <select name="roadmap_global_settings[suggestion_default_status]" id="suggestion_default_status">
                            <?php foreach ($statuses as $status): ?>
                                <option value="<?php echo esc_attr($status->slug); ?>" 
                                    <?php selected($settings['suggestion_default_status'] ?? 'under-review', $status->slug); ?>>
                                    <?php echo esc_html($status->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php esc_html_e('Status assigned to user-submitted suggestions.', 'product-roadmap'); ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_notifications_settings($settings) {
        // Legacy method kept for compatibility
        $this->render_notifications_settings_modern($settings);
    }
    
    private function render_notifications_settings_modern($settings) {
        ?>
        <div class="roadmap-settings-section">
            <h2><?php esc_html_e('Email Notifications', 'product-roadmap'); ?></h2>
            <p class="section-description">
                <?php esc_html_e('Configure email notifications to stay informed about roadmap activity.', 'product-roadmap'); ?>
            </p>
            
            <!-- Email Configuration Card -->
            <div class="roadmap-settings-card">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-email-alt"></i> <?php esc_html_e('Email Configuration', 'product-roadmap'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="feature-toggle large">
                        <label class="toggle-switch">
                            <input type="checkbox" name="roadmap_global_settings[enable_notifications]" 
                                   id="enable_notifications" value="1" 
                                   <?php checked($settings['enable_notifications'], true); ?> />
                            <span class="toggle-slider"></span>
                        </label>
                        <div class="feature-info">
                            <h4><?php esc_html_e('Enable Email Notifications', 'product-roadmap'); ?></h4>
                            <p><?php esc_html_e('Receive email alerts for roadmap activity.', 'product-roadmap'); ?></p>
                        </div>
                    </div>
                    
                    <div class="form-row" style="margin-top: 20px;">
                        <label for="notification_email"><?php esc_html_e('Notification Email Address', 'product-roadmap'); ?></label>
                        <input type="email" name="roadmap_global_settings[notification_email]" 
                               id="notification_email" value="<?php echo esc_attr($settings['notification_email']); ?>" 
                               class="regular-text" placeholder="admin@example.com" />
                        <p class="description"><?php esc_html_e('Where notification emails should be sent.', 'product-roadmap'); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Notification Events Card -->
            <div class="roadmap-settings-card">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-bell"></i> <?php esc_html_e('Notification Events', 'product-roadmap'); ?></h3>
                </div>
                <div class="card-body">
                    <p class="card-description"><?php esc_html_e('Choose which events trigger email notifications.', 'product-roadmap'); ?></p>
                    
                    <div class="notification-events">
                        <div class="notification-event">
                            <label class="checkbox-label">
                                <input type="checkbox" name="roadmap_global_settings[notify_on_vote]" value="1" 
                                       <?php checked($settings['notify_on_vote'] ?? false, true); ?> />
                                <span class="checkbox-text">
                                    <strong><?php esc_html_e('New Votes', 'product-roadmap'); ?></strong>
                                    <small><?php esc_html_e('When users vote on roadmap items', 'product-roadmap'); ?></small>
                                </span>
                            </label>
                        </div>
                        
                        <div class="notification-event">
                            <label class="checkbox-label">
                                <input type="checkbox" name="roadmap_global_settings[notify_on_comment]" value="1" 
                                       <?php checked($settings['notify_on_comment'], true); ?> />
                                <span class="checkbox-text">
                                    <strong><?php esc_html_e('New Comments', 'product-roadmap'); ?></strong>
                                    <small><?php esc_html_e('When users comment on items', 'product-roadmap'); ?></small>
                                </span>
                            </label>
                        </div>
                        
                        <div class="notification-event">
                            <label class="checkbox-label">
                                <input type="checkbox" name="roadmap_global_settings[notify_on_suggestion]" value="1" 
                                       <?php checked($settings['notify_on_suggestion'], true); ?> />
                                <span class="checkbox-text">
                                    <strong><?php esc_html_e('New Suggestions', 'product-roadmap'); ?></strong>
                                    <small><?php esc_html_e('When users submit feature suggestions', 'product-roadmap'); ?></small>
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE): ?>
            <!-- Pro Features Preview -->
            <div class="roadmap-settings-card pro-preview">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-star-filled"></i> <?php esc_html_e('Pro Notification Features', 'product-roadmap'); ?></h3>
                    <span class="pro-badge">PRO</span>
                </div>
                <div class="card-body">
                    <ul class="pro-features-list">
                        <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Custom email templates', 'product-roadmap'); ?></li>
                        <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Multiple notification recipients', 'product-roadmap'); ?></li>
                        <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Digest emails (daily/weekly)', 'product-roadmap'); ?></li>
                        <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('User notification preferences', 'product-roadmap'); ?></li>
                    </ul>
                    <a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
                        <?php esc_html_e('Upgrade to Pro', 'product-roadmap'); ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function render_products_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $products = get_terms(['taxonomy' => 'roadmap_product', 'hide_empty' => false]);
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading product parameter for display only
        $selected_product = isset($_GET['product']) ? sanitize_text_field(wp_unslash($_GET['product'])) : '';
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e('Roadmap Products', 'product-roadmap'); ?>
                <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=roadmap_product&post_type=roadmap_item')); ?>" 
                   class="page-title-action"><?php esc_html_e('Manage Products', 'product-roadmap'); ?></a>
            </h1>
            
            <?php if (empty($products)): ?>
                <div class="notice notice-info">
                    <p><?php esc_html_e('No products found. Create your first product to get started.', 'product-roadmap'); ?></p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('edit-tags.php?taxonomy=roadmap_product&post_type=roadmap_item')); ?>" 
                           class="button button-primary"><?php esc_html_e('Create Product', 'product-roadmap'); ?></a>
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
                                    <strong><?php echo esc_html($item_count); ?></strong> 
                                    <?php echo esc_html(_n('Item', 'Items', $item_count, 'product-roadmap')); ?>
                                </span>
                            </div>
                            
                            <div class="product-shortcode">
                                <code>[roadmap product="<?php echo esc_attr($product->slug); ?>"]</code>
                            </div>
                            
                            <div class="product-actions">
                                <a href="#" class="button" onclick="editProductSettings('<?php echo esc_attr($product->term_id); ?>')">
                                    <?php esc_html_e('Settings', 'product-roadmap'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=roadmap_item&roadmap_product=' . $product->slug)); ?>" 
                                   class="button">
                                    <?php esc_html_e('View Items', 'product-roadmap'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Product Settings Modal -->
                <div id="product-settings-modal" class="roadmap-modal" style="display: none;">
                    <div class="roadmap-modal-content">
                        <span class="roadmap-modal-close">&times;</span>
                        <h2><?php esc_html_e('Product Settings', 'product-roadmap'); ?></h2>
                        <form id="product-settings-form">
                            <input type="hidden" id="product_id" name="product_id" value="">
                            <?php wp_nonce_field('roadmap_product_settings', 'roadmap_nonce'); ?>
                            
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="product_default_status"><?php esc_html_e('Default Status', 'product-roadmap'); ?></label>
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
                                    <th scope="row"><?php esc_html_e('Features', 'product-roadmap'); ?></th>
                                    <td>
                                        <label>
                                            <input type="checkbox" name="enable_voting" value="1" checked>
                                            <?php esc_html_e('Enable Voting', 'product-roadmap'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="enable_comments" value="1" checked>
                                            <?php esc_html_e('Enable Comments', 'product-roadmap'); ?>
                                        </label><br>
                                        
                                        <label>
                                            <input type="checkbox" name="enable_suggestions" value="1" checked>
                                            <?php esc_html_e('Enable Suggestions', 'product-roadmap'); ?>
                                        </label>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                        <label for="product_theme_color"><?php esc_html_e('Theme Color', 'product-roadmap'); ?></label>
                                    </th>
                                    <td>
                                        <input type="color" name="theme_color" id="product_theme_color" value="#2271b1">
                                    </td>
                                </tr>
                            </table>
                            
                            <p class="submit">
                                <button type="submit" class="button button-primary"><?php esc_html_e('Save Settings', 'product-roadmap'); ?></button>
                            </p>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    public function ajax_save_product_settings() {
        check_ajax_referer('roadmap_product_settings', 'roadmap_nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die();
        }
        
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $settings = [
            'default_status' => isset($_POST['default_status']) ? sanitize_text_field(wp_unslash($_POST['default_status'])) : '',
            'enable_voting' => !empty($_POST['enable_voting']),
            'enable_comments' => !empty($_POST['enable_comments']),
            'enable_suggestions' => !empty($_POST['enable_suggestions']),
            'theme_color' => isset($_POST['theme_color']) ? sanitize_hex_color(wp_unslash($_POST['theme_color'])) : '',
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
        
        $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $settings = $this->get_product_settings($product_id);
        
        wp_send_json_success($settings);
    }
    
    /**
     * Sanitize yes/no option
     */
    public function sanitize_yes_no($input) {
        return ($input === 'yes') ? 'yes' : 'no';
    }
    
    public function sanitize_global_settings($input) {
        $sanitized = [];
        
        $sanitized['default_status'] = sanitize_text_field($input['default_status'] ?? 'planned');
        $sanitized['items_per_page'] = absint($input['items_per_page'] ?? 100);
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

        // Enqueue modern admin CSS
        wp_enqueue_style('roadmap-admin', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-admin.css', [], ROADMAP_VERSION);

        // Enqueue admin JavaScript
        wp_enqueue_script('roadmap-admin', ROADMAP_PLUGIN_URL . 'assets/js/roadmap-admin.js', ['jquery'], ROADMAP_VERSION, true);

        // Localize script for AJAX and translations
        wp_localize_script('roadmap-admin', 'roadmap_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('roadmap_admin_nonce'),
            'confirm_demo_import' => __('Are you sure you want to import demo data? This will create sample roadmap items.', 'product-roadmap'),
            'importing_text' => __('Importing...', 'product-roadmap'),
            'import_success' => __('Demo data imported successfully!', 'product-roadmap'),
            'import_error' => __('Error importing demo data. Please try again.', 'product-roadmap'),
            'import_demo_text' => __('Import Demo Data', 'product-roadmap')
        ]);

        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
    
    public function add_product_columns($columns) {
        $new_columns = [];
        
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            
            // Add shortcode column after name
            if ($key === 'name') {
                $new_columns['shortcode'] = __('Shortcode', 'product-roadmap');
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
    
    private function render_pro_tab_content($tab, $settings) {
        // Provide card-based wrapper for pro tabs
        switch ($tab) {
            case 'templates':
                $this->render_templates_tab($settings);
                break;
            case 'advanced':
                $this->render_advanced_tab($settings);
                break;
            case 'license':
                // Let pro addon handle license tab
                do_action('roadmap_render_license_tab');
                break;
            default:
                // Allow other pro tabs
                do_action('roadmap_settings_tab_content', $tab);
                break;
        }
    }
    
    private function render_templates_tab($settings) {
        ?>
        <div class="roadmap-settings-section">
            <h2><?php esc_html_e('Template Settings', 'product-roadmap'); ?></h2>
            <p class="section-description">
                <?php esc_html_e('Customize the appearance and layout of your roadmap.', 'product-roadmap'); ?>
            </p>
            
            <?php if (!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE): ?>
            <!-- Pro Feature Notice -->
            <div class="roadmap-settings-card pro-preview">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-layout"></i> <?php esc_html_e('Template Options', 'product-roadmap'); ?></h3>
                    <span class="pro-badge">PRO</span>
                </div>
                <div class="card-body">
                    <div class="roadmap-empty-state">
                        <i class="dashicons dashicons-star-filled"></i>
                        <h3><?php esc_html_e('Pro Feature', 'product-roadmap'); ?></h3>
                        <p><?php esc_html_e('Unlock multiple template layouts and customization options with the Pro version.', 'product-roadmap'); ?></p>
                        <ul class="pro-features-list" style="text-align: left; max-width: 400px; margin: 20px auto;">
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Timeline view template', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Grid view template', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Kanban board template', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Compact list template', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Custom CSS editor', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Dark mode support', 'product-roadmap'); ?></li>
                        </ul>
                        <a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
                            <?php esc_html_e('Get Pro Version', 'product-roadmap'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <?php // Pro version would render actual settings here ?>
                <?php do_action('roadmap_render_templates_settings', $settings); ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    private function render_advanced_tab($settings) {
        ?>
        <div class="roadmap-settings-section">
            <h2><?php esc_html_e('Advanced Settings', 'product-roadmap'); ?></h2>
            <p class="section-description">
                <?php esc_html_e('Fine-tune your roadmap with advanced configuration options.', 'product-roadmap'); ?>
            </p>
            
            <!-- Cache Settings Card -->
            <div class="roadmap-settings-card">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-performance"></i> <?php esc_html_e('Performance Settings', 'product-roadmap'); ?></h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <label for="cache_expiry"><?php esc_html_e('Cache Expiry (seconds)', 'product-roadmap'); ?></label>
                        <input type="number" name="roadmap_cache_expiry" id="cache_expiry" 
                               value="<?php echo esc_attr(get_option('roadmap_cache_expiry', '3600')); ?>" 
                               min="0" max="86400" />
                        <p class="description"><?php esc_html_e('How long to cache roadmap data. Set to 0 to disable caching.', 'product-roadmap'); ?></p>
                    </div>
                </div>
            </div>
            
            <?php if (!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE): ?>
            <!-- Pro Feature Notice -->
            <div class="roadmap-settings-card pro-preview">
                <div class="card-header">
                    <h3><i class="dashicons dashicons-admin-tools"></i> <?php esc_html_e('More Advanced Options', 'product-roadmap'); ?></h3>
                    <span class="pro-badge">PRO</span>
                </div>
                <div class="card-body">
                    <div class="roadmap-empty-state">
                        <i class="dashicons dashicons-star-filled"></i>
                        <h3><?php esc_html_e('Pro Features', 'product-roadmap'); ?></h3>
                        <p><?php esc_html_e('Access more advanced settings and customization options with the Pro version.', 'product-roadmap'); ?></p>
                        <ul class="pro-features-list" style="text-align: left; max-width: 400px; margin: 20px auto;">
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Custom post type slug', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Advanced permissions', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('API access control', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Export/Import settings', 'product-roadmap'); ?></li>
                            <li><i class="dashicons dashicons-yes"></i> <?php esc_html_e('Database optimization', 'product-roadmap'); ?></li>
                        </ul>
                        <a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
                            <?php esc_html_e('Get Pro Version', 'product-roadmap'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php else: ?>
                <?php // Pro version would render actual settings here ?>
                <?php do_action('roadmap_render_advanced_settings', $settings); ?>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_settings_styles() {
        // Styles moved to assets/css/roadmap-admin.css
        return;
        /* Main Container */
        .roadmap-settings-wrap {
            margin-top: 20px;
            max-width: 1400px;
        }
        
        .roadmap-settings-header {
            margin-bottom: 30px;
        }
        
        .roadmap-settings-header h1 {
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 600;
            color: #1d2327;
        }
        
        .roadmap-settings-description {
            color: #646970;
            font-size: 16px;
            margin: 0;
        }
        
        /* Layout */
        .roadmap-settings-container {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        
        /* Sidebar */
        .roadmap-settings-sidebar {
            width: 250px;
            flex-shrink: 0;
        }
        
        .roadmap-settings-nav {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            padding: 0;
            margin: 0 0 20px 0;
            list-style: none;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        
        .roadmap-settings-nav li {
            margin: 0;
            border-bottom: 1px solid #f0f0f1;
        }
        
        .roadmap-settings-nav li:last-child {
            border-bottom: none;
        }
        
        .roadmap-nav-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            text-decoration: none;
            color: #3c434a;
            transition: all 0.2s;
        }
        
        .roadmap-nav-item:hover {
            background: #f6f7f7;
            color: #2271b1;
        }
        
        .roadmap-nav-item.active {
            background: #f0f6fc;
            color: #2271b1;
            font-weight: 600;
            position: relative;
        }
        
        .roadmap-nav-item.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 3px;
            background: #2271b1;
        }
        
        .roadmap-nav-item i {
            margin-right: 10px;
            font-size: 18px;
        }
        
        /* Pro CTA */
        .roadmap-pro-cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .roadmap-pro-cta h3 {
            margin: 0 0 10px 0;
            color: #fff;
            font-size: 18px;
        }
        
        .roadmap-pro-cta p {
            margin: 0 0 15px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        
        .roadmap-pro-cta .button-primary {
            background: #fff;
            color: #667eea;
            border: none;
            font-weight: 600;
        }
        
        .roadmap-pro-cta .button-primary:hover {
            background: #f0f0f1;
        }
        
        /* Content Area */
        .roadmap-settings-content {
            flex: 1;
            min-width: 0;
        }
        
        .roadmap-settings-section h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            font-weight: 600;
            color: #1d2327;
        }
        
        .section-description {
            color: #646970;
            margin: 0 0 25px 0;
            font-size: 14px;
        }
        
        /* Cards */
        .roadmap-settings-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            transition: box-shadow 0.2s;
        }
        
        .roadmap-settings-card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f1;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1d2327;
            display: flex;
            align-items: center;
        }
        
        .card-header h3 i {
            margin-right: 10px;
            color: #646970;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-description {
            color: #646970;
            margin: 0 0 20px 0;
            font-size: 14px;
        }
        
        /* Form Elements */
        .form-row {
            margin-bottom: 20px;
        }
        
        .form-row:last-child {
            margin-bottom: 0;
        }
        
        .form-row label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1d2327;
            font-size: 14px;
        }
        
        .form-row input[type="text"],
        .form-row input[type="email"],
        .form-row input[type="number"],
        .form-row select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #8c8f94;
            border-radius: 4px;
            font-size: 14px;
            transition: border-color 0.2s;
        }
        
        .form-row input[type="text"]:focus,
        .form-row input[type="email"]:focus,
        .form-row input[type="number"]:focus,
        .form-row select:focus {
            border-color: #2271b1;
            outline: none;
            box-shadow: 0 0 0 1px #2271b1;
        }
        
        .form-row .description {
            margin-top: 5px;
            color: #646970;
            font-size: 13px;
        }
        
        .form-row-group {
            display: flex;
            gap: 20px;
        }
        
        .form-row.half {
            flex: 1;
        }
        
        /* Toggle Switches */
        .feature-toggle {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .feature-toggle.large {
            padding: 15px;
            background: #f6f7f7;
            border-radius: 6px;
            margin-bottom: 0;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #2271b1;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(20px);
        }
        
        .feature-info h4 {
            margin: 0 0 5px 0;
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
        }
        
        .feature-info p {
            margin: 0;
            color: #646970;
            font-size: 13px;
        }
        
        /* Notification Events */
        .notification-events {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .notification-event {
            padding: 15px;
            background: #f6f7f7;
            border-radius: 6px;
            transition: background 0.2s;
        }
        
        .notification-event:hover {
            background: #f0f0f1;
        }
        
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            cursor: pointer;
        }
        
        .checkbox-label input[type="checkbox"] {
            margin-right: 10px;
            margin-top: 3px;
        }
        
        .checkbox-text strong {
            display: block;
            margin-bottom: 3px;
            color: #1d2327;
        }
        
        .checkbox-text small {
            display: block;
            color: #646970;
            font-size: 12px;
        }
        
        /* Pro Preview Card */
        .roadmap-settings-card.pro-preview {
            background: linear-gradient(135deg, #f6f7f7 0%, #f0f0f1 100%);
            border: 2px dashed #8c8f94;
            position: relative;
        }
        
        .pro-badge {
            background: #667eea;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .pro-features-list {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
        }
        
        .pro-features-list li {
            padding: 8px 0;
            display: flex;
            align-items: center;
            color: #646970;
        }
        
        .pro-features-list li i {
            color: #00a32a;
            margin-right: 10px;
        }
        
        /* Footer */
        .roadmap-settings-footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #f0f0f1;
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .roadmap-settings-saved {
            color: #00a32a;
            display: flex;
            align-items: center;
            gap: 5px;
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Responsive */
        @media (max-width: 782px) {
            .roadmap-settings-container {
                flex-direction: column;
            }
            
            .roadmap-settings-sidebar {
                width: 100%;
            }
            
            .form-row-group {
                flex-direction: column;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
            }
        }
    }
}

// Initialize
Roadmap_Settings::get_instance();