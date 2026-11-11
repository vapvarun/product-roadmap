<?php

if (!defined('ABSPATH')) {
    exit;
}

class Roadmap_Notifications {

    /**
     * Recipients per queued email batch.
     *
     * Keeps each cron run short enough to finish well inside a request, so a
     * large send makes progress in steps instead of timing out as one job.
     */
    const EMAIL_BATCH_SIZE = 50;

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
        add_action('set_object_terms', [$this, 'handle_roadmap_status_change'], 10, 6);
        add_action('init', [$this, 'handle_unsubscribe']);
        // Delivery of queued status-change emails.
        add_action('roadmap_send_status_emails', [$this, 'send_status_email_batch'], 10, 3);
    }

    /**
     * Build a per-user unsubscribe URL. The token is a keyed hash of the user
     * ID (uses the site's AUTH salts), so no token needs to be stored.
     *
     * @param int $user_id User ID.
     * @return string
     */
    private function get_unsubscribe_url($user_id) {
        return add_query_arg(
            array(
                'roadmap_unsubscribe' => $user_id,
                'token'               => wp_hash('roadmap_unsub_' . $user_id),
            ),
            home_url('/')
        );
    }

    /**
     * Process an unsubscribe link click: verify the token, then set the
     * per-user opt-out meta that get_users_to_notify() honours.
     */
    public function handle_unsubscribe() {
        if (!isset($_GET['roadmap_unsubscribe'], $_GET['token'])) {
            return;
        }

        $user_id = absint($_GET['roadmap_unsubscribe']);
        $token   = sanitize_text_field(wp_unslash($_GET['token']));

        if (!$user_id || !hash_equals(wp_hash('roadmap_unsub_' . $user_id), $token)) {
            wp_die(esc_html__('This unsubscribe link is invalid or has expired.', 'product-roadmap'));
        }

        update_user_meta($user_id, 'roadmap_notifications_optout', '1');
        wp_die(
            esc_html__('You have been unsubscribed from roadmap update emails.', 'product-roadmap'),
            esc_html__('Unsubscribed', 'product-roadmap'),
            array('response' => 200)
        );
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
    
    /**
     * Queue status-change emails for background delivery, in batches.
     *
     * @param array<int,string> $recipients user_id => email.
     * @param string            $subject    Email subject.
     * @param string            $message    Email body, before the unsubscribe line.
     * @return void
     */
    private function queue_status_emails($recipients, $subject, $message) {
        if (empty($recipients)) {
            return;
        }

        foreach (array_chunk($recipients, self::EMAIL_BATCH_SIZE, true) as $index => $batch) {
            wp_schedule_single_event(
                time() + ($index * MINUTE_IN_SECONDS),
                'roadmap_send_status_emails',
                [$batch, $subject, $message]
            );
        }
    }

    /**
     * Deliver one queued batch of status-change emails.
     *
     * @param array<int,string> $recipients user_id => email.
     * @param string            $subject    Email subject.
     * @param string            $message    Email body.
     * @return void
     */
    public function send_status_email_batch($recipients, $subject, $message) {
        foreach ((array) $recipients as $uid => $user_email) {
            $body = $message . "\n\n---\n" . sprintf(
                /* translators: %s: unsubscribe URL */
                __('You are receiving this because you voted on or commented on this item. Unsubscribe: %s', 'product-roadmap'),
                $this->get_unsubscribe_url((int) $uid)
            );
            wp_mail($user_email, $subject, $body);
        }
    }

    public function handle_roadmap_status_change($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) {
        // Only process roadmap_status taxonomy changes
        if ('roadmap_status' !== $taxonomy) {
            return;
        }

        // Only for roadmap_item post type
        $post = get_post($object_id);
        if (!$post || 'roadmap_item' !== $post->post_type) {
            return;
        }

        if (!$this->should_send_notification('status_change')) {
            return;
        }

        // Same status as before: nothing changed, nobody is emailed. Compare the term
        // taxonomy IDs, not the names, because $terms may be IDs, slugs or names.
        $old_ids = array_map('intval', (array) $old_tt_ids);
        $new_ids = array_map('intval', (array) $tt_ids);
        sort($old_ids);
        sort($new_ids);
        if ($old_ids === $new_ids) {
            return;
        }

        // Get old status name from old term taxonomy IDs
        $old_status_name = '';
        if (!empty($old_tt_ids)) {
            $old_term = get_term_by('term_taxonomy_id', $old_tt_ids[0]);
            if ($old_term) {
                $old_status_name = $old_term->name;
            }
        }

        // Get new status name from the assigned term taxonomy IDs
        $new_status_name = '';
        if (!empty($tt_ids)) {
            $new_term = get_term_by('term_taxonomy_id', (int) reset($tt_ids));
            if ($new_term && !is_wp_error($new_term)) {
                $new_status_name = $new_term->name;
            }
        }

        // Get users who voted or commented on this item
        $users_to_notify = $this->get_users_to_notify($object_id);

        if (empty($users_to_notify)) {
            return;
        }

        $subject = sprintf(
            /* translators: %1$s: Site name, %2$s: Item title */
            __('[%1$s] Status update: %2$s', 'product-roadmap'),
            get_bloginfo('name'),
            $post->post_title
        );

        $message = sprintf(
            /* translators: %1$s: Item title, %2$s: Old status, %3$s: New status, %4$s: Item URL */
            __("A roadmap item you're following has changed status:\n\nItem: %1\$s\nOld Status: %2\$s\nNew Status: %3\$s\n\nView item: %4\$s", 'product-roadmap'),
            $post->post_title,
            $old_status_name ? $old_status_name : __('None', 'product-roadmap'),
            $new_status_name ? $new_status_name : __('None', 'product-roadmap'),
            get_permalink($object_id)
        );

        /**
         * Fires when a roadmap item's status taxonomy term changes.
         * Fired BEFORE emails so pro addon can set template context.
         *
         * @param int    $object_id       The roadmap item post ID.
         * @param string $new_status_name The new status term name.
         * @param string $old_status_name The old status term name.
         */
        do_action('roadmap_status_changed', $object_id, $new_status_name, $old_status_name);

        // Queued, not sent inline - see queue_status_emails(). This hook fires
        // from wp_set_object_terms(), which runs inside the admin's save and
        // inside the REST suggest path, so a popular item used to block both.
        $this->queue_status_emails($users_to_notify, $subject, $message);
    }

    /**
     * Recipients for a status-change email: users who voted or commented on the
     * item, minus anyone who opted out. Returns a map of user_id => email so the
     * caller can build a per-user unsubscribe link.
     *
     * @param int $item_id Roadmap item ID.
     * @return array<int,string>
     */
    private function get_users_to_notify($item_id) {
        global $wpdb;

        $users = []; // user_id => email

        // Users who voted.
        $votes_table = $wpdb->prefix . 'roadmap_votes';
        // phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $voted = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT u.ID, u.user_email
            FROM {$votes_table} v
            // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared -- the fragment is a constant from Roadmap_API::active_vote_sql() or absint'd ids, never user input.
            JOIN {$wpdb->users} u ON v.user_id = u.ID
            WHERE v.item_id = %d AND " . Roadmap_API::active_vote_sql('v.'),
            $item_id
        )); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table query
            // phpcs:enable WordPress.DB.PreparedSQL.NotPrepared
        // phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        foreach ((array) $voted as $u) {
            $users[(int) $u->ID] = $u->user_email;
        }

        // Users who commented.
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Core table query
        $commented = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT u.ID, u.user_email
            FROM {$wpdb->comments} c
            JOIN {$wpdb->users} u ON c.user_id = u.ID
            WHERE c.comment_post_ID = %d AND c.comment_approved = '1'",
            $item_id
        ));
        foreach ((array) $commented as $u) {
            $users[(int) $u->ID] = $u->user_email;
        }

        // Drop anyone who unsubscribed from roadmap emails.
        if (!empty($users)) {
            $opted_out = get_users(array(
                'include'    => array_keys($users),
                'meta_key'   => 'roadmap_notifications_optout',
                'meta_value' => '1',
                'fields'     => 'ID',
            ));
            foreach ($opted_out as $oid) {
                unset($users[(int) $oid]);
            }
        }

        return $users;
    }
    
    private function should_send_notification($type) {
        // Read from the roadmap_global_settings array that the settings UI
        // actually writes (sanitize_global_settings stores each toggle as a
        // bool). The old standalone roadmap_notify_on_* options were never
        // written, so the per-type toggles had no effect - disabling a
        // notification in settings did nothing. Defaults mirror the settings.
        $settings = Roadmap_Settings::get();
        if (empty($settings['enable_notifications'])) {
            return false;
        }

        // status_change has no UI toggle; it is on whenever notifications are on
        // (this is how voters/commenters get notified of a status change).
        if ('status_change' === $type) {
            return true;
        }

        $key = 'notify_on_' . $type;
        return array_key_exists($key, $settings) && ! empty($settings[$key]);
    }
    
    private function send_notification($subject, $message) {
        $notification_emails = implode("\n", Roadmap_Settings::notification_recipients());

        // Parse multiple emails (one per line)
        $emails = array_map('trim', explode("\n", $notification_emails));
        $emails = array_filter($emails); // Remove empty lines
        $emails = array_map('sanitize_email', $emails);
        $emails = array_filter($emails); // Remove invalid emails

        // Fallback to admin email if no valid emails
        if (empty($emails)) {
            $emails = [get_option('admin_email')];
        }

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];

        // Send to all recipients
        foreach ($emails as $to) {
            wp_mail($to, $subject, $message, $headers);
        }
    }
}