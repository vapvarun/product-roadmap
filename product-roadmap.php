<?php
/**
 * Plugin Name: Product Roadmap
 * Plugin URI: https://wbcomdesigns.com/plugins/product-roadmap/
 * Description: Create public product roadmaps to share your vision, collect user feedback, and build products your customers actually want.
 * Version: 1.1.0
 * Author: wbcomdesigns
 * Author URI: https://wbcomdesigns.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: product-roadmap
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ROADMAP_VERSION', '1.1.0');
define('ROADMAP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ROADMAP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ROADMAP_PLUGIN_BASENAME', plugin_basename(__FILE__));

class RoadmapPlugin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->load_dependencies();
        $this->define_hooks();
    }
    
    private function load_dependencies() {
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-cpt.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-api.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-shortcode.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-settings.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-permissions.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-notifications.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-migration.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-admin.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-demo-import.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-setup-wizard.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-voting.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-comments.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-suggestions.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-template.php';
    }
    
    private function define_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
        
        add_action('init', [$this, 'init']);
        
        // Initialize components
        Roadmap_CPT::get_instance();
        Roadmap_API::get_instance();
        Roadmap_Shortcode::get_instance();
        Roadmap_Settings::get_instance();
        Roadmap_Permissions::get_instance();
        Roadmap_Notifications::get_instance();
        Roadmap_Admin::get_instance();
        Roadmap_Voting::get_instance();
        Roadmap_Comments::get_instance();
        Roadmap_Suggestions::get_instance();
        Roadmap_Template::get_instance();
        
        // Pro addon hooks
        do_action('roadmap_plugin_loaded');
    }
    
    public function init() {
        // Check if pro addon is active
        if (!defined('ROADMAP_PRO_ACTIVE')) {
            define('ROADMAP_PRO_ACTIVE', false);
        }
        
        // Run migration check on admin init
        if (is_admin()) {
            add_action('admin_init', [$this, 'check_migration']);
        }
    }
    
    public function check_migration() {
        // Check if migration is needed and not already running
        if (class_exists('Roadmap_Migration') && !get_transient('roadmap_migration_running') && Roadmap_Migration::needs_migration()) {
            set_transient('roadmap_migration_running', true, 300); // 5 minute lock
            
            $result = Roadmap_Migration::migrate_products_to_taxonomy();
            
            delete_transient('roadmap_migration_running');
            
            // Log migration result
            if ($result['errors'] === 0) {
                update_option('roadmap_migration_completed', current_time('timestamp'));
            }
        }
    }
    
    // Text domain loading is handled automatically by WordPress.org since WP 4.6
    
    public function activate() {
        // Create database tables if needed
        $this->create_tables();
        
        // Set default options
        $this->set_default_options();
        
        // Register taxonomies first to ensure they exist
        Roadmap_CPT::get_instance()->register_taxonomies();
        
        // Run migration if needed
        if (class_exists('Roadmap_Migration') && Roadmap_Migration::needs_migration()) {
            Roadmap_Migration::migrate_products_to_taxonomy();
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation flag for onboarding
        set_transient('roadmap_activation_redirect', true, 30);
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Votes table
        $votes_table = $wpdb->prefix . 'roadmap_votes';
        $sql_votes = "CREATE TABLE IF NOT EXISTS $votes_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            item_id bigint(20) unsigned NOT NULL,
            vote_value int(1) NOT NULL DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_item (user_id, item_id),
            KEY item_id (item_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_votes);
    }
    
    private function set_default_options() {
        $defaults = [
            'roadmap_enable_voting' => 'yes',
            'roadmap_allow_guest_voting' => 'yes',
            'roadmap_enable_comments' => 'yes',
            'roadmap_enable_suggestions' => 'yes',
            'roadmap_default_product' => 'main',
            'roadmap_items_per_page' => 100,
            'roadmap_enable_notifications' => 'yes',
            'roadmap_notification_email' => get_option('admin_email'),
        ];
        
        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}

// Initialize plugin
function roadmap_plugin() {
    return RoadmapPlugin::get_instance();
}

// Start the plugin
roadmap_plugin();