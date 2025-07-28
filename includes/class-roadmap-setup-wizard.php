<?php
/**
 * Setup Wizard
 *
 * @package Product_Roadmap
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Roadmap_Setup_Wizard
 * Handles the initial setup and configuration wizard
 */
class Roadmap_Setup_Wizard {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_setup_menu'), 101);
        add_action('admin_init', array($this, 'handle_setup_actions'), 5); // Handle early to avoid headers issue
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Handle start over before any output
        add_action('admin_init', array($this, 'handle_start_over'), 1);
    }
    
    /**
     * Handle start over action early before any output
     */
    public function handle_start_over() {
        // Only process on our setup wizard page
        if (!isset($_GET['page']) || $_GET['page'] !== 'roadmap-setup-wizard') {
            return;
        }
        
        // Handle start over action
        if (isset($_GET['start_over']) && $_GET['start_over'] == '1' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'roadmap_start_over')) {
            delete_option('roadmap_wizard_completed');
            wp_redirect(admin_url('admin.php?page=roadmap-setup-wizard'));
            exit;
        }
    }
    
    /**
     * Add setup menu
     */
    public function add_setup_menu() {
        // Add as submenu under Roadmap
        add_submenu_page(
            'roadmap-dashboard',
            __('Setup Wizard', 'roadmap'),
            __('Setup Wizard', 'roadmap'),
            'manage_options',
            'roadmap-setup-wizard',
            array($this, 'render_setup_page')
        );
    }
    
    /**
     * Render the setup page
     */
    public function render_setup_page() {
        $current_step = isset($_GET['step']) ? intval($_GET['step']) : 1;
        $wizard_completed = get_option('roadmap_wizard_completed');
        ?>
        <div class="wrap roadmap-setup-wrap">
            <style>
                .roadmap-setup-wrap {
                    max-width: 800px;
                    margin: 40px auto;
                }
                .roadmap-setup-header {
                    text-align: center;
                    margin-bottom: 40px;
                }
                .roadmap-setup-steps {
                    display: flex;
                    justify-content: center;
                    margin-bottom: 40px;
                    padding: 0;
                    list-style: none;
                }
                .roadmap-setup-steps li {
                    margin: 0 20px;
                    padding: 10px 20px;
                    background: #f0f0f1;
                    border-radius: 3px;
                    color: #50575e;
                }
                .roadmap-setup-steps li.active {
                    background: #2271b1;
                    color: #fff;
                }
                .roadmap-setup-steps li.completed {
                    background: #00a32a;
                    color: #fff;
                }
                .roadmap-setup-content {
                    background: #fff;
                    padding: 40px;
                    border: 1px solid #c3c4c7;
                    box-shadow: 0 1px 1px rgba(0,0,0,0.04);
                }
                .roadmap-setup-content h2 {
                    margin-top: 0;
                    font-size: 24px;
                    font-weight: 400;
                }
                .roadmap-setup-actions {
                    margin-top: 30px;
                    padding-top: 30px;
                    border-top: 1px solid #eee;
                }
                .roadmap-pro-tip {
                    background: #f0f6fc;
                    border-left: 4px solid #2271b1;
                    padding: 12px;
                    margin: 20px 0;
                }
            </style>
            
            <div class="roadmap-setup-header">
                <h1><?php _e('Welcome to Product Roadmap Setup', 'roadmap'); ?></h1>
                <?php if ($wizard_completed && $current_step == 1): ?>
                    <div class="notice notice-info" style="max-width: 600px; margin: 20px auto;">
                        <p>
                            <?php _e('You have already completed the setup wizard. You can review or update your settings below.', 'roadmap'); ?>
                            <?php 
                            $start_over_url = wp_nonce_url(
                                admin_url('admin.php?page=roadmap-setup-wizard&start_over=1'),
                                'roadmap_start_over'
                            );
                            ?>
                            <a href="<?php echo esc_url($start_over_url); ?>" class="button" 
                               onclick="return confirm('<?php _e('This will reset the wizard. Your existing roadmap items will not be affected. Continue?', 'roadmap'); ?>');">
                                <?php _e('Start Over', 'roadmap'); ?>
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            
            <ul class="roadmap-setup-steps">
                <li class="<?php echo $current_step == 1 ? 'active' : ($current_step > 1 ? 'completed' : ''); ?>">
                    <?php _e('Product Info', 'roadmap'); ?>
                </li>
                <li class="<?php echo $current_step == 2 ? 'active' : ($current_step > 2 ? 'completed' : ''); ?>">
                    <?php _e('Settings', 'roadmap'); ?>
                </li>
                <li class="<?php echo $current_step == 3 ? 'active' : ($current_step > 3 ? 'completed' : ''); ?>">
                    <?php _e('Import Demo', 'roadmap'); ?>
                </li>
                <li class="<?php echo $current_step == 4 ? 'active' : ''; ?>">
                    <?php _e('Finish', 'roadmap'); ?>
                </li>
            </ul>
            
            <div class="roadmap-setup-content">
                <?php
                switch ($current_step) {
                    case 1:
                        $this->render_step_product();
                        break;
                    case 2:
                        $this->render_step_settings();
                        break;
                    case 3:
                        $this->render_step_demo();
                        break;
                    case 4:
                        $this->render_step_finish();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }
    
    /**
     * Step 1: Product Configuration
     */
    private function render_step_product() {
        $product_name = get_option('roadmap_product_name', '');
        $item_singular = get_option('roadmap_item_singular_label', 'Feature');
        $item_plural = get_option('roadmap_item_plural_label', 'Features');
        ?>
        <h2><?php _e('Configure Your Product', 'roadmap'); ?></h2>
        <p><?php _e('Let\'s personalize your roadmap. In the free version, you can manage one product roadmap.', 'roadmap'); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field('roadmap_setup_step_1'); ?>
            <input type="hidden" name="roadmap_setup_step" value="1">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="product_name"><?php _e('Product Name', 'roadmap'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="product_name" name="product_name" 
                               value="<?php echo esc_attr($product_name); ?>" 
                               class="regular-text" required />
                        <p class="description">
                            <?php _e('The name of your product or project (e.g., "My App", "WordPress Plugin")', 'roadmap'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Item Labels', 'roadmap'); ?></th>
                    <td>
                        <p><?php _e('Customize how roadmap items are labeled:', 'roadmap'); ?></p>
                        <p>
                            <label><?php _e('Singular:', 'roadmap'); ?></label><br>
                            <input type="text" name="item_singular" 
                                   value="<?php echo esc_attr($item_singular); ?>" 
                                   class="regular-text" style="width: 200px;" />
                            <span class="description"><?php _e('(e.g., Feature, Task, Story)', 'roadmap'); ?></span>
                        </p>
                        <p>
                            <label><?php _e('Plural:', 'roadmap'); ?></label><br>
                            <input type="text" name="item_plural" 
                                   value="<?php echo esc_attr($item_plural); ?>" 
                                   class="regular-text" style="width: 200px;" />
                            <span class="description"><?php _e('(e.g., Features, Tasks, Stories)', 'roadmap'); ?></span>
                        </p>
                    </td>
                </tr>
            </table>
            
            <div class="roadmap-pro-tip">
                <strong><?php _e('Pro Tip:', 'roadmap'); ?></strong> 
                <?php _e('Need multiple products? The Pro version allows unlimited products with separate roadmaps for each.', 'roadmap'); ?>
            </div>
            
            <div class="roadmap-setup-actions">
                <input type="submit" class="button button-primary button-large" 
                       value="<?php _e('Save & Continue', 'roadmap'); ?>" />
            </div>
        </form>
        <?php
    }
    
    /**
     * Step 2: Basic Settings
     */
    private function render_step_settings() {
        $voting_enabled = get_option('roadmap_enable_voting', 'yes');
        $comments_enabled = get_option('roadmap_enable_comments', 'yes');
        $guest_voting = get_option('roadmap_allow_guest_voting', 'no');
        ?>
        <h2><?php _e('Basic Settings', 'roadmap'); ?></h2>
        <p><?php _e('Configure how users can interact with your roadmap.', 'roadmap'); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field('roadmap_setup_step_2'); ?>
            <input type="hidden" name="roadmap_setup_step" value="2">
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Voting', 'roadmap'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="voting_enabled" value="yes" 
                                   <?php checked($voting_enabled, 'yes'); ?> />
                            <?php _e('Allow users to vote on roadmap items', 'roadmap'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Guest Voting', 'roadmap'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="guest_voting" value="yes" 
                                   <?php checked($guest_voting, 'yes'); ?> />
                            <?php _e('Allow non-logged in users to vote', 'roadmap'); ?>
                        </label>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable Comments', 'roadmap'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="comments_enabled" value="yes" 
                                   <?php checked($comments_enabled, 'yes'); ?> />
                            <?php _e('Allow users to comment on roadmap items', 'roadmap'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <div class="roadmap-setup-actions">
                <a href="<?php echo admin_url('admin.php?page=roadmap-setup-wizard&step=1'); ?>" 
                   class="button button-large"><?php _e('← Back', 'roadmap'); ?></a>
                <input type="submit" class="button button-primary button-large" 
                       value="<?php _e('Save & Continue', 'roadmap'); ?>" />
            </div>
        </form>
        <?php
    }
    
    /**
     * Step 3: Demo Content
     */
    private function render_step_demo() {
        $product_name = get_option('roadmap_product_name', 'Product');
        $existing_page_id = get_option('roadmap_page_id');
        $has_existing_page = $existing_page_id && get_post($existing_page_id);
        ?>
        <h2><?php _e('Import Demo Content', 'roadmap'); ?></h2>
        <p><?php printf(__('Would you like to import demo content for "%s"? This helps you see how the roadmap works.', 'roadmap'), esc_html($product_name)); ?></p>
        
        <form method="post" action="">
            <?php wp_nonce_field('roadmap_setup_step_3'); ?>
            <input type="hidden" name="roadmap_setup_step" value="3">
            
            <div style="background: #f9f9f9; padding: 20px; margin: 20px 0;">
                <h3 style="margin-top: 0;"><?php _e('Demo content includes:', 'roadmap'); ?></h3>
                <ul style="list-style: disc; margin-left: 20px;">
                    <li><?php _e('10 sample roadmap items with different statuses', 'roadmap'); ?></li>
                    <li><?php _e('Various priority levels and categories', 'roadmap'); ?></li>
                    <li><?php _e('Example votes and comments', 'roadmap'); ?></li>
                </ul>
                <label>
                    <input type="checkbox" name="import_demo" value="yes" checked />
                    <strong><?php _e('Yes, import demo content', 'roadmap'); ?></strong>
                </label>
            </div>
            
            <?php if ($has_existing_page): ?>
                <div style="background: #f0f6fc; border-left: 4px solid #2271b1; padding: 12px; margin: 20px 0;">
                    <p style="margin: 0;">
                        <strong><?php _e('Note:', 'roadmap'); ?></strong> 
                        <?php _e('A roadmap page already exists. The setup will use the existing page.', 'roadmap'); ?>
                        <a href="<?php echo get_permalink($existing_page_id); ?>" target="_blank"><?php _e('View existing page', 'roadmap'); ?></a>
                    </p>
                </div>
            <?php else: ?>
                <div style="background: #f0f9ff; border: 1px solid #0073aa; padding: 12px; margin: 20px 0;">
                    <p style="margin: 0;">
                        <?php printf(__('After this step, we\'ll create a "%s Roadmap" page with the [roadmap] shortcode.', 'roadmap'), esc_html($product_name)); ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <div class="roadmap-setup-actions">
                <a href="<?php echo admin_url('admin.php?page=roadmap-setup-wizard&step=2'); ?>" 
                   class="button button-large"><?php _e('← Back', 'roadmap'); ?></a>
                <input type="submit" class="button button-primary button-large" 
                       value="<?php _e('Import & Continue', 'roadmap'); ?>" />
                <a href="<?php echo admin_url('admin.php?page=roadmap-setup-wizard&step=4'); ?>" 
                   class="button button-large"><?php _e('Skip', 'roadmap'); ?></a>
            </div>
        </form>
        <?php
    }
    
    /**
     * Step 4: Finish
     */
    private function render_step_finish() {
        $was_completed = get_option('roadmap_wizard_completed');
        update_option('roadmap_wizard_completed', true);
        $product_name = get_option('roadmap_product_name', 'Product');
        $roadmap_page_id = get_option('roadmap_page_id');
        ?>
        <h2><?php _e('🎉 Setup Complete!', 'roadmap'); ?></h2>
        <p><?php printf(__('Your %s roadmap is ready to use!', 'roadmap'), esc_html($product_name)); ?></p>
        
        <?php if ($roadmap_page_id && get_post($roadmap_page_id)): ?>
            <div style="background: #d4f4dd; border: 1px solid #00a32a; padding: 20px; margin: 20px 0; text-align: center;">
                <h3 style="margin-top: 0; color: #00a32a;"><?php _e('Your Roadmap Page is Ready!', 'roadmap'); ?></h3>
                <p><?php _e('We\'ve created a page with your roadmap. You can view it now:', 'roadmap'); ?></p>
                <p>
                    <a href="<?php echo get_permalink($roadmap_page_id); ?>" class="button button-primary button-hero" target="_blank">
                        <?php _e('View Your Roadmap', 'roadmap'); ?> →
                    </a>
                </p>
                <p style="margin-top: 10px;">
                    <a href="<?php echo admin_url('post.php?post=' . $roadmap_page_id . '&action=edit'); ?>">
                        <?php _e('Edit Page', 'roadmap'); ?>
                    </a>
                </p>
            </div>
        <?php else: ?>
            <div style="background: #f0f9ff; border: 1px solid #0073aa; padding: 20px; margin: 20px 0;">
                <h3 style="margin-top: 0;"><?php _e('Quick Start:', 'roadmap'); ?></h3>
                <p><?php _e('Add this shortcode to any page or post to display your roadmap:', 'roadmap'); ?></p>
                <p style="font-size: 18px; font-family: monospace; background: #fff; padding: 10px; border: 1px solid #ddd;">
                    [roadmap]
                </p>
            </div>
        <?php endif; ?>
        
        <h3><?php _e('What\'s Next?', 'roadmap'); ?></h3>
        <ul style="list-style: disc; margin-left: 20px; line-height: 2;">
            <li>
                <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>">
                    <?php _e('Create a new page and add the [roadmap] shortcode', 'roadmap'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo admin_url('post-new.php?post_type=roadmap_item'); ?>">
                    <?php _e('Add your first real roadmap item', 'roadmap'); ?>
                </a>
            </li>
            <li>
                <a href="<?php echo admin_url('edit.php?post_type=roadmap_item&page=roadmap-settings'); ?>">
                    <?php _e('Configure advanced settings', 'roadmap'); ?>
                </a>
            </li>
        </ul>
        
        <div class="roadmap-setup-actions">
            <a href="<?php echo admin_url('edit.php?post_type=roadmap_item'); ?>" 
               class="button button-primary button-large">
                <?php _e('Go to Roadmap Dashboard', 'roadmap'); ?>
            </a>
            <?php if ($was_completed): ?>
                <p style="margin-top: 20px; color: #666;">
                    <?php _e('You can access this setup wizard anytime from the Roadmap menu.', 'roadmap'); ?>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Handle setup actions
     */
    public function handle_setup_actions() {
        if (!isset($_POST['roadmap_setup_step'])) {
            return;
        }
        
        $step = intval($_POST['roadmap_setup_step']);
        
        switch ($step) {
            case 1:
                if (!wp_verify_nonce($_POST['_wpnonce'], 'roadmap_setup_step_1')) {
                    return;
                }
                
                // Save product info
                update_option('roadmap_product_name', sanitize_text_field($_POST['product_name']));
                update_option('roadmap_item_singular_label', sanitize_text_field($_POST['item_singular']));
                update_option('roadmap_item_plural_label', sanitize_text_field($_POST['item_plural']));
                
                wp_redirect(admin_url('admin.php?page=roadmap-setup-wizard&step=2'));
                exit;
                
            case 2:
                if (!wp_verify_nonce($_POST['_wpnonce'], 'roadmap_setup_step_2')) {
                    return;
                }
                
                // Save settings
                update_option('roadmap_enable_voting', isset($_POST['voting_enabled']) ? 'yes' : 'no');
                update_option('roadmap_allow_guest_voting', isset($_POST['guest_voting']) ? 'yes' : 'no');
                update_option('roadmap_enable_comments', isset($_POST['comments_enabled']) ? 'yes' : 'no');
                
                wp_redirect(admin_url('admin.php?page=roadmap-setup-wizard&step=3'));
                exit;
                
            case 3:
                if (!wp_verify_nonce($_POST['_wpnonce'], 'roadmap_setup_step_3')) {
                    return;
                }
                
                // Import demo content if requested
                if (isset($_POST['import_demo']) && $_POST['import_demo'] === 'yes') {
                    // Ensure CPT and taxonomies are loaded
                    Roadmap_CPT::get_instance();
                    
                    require_once ROADMAP_PLUGIN_PATH . 'includes/class-roadmap-demo-import.php';
                    $demo_import = new Roadmap_Demo_Import();
                    $demo_import->import_demo_content_direct();
                }
                
                // Always create a roadmap page
                $this->create_roadmap_page();
                
                wp_redirect(admin_url('admin.php?page=roadmap-setup-wizard&step=4'));
                exit;
        }
    }
    
    /**
     * Create roadmap page with shortcode
     */
    private function create_roadmap_page() {
        // Check if page already exists
        $existing_page_id = get_option('roadmap_page_id');
        if ($existing_page_id) {
            $existing_page = get_post($existing_page_id);
            // Verify the page still exists and is published
            if ($existing_page && $existing_page->post_status === 'publish') {
                return $existing_page_id;
            }
        }
        
        // Also check if a page with roadmap shortcode already exists
        $args = array(
            'post_type' => 'page',
            'post_status' => 'publish',
            's' => '[roadmap]',
            'posts_per_page' => 1
        );
        $existing_pages = get_posts($args);
        
        if (!empty($existing_pages)) {
            $existing_page_id = $existing_pages[0]->ID;
            update_option('roadmap_page_id', $existing_page_id);
            return $existing_page_id;
        }
        
        $product_name = get_option('roadmap_product_name', 'Product');
        
        // Create a new page with the roadmap shortcode
        $page_data = array(
            'post_title' => sprintf(__('%s Roadmap', 'roadmap'), $product_name),
            'post_content' => '[roadmap]',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => get_current_user_id(),
            'comment_status' => 'closed'
        );
        
        $page_id = wp_insert_post($page_data);
        
        if ($page_id && !is_wp_error($page_id)) {
            update_option('roadmap_page_id', $page_id);
            return $page_id;
        }
        
        return false;
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        // Check for submenu page hook
        if ($hook !== 'roadmap_page_roadmap-setup-wizard') {
            return;
        }
        
        // Add any additional styles if needed
        wp_enqueue_style('roadmap-admin', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-admin.css', array(), ROADMAP_VERSION);
    }
}

// Initialize the setup wizard
new Roadmap_Setup_Wizard();