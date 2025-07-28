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
        return $this->import_demo_content();
    }
    
    /**
     * Import demo content
     */
    private function import_demo_content() {
        // Create categories first
        $categories = array(
            'Features' => __('New features and enhancements', 'roadmap'),
            'Improvements' => __('Improvements to existing functionality', 'roadmap'),
            'Bug Fixes' => __('Bug fixes and issues', 'roadmap'),
            'Infrastructure' => __('Backend and infrastructure updates', 'roadmap')
        );
        
        $category_ids = array();
        foreach ($categories as $name => $description) {
            $term = wp_insert_term($name, 'roadmap_category', array(
                'description' => $description
            ));
            if (!is_wp_error($term)) {
                $category_ids[$name] = $term['term_id'];
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
                'votes' => 45
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
                'votes' => 52
            ),
            array(
                'title' => __('Performance Optimization', 'roadmap'),
                'content' => __('Optimize database queries and implement caching strategies to improve page load times. Target is to achieve 50% faster load times for large datasets.', 'roadmap'),
                'status' => 'in-progress',
                'priority' => 'high',
                'category' => 'Improvements',
                'votes' => 31
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
                'status' => 'planned',
                'priority' => 'medium',
                'category' => 'Infrastructure',
                'votes' => 18
            ),
            array(
                'title' => __('Multi-language Support', 'roadmap'),
                'content' => __('Add full internationalization support with translations for major languages. Include RTL language support and locale-specific formatting.', 'roadmap'),
                'status' => 'planned',
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
                'status' => 'planned',
                'priority' => 'high',
                'category' => 'Infrastructure',
                'votes' => 55
            )
        );
        
        // Import each item
        foreach ($demo_items as $item) {
            $post_data = array(
                'post_title' => $item['title'],
                'post_content' => $item['content'],
                'post_status' => 'publish',
                'post_type' => 'roadmap_item',
                'meta_input' => array(
                    'roadmap_status' => $item['status'],
                    'roadmap_priority' => $item['priority'],
                    'roadmap_votes' => $item['votes']
                )
            );
            
            $post_id = wp_insert_post($post_data);
            
            if ($post_id && !is_wp_error($post_id)) {
                // Assign category
                if (isset($category_ids[$item['category']])) {
                    wp_set_object_terms($post_id, $category_ids[$item['category']], 'roadmap_category');
                }
                
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
    }
}

// Initialize the demo import class
new Roadmap_Demo_Import();