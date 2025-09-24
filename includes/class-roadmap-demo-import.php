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
                <strong><?php esc_html_e('Welcome to Product Roadmap!', 'product-roadmap'); ?></strong>
                <?php esc_html_e('Get started quickly by importing demo content to see how the plugin works.', 'product-roadmap'); ?>
            </p>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=roadmap-setup-wizard')); ?>" class="button button-primary">
                    <?php esc_html_e('Run Setup Wizard', 'product-roadmap'); ?>
                </a>
                <a href="#" class="button roadmap-dismiss-setup-notice" data-nonce="<?php echo esc_attr(wp_create_nonce('roadmap_dismiss_notice')); ?>">
                    <?php esc_html_e('Dismiss', 'product-roadmap'); ?>
                </a>
            </p>
        </div>
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
            'Features' => __('New features and enhancements', 'product-roadmap'),
            'Improvements' => __('Improvements to existing functionality', 'product-roadmap'),
            'Bug Fixes' => __('Bug fixes and issues', 'product-roadmap'),
            'Infrastructure' => __('Backend and infrastructure updates', 'product-roadmap')
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
                'title' => __('Dark Mode Support', 'product-roadmap'),
                'content' => __('Add a dark mode toggle to provide users with a comfortable viewing experience in low-light environments. This feature will include automatic theme switching based on system preferences and a manual toggle in the user interface.', 'product-roadmap'),
                'status' => 'planned',
                'priority' => 'high',
                'category' => 'Features',
                'votes' => 45,
                'target_date' => gmdate('Y-m-d', strtotime('+3 months'))
            ),
            array(
                'title' => __('Mobile App Development', 'product-roadmap'),
                'content' => __('Develop native mobile applications for iOS and Android platforms. The apps will provide core functionality with offline support and push notifications for important updates.', 'product-roadmap'),
                'status' => 'planned',
                'priority' => 'medium',
                'category' => 'Features',
                'votes' => 38
            ),
            array(
                'title' => __('Advanced Search Filters', 'product-roadmap'),
                'content' => __('Implement advanced search capabilities with filters for date ranges, categories, tags, and custom fields. Include saved search functionality for frequently used queries.', 'product-roadmap'),
                'status' => 'in-progress',
                'priority' => 'high',
                'category' => 'Features',
                'votes' => 52,
                'target_date' => gmdate('Y-m-d', strtotime('+1 month'))
            ),
            array(
                'title' => __('Performance Optimization', 'product-roadmap'),
                'content' => __('Optimize database queries and implement caching strategies to improve page load times. Target is to achieve 50% faster load times for large datasets.', 'product-roadmap'),
                'status' => 'in-progress',
                'priority' => 'high',
                'category' => 'Improvements',
                'votes' => 31,
                'target_date' => gmdate('Y-m-d', strtotime('+2 weeks'))
            ),
            array(
                'title' => __('Email Notification System', 'product-roadmap'),
                'content' => __('Build a comprehensive email notification system that allows users to subscribe to updates for specific items or categories. Include customizable email templates and frequency settings.', 'product-roadmap'),
                'status' => 'completed',
                'priority' => 'medium',
                'category' => 'Features',
                'votes' => 67
            ),
            array(
                'title' => __('Fix Export Function Memory Issue', 'product-roadmap'),
                'content' => __('Resolve the memory exhaustion error that occurs when exporting large datasets. Implement chunked processing to handle exports of any size.', 'product-roadmap'),
                'status' => 'completed',
                'priority' => 'high',
                'category' => 'Bug Fixes',
                'votes' => 23
            ),
            array(
                'title' => __('API Rate Limiting', 'product-roadmap'),
                'content' => __('Implement rate limiting for API endpoints to prevent abuse and ensure fair usage. Include configurable limits per user and IP address.', 'product-roadmap'),
                'status' => 'under-review',
                'priority' => 'medium',
                'category' => 'Infrastructure',
                'votes' => 18
            ),
            array(
                'title' => __('Multi-language Support', 'product-roadmap'),
                'content' => __('Add full internationalization support with translations for major languages. Include RTL language support and locale-specific formatting.', 'product-roadmap'),
                'status' => 'on-hold',
                'priority' => 'low',
                'category' => 'Features',
                'votes' => 29
            ),
            array(
                'title' => __('Dashboard Widgets', 'product-roadmap'),
                'content' => __('Create customizable dashboard widgets showing roadmap statistics, recent updates, and voting trends. Allow users to arrange and configure widgets to their preference.', 'product-roadmap'),
                'status' => 'in-progress',
                'priority' => 'medium',
                'category' => 'Features',
                'votes' => 41
            ),
            array(
                'title' => __('Backup and Restore', 'product-roadmap'),
                'content' => __('Implement automated backup functionality with scheduled backups and one-click restore options. Include selective backup for specific data types.', 'product-roadmap'),
                'status' => 'under-review',
                'priority' => 'high',
                'category' => 'Infrastructure',
                'votes' => 55
            ),
            array(
                'title' => __('Social Media Integration', 'product-roadmap'),
                'content' => __('Add social media sharing capabilities and integration with major platforms. Allow users to share roadmap items and updates on Twitter, LinkedIn, and Facebook.', 'product-roadmap'),
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
                    // Silently handle errors
                    if (is_wp_error($cat_result)) {
                        // Category assignment failed, but continue
                    }
                }
                
                // Assign product (main product for free version)
                if ($default_product_id) {
                    $prod_result = wp_set_object_terms($post_id, (int)$default_product_id, 'roadmap_product', false);
                    // Silently handle errors
                }
                
                // Assign status
                $status_term = get_term_by('slug', $item['status'], 'roadmap_status');
                if ($status_term && !is_wp_error($status_term)) {
                    $status_result = wp_set_object_terms($post_id, (int)$status_term->term_id, 'roadmap_status', false);
                    // Silently handle errors
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
                        'comment_content' => __('This would be a great addition! Looking forward to seeing this implemented.', 'product-roadmap'),
                        'comment_author' => __('Demo User', 'product-roadmap'),
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
            // Taxonomy not registered yet
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
                // Failed to create term
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
            // Taxonomy not registered yet
            return false;
        }
        
        $default_statuses = array(
            'planned' => __('Planned', 'product-roadmap'),
            'in-progress' => __('In Progress', 'product-roadmap'),
            'completed' => __('Completed', 'product-roadmap'),
            'on-hold' => __('On Hold', 'product-roadmap'),
            'under-review' => __('Under Review', 'product-roadmap')
        );
        
        foreach ($default_statuses as $slug => $name) {
            $existing = get_term_by('slug', $slug, 'roadmap_status');
            if (!$existing || is_wp_error($existing)) {
                $result = wp_insert_term($name, 'roadmap_status', array('slug' => $slug));
                // Silently handle errors
            }
        }
        
        return true;
    }
}

// Initialize the demo import class
new Roadmap_Demo_Import();