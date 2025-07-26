<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Shortcode {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_shortcode('roadmap', [$this, 'render_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'maybe_enqueue_assets']);
    }
    
    public function render_shortcode($atts) {
        $atts = shortcode_atts([
            'mode' => 'view',
            'allow_frontend_edit' => 'false',
            'product' => '',
            'class' => '',
            'template' => 'board',
        ], $atts, 'roadmap');
        
        // Handle product parameter
        if (empty($atts['product'])) {
            // If Pro is not active, default to 'main'
            if (!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE) {
                $atts['product'] = 'main';
            } else {
                // Pro is active but no product specified
                return '<div class="roadmap-error">' . __('Error: Product parameter is required. Example: [roadmap product="main"]', 'roadmap-plugin') . '</div>';
            }
        }
        
        // If Pro is not active and product is not 'main', show error
        if ((!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE) && $atts['product'] !== 'main') {
            return '<div class="roadmap-error">' . __('Multiple products require the Pro version. Upgrade to use different products.', 'roadmap-plugin') . '</div>';
        }
        
        // Handle template fallback for pro templates when pro is not active
        $pro_templates = ['timeline', 'list', 'cards', 'minimal'];
        if ((!defined('ROADMAP_PRO_ACTIVE') || !ROADMAP_PRO_ACTIVE) && in_array($atts['template'], $pro_templates)) {
            $atts['template'] = 'board'; // Fallback to default board template
        }
        
        // Validate mode
        $allowed_modes = ['view', 'manage'];
        if (!in_array($atts['mode'], $allowed_modes)) {
            $atts['mode'] = 'view';
        }
        
        // Check permissions for manage mode
        if ($atts['mode'] === 'manage' && !current_user_can('edit_posts')) {
            $atts['mode'] = 'view';
        }
        
        // Enqueue assets
        $this->enqueue_assets();
        
        // Prepare data for Vue app
        $app_data = [
            'api_url' => rest_url('roadmap/v1'),
            'nonce' => wp_create_nonce('wp_rest'),
            'mode' => $atts['mode'],
            'allow_frontend_edit' => filter_var($atts['allow_frontend_edit'], FILTER_VALIDATE_BOOLEAN),
            'product' => $atts['product'],
            'template' => $atts['template'],
            'is_pro' => (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE),
            'settings' => [
                'enable_voting' => get_option('roadmap_enable_voting', 'yes') === 'yes',
                'enable_comments' => get_option('roadmap_enable_comments', 'yes') === 'yes',
                'enable_suggestions' => get_option('roadmap_enable_suggestions', 'yes') === 'yes',
                'items_per_page' => (int) get_option('roadmap_items_per_page', 20),
            ],
            'user' => $this->get_user_data(),
            'i18n' => $this->get_translations(),
        ];
        
        $app_data = apply_filters('roadmap_shortcode_data', $app_data, $atts);
        
        // Generate unique ID for multiple instances
        $app_id = 'roadmap-app-' . uniqid();
        
        ob_start();
        ?>
        <div id="<?php echo esc_attr($app_id); ?>" 
             class="roadmap-app <?php echo esc_attr($atts['class']); ?> <?php echo $atts['product'] ? 'roadmap-single-product' : ''; ?>"
             data-config='<?php echo esc_attr(json_encode($app_data)); ?>'>
            <div class="roadmap-loading">
                <?php _e('Loading roadmap...', 'roadmap-plugin'); ?>
            </div>
        </div>
        <?php
        
        return ob_get_clean();
    }
    
    public function maybe_enqueue_assets() {
        global $post;
        
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'roadmap')) {
            $this->enqueue_assets();
        }
    }
    
    public function enqueue_assets() {
        $version = ROADMAP_VERSION;
        $is_dev = defined('WP_DEBUG') && WP_DEBUG;
        
        if ($is_dev && file_exists(ROADMAP_PLUGIN_PATH . 'vue-app/dist/assets/index.js')) {
            // Development mode - use Vite build
            wp_enqueue_script(
                'roadmap-vue-app',
                ROADMAP_PLUGIN_URL . 'vue-app/dist/assets/index.js',
                [],
                $version,
                true
            );
            
            wp_enqueue_style(
                'roadmap-vue-app',
                ROADMAP_PLUGIN_URL . 'vue-app/dist/assets/index.css',
                [],
                $version
            );
        } else {
            // Production mode - use built assets
            wp_enqueue_script(
                'roadmap-vue-app',
                ROADMAP_PLUGIN_URL . 'assets/js/roadmap-app.js',
                ['wp-api'],
                $version,
                true
            );
            
            wp_enqueue_style(
                'roadmap-vue-app',
                ROADMAP_PLUGIN_URL . 'assets/css/roadmap-app.css',
                [],
                $version
            );
        }
        
        // Add inline CSS to hide product filter for single product roadmaps
        wp_add_inline_style('roadmap-vue-app', '
            /* Hide the third select (product filter) when viewing single product roadmap */
            .roadmap-single-product .roadmap-filters select:nth-child(3) {
                display: none !important;
            }
        ');
        
        // Add inline script to initialize Vue apps
        wp_add_inline_script('roadmap-vue-app', '
            if (!window.roadmapInitialized) {
                window.roadmapInitialized = true;
                
                document.addEventListener("DOMContentLoaded", function() {
                    const roadmapApps = document.querySelectorAll(".roadmap-app");
                    
                    roadmapApps.forEach(function(container, index) {
                        // Skip if already mounted
                        if (container.__vue_app__) {
                            return;
                        }
                        
                        if (window.RoadmapApp && container.dataset.config) {
                            try {
                                const config = JSON.parse(container.dataset.config);
                                window.RoadmapApp.mount(container, config);
                            } catch (e) {
                                // Silent error handling
                            }
                        }
                    });
                });
            }
        ');
        
        // Allow pro addon to enqueue additional assets
        do_action('roadmap_enqueue_assets');
    }
    
    public function get_user_data() {
        if (!is_user_logged_in()) {
            return null;
        }
        
        $user = wp_get_current_user();
        
        return [
            'id' => $user->ID,
            'name' => $user->display_name,
            'email' => $user->user_email,
            'avatar' => get_avatar_url($user->ID),
            'can_vote' => get_option('roadmap_enable_voting', 'yes') === 'yes',
            'can_comment' => get_option('roadmap_enable_comments', 'yes') === 'yes',
            'can_suggest' => get_option('roadmap_enable_suggestions', 'yes') === 'yes',
            'can_edit' => current_user_can('edit_posts'),
            'can_manage' => current_user_can('manage_options'),
        ];
    }
    
    public function get_translations() {
        return [
            'loading' => __('Loading...', 'roadmap-plugin'),
            'error' => __('An error occurred', 'roadmap-plugin'),
            'no_items' => __('No roadmap items found', 'roadmap-plugin'),
            'vote' => __('Vote', 'roadmap-plugin'),
            'votes' => __('votes', 'roadmap-plugin'),
            'comment' => __('Comment', 'roadmap-plugin'),
            'comments' => __('comments', 'roadmap-plugin'),
            'suggest' => __('Suggest Feature', 'roadmap-plugin'),
            'search' => __('Search...', 'roadmap-plugin'),
            'filter_by_status' => __('Filter by Status', 'roadmap-plugin'),
            'filter_by_category' => __('Filter by Category', 'roadmap-plugin'),
            'all_statuses' => __('All Statuses', 'roadmap-plugin'),
            'all_categories' => __('All Categories', 'roadmap-plugin'),
            'view_mode' => __('View Mode', 'roadmap-plugin'),
            'manage_mode' => __('Manage Mode', 'roadmap-plugin'),
            'add_item' => __('Add Item', 'roadmap-plugin'),
            'edit_item' => __('Edit Item', 'roadmap-plugin'),
            'delete_item' => __('Delete Item', 'roadmap-plugin'),
            'save' => __('Save', 'roadmap-plugin'),
            'cancel' => __('Cancel', 'roadmap-plugin'),
            'confirm_delete' => __('Are you sure you want to delete this item?', 'roadmap-plugin'),
            'item_added' => __('Item added successfully', 'roadmap-plugin'),
            'item_updated' => __('Item updated successfully', 'roadmap-plugin'),
            'item_deleted' => __('Item deleted successfully', 'roadmap-plugin'),
            'vote_submitted' => __('Your vote has been submitted', 'roadmap-plugin'),
            'comment_submitted' => __('Your comment has been submitted', 'roadmap-plugin'),
            'suggestion_submitted' => __('Your suggestion has been submitted', 'roadmap-plugin'),
            'login_required' => __('Please login to perform this action', 'roadmap-plugin'),
            'title' => __('Title', 'roadmap-plugin'),
            'description' => __('Description', 'roadmap-plugin'),
            'status' => __('Status', 'roadmap-plugin'),
            'category' => __('Category', 'roadmap-plugin'),
            'priority' => __('Priority', 'roadmap-plugin'),
            'target_date' => __('Target Date', 'roadmap-plugin'),
            'low' => __('Low', 'roadmap-plugin'),
            'medium' => __('Medium', 'roadmap-plugin'),
            'high' => __('High', 'roadmap-plugin'),
            'critical' => __('Critical', 'roadmap-plugin'),
        ];
    }
}