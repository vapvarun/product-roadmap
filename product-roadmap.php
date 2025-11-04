<?php
/**
 * Plugin Name: Product Roadmap
 * Plugin URI: https://wbcomdesigns.com/plugins/product-roadmap/
 * Description: Create public product roadmaps to share your vision, collect user feedback, and build products your customers actually want.
 * Version: 1.2.1
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

define('ROADMAP_VERSION', '1.2.1');
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
        add_action('upgrader_process_complete', [$this, 'handle_plugin_update'], 10, 2);
        
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
            add_action('admin_init', [$this, 'check_version_update']);
        }
    }

    /**
     * Check if plugin version has changed and flush rewrite rules if needed
     */
    public function check_version_update() {
        $stored_version = get_option('roadmap_version');

        // If version changed or not set, flush rewrite rules
        if ($stored_version !== ROADMAP_VERSION) {
            // Re-register post types and taxonomies
            if (class_exists('Roadmap_CPT')) {
                $cpt_instance = Roadmap_CPT::get_instance();
                $cpt_instance->register_post_types();
                $cpt_instance->register_taxonomies();
            }

            // Flush rewrite rules
            flush_rewrite_rules();

            // Update stored version
            update_option('roadmap_version', ROADMAP_VERSION);
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

    /**
     * Handle plugin updates - flush rewrite rules when this plugin is updated
     *
     * @param WP_Upgrader $upgrader_object WP_Upgrader instance
     * @param array $options Array of bulk item update data
     */
    public function handle_plugin_update($upgrader_object, $options) {
        // Check if this is a plugin update
        if ($options['action'] !== 'update' || $options['type'] !== 'plugin') {
            return;
        }

        // Check if our plugin is being updated
        $our_plugin = plugin_basename(__FILE__);

        // Handle single plugin update
        if (isset($options['plugin']) && $options['plugin'] === $our_plugin) {
            $this->flush_rewrite_on_update();
        }

        // Handle bulk plugin updates
        if (isset($options['plugins']) && is_array($options['plugins']) && in_array($our_plugin, $options['plugins'])) {
            $this->flush_rewrite_on_update();
        }
    }

    /**
     * Flush rewrite rules on plugin update
     */
    private function flush_rewrite_on_update() {
        // Re-register post types and taxonomies to ensure they're loaded
        if (class_exists('Roadmap_CPT')) {
            $cpt_instance = Roadmap_CPT::get_instance();
            $cpt_instance->register_post_types();
            $cpt_instance->register_taxonomies();
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        // Store version for comparison on next update
        update_option('roadmap_version', ROADMAP_VERSION);
    }
    
    private function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Votes table
        $votes_table = $wpdb->prefix . 'roadmap_votes';
        $sql_votes = "CREATE TABLE IF NOT EXISTS $votes_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned DEFAULT NULL,
            item_id bigint(20) unsigned NOT NULL,
            vote_value int(1) NOT NULL DEFAULT 1,
            guest_ip varchar(45) DEFAULT NULL,
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_item (user_id, item_id),
            UNIQUE KEY guest_item (guest_ip, item_id),
            KEY item_id (item_id),
            KEY guest_ip (guest_ip)
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