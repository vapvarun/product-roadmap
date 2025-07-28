<?php
/**
 * Demo Content Import functionality
 *
 * @package Product_Roadmap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Roadmap_Demo_Import
 * Handles demo content import for quick setup
 */
class Roadmap_Demo_Import {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Only show the setup notice - demo import is handled by setup wizard
        add_action('admin_notices', array($this, 'show_setup_notice'));
        add_action('wp_ajax_roadmap_dismiss_setup_notice', array($this, 'dismiss_setup_notice'));
        
        // Legacy hook - kept to prevent errors during transition
        add_action('admin_init', array($this, 'handle_demo_import'));
    }
    
    
    /**
     * Show setup notice for new installations
     */
    public function show_setup_notice() {
        // Only show on roadmap pages
        $screen = get_current_screen();
        if (!$screen || strpos($screen->id, 'roadmap') === false) {
            return;
        }
        
        // Check if notice was dismissed or demo was already imported
        if (get_option('roadmap_setup_notice_dismissed') || get_option('roadmap_demo_imported')) {
            return;
        }
        
        // Check if there are any roadmap items
        $items_count = wp_count_posts('roadmap_item');
        if ($items_count->publish > 0) {
            return;
        }
        
        ?>
        <div class="notice notice-info is-dismissible" id="roadmap-setup-notice">
            <p>
                <strong><?php _e('Welcome to Product Roadmap!', 'roadmap'); ?></strong>
                <?php _e('Get started quickly by importing demo content to see how the plugin works.', 'roadmap'); ?>
            </p>
            <p>
                <a href="<?php echo admin_url('admin.php?page=roadmap-setup-wizard'); ?>" class="button button-primary">
                    <?php _e('Run Setup Wizard', 'roadmap'); ?>
                </a>
                <a href="#" class="button" onclick="roadmapDismissSetupNotice(); return false;">
                    <?php _e('Dismiss', 'roadmap'); ?>
                </a>
            </p>
        </div>
        <script>
        function roadmapDismissSetupNotice() {
            jQuery.post(ajaxurl, {
                action: 'roadmap_dismiss_setup_notice',
                nonce: '<?php echo wp_create_nonce('roadmap_dismiss_notice'); ?>'
            });
            jQuery('#roadmap-setup-notice').fadeOut();
        }
        </script>
        <?php
    }
    
    /**
     * Dismiss setup notice
     */
    public function dismiss_setup_notice() {
        check_ajax_referer('roadmap_dismiss_notice', 'nonce');
        update_option('roadmap_setup_notice_dismissed', true);
        wp_die();
    }
    
    
    /**
     * Legacy demo import handler - now handled by setup wizard
     * Kept to prevent errors during transition
     */
    public function handle_demo_import() {
        // This functionality is now handled by the setup wizard
        // This method is kept empty to prevent errors
        return;
    }
    
    /**
     * Import demo content - public wrapper
     */
    public function import_demo_content_direct() {
        // Ensure post type and taxonomies are registered
        if (!post_type_exists('roadmap_item')) {
            Roadmap_CPT::get_instance()->register_post_types();
        }
        
        if (!taxonomy_exists('roadmap_product') || !taxonomy_exists('roadmap_status') || !taxonomy_exists('roadmap_category')) {
            Roadmap_CPT::get_instance()->register_taxonomies();
        }
        
        return $this->import_demo_content();
    }
    
    /**
     * Import demo content
     */
    private function import_demo_content() {
        // First ensure we have the default product term
        $default_product_id = $this->ensure_default_product();
        
        // Create status terms if they don't exist
        $this->ensure_status_terms();
        
        // Create categories first
        $categories = array(
            'Features' => __('New features and enhancements', 'roadmap'),
            'Improvements' => __('Improvements to existing functionality', 'roadmap'),
            'Bug Fixes' => __('Bug fixes and issues', 'roadmap'),
            'Infrastructure' => __('Backend and infrastructure updates', 'roadmap')
        );
        
        $category_ids = array();
        foreach ($categories as $name => $description) {
            // Check if term already exists
            $existing_term = get_term_by('name', $name, 'roadmap_category');
            if ($existing_term) {
                $category_ids[$name] = $existing_term->term_id;
            } else {
                $term = wp_insert_term($name, 'roadmap_category', array(
                    'description' => $description
                ));
                if (!is_wp_error($term)) {
                    $category_ids[$name] = $term['term_id'];
                }
            }
        }
        
        // Demo roadmap items
        $demo_items = array(
            array(
                'title' => __('Dark Mode Support', 'roadmap'),
                'content' => __('Add a dark mode toggle to provide users with a comfortable viewing experience in low-light environments. This feature will include automatic theme switching based on system preferences and a manual toggle in the user interface.', 'roadmap'),
                'status' => 'planned',
                'priority' => 'high',
                'category' => 'Features',
                'votes' => 45,
                'target_date' => date('Y-m-d', strtotime('+3 months'))
            ),
            array(
                'title' => __('Mobile App Development', 'roadmap'),
                'content' => __('Develop native mobile applications for iOS and Android platforms. The apps will provide core functionality with offline support and push notifications for important updates.', 'roadmap'),
                'status' => 'planned',
                'priority' => 'medium',
                'category' => 'Features',
                'votes' => 38
            ),
            array(
                'title' => __('Advanced Search Filters', 'roadmap'),
                'content' => __('Implement advanced search capabilities with filters for date ranges, categories, tags, and custom fields. Include saved search functionality for frequently used queries.', 'roadmap'),
                'status' => 'in-progress',
                'priority' => 'high',
                'category' => 'Features',
                'votes' => 52,
                'target_date' => date('Y-m-d', strtotime('+1 month'))
            ),
            array(
                'title' => __('Performance Optimization', 'roadmap'),
                'content' => __('Optimize database queries and implement caching strategies to improve page load times. Target is to achieve 50% faster load times for large datasets.', 'roadmap'),
                'status' => 'in-progress',
                'priority' => 'high',
                'category' => 'Improvements',
                'votes' => 31,
                'target_date' => date('Y-m-d', strtotime('+2 weeks'))
            ),
            array(
                'title' => __('Email Notification System', 'roadmap'),
                'content' => __('Build a comprehensive email notification system that allows users to subscribe to updates for specific items or categories. Include customizable email templates and frequency settings.', 'roadmap'),
                'status' => 'completed',
                'priority' => 'medium',
                'category' => 'Features',
                'votes' => 67
            ),
            array(
                'title' => __('Fix Export Function Memory Issue', 'roadmap'),
                'content' => __('Resolve the memory exhaustion error that occurs when exporting large datasets. Implement chunked processing to handle exports of any size.', 'roadmap'),
                'status' => 'completed',
                'priority' => 'high',
                'category' => 'Bug Fixes',
                'votes' => 23
            ),
            array(
                'title' => __('API Rate Limiting', 'roadmap'),
                'content' => __('Implement rate limiting for API endpoints to prevent abuse and ensure fair usage. Include configurable limits per user and IP address.', 'roadmap'),
                'status' => 'under-review',
                'priority' => 'medium',
                'category' => 'Infrastructure',
                'votes' => 18
            ),
            array(
                'title' => __('Multi-language Support', 'roadmap'),
                'content' => __('Add full internationalization support with translations for major languages. Include RTL language support and locale-specific formatting.', 'roadmap'),
                'status' => 'on-hold',
                'priority' => 'low',
                'category' => 'Features',
                'votes' => 29
            ),
            array(
                'title' => __('Dashboard Widgets', 'roadmap'),
                'content' => __('Create customizable dashboard widgets showing roadmap statistics, recent updates, and voting trends. Allow users to arrange and configure widgets to their preference.', 'roadmap'),
                'status' => 'in-progress',
                'priority' => 'medium',
                'category' => 'Features',
                'votes' => 41
            ),
            array(
                'title' => __('Backup and Restore', 'roadmap'),
                'content' => __('Implement automated backup functionality with scheduled backups and one-click restore options. Include selective backup for specific data types.', 'roadmap'),
                'status' => 'under-review',
                'priority' => 'high',
                'category' => 'Infrastructure',
                'votes' => 55
            ),
            array(
                'title' => __('Social Media Integration', 'roadmap'),
                'content' => __('Add social media sharing capabilities and integration with major platforms. Allow users to share roadmap items and updates on Twitter, LinkedIn, and Facebook.', 'roadmap'),
                'status' => 'on-hold',
                'priority' => 'low',
                'category' => 'Features',
                'votes' => 22
            )
        );
        
        // Import each item
        foreach ($demo_items as $item) {
            $post_data = array(
                'post_title' => $item['title'],
                'post_content' => $item['content'],
                'post_status' => 'publish',
                'post_type' => 'roadmap_item',
                'meta_input' => array()
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id && !is_wp_error($post_id)) {
                // Assign category
                if (isset($category_ids[$item['category']])) {
                    $cat_result = wp_set_object_terms($post_id, (int)$category_ids[$item['category']], 'roadmap_category', false);
                    if (is_wp_error($cat_result)) {
                        error_log('Failed to assign category: ' . $cat_result->get_error_message());
                    }
                }
                
                // Assign product (main product for free version)
                if ($default_product_id) {
                    $prod_result = wp_set_object_terms($post_id, (int)$default_product_id, 'roadmap_product', false);
                    if (is_wp_error($prod_result)) {
                        error_log('Failed to assign product: ' . $prod_result->get_error_message());
                    }
                }
                
                // Assign status
                $status_term = get_term_by('slug', $item['status'], 'roadmap_status');
                if ($status_term && !is_wp_error($status_term)) {
                    $status_result = wp_set_object_terms($post_id, (int)$status_term->term_id, 'roadmap_status', false);
                    if (is_wp_error($status_result)) {
                        error_log('Failed to assign status: ' . $status_result->get_error_message());
                    }
                } else {
                    error_log('Status term not found: ' . $item['status']);
                }
                
                // Update meta fields with proper values
                update_post_meta($post_id, '_roadmap_priority', $item['priority']);
                update_post_meta($post_id, '_roadmap_vote_count', $item['votes']);
                
                // Set target date if provided
                if (isset($item['target_date'])) {
                    update_post_meta($post_id, '_roadmap_target_date', $item['target_date']);
                }
                
                // Set progress based on status
                $progress = 0;
                switch($item['status']) {
                    case 'completed':
                        $progress = 100;
                        break;
                    case 'in-progress':
                        $progress = 50;
                        break;
                    case 'under-review':
                        $progress = 20;
                        break;
                    case 'on-hold':
                        $progress = 10;
                        break;
                    case 'planned':
                    default:
                        $progress = 0;
                        break;
                }
                update_post_meta($post_id, '_roadmap_progress', $progress);
                
                // Add some sample comments for items with more votes
                if ($item['votes'] > 40) {
                    $comment_data = array(
                        'comment_post_ID' => $post_id,
                        'comment_content' => __('This would be a great addition! Looking forward to seeing this implemented.', 'roadmap'),
                        'comment_author' => __('Demo User', 'roadmap'),
                        'comment_author_email' => 'demo@example.com',
                        'comment_approved' => 1
                    );
                    wp_insert_comment($comment_data);
                }
            }
        }
        
        // Mark demo as imported
        update_option('roadmap_demo_imported', true);
        update_option('roadmap_setup_notice_dismissed', true);
        
        // Flush rewrite rules to ensure permalinks work
        flush_rewrite_rules();
        
        return true;
    }
    
    /**
     * Ensure default product exists
     */
    private function ensure_default_product() {
        // First check if taxonomy exists
        if (!taxonomy_exists('roadmap_product')) {
            error_log('roadmap_product taxonomy does not exist');
            return false;
        }
        
        $default_product = get_option('roadmap_default_product', 'main');
        $term = get_term_by('slug', $default_product, 'roadmap_product');
        
        if (!$term || is_wp_error($term)) {
            // Create default product term
            $product_name = get_option('roadmap_product_name', 'Main Product');
            $result = wp_insert_term(
                $product_name,
                'roadmap_product',
                array('slug' => $default_product)
            );
            
            if (!is_wp_error($result)) {
                update_option('roadmap_default_product_id', $result['term_id']);
                return $result['term_id'];
            } else {
                error_log('Failed to create default product term: ' . $result->get_error_message());
                return false;
            }
        } else {
            update_option('roadmap_default_product_id', $term->term_id);
            return $term->term_id;
        }
    }
    
    /**
     * Ensure status terms exist
     */
    private function ensure_status_terms() {
        // First check if taxonomy exists
        if (!taxonomy_exists('roadmap_status')) {
            error_log('roadmap_status taxonomy does not exist');
            return false;
        }
        
        $default_statuses = array(
            'planned' => __('Planned', 'roadmap'),
            'in-progress' => __('In Progress', 'roadmap'),
            'completed' => __('Completed', 'roadmap'),
            'on-hold' => __('On Hold', 'roadmap'),
            'under-review' => __('Under Review', 'roadmap')
        );
        
        foreach ($default_statuses as $slug => $name) {
            $existing = get_term_by('slug', $slug, 'roadmap_status');
            if (!$existing || is_wp_error($existing)) {
                $result = wp_insert_term($name, 'roadmap_status', array('slug' => $slug));
                if (is_wp_error($result)) {
                    error_log('Failed to create status term ' . $slug . ': ' . $result->get_error_message());
                }
            }
        }
        
        return true;
    }
}

// Initialize the demo import class
new Roadmap_Demo_Import();