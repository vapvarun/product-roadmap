<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Notifications {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('roadmap_vote_submitted', [$this, 'handle_vote_notification'], 10, 3);
        add_action('roadmap_comment_submitted', [$this, 'handle_comment_notification'], 10, 3);
        add_action('roadmap_suggestion_submitted', [$this, 'handle_suggestion_notification'], 10, 2);
        add_action('transition_post_status', [$this, 'handle_status_change_notification'], 10, 3);
    }
    
    public function handle_vote_notification($item_id, $user_id, $vote_value) {
        if (!$this->should_send_notification('vote')) {
            return;
        }
        
        $item = get_post($item_id);
        $user = get_user_by('id', $user_id);
        
        if (!$item || !$user) {
            return;
        }
        
        $subject = sprintf(
            /* translators: %1$s: Site name, %2$s: Item title */
            __('[%1$s] New vote on: %2$s', 'product-roadmap'),
            get_bloginfo('name'),
            $item->post_title
        );
        
        $message = sprintf(
            /* translators: %1$s: Item title, %2$s: User name, %3$s: Vote value, %4$s: Item URL */
            __("A new vote has been submitted on your roadmap:\n\nItem: %1\$s\nUser: %2\$s\nVote: %3\$s\n\nView item: %4\$s", 'product-roadmap'),
            $item->post_title,
            $user->display_name,
            $vote_value > 0 ? '+1' : '-1',
            get_permalink($item_id)
        );
        
        $this->send_notification($subject, $message);
    }
    
    public function handle_comment_notification($comment_id, $item_id, $user_id) {
        if (!$this->should_send_notification('comment')) {
            return;
        }
        
        $comment = get_comment($comment_id);
        $item = get_post($item_id);
        $user = get_user_by('id', $user_id);
        
        if (!$comment || !$item || !$user) {
            return;
        }
        
        $subject = sprintf(
            /* translators: %1$s: Site name, %2$s: Item title */
            __('[%1$s] New comment on: %2$s', 'product-roadmap'),
            get_bloginfo('name'),
            $item->post_title
        );
        
        $message = sprintf(
            /* translators: %1$s: Item title, %2$s: User name, %3$s: Comment excerpt, %4$s: Item URL */
            __("A new comment has been posted on your roadmap:\n\nItem: %1\$s\nUser: %2\$s\nComment: %3\$s\n\nView item: %4\$s", 'product-roadmap'),
            $item->post_title,
            $user->display_name,
            wp_trim_words($comment->comment_content, 50),
            get_permalink($item_id)
        );
        
        $this->send_notification($subject, $message);
    }
    
    public function handle_suggestion_notification($post_id, $user_id) {
        if (!$this->should_send_notification('suggestion')) {
            return;
        }
        
        $post = get_post($post_id);
        $user = get_user_by('id', $user_id);
        
        if (!$post || !$user) {
            return;
        }
        
        $subject = sprintf(
            /* translators: %1$s: Site name, %2$s: Suggestion title */
            __('[%1$s] New feature suggestion: %2$s', 'product-roadmap'),
            get_bloginfo('name'),
            $post->post_title
        );
        
        $message = sprintf(
            /* translators: %1$s: Suggestion title, %2$s: User name, %3$s: Description excerpt, %4$s: Admin URL */
            __("A new feature has been suggested:\n\nTitle: %1\$s\nUser: %2\$s\nDescription: %3\$s\n\nReview suggestion: %4\$s", 'product-roadmap'),
            $post->post_title,
            $user->display_name,
            wp_trim_words($post->post_content, 100),
            admin_url('post.php?post=' . $post_id . '&action=edit')
        );
        
        $this->send_notification($subject, $message);
    }
    
    public function handle_status_change_notification($new_status, $old_status, $post) {
        if ($post->post_type !== 'roadmap_item' || $new_status === $old_status) {
            return;
        }
        
        // Only notify on publish status changes
        if ($new_status !== 'publish' && $old_status !== 'publish') {
            return;
        }
        
        // Get users who voted or commented on this item
        $users_to_notify = $this->get_users_to_notify($post->ID);
        
        if (empty($users_to_notify)) {
            return;
        }
        
        $status_term = false;
        $statuses = wp_get_post_terms($post->ID, 'roadmap_status');
        if (!empty($statuses)) {
            $status_term = $statuses[0];
        }
        
        $subject = sprintf(
            /* translators: %1$s: Site name, %2$s: Item title */
            __('[%1$s] Status update: %2$s', 'product-roadmap'),
            get_bloginfo('name'),
            $post->post_title
        );
        
        $message = sprintf(
            /* translators: %1$s: Item title, %2$s: New status, %3$s: Item URL */
            __("A roadmap item you're following has been updated:\n\nItem: %1\$s\nNew Status: %2\$s\n\nView item: %3\$s", 'product-roadmap'),
            $post->post_title,
            $status_term ? $status_term->name : __('Updated', 'product-roadmap'),
            get_permalink($post->ID)
        );
        
        // Send to each user
        foreach ($users_to_notify as $user_email) {
            wp_mail($user_email, $subject, $message);
        }
    }
    
    private function get_users_to_notify($item_id) {
        global $wpdb;
        
        $users = [];
        
        // Get users who voted
        $cache_key = 'roadmap_voted_users_' . $item_id;
        $voted_users = wp_cache_get($cache_key, 'roadmap');
        
        if (false === $voted_users) {
            $votes_table = $wpdb->prefix . 'roadmap_votes';
            // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $voted_users = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT u.user_email 
                FROM {$votes_table} v 
                JOIN {$wpdb->users} u ON v.user_id = u.ID 
                WHERE v.item_id = %d",
                $item_id
            )); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query with caching
            // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            wp_cache_set($cache_key, $voted_users, 'roadmap', 3600);
        }
        
        if ($voted_users) {
            $users = array_merge($users, $voted_users);
        }
        
        // Get users who commented
        $cache_key_comments = 'roadmap_comment_users_' . $item_id;
        $comment_users = wp_cache_get($cache_key_comments, 'roadmap');
        
        if (false === $comment_users) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Core table query with caching
            $comment_users = $wpdb->get_col($wpdb->prepare(
                "SELECT DISTINCT u.user_email 
                FROM {$wpdb->comments} c 
                JOIN {$wpdb->users} u ON c.user_id = u.ID 
                WHERE c.comment_post_ID = %d 
                AND c.comment_approved = '1'",
                $item_id
            ));
            wp_cache_set($cache_key_comments, $comment_users, 'roadmap', 3600);
        }
        
        if ($comment_users) {
            $users = array_merge($users, $comment_users);
        }
        
        return array_unique($users);
    }
    
    private function should_send_notification($type) {
        if (get_option('roadmap_enable_notifications', 'yes') !== 'yes') {
            return false;
        }
        
        switch ($type) {
            case 'vote':
                return get_option('roadmap_notify_on_vote', 'yes') === 'yes';
            case 'comment':
                return get_option('roadmap_notify_on_comment', 'yes') === 'yes';
            case 'suggestion':
                return get_option('roadmap_notify_on_suggestion', 'yes') === 'yes';
            default:
                return false;
        }
    }
    
    private function send_notification($subject, $message) {
        $to = get_option('roadmap_notification_email', get_option('admin_email'));
        
        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];
        
        wp_mail($to, $subject, $message, $headers);
    }
}