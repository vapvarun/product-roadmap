<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Comments {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        // Comments functionality will be implemented here
    }
}