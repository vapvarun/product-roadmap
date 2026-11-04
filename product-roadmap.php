<?php
/**
 * Plugin Name: Product Roadmap
 * Plugin URI: https://wbcomdesigns.com/plugins/product-roadmap/
 * Description: Create public product roadmaps to share your vision, collect user feedback, and build products your customers actually want.
 * Version: 1.8.1
 * Author: wbcomdesigns
 * Author URI: https://wbcomdesigns.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: product-roadmap
 * Domain Path: /languages
 * Requires at least: 6.5
 * Requires PHP: 8.1
 */

if (!defined('ABSPATH')) {
    exit;
}

define('ROADMAP_VERSION', '1.8.1');
define('ROADMAP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ROADMAP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ROADMAP_PLUGIN_BASENAME', plugin_basename(__FILE__));
// The Product Roadmap Pro release paired with this Free. An older Pro keeps
// running, but the admin is told to update it so both stay in step. Pro keeps
// its own minimum Free version and pauses itself on an older Free.
define( 'ROADMAP_MIN_PRO_VERSION', '1.8.0' );

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
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-template.php';
        require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-changelog.php';
    }
    
    private function define_hooks() {
        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);

        add_action('init', [$this, 'init']);
        // Schema repair must NOT be admin-only: guest voting is a frontend
        // feature, and a site whose owner rarely opens wp-admin would never
        // pick up the votes-table columns it depends on. Version-gated, so on
        // every other request this is a single get_option().
        add_action('init', [$this, 'check_version_update'], 5);
        add_action('upgrader_process_complete', [$this, 'handle_plugin_update'], 10, 2);
        add_action( 'admin_notices', array( $this, 'maybe_outdated_pro_notice' ) );
        add_action( 'network_admin_notices', array( $this, 'maybe_outdated_pro_notice' ) );
        
        // Initialize components
        Roadmap_CPT::get_instance();
        Roadmap_API::get_instance();
        Roadmap_Shortcode::get_instance();
        Roadmap_Settings::get_instance();
        Roadmap_Permissions::get_instance();
        Roadmap_Notifications::get_instance();
        Roadmap_Admin::get_instance();
        Roadmap_Template::get_instance();
        Roadmap_Changelog::get_instance();

        // Pro addon hooks
        do_action('roadmap_plugin_loaded');
    }
    
    /**
     * Admin notice when the installed Pro is older than the one paired with this Free.
     */
    public function maybe_outdated_pro_notice() {
        if ( ! defined( 'ROADMAP_PRO_VERSION' ) || ! current_user_can( 'update_plugins' )
            || version_compare( ROADMAP_PRO_VERSION, ROADMAP_MIN_PRO_VERSION, '>=' ) ) {
            return;
        }
        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            wp_kses(
                sprintf(
                    /* translators: 1: Free version, 2: paired Pro version, 3: installed Pro version, 4: Plugins screen URL */
                    __( '<strong>Product Roadmap %1$s</strong> is paired with <strong>Product Roadmap Pro %2$s</strong>, and this site has Pro %3$s. <a href="%4$s">Update Product Roadmap Pro</a> so both stay in step.', 'product-roadmap' ),
                    esc_html( ROADMAP_VERSION ),
                    esc_html( ROADMAP_MIN_PRO_VERSION ),
                    esc_html( ROADMAP_PRO_VERSION ),
                    esc_url( self_admin_url( 'plugins.php' ) )
                ),
                array(
                    'strong' => array(),
                    'a'      => array( 'href' => array() ),
                )
            )
        );
    }

    public function init() {
        // Load translations.
        load_plugin_textdomain( 'product-roadmap', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

        // Check if pro addon is active
        if (!defined('ROADMAP_PRO_ACTIVE')) {
            define('ROADMAP_PRO_ACTIVE', false);
        }

        // Colour tokens + shared status/priority chips. Every frontend stylesheet
        // (Free and Pro) lists this handle as a dependency; see the file header.
        $tokens = ROADMAP_PLUGIN_PATH . 'assets/css/roadmap-tokens.css';
        wp_register_style( 'roadmap-tokens', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-tokens.css', array(), file_exists( $tokens ) ? (string) filemtime( $tokens ) : ROADMAP_VERSION );

        // Admin confirmations and notices without alert()/confirm() (Free and Pro).
        wp_register_script( 'roadmap-admin-feedback', ROADMAP_PLUGIN_URL . 'assets/js/roadmap-admin-feedback.js', array(), ROADMAP_VERSION, true );

        // Run migration check on admin init
        if (is_admin()) {
            // The product->taxonomy data migration is expensive, so it stays on
            // admin_init behind its own lock.
            add_action('admin_init', [$this, 'check_migration']);
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

            // Run table migrations so existing installs pick up new columns/indexes.
            // create_tables() runs dbDelta for columns and then adds the votes
            // indexes via upgrade_votes_indexes().
            $this->create_tables();

            // Recompute the cached vote counts. They used to store
            // SUM(vote_value), a net score, while everything that reads them
            // presents "Votes" as a number of voters.
            $this->recount_votes();

            // Move the pre-1.8 standalone settings into roadmap_global_settings.
            Roadmap_Settings::migrate_legacy_options();

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
        $sql_votes = "CREATE TABLE $votes_table (
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
            KEY guest_ip (guest_ip),
            KEY created_at (created_at),
            KEY item_created (item_id, created_at)
        ) $charset_collate;";
        
        // NOTE: the statement above must NOT say "CREATE TABLE IF NOT EXISTS".
        // dbDelta parses the table name with preg_match('|CREATE TABLE ([^ ]*)|'),
        // so "IF" is captured as the table name, dbDelta diffs a table called
        // "IF", finds nothing to do, and every column added here after 1.0 is
        // silently skipped on existing installs. dbDelta is already idempotent;
        // the guard bought nothing and cost every migration since.
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_votes);

        // dbDelta adds columns reliably but is unreliable about indexes on an
        // existing table, so those are added explicitly and idempotently.
        $this->upgrade_votes_indexes();
    }

    /**
     * Ensure the votes table has the created_at / item_created indexes.
     *
     * dbDelta does not reliably add indexes to an existing table, so add them
     * explicitly and idempotently. Speeds up every date-window vote query
     * (analytics charts, digests) at scale.
     */
    /**
     * Rebuild the cached vote counts from the votes table.
     *
     * Runs once per version change. On a board that has ever received a
     * downvote the displayed numbers go UP, because the stored value was a net
     * score and is now a count of voters - that change is called out in the
     * changelog.
     *
     * Done in one grouped query rather than per item so it stays cheap on a
     * large board.
     *
     * @return void
     */
    private function recount_votes() {
        global $wpdb;

        $table = $wpdb->prefix . 'roadmap_votes';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $counts = $wpdb->get_results(
            "SELECT item_id, COUNT(*) AS total FROM {$table} WHERE vote_value > 0 GROUP BY item_id"
        );

        foreach ( $counts as $row ) {
            update_post_meta( (int) $row->item_id, '_roadmap_votes', (int) $row->total );
        }
    }

    private function upgrade_votes_indexes() {
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
            return;
        }

        $indexes = array(
            'created_at'   => "ALTER TABLE `{$table}` ADD KEY `created_at` (`created_at`)",
            'item_created' => "ALTER TABLE `{$table}` ADD KEY `item_created` (`item_id`, `created_at`)",
        );

        foreach ( $indexes as $name => $alter_sql ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
            $exists = $wpdb->get_var( $wpdb->prepare( "SHOW INDEX FROM `{$table}` WHERE Key_name = %s", $name ) );
            if ( ! $exists ) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
                $wpdb->query( $alter_sql );
            }
        }
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
            'roadmap_notify_on_status_change' => 'yes',
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