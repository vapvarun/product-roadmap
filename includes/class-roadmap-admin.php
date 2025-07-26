<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Admin {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        global $post;
        
        // Only load on roadmap item edit screens
        if (($hook === 'post.php' || $hook === 'post-new.php') && 
            isset($post) && $post->post_type === 'roadmap_item') {
            
            // Enqueue meta box styles
            wp_enqueue_style(
                'roadmap-meta-box',
                ROADMAP_PLUGIN_URL . 'assets/css/meta-box.css',
                [],
                ROADMAP_VERSION
            );
            
            // Enqueue jQuery for range slider
            wp_enqueue_script('jquery');
        }
    }
}