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
        add_action( 'init', [ $this, 'maybe_add_capabilities' ] );
        add_filter( 'user_has_cap', [ $this, 'filter_capabilities' ], 10, 4 );
        add_filter( 'comments_open', [ __CLASS__, 'filter_comments_open' ], 10, 2 );
    }

    /**
     * Check if capabilities need to be added (on activation or version change)
     *
     * This prevents database writes on every page load.
     */
    public function maybe_add_capabilities() {
        $caps_version = get_option( 'roadmap_capabilities_version', '0' );

        // Only add capabilities on first install or version upgrade
        if ( version_compare( $caps_version, ROADMAP_VERSION, '<' ) ) {
            $this->add_capabilities();
            update_option( 'roadmap_capabilities_version', ROADMAP_VERSION, false );
        }
    }

    /**
     * Add roadmap capabilities to roles
     *
     * Called during activation or version upgrade only.
     */
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
            'editor'        => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
                'roadmap_edit_items',
                'roadmap_delete_items',
            ],
            'author'        => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
                'roadmap_edit_own_items',
            ],
            'contributor'   => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
            ],
            'subscriber'    => [
                'roadmap_vote',
                'roadmap_comment',
                'roadmap_suggest',
            ],
        ];

        foreach ( $roles as $role_name => $caps ) {
            $role = get_role( $role_name );
            if ( $role ) {
                foreach ( $caps as $cap ) {
                    $role->add_cap( $cap );
                }
            }
        }
    }
    
    public function filter_capabilities($allcaps, $caps, $args, $user) {
        $global_settings = Roadmap_Settings::get();

        // Revoke the feature cap when the corresponding feature is disabled, so
        // current_user_can('roadmap_vote'|'roadmap_comment'|'roadmap_suggest')
        // reflects the setting for third-party callers too.
        $voting_enabled = (bool) $global_settings['enable_voting'];
        if (in_array('roadmap_vote', $caps, true) && !$voting_enabled) {
            $allcaps['roadmap_vote'] = false;
        }

        $comments_enabled = (bool) $global_settings['enable_comments'];
        if (in_array('roadmap_comment', $caps, true) && !$comments_enabled) {
            $allcaps['roadmap_comment'] = false;
        }

        $suggestions_enabled = (bool) $global_settings['enable_suggestions'];
        if (in_array('roadmap_suggest', $caps, true) && !$suggestions_enabled) {
            $allcaps['roadmap_suggest'] = false;
        }

        return $allcaps;
    }
    
    public static function can_vote($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $global_settings = Roadmap_Settings::get();
        $voting_enabled = (bool) $global_settings['enable_voting'];

        return user_can($user_id, 'roadmap_vote') && $voting_enabled;
    }

    public static function can_comment($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $global_settings = Roadmap_Settings::get();
        $comments_enabled = (bool) $global_settings['enable_comments'];

        return user_can($user_id, 'roadmap_comment') && $comments_enabled;
    }

    public static function can_suggest($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $global_settings = Roadmap_Settings::get();
        $suggestions_enabled = (bool) $global_settings['enable_suggestions'];

        return user_can($user_id, 'roadmap_suggest') && $suggestions_enabled;
    }
    
    /**
     * Whether a user may edit one specific roadmap item.
     *
     * The blanket `edit_posts` capability is NOT sufficient: it is held by
     * Contributors and says nothing about *this* post. Every REST route that
     * mutates a post by id must route through here so the post-type guard and
     * the per-object capability check cannot be forgotten at a call site.
     *
     * @param int      $post_id Post to check.
     * @param int|null $user_id Defaults to the current user.
     * @return bool
     */
    /**
     * Close WordPress comments on roadmap items when the feature is off.
     *
     * The board and the REST route were both gated correctly, but the single
     * item template asks core via comments_open(), which knows nothing about
     * this plugin's setting - so the full reply form still rendered and a
     * posted comment was accepted and auto-approved. Filtering comments_open
     * fixes the template, the REST route and any theme at once, which is why
     * it belongs here rather than in the template.
     *
     * @param bool $open    Whether comments are open.
     * @param int  $post_id Post being checked.
     * @return bool
     */
    public static function filter_comments_open($open, $post_id) {
        if ('roadmap_item' !== get_post_type($post_id)) {
            return $open;
        }

        $global_settings  = Roadmap_Settings::get();
        $comments_enabled = (bool) $global_settings['enable_comments'];

        return $comments_enabled ? $open : false;
    }

    public static function can_edit_item($post_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $post = get_post($post_id);

        // Only ever act on our own post type. Without this the route regex
        // /items/(?P<id>\d+) matches every post, page and product on the site.
        if (!$post || 'roadmap_item' !== $post->post_type) {
            return false;
        }

        // WordPress decides ownership and post-level locking; we only add our
        // own capability on top of it.
        if (!user_can($user_id, 'edit_post', $post_id)) {
            return false;
        }

        return user_can($user_id, 'roadmap_edit_items')
            || user_can($user_id, 'roadmap_edit_own_items');
    }

    /**
     * Whether a user may delete one specific roadmap item.
     *
     * @param int      $post_id Post to check.
     * @param int|null $user_id Defaults to the current user.
     * @return bool
     */
    public static function can_delete_item($post_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }

        $post = get_post($post_id);

        if (!$post || 'roadmap_item' !== $post->post_type) {
            return false;
        }

        if (!user_can($user_id, 'delete_post', $post_id)) {
            return false;
        }

        return user_can($user_id, 'roadmap_delete_items');
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