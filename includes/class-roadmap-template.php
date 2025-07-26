<?php
/**
 * Template handler for Roadmap plugin
 */

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Template {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_filter('single_template', [$this, 'load_single_template']);
        add_filter('archive_template', [$this, 'load_archive_template']);
    }
    
    public function load_single_template($template) {
        global $post;
        
        if ($post->post_type === 'roadmap_item') {
            $plugin_template = ROADMAP_PLUGIN_PATH . 'templates/single-roadmap_item.php';
            
            // Check if template exists in theme first
            $theme_template = locate_template(['single-roadmap_item.php']);
            
            if ($theme_template) {
                return $theme_template;
            } elseif (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
    
    public function load_archive_template($template) {
        if (is_tax('roadmap_product')) {
            $plugin_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap_product.php';
            
            // Check if template exists in theme first
            $theme_template = locate_template(['archive-roadmap_product.php', 'taxonomy-roadmap_product.php']);
            
            if ($theme_template) {
                return $theme_template;
            } elseif (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }
        
        return $template;
    }
}