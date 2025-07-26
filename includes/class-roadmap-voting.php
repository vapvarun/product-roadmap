<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Voting {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('wp_ajax_roadmap_vote', [$this, 'handle_vote']);
        add_action('wp_ajax_nopriv_roadmap_vote', [$this, 'handle_vote']);
    }
    
    public function handle_vote() {
        // Voting logic will be implemented here
        wp_die();
    }
}