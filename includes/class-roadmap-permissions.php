<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Permissions {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'add_capabilities']);
        add_filter('user_has_cap', [$this, 'filter_capabilities'], 10, 4);
    }
    
    public function add_capabilities() {
        $roles = [
            'administrator' => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
                'roadmap_edit_items',
                'roadmap_delete_items',
                'roadmap_manage_settings',
                'roadmap_view_analytics',
            ],
            'editor' => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
                'roadmap_edit_items',
                'roadmap_delete_items',
            ],
            'author' => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
                'roadmap_edit_own_items',
            ],
            'contributor' => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
            ],
            'subscriber' => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
            ],
        ];
        
        foreach ($roles as $role_name => $caps) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }
    
    public function filter_capabilities($allcaps, $caps, $args, $user) {
        $global_settings = get_option('roadmap_global_settings', []);

        // Check if voting is enabled
        $voting_enabled = $global_settings['enable_voting'] ?? (get_option('roadmap_enable_voting', 'yes') === 'yes');
        if (in_array('roadmap_vote', $caps) && !$voting_enabled) {
            return $allcaps;
        }

        // Check if comments are enabled
        $comments_enabled = $global_settings['enable_comments'] ?? (get_option('roadmap_enable_comments', 'yes') === 'yes');
        if (in_array('roadmap_comment', $caps) && !$comments_enabled) {
            return $allcaps;
        }

        // Check if suggestions are enabled
        $suggestions_enabled = $global_settings['enable_suggestions'] ?? (get_option('roadmap_enable_suggestions', 'yes') === 'yes');
        if (in_array('roadmap_suggest', $caps) && !$suggestions_enabled) {
            return $allcaps;
        }

        return $allcaps;
    }
    
    public static function can_vote($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $global_settings = get_option('roadmap_global_settings', []);
        $voting_enabled = $global_settings['enable_voting'] ?? (get_option('roadmap_enable_voting', 'yes') === 'yes');

        return user_can($user_id, 'roadmap_vote') && $voting_enabled;
    }

    public static function can_comment($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $global_settings = get_option('roadmap_global_settings', []);
        $comments_enabled = $global_settings['enable_comments'] ?? (get_option('roadmap_enable_comments', 'yes') === 'yes');

        return user_can($user_id, 'roadmap_comment') && $comments_enabled;
    }

    public static function can_suggest($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $global_settings = get_option('roadmap_global_settings', []);
        $suggestions_enabled = $global_settings['enable_suggestions'] ?? (get_option('roadmap_enable_suggestions', 'yes') === 'yes');

        return user_can($user_id, 'roadmap_suggest') && $suggestions_enabled;
    }
    
    public static function can_edit_items($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'roadmap_edit_items');
    }
    
    public static function can_delete_items($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'roadmap_delete_items');
    }
    
    public static function can_manage_settings($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'roadmap_manage_settings');
    }
    
    public static function can_view_analytics($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        return user_can($user_id, 'roadmap_view_analytics');
    }
}