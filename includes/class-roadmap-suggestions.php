<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Suggestions {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Suggestions functionality will be implemented here
    }
}