<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Roadmap_API {

	private static $instance = null;
	private $namespace       = 'roadmap/v1';

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		// Get roadmap items
		register_rest_route(
			$this->namespace,
			'/items',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'product'  => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'description'       => 'Product slug to filter roadmap items',
					),
					'status'   => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'category' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'page'     => array(
						'default'           => 1,
						'sanitize_callback' => 'absint',
					),
					'per_page' => array(
						'default'           => 100,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		// Vote on item
		register_rest_route(
			$this->namespace,
			'/vote',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'submit_vote' ),
				'permission_callback' => array( $this, 'can_vote' ),
				'args'                => array(
					'item_id'    => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'vote_value' => array(
						'default'           => 1,
						'sanitize_callback' => function ( $value ) {
							return intval( $value );
						},
						'validate_callback' => function ( $value ) {
							$int_val = intval( $value );
							return in_array( $int_val, array( -1, 0, 1 ), true );
						},
					),
				),
			)
		);

		// Submit comment
		register_rest_route(
			$this->namespace,
			'/comment',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'submit_comment' ),
				'permission_callback' => array( $this, 'can_comment' ),
				'args'                => array(
					'item_id' => array(
						'required'          => true,
						'sanitize_callback' => 'absint',
					),
					'content' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
				),
			)
		);

		// Suggest new item
		register_rest_route(
			$this->namespace,
			'/suggest',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'submit_suggestion' ),
				'permission_callback' => array( $this, 'can_suggest' ),
				'args'                => array(
					'title'       => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'description' => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'category'    => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Get statistics
		register_rest_route(
			$this->namespace,
			'/stats',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_stats' ),
				'permission_callback' => '__return_true',
			)
		);

		// Frontend management endpoints
		register_rest_route(
			$this->namespace,
			'/items/(?P<id>\d+)',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => array( $this, 'can_edit_items' ),
					'args'                => array(
						'title'       => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'content'     => array(
							'sanitize_callback' => 'sanitize_textarea_field',
						),
						'status'      => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'category'    => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'priority'    => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
						'target_date' => array(
							'sanitize_callback' => 'sanitize_text_field',
						),
					),
				),
				array(
					'methods'             => 'DELETE',
					'callback'            => array( $this, 'delete_item' ),
					'permission_callback' => array( $this, 'can_edit_items' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/items',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'can_edit_items' ),
				'args'                => array(
					'title'       => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'content'     => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'status'      => array(
						'default'           => 'planned',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'category'    => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'priority'    => array(
						'default'           => 'medium',
						'sanitize_callback' => 'sanitize_text_field',
					),
					'target_date' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);

		// Settings endpoints
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => '__return_true',
				),
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'can_manage_settings' ),
				),
			)
		);

		// Changelog endpoints
		register_rest_route(
			$this->namespace,
			'/changelog',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_changelogs' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'product' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
					'limit'   => array(
						'default'           => 10,
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/changelog/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_changelog' ),
				'permission_callback' => '__return_true',
			)
		);

		register_rest_route(
			$this->namespace,
			'/changelog-types',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_changelog_types' ),
				'permission_callback' => '__return_true',
			)
		);

		// Comment listing endpoint (the POST /comment route is registered above).
		register_rest_route(
			$this->namespace,
			'/items/(?P<id>\d+)/comments',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this, 'get_item_comments' ),
				'permission_callback' => '__return_true',
			)
		);

		// Allow pro addon to register additional routes
		do_action( 'roadmap_register_api_routes', $this->namespace );
	}

	public function get_items( $request ) {
		$params = $request->get_params();

		$args = array(
			'post_type'      => 'roadmap_item',
			'posts_per_page' => $params['per_page'],
			'paged'          => $params['page'],
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- API endpoint requires filtering
			'meta_query'     => array(),
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- API endpoint requires filtering
			'tax_query'      => array(
				'relation' => 'AND',
			),
		);

		// Only show published posts on frontend (pending is for backend only)
		$args['post_status'] = 'publish';

		// Filter by product (mandatory)
		$product_filter = $params['product'];

		if ( ! $product_filter ) {
			return new WP_Error( 'missing_product', 'Product parameter is required', array( 'status' => 400 ) );
		}

		if ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) {
			// Let pro addon handle product filtering
			$args = apply_filters( 'roadmap_api_filter_by_product', $args, $product_filter );
		} else {
			// Free version - filter by default product using taxonomy
			$args['tax_query'][] = array(
				'taxonomy' => 'roadmap_product',
				'field'    => 'slug',
				'terms'    => $product_filter,
			);
		}

		// Filter by status
		if ( ! empty( $params['status'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'roadmap_status',
				'field'    => 'slug',
				'terms'    => $params['status'],
			);
		}

		// Filter by category
		if ( ! empty( $params['category'] ) ) {
			$args['tax_query'][] = array(
				'taxonomy' => 'roadmap_category',
				'field'    => 'slug',
				'terms'    => $params['category'],
			);
		}

		/**
		 * Allow the pro addon to add advanced query filters (priority, progress,
		 * assignee, date range, dependencies) to the roadmap items query.
		 *
		 * @param array $args   The WP_Query args.
		 * @param array $params The REST request params.
		 */
		$args = apply_filters( 'roadmap_api_query_args', $args, $params );

		// Run the query
		$query = new WP_Query( $args );
		$items = array();

		// Prime caches to avoid N+1 queries
		if ( ! empty( $query->posts ) ) {
			$post_ids = wp_list_pluck( $query->posts, 'ID' );

			// Prime post meta cache for all posts at once
			update_postmeta_cache( $post_ids );

			// Prime term cache for all posts at once
			update_object_term_cache( $post_ids, 'roadmap_item' );

			// Batch fetch all vote statuses in single query
			$user_votes = $this->get_user_votes_batch( $post_ids );

			foreach ( $query->posts as $post ) {
				$items[] = $this->format_item( $post, $user_votes );
			}
		}

		return new WP_REST_Response(
			array(
				'items'        => $items,
				'total'        => $query->found_posts,
				'pages'        => $query->max_num_pages,
				'current_page' => $params['page'],
			),
			200
		);
	}

	/**
	 * Format a roadmap item for API response.
	 *
	 * @param WP_Post    $post       The post object.
	 * @param array|null $user_votes Optional pre-fetched user votes for batch optimization.
	 * @return array Formatted item data.
	 */
	private function format_item( $post, $user_votes = null ) {
		// Use batch vote data if available, otherwise query individually
		$user_voted = null !== $user_votes
			? ( isset( $user_votes[ $post->ID ] ) ? $user_votes[ $post->ID ] : false )
			: $this->has_user_voted( $post->ID );

		$item = array(
			'id'              => $post->ID,
			'title'           => html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ),
			'content'         => html_entity_decode( $post->post_content, ENT_QUOTES, 'UTF-8' ),
			'excerpt'         => html_entity_decode( wp_trim_words( $post->post_content, 20 ), ENT_QUOTES, 'UTF-8' ),
			'author'          => get_the_author_meta( 'display_name', (int) $post->post_author ),
			'date'            => $post->post_date,
			'modified'        => $post->post_modified,
			'votes'           => (int) get_post_meta( $post->ID, '_roadmap_votes', true ),
			'comment_count'   => $post->comment_count,
			'priority'        => get_post_meta( $post->ID, '_roadmap_priority', true ),
			'progress'        => (int) get_post_meta( $post->ID, '_roadmap_progress', true ),
			'completion_notes' => get_post_meta( $post->ID, '_roadmap_completion_notes', true ),
			'target_date'     => get_post_meta( $post->ID, '_roadmap_target_date', true ),
			'external_link'   => get_post_meta( $post->ID, '_roadmap_external_link', true ),
			'start_date'      => get_post_meta( $post->ID, '_roadmap_start_date', true ),
			'end_date'        => get_post_meta( $post->ID, '_roadmap_end_date', true ),
			'effort_estimate' => get_post_meta( $post->ID, '_roadmap_effort_estimate', true ),
			'github_issue'    => get_post_meta( $post->ID, '_roadmap_github_issue', true ),
			'status'          => $this->get_item_terms( $post->ID, 'roadmap_status' ),
			'categories'      => $this->get_item_terms( $post->ID, 'roadmap_category' ),
			'user_voted'      => $user_voted,
			'product'         => $this->get_item_terms( $post->ID, 'roadmap_product' ),
			'link'            => get_permalink( $post->ID ),
			'is_suggestion'   => ( 'suggestion' === get_post_meta( $post->ID, '_roadmap_source', true ) ),
		);

		return apply_filters( 'roadmap_api_format_item', $item, $post );
	}

	private function get_item_terms( $post_id, $taxonomy ) {
		$terms     = wp_get_post_terms( $post_id, $taxonomy );
		$formatted = array();

		foreach ( $terms as $term ) {
			$formatted[] = array(
				'id'   => $term->term_id,
				'name' => $term->name,
				'slug' => $term->slug,
			);
		}

		return $formatted;
	}

	private function has_user_voted( $item_id ) {
		global $wpdb;
		$table   = $wpdb->prefix . 'roadmap_votes';
		$user_id = get_current_user_id();

		$vote = null;

		if ( $user_id ) {
			// Check logged in user vote
			$vote = $wpdb->get_var(
				$wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT vote_value FROM {$table} WHERE user_id = %d AND item_id = %d",
					$user_id,
					$item_id
				)
			); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, caching not needed for single user vote check
		} else {
			// Check guest vote by IP
			$guest_ip = $this->get_user_ip();
			$vote     = $wpdb->get_var(
				$wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT vote_value FROM {$table} WHERE guest_ip = %s AND item_id = %d",
					$guest_ip,
					$item_id
				)
			); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, caching not needed for single user vote check
		}

		return $vote ? (int) $vote : false;
	}

	/**
	 * Batch fetch user votes for multiple items in a single query.
	 *
	 * @param array $post_ids Array of post IDs to check votes for.
	 * @return array Associative array of post_id => vote_value.
	 */
	private function get_user_votes_batch( $post_ids ) {
		if ( empty( $post_ids ) ) {
			return array();
		}

		global $wpdb;
		$table   = $wpdb->prefix . 'roadmap_votes';
		$user_id = get_current_user_id();

		// Build placeholders for IN clause
		$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );

		if ( $user_id ) {
			// Logged in user - query by user_id
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Placeholders are safe integers
			$query = $wpdb->prepare(
				"SELECT item_id, vote_value FROM {$table} WHERE user_id = %d AND item_id IN ({$placeholders})",
				array_merge( array( $user_id ), $post_ids )
			);
		} else {
			// Guest user - query by IP
			$guest_ip = $this->get_user_ip();
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Placeholders are safe integers
			$query = $wpdb->prepare(
				"SELECT item_id, vote_value FROM {$table} WHERE guest_ip = %s AND item_id IN ({$placeholders})",
				array_merge( array( $guest_ip ), $post_ids )
			);
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Query is prepared above, custom table for batch performance
		$results = $wpdb->get_results( $query );

		$votes = array();
		if ( $results ) {
			foreach ( $results as $row ) {
				$votes[ (int) $row->item_id ] = (int) $row->vote_value;
			}
		}

		return $votes;
	}

	/**
	 * Transient-based rate limiter. Keys by user ID when logged in, otherwise by
	 * IP. Returns WP_Error (429) once the window limit is reached.
	 *
	 * @param string $action Action slug (vote, suggestion).
	 * @param int    $max    Max attempts allowed within the window.
	 * @param int    $window Window length in seconds.
	 * @return true|WP_Error
	 */
	private function rate_limit_check( $action, $max, $window ) {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$id = 'u' . $user_id;
		} else {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			$id = 'ip' . md5( $ip );
		}

		$key      = 'roadmap_rl_' . $action . '_' . $id;
		$attempts = (int) get_transient( $key );

		if ( $attempts >= $max ) {
			return new WP_Error(
				'rate_limited',
				__( 'You are doing that too often. Please slow down and try again shortly.', 'product-roadmap' ),
				array( 'status' => 429 )
			);
		}

		set_transient( $key, $attempts + 1, $window );
		return true;
	}

	public function submit_vote( $request ) {
		$item_id    = $request->get_param( 'item_id' );
		$vote_value = $request->get_param( 'vote_value' );
		$user_id    = get_current_user_id();

		// Throttle vote flooding (per user, or per IP for guests). Generous so
		// normal browsing is never blocked, but stops rapid-fire abuse.
		$limit = $this->rate_limit_check( 'vote', 30, MINUTE_IN_SECONDS );
		if ( is_wp_error( $limit ) ) {
			return $limit;
		}

		global $wpdb;
		$table = $wpdb->prefix . 'roadmap_votes';

		$existing_vote = null;
		$insert_data   = array(
			'item_id'    => $item_id,
			'vote_value' => $vote_value,
		);
		$where_clause  = array();

		if ( $user_id ) {
			// Logged in user
			$existing_vote = $wpdb->get_row(
				$wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM {$table} WHERE user_id = %d AND item_id = %d",
					$user_id,
					$item_id
				)
			); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, caching not needed for vote submission

			$insert_data['user_id'] = $user_id;
			$where_clause           = array(
				'user_id' => $user_id,
				'item_id' => $item_id,
			);
		} else {
			// Guest user
			$guest_ip      = $this->get_user_ip();
			$existing_vote = $wpdb->get_row(
				$wpdb->prepare(
                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					"SELECT * FROM {$table} WHERE guest_ip = %s AND item_id = %d",
					$guest_ip,
					$item_id
				)
			); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, caching not needed for vote submission

			$insert_data['guest_ip']   = $guest_ip;
			$insert_data['user_agent'] = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			$where_clause              = array(
				'guest_ip' => $guest_ip,
				'item_id'  => $item_id,
			);
		}

		if ( $existing_vote ) {
			// Update existing vote
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table update
			$wpdb->update(
				$table,
				array( 'vote_value' => $vote_value ),
				$where_clause
			);
		} else {
			// Insert new vote
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Custom table insert
			$wpdb->insert( $table, $insert_data );
		}

		// Update vote count
		$this->update_vote_count( $item_id );

		do_action( 'roadmap_vote_submitted', $item_id, $user_id, $vote_value );

		return new WP_REST_Response(
			array(
				'success'    => true,
				'vote_count' => (int) get_post_meta( $item_id, '_roadmap_votes', true ),
			),
			200
		);
	}

	private function update_vote_count( $item_id ) {
		global $wpdb;
		$table = $wpdb->prefix . 'roadmap_votes';

		$vote_count = $wpdb->get_var(
			$wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT SUM(vote_value) FROM {$table} WHERE item_id = %d",
				$item_id
			)
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Custom table, vote count is cached in post meta

		update_post_meta( $item_id, '_roadmap_votes', (int) $vote_count );
	}

	public function submit_suggestion( $request ) {
		// Honeypot: a hidden field real users never fill. Bots that fill every
		// input trip it; return a fake success so they don't retry.
		if ( ! empty( $request->get_param( 'website' ) ) ) {
			return rest_ensure_response( array( 'success' => true, 'id' => 0 ) );
		}

		// Throttle suggestion spam (per user, or per IP for guests).
		$limit = $this->rate_limit_check( 'suggestion', 5, HOUR_IN_SECONDS );
		if ( is_wp_error( $limit ) ) {
			return $limit;
		}

		$title       = $request->get_param( 'title' );
		$description = $request->get_param( 'description' );
		$category    = $request->get_param( 'category' );
		$user_id     = get_current_user_id();

		// Check if auto-approve is enabled (default is false - submissions go to pending)
		$global_settings = get_option( 'roadmap_global_settings', array() );
		$auto_approve    = ! empty( $global_settings['auto_approve_suggestions'] );
		// IMPORTANT: Default behavior is to set posts to 'pending' for admin review
		$post_status = $auto_approve ? 'publish' : 'pending';

		$post_data = array(
			'post_title'   => $title,
			'post_content' => $description,
			'post_type'    => 'roadmap_item',
			'post_status'  => $post_status,
			'post_author'  => $user_id,
		);

		$post_id = wp_insert_post( $post_data, true );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			// Flag this item as user-suggested so it can be told apart from
			// admin-created items even after it is approved and published.
			update_post_meta( $post_id, '_roadmap_source', 'suggestion' );

			// Set status based on settings - use configured default status for all suggestions
			$default_status = $global_settings['suggestion_default_status'] ?? 'planned';
			wp_set_object_terms( $post_id, $default_status, 'roadmap_status' );

			// Set category if provided
			if ( $category ) {
				wp_set_object_terms( $post_id, $category, 'roadmap_category' );
			}

			// Assign the product taxonomy: use the product the suggestion was made
			// on (passed from the shortcode's current product), falling back to the
			// configured default product. (Matches create_item; the taxonomy is the
			// single source of truth for product association.)
			$product = $request->get_param( 'product' );
			if ( empty( $product ) ) {
				$product = get_option( 'roadmap_default_product', 'main' );
			}
			wp_set_object_terms( $post_id, sanitize_text_field( $product ), 'roadmap_product' );

			// Set priority if provided
			$priority = $request->get_param( 'priority' );
			if ( $priority ) {
				update_post_meta( $post_id, '_roadmap_priority', sanitize_text_field( $priority ) );
			}

			// Set external link if provided
			$external_link = $request->get_param( 'external_link' );
			if ( $external_link ) {
				update_post_meta( $post_id, '_roadmap_external_link', esc_url_raw( $external_link ) );
			}

			do_action( 'roadmap_suggestion_submitted', $post_id, $user_id );

			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'Thank you for your suggestion!', 'product-roadmap' ),
					'id'      => $post_id,
				),
				200
			);
		}

		return new WP_Error( 'suggestion_failed', __( 'Failed to submit suggestion', 'product-roadmap' ), array( 'status' => 500 ) );
	}

	public function get_stats( $request ) {
		$stats = array(
			'total_items'     => wp_count_posts( 'roadmap_item' )->publish,
			'total_votes'     => $this->get_total_votes(),
			'total_comments'  => $this->get_total_comments(),
			'items_by_status' => $this->get_items_by_status(),
		);

		return new WP_REST_Response( $stats, 200 );
	}

	private function get_total_votes() {
		global $wpdb;
		$table = $wpdb->prefix . 'roadmap_votes';
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Statistics endpoint, caching not needed
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Statistics endpoint, table name is safe
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
	}

	private function get_total_comments() {
		global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Statistics endpoint, caching not needed
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $wpdb->comments WHERE comment_post_ID IN (
                SELECT ID FROM $wpdb->posts WHERE post_type = %s
            )",
				'roadmap_item'
			)
		);
	}

	private function get_items_by_status() {
		$statuses = get_terms(
			array(
				'taxonomy'   => 'roadmap_status',
				'hide_empty' => false,
			)
		);

		$counts = array();

		foreach ( $statuses as $status ) {
			$counts[ $status->slug ] = array(
				'name'  => $status->name,
				'count' => $status->count,
			);
		}

		return $counts;
	}

	public function create_item( $request ) {
		$post_data = array(
			'post_title'   => $request->get_param( 'title' ),
			'post_content' => $request->get_param( 'content' ),
			'post_type'    => 'roadmap_item',
			'post_status'  => 'publish',
		);

		$post_id = wp_insert_post( $post_data, true );

		if ( $post_id && ! is_wp_error( $post_id ) ) {
			// Get default status from settings
			$global_settings = get_option( 'roadmap_global_settings', array() );
			$default_status  = $global_settings['default_status'] ?? 'planned';

			// Set taxonomies
			$status = $request->get_param( 'status' ) ?: $default_status;
			wp_set_object_terms( $post_id, $status, 'roadmap_status' );

			if ( $request->get_param( 'categories' ) ) {
				// Categories come as array of IDs from frontend
				$categories = $request->get_param( 'categories' );
				if ( is_array( $categories ) ) {
					$categories = array_map( 'intval', $categories );
					wp_set_object_terms( $post_id, $categories, 'roadmap_category' );
				}
			}

			// Set all meta fields
			$meta_fields = array(
				'_roadmap_priority'         => $request->get_param( 'priority' ),
				'_roadmap_progress'         => $request->get_param( 'progress' ),
				'_roadmap_completion_notes' => $request->get_param( 'completion_notes' ),
				'_roadmap_target_date'      => $request->get_param( 'target_date' ),
				'_roadmap_external_link'    => $request->get_param( 'external_link' ),
				'_roadmap_start_date'       => $request->get_param( 'start_date' ),
				'_roadmap_end_date'         => $request->get_param( 'end_date' ),
				'_roadmap_effort_estimate'  => $request->get_param( 'effort_estimate' ),
				'_roadmap_github_issue'     => $request->get_param( 'github_issue' ),
			);

			foreach ( $meta_fields as $key => $value ) {
				if ( $value !== null ) {
					update_post_meta( $post_id, $key, sanitize_textarea_field( $value ) );
				}
			}

			// Set product taxonomy
			$product = $request->get_param( 'product' );
			if ( $product ) {
				wp_set_object_terms( $post_id, $product, 'roadmap_product' );
			}

			return new WP_REST_Response( $this->format_item( get_post( $post_id ) ), 201 );
		}

		return new WP_Error( 'create_failed', __( 'Failed to create item', 'product-roadmap' ), array( 'status' => 500 ) );
	}

	public function update_item( $request ) {
		$post_id = $request->get_param( 'id' );

		$post_data = array(
			'ID' => $post_id,
		);

		if ( $request->get_param( 'title' ) ) {
			$post_data['post_title'] = $request->get_param( 'title' );
		}

		if ( $request->get_param( 'content' ) ) {
			$post_data['post_content'] = $request->get_param( 'content' );
		}

		$updated = wp_update_post( $post_data, true );

		if ( $updated && ! is_wp_error( $updated ) ) {
			// Update taxonomies
			if ( $request->get_param( 'status' ) ) {
				wp_set_object_terms( $post_id, $request->get_param( 'status' ), 'roadmap_status' );
			}
			if ( $request->get_param( 'categories' ) ) {
				// Categories come as array of IDs from frontend
				$categories = $request->get_param( 'categories' );
				if ( is_array( $categories ) ) {
					$categories = array_map( 'intval', $categories );
					wp_set_object_terms( $post_id, $categories, 'roadmap_category' );
				}
			}

			// Update all meta fields
			$meta_fields = array(
				'_roadmap_priority'         => $request->get_param( 'priority' ),
				'_roadmap_progress'         => $request->get_param( 'progress' ),
				'_roadmap_completion_notes' => $request->get_param( 'completion_notes' ),
				'_roadmap_target_date'      => $request->get_param( 'target_date' ),
				'_roadmap_external_link'    => $request->get_param( 'external_link' ),
				'_roadmap_start_date'       => $request->get_param( 'start_date' ),
				'_roadmap_end_date'         => $request->get_param( 'end_date' ),
				'_roadmap_effort_estimate'  => $request->get_param( 'effort_estimate' ),
				'_roadmap_github_issue'     => $request->get_param( 'github_issue' ),
			);

			foreach ( $meta_fields as $key => $value ) {
				if ( $value !== null ) {
					update_post_meta( $post_id, $key, sanitize_textarea_field( $value ) );
				}
			}

			return new WP_REST_Response( $this->format_item( get_post( $post_id ) ), 200 );
		}

		return new WP_Error( 'update_failed', __( 'Failed to update item', 'product-roadmap' ), array( 'status' => 500 ) );
	}

	public function delete_item( $request ) {
		$post_id = $request->get_param( 'id' );

		if ( wp_delete_post( $post_id, true ) ) {
			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		return new WP_Error( 'delete_failed', __( 'Failed to delete item', 'product-roadmap' ), array( 'status' => 500 ) );
	}

	public function get_settings( $request ) {
		$global_settings = get_option( 'roadmap_global_settings', array() );
		$settings        = array(
			'enable_voting'      => $global_settings['enable_voting'] ?? ( get_option( 'roadmap_enable_voting', 'yes' ) === 'yes' ),
			'allow_guest_voting' => $global_settings['allow_guest_voting'] ?? ( get_option( 'roadmap_allow_guest_voting', 'yes' ) === 'yes' ),
			'enable_comments'    => $global_settings['enable_comments'] ?? ( get_option( 'roadmap_enable_comments', 'yes' ) === 'yes' ),
			'enable_suggestions' => $global_settings['enable_suggestions'] ?? ( get_option( 'roadmap_enable_suggestions', 'yes' ) === 'yes' ),
			'items_per_page'     => $global_settings['items_per_page'] ?? (int) get_option( 'roadmap_items_per_page', 100 ),
			'current_user'       => $this->get_current_user_data(),
			'statuses'           => $this->get_all_statuses(),
			'categories'         => $this->get_all_categories(),
			'products'           => $this->get_all_products(),
			'is_pro'             => ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ),
		);

		return new WP_REST_Response( $settings, 200 );
	}

	private function get_current_user_data() {
		if ( ! is_user_logged_in() ) {
			return null;
		}

		$user = wp_get_current_user();

		return array(
			'id'           => $user->ID,
			'name'         => $user->display_name,
			'email'        => $user->user_email,
			'capabilities' => array(
				'vote'            => $this->can_vote(),
				'comment'         => $this->can_comment(),
				'suggest'         => $this->can_suggest(),
				'edit_items'      => $this->can_edit_items(),
				'manage_settings' => $this->can_manage_settings(),
			),
		);
	}

	private function get_all_statuses() {
		$statuses = get_terms(
			array(
				'taxonomy'   => 'roadmap_status',
				'hide_empty' => false,
			)
		);

		$formatted = array();
		foreach ( $statuses as $status ) {
			$formatted[] = array(
				'id'   => $status->term_id,
				'name' => $status->name,
				'slug' => $status->slug,
			);
		}

		return $formatted;
	}

	private function get_all_categories() {
		$categories = get_terms(
			array(
				'taxonomy'   => 'roadmap_category',
				'hide_empty' => false,
			)
		);

		$formatted = array();
		foreach ( $categories as $category ) {
			$formatted[] = array(
				'id'   => $category->term_id,
				'name' => $category->name,
				'slug' => $category->slug,
			);
		}

		return $formatted;
	}

	private function get_all_products() {
		$products = get_terms(
			array(
				'taxonomy'   => 'roadmap_product',
				'hide_empty' => false,
			)
		);

		$formatted = array();
		foreach ( $products as $product ) {
			$formatted[] = array(
				'id'   => $product->term_id,
				'name' => $product->name,
				'slug' => $product->slug,
			);
		}

		return $formatted;
	}

	public function update_settings( $request ) {
		$settings        = $request->get_json_params();
		$global_settings = get_option( 'roadmap_global_settings', array() );

		if ( isset( $settings['enable_voting'] ) ) {
			$global_settings['enable_voting'] = (bool) $settings['enable_voting'];
		}

		if ( isset( $settings['allow_guest_voting'] ) ) {
			$global_settings['allow_guest_voting'] = (bool) $settings['allow_guest_voting'];
		}

		if ( isset( $settings['enable_comments'] ) ) {
			$global_settings['enable_comments'] = (bool) $settings['enable_comments'];
		}

		if ( isset( $settings['enable_suggestions'] ) ) {
			$global_settings['enable_suggestions'] = (bool) $settings['enable_suggestions'];
		}

		if ( isset( $settings['items_per_page'] ) ) {
			$global_settings['items_per_page'] = absint( $settings['items_per_page'] );
		}

		update_option( 'roadmap_global_settings', $global_settings );

		do_action( 'roadmap_settings_updated', $settings );

		return new WP_REST_Response( array( 'success' => true ), 200 );
	}

	// Comment methods
	public function submit_comment( $request ) {
		$item_id = $request->get_param( 'item_id' );
		$content = $request->get_param( 'content' );

		// Verify the roadmap item exists
		$post = get_post( $item_id );
		if ( ! $post || $post->post_type !== 'roadmap_item' ) {
			return new WP_Error( 'invalid_item', __( 'Invalid roadmap item', 'product-roadmap' ), array( 'status' => 404 ) );
		}

		// Create the comment
		$comment_data = array(
			'comment_post_ID'  => $item_id,
			'comment_content'  => $content,
			'comment_type'     => '',
			'comment_approved' => 1, // Auto-approve for logged-in users
			'user_id'          => get_current_user_id(),
		);

		$comment_id = wp_insert_comment( $comment_data );

		if ( $comment_id ) {
			/**
			 * Fires after a comment is submitted on a roadmap item.
			 *
			 * @param int $comment_id The new comment ID.
			 * @param int $item_id    The roadmap item post ID.
			 * @param int $user_id    The commenting user's ID.
			 */
			do_action( 'roadmap_comment_submitted', $comment_id, $item_id, get_current_user_id() );
		}

		if ( ! $comment_id ) {
			return new WP_Error( 'comment_failed', __( 'Failed to add comment', 'product-roadmap' ), array( 'status' => 500 ) );
		}

		return new WP_REST_Response(
			array(
				'success'    => true,
				'comment_id' => $comment_id,
				'message'    => __( 'Comment added successfully', 'product-roadmap' ),
			),
			201
		);
	}

	public function get_item_comments( $request ) {
		$item_id = $request->get_param( 'id' );

		$comments = get_comments(
			array(
				'post_id' => $item_id,
				'status'  => 'approve',
				'orderby' => 'comment_date',
				'order'   => 'DESC',
			)
		);

		$formatted_comments = array();
		foreach ( $comments as $comment ) {
			$formatted_comments[] = array(
				'id'             => $comment->comment_ID,
				'content'        => $comment->comment_content,
				'author'         => $comment->comment_author,
				'author_name'    => get_comment_author( $comment ),
				'author_avatar'  => get_avatar_url( $comment->user_id ?: $comment->comment_author_email ),
				'date'           => $comment->comment_date,
				'formatted_date' => get_comment_date( 'M j, Y', $comment ),
			);
		}

		return new WP_REST_Response( $formatted_comments, 200 );
	}

	// Changelog API methods
	public function get_changelogs( $request ) {
		$product = $request->get_param( 'product' );
		$limit   = $request->get_param( 'limit' ) ?: 10;

		$args = array(
			'post_type'      => 'roadmap_changelog',
			'posts_per_page' => $limit,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
		);

		// Handle product filtering
		if ( ! empty( $product ) ) {
			$is_pro = defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE;

			// Free version: only allow 'main' product
			if ( ! $is_pro && $product !== 'main' ) {
				return new WP_Error(
					'pro_required',
					__( 'Multiple products require the Pro version. Use product=main for free version.', 'product-roadmap' ),
					array( 'status' => 403 )
				);
			}

            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- API endpoint requires product filtering
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'roadmap_product',
					'field'    => 'slug',
					'terms'    => $product,
				),
			);
		}

		/**
		 * Filter changelog API query args
		 *
		 * Allows Pro addon to modify query args (e.g., multi-product support)
		 *
		 * @since 1.4.0
		 * @param array $args WP_Query arguments
		 * @param WP_REST_Request $request REST request object
		 */
		$args = apply_filters( 'roadmap_api_changelog_args', $args, $request );

		$query      = new WP_Query( $args );
		$changelogs = array();

		foreach ( $query->posts as $post ) {
			$changelogs[] = $this->format_changelog( $post );
		}

		return new WP_REST_Response(
			array(
				'changelogs' => $changelogs,
				'total'      => $query->found_posts,
			),
			200
		);
	}

	public function get_changelog( $request ) {
		$id   = $request->get_param( 'id' );
		$post = get_post( $id );

		if ( ! $post || $post->post_type !== 'roadmap_changelog' ) {
			return new WP_Error( 'not_found', __( 'Changelog not found', 'product-roadmap' ), array( 'status' => 404 ) );
		}

		return new WP_REST_Response( $this->format_changelog( $post ), 200 );
	}

	public function get_changelog_types( $request ) {
		$types = get_terms(
			array(
				'taxonomy'   => 'changelog_type',
				'hide_empty' => false,
			)
		);

		if ( is_wp_error( $types ) ) {
			return new WP_REST_Response( array(), 200 );
		}

		$formatted = array();
		foreach ( $types as $type ) {
			$color       = get_term_meta( $type->term_id, 'changelog_type_color', true );
			$formatted[] = array(
				'id'    => $type->term_id,
				'name'  => $type->name,
				'slug'  => $type->slug,
				'color' => $color ?: '#6b7280',
			);
		}

		return new WP_REST_Response( $formatted, 200 );
	}

	private function format_changelog( $post ) {
		$version      = get_post_meta( $post->ID, '_changelog_version', true );
		$release_date = get_post_meta( $post->ID, '_changelog_release_date', true );
		$items        = get_post_meta( $post->ID, '_changelog_items', true );

		// Get product terms
		$products     = wp_get_post_terms( $post->ID, 'roadmap_product' );
		$product_data = array();
		foreach ( $products as $product ) {
			$product_data[] = array(
				'id'   => $product->term_id,
				'name' => $product->name,
				'slug' => $product->slug,
			);
		}

		// Format items with type details
		$formatted_items = array();
		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				$type_term  = get_term_by( 'slug', $item['type'], 'changelog_type' );
				$type_color = $type_term ? get_term_meta( $type_term->term_id, 'changelog_type_color', true ) : '#6b7280';

				$formatted_items[] = array(
					'description' => html_entity_decode( $item['description'], ENT_QUOTES, 'UTF-8' ),
					'type'        => $item['type'],
					'type_name'   => $type_term ? $type_term->name : ucfirst( $item['type'] ),
					'type_color'  => $type_color,
				);
			}
		}

		return array(
			'id'             => $post->ID,
			'title'          => html_entity_decode( $post->post_title, ENT_QUOTES, 'UTF-8' ),
			'content'        => html_entity_decode( $post->post_content, ENT_QUOTES, 'UTF-8' ),
			'version'        => $version,
			'release_date'   => $release_date,
			'formatted_date' => $release_date ? date_i18n( get_option( 'date_format' ), strtotime( $release_date ) ) : '',
			'items'          => $formatted_items,
			'products'       => $product_data,
			'date'           => $post->post_date,
			'link'           => get_permalink( $post->ID ),
		);
	}

	// Permission callbacks
	public function can_vote() {
		$global_settings      = get_option( 'roadmap_global_settings', array() );
		$voting_enabled       = $global_settings['enable_voting'] ?? ( get_option( 'roadmap_enable_voting', 'yes' ) === 'yes' );
		$guest_voting_allowed = $global_settings['allow_guest_voting'] ?? ( get_option( 'roadmap_allow_guest_voting', 'yes' ) === 'yes' );

		return $voting_enabled && ( $guest_voting_allowed || is_user_logged_in() );
	}

	public function can_comment() {
		$global_settings  = get_option( 'roadmap_global_settings', array() );
		$comments_enabled = $global_settings['enable_comments'] ?? ( get_option( 'roadmap_enable_comments', 'yes' ) === 'yes' );

		return is_user_logged_in() && $comments_enabled;
	}

	public function can_suggest() {
		return Roadmap_Permissions::can_suggest();
	}

	public function can_edit_items() {
		return current_user_can( 'edit_posts' );
	}

	public function can_manage_settings() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get user IP address for guest voting.
	 *
	 * Security: Only use REMOTE_ADDR which cannot be spoofed by the client.
	 * HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR headers can be manipulated
	 * by malicious users to bypass one-vote-per-IP restrictions.
	 *
	 * Note: If your site is behind a trusted reverse proxy (like Cloudflare),
	 * you may need to configure WordPress to trust those headers via a filter.
	 *
	 * @return string The validated IP address or empty string.
	 */
	private function get_user_ip() {
		$ip = '';

		// Only use REMOTE_ADDR for security - it cannot be spoofed by the client
		// Proxy headers (HTTP_CLIENT_IP, HTTP_X_FORWARDED_FOR) can be easily faked
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		// Validate IP format to prevent injection
		if ( ! empty( $ip ) && filter_var( $ip, FILTER_VALIDATE_IP ) === false ) {
			$ip = '';
		}

		// Allow filtering for sites behind trusted proxies
		return apply_filters( 'roadmap_guest_user_ip', $ip );
	}
}
