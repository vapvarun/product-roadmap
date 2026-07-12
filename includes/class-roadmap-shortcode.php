<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Roadmap_Shortcode {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_shortcode( 'roadmap', array( $this, 'render_shortcode' ) );
		add_shortcode( 'roadmap_changelog', array( $this, 'render_changelog_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'maybe_enqueue_assets' ) );
	}

	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'mode'                => 'view',
				'allow_frontend_edit' => 'false',
				'product'             => '',
				'class'               => '',
				'template'            => 'minimal',
				'sort'                => '', // Default sort: votes, recent, comments, priority
			),
			$atts,
			'roadmap'
		);

		// Handle product parameter
		if ( empty( $atts['product'] ) ) {
			// If Pro is not active, default to 'main'
			if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) {
				$atts['product'] = 'main';
			} else {
				// Pro is active but no product specified
				return '<div class="roadmap-error">' . __( 'Error: Product parameter is required. Example: [roadmap product="main"]', 'product-roadmap' ) . '</div>';
			}
		}

		// If Pro is not active and product is not 'main', show error
		if ( ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) && $atts['product'] !== 'main' ) {
			return '<div class="roadmap-error">' . __( 'Multiple products require the Pro version. Upgrade to use different products.', 'product-roadmap' ) . '</div>';
		}

		// Handle template fallback for pro templates when pro is not active
		$pro_templates = array( 'timeline', 'list', 'cards', 'board' );
		if ( ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) && in_array( $atts['template'], $pro_templates ) ) {
			$atts['template'] = 'minimal'; // Fallback to default minimal template
		}

		// Validate mode
		$allowed_modes = array( 'view', 'manage' );
		if ( ! in_array( $atts['mode'], $allowed_modes ) ) {
			$atts['mode'] = 'view';
		}

		// Check permissions for manage mode
		if ( $atts['mode'] === 'manage' && ! current_user_can( 'edit_posts' ) ) {
			$atts['mode'] = 'view';
		}

		// Enqueue assets
		$this->enqueue_assets();

		// Prepare data for Vue app
		$global_settings = get_option( 'roadmap_global_settings', array() );

		$app_data = array(
			'api_url'             => rest_url( 'roadmap/v1' ),
			'nonce'               => wp_create_nonce( 'wp_rest' ),
			'mode'                => $atts['mode'],
			'allow_frontend_edit' => filter_var( $atts['allow_frontend_edit'], FILTER_VALIDATE_BOOLEAN ),
			'product'             => $atts['product'],
			'template'            => $atts['template'],
			'default_sort'        => $atts['sort'],
			'is_pro'              => ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ),
			'settings'            => array(
				'enable_voting'      => $global_settings['enable_voting'] ?? ( get_option( 'roadmap_enable_voting', 'yes' ) === 'yes' ),
				'allow_guest_voting' => $global_settings['allow_guest_voting'] ?? ( get_option( 'roadmap_allow_guest_voting', 'yes' ) === 'yes' ),
				'enable_comments'    => $global_settings['enable_comments'] ?? ( get_option( 'roadmap_enable_comments', 'yes' ) === 'yes' ),
				'enable_suggestions' => $global_settings['enable_suggestions'] ?? ( get_option( 'roadmap_enable_suggestions', 'yes' ) === 'yes' ),
				'items_per_page'     => $global_settings['items_per_page'] ?? (int) get_option( 'roadmap_items_per_page', 100 ),
			),
			'user'                => $this->get_user_data(),
			'i18n'                => $this->get_translations(),
		);

		$app_data = apply_filters( 'roadmap_shortcode_data', $app_data, $atts );

		// Generate unique ID for multiple instances
		$app_id = 'roadmap-app-' . uniqid();

		ob_start();
		?>
		<div id="<?php echo esc_attr( $app_id ); ?>" 
			class="roadmap-app <?php echo esc_attr( $atts['class'] ); ?> <?php echo esc_attr( $atts['product'] ? 'roadmap-single-product' : '' ); ?>"
			data-config='<?php echo esc_attr( json_encode( $app_data ) ); ?>'>
			<div class="roadmap-loading">
				<?php esc_html_e( 'Loading roadmap...', 'product-roadmap' ); ?>
			</div>
		</div>
		<?php

		/**
		 * Fires after the main roadmap app container is output.
		 *
		 * Lets the Pro addon inject additional UI (such as the analytics tab)
		 * after the board. Receives the resolved shortcode attributes,
		 * including 'product'.
		 *
		 * @param array $atts The resolved shortcode attributes.
		 */
		do_action( 'roadmap_after_main_content', $atts );

		return ob_get_clean();
	}

	/**
	 * Render changelog shortcode
	 *
	 * @param array $atts Shortcode attributes
	 * @return string HTML output
	 */
	public function render_changelog_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'product'     => '',
				'version'     => '',
				'id'          => '',
				'limit'       => 10,
				'show_date'   => 'true',
				'collapsible' => 'false',
				'class'       => '',
			),
			$atts,
			'roadmap_changelog'
		);

		// Handle product parameter - Free version uses default product
		if ( empty( $atts['product'] ) ) {
			if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) {
				$atts['product'] = 'main';
			}
		}

		// If Pro is not active and product is not 'main', show error
		if ( ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) && ! empty( $atts['product'] ) && $atts['product'] !== 'main' ) {
			return '<div class="roadmap-error">' . __( 'Multiple products require the Pro version.', 'product-roadmap' ) . '</div>';
		}

		// Query changelog entries
		$query_args = array(
			'post_type'      => 'roadmap_changelog',
			'posts_per_page' => absint( $atts['limit'] ),
			'orderby'        => 'meta_value',
			'meta_key'       => '_changelog_release_date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
		);

		// Filter by product if specified
		if ( ! empty( $atts['product'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy' => 'roadmap_product',
					'field'    => 'slug',
					'terms'    => $atts['product'],
				),
			);
		}

		/**
		 * Filter changelog shortcode query args
		 *
		 * Allows Pro addon to modify query args (e.g., multi-product support)
		 *
		 * @since 1.4.0
		 * @param array $query_args WP_Query arguments
		 * @param array $atts Shortcode attributes
		 */
		$query_args = apply_filters( 'roadmap_changelog_shortcode_args', $query_args, $atts );

		$changelogs = new WP_Query( $query_args );

		if ( ! $changelogs->have_posts() ) {
			return '<div class="roadmap-changelog-empty">' . __( 'No changelog entries found.', 'product-roadmap' ) . '</div>';
		}

		// Get change types for styling
		$change_types = Roadmap_Changelog::get_change_types();

		$show_date   = filter_var( $atts['show_date'], FILTER_VALIDATE_BOOLEAN );
		$collapsible = filter_var( $atts['collapsible'], FILTER_VALIDATE_BOOLEAN );

		// Enqueue changelog styles
		$this->enqueue_changelog_assets();

		ob_start();
		?>
		<div class="roadmap-changelog <?php echo esc_attr( $atts['class'] ); ?>" <?php echo $collapsible ? 'data-collapsible="true"' : ''; ?>>
			<?php
			while ( $changelogs->have_posts() ) :
				$changelogs->the_post();
				$post_id      = get_the_ID();
				$version      = get_post_meta( $post_id, '_changelog_version', true );
				$release_date = get_post_meta( $post_id, '_changelog_release_date', true );
				$release_type = get_post_meta( $post_id, '_changelog_release_type', true );
				$items        = get_post_meta( $post_id, '_changelog_items', true );

				$version_class = 'roadmap-changelog-version';
				if ( $release_type ) {
					$version_class .= ' changelog-' . esc_attr( $release_type );
				}
				?>
				<div class="<?php echo esc_attr( $version_class ); ?>" data-version="<?php echo esc_attr( $version ); ?>">
					<div class="roadmap-changelog-header">
						<h3 class="roadmap-changelog-title">
							<?php if ( $collapsible ) : ?>
								<button type="button" class="roadmap-changelog-toggle" aria-expanded="true">
									<span class="toggle-icon">&#9660;</span>
							<?php endif; ?>
							<?php if ( $version ) : ?>
								<span class="version-number">v<?php echo esc_html( $version ); ?></span>
							<?php else : ?>
								<?php the_title(); ?>
							<?php endif; ?>
							<?php if ( $collapsible ) : ?>
								</button>
							<?php endif; ?>
						</h3>
						<?php if ( $show_date && $release_date ) : ?>
							<span class="roadmap-changelog-date">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $release_date ) ) ); ?>
							</span>
						<?php endif; ?>
					</div>

					<div class="roadmap-changelog-content">
						<?php if ( ! empty( get_the_content() ) ) : ?>
							<div class="roadmap-changelog-description">
								<?php the_content(); ?>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $items ) && is_array( $items ) ) : ?>
							<ul class="roadmap-changelog-items">
								<?php
								foreach ( $items as $item ) :
									$type_slug   = $item['type'] ?? '';
									$type_data   = $change_types[ $type_slug ] ?? null;
									$badge_style = '';
									$badge_name  = ucfirst( $type_slug );

									if ( $type_data ) {
										$badge_style = sprintf(
											'color: %s; background: %s;',
											esc_attr( $type_data['color'] ),
											esc_attr( $type_data['background'] )
										);
										$badge_name  = $type_data['name'];
									}
									?>
									<li class="roadmap-changelog-item">
										<?php if ( $type_slug ) : ?>
											<span class="roadmap-changelog-badge" style="<?php echo esc_attr( $badge_style ); ?>">
												<?php echo esc_html( $badge_name ); ?>
											</span>
										<?php endif; ?>
										<span class="roadmap-changelog-item-text">
											<?php echo esc_html( $item['description'] ?? '' ); ?>
										</span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</div>
			<?php endwhile; ?>
			<?php wp_reset_postdata(); ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Enqueue changelog-specific assets
	 */
	private function enqueue_changelog_assets() {
		$version = ROADMAP_VERSION;

		// Inline CSS for changelog
		wp_register_style( 'roadmap-changelog', false, array(), $version );
		wp_enqueue_style( 'roadmap-changelog' );
		wp_add_inline_style(
			'roadmap-changelog',
			'
            .roadmap-changelog {
                max-width: 800px;
                margin: 0 auto;
            }
            .roadmap-changelog-version {
                background: #fff;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 20px;
                overflow: hidden;
            }
            .roadmap-changelog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 20px;
                background: #f9fafb;
                border-bottom: 1px solid #e5e7eb;
            }
            .roadmap-changelog-title {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
            }
            .roadmap-changelog-title .version-number {
                color: #1f2937;
            }
            .roadmap-changelog-date {
                color: #6b7280;
                font-size: 14px;
            }
            .roadmap-changelog-content {
                padding: 20px;
            }
            .roadmap-changelog-description {
                margin-bottom: 16px;
                color: #4b5563;
                line-height: 1.6;
            }
            .roadmap-changelog-items {
                list-style: none;
                margin: 0;
                padding: 0;
            }
            .roadmap-changelog-item {
                display: flex;
                align-items: flex-start;
                gap: 12px;
                padding: 10px 0;
                border-bottom: 1px solid #f3f4f6;
            }
            .roadmap-changelog-item:last-child {
                border-bottom: none;
            }
            .roadmap-changelog-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 500;
                white-space: nowrap;
                flex-shrink: 0;
            }
            .roadmap-changelog-item-text {
                color: #374151;
                line-height: 1.5;
            }
            .roadmap-changelog-toggle {
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
                display: flex;
                align-items: center;
                gap: 8px;
                font-size: inherit;
                font-weight: inherit;
                color: inherit;
            }
            .roadmap-changelog-toggle .toggle-icon {
                transition: transform 0.2s;
                font-size: 12px;
            }
            .roadmap-changelog-toggle[aria-expanded="false"] .toggle-icon {
                transform: rotate(-90deg);
            }
            .roadmap-changelog-version.collapsed .roadmap-changelog-content {
                display: none;
            }
            .roadmap-changelog-empty {
                text-align: center;
                padding: 40px;
                color: #6b7280;
            }
            /* Release type badges */
            .changelog-major .roadmap-changelog-header {
                border-left: 4px solid #dc2626;
            }
            .changelog-minor .roadmap-changelog-header {
                border-left: 4px solid #2563eb;
            }
            .changelog-patch .roadmap-changelog-header {
                border-left: 4px solid #16a34a;
            }
        '
		);

		// Inline JS for collapsible functionality
		wp_add_inline_script(
			'jquery',
			'
            jQuery(document).ready(function($) {
                $(".roadmap-changelog[data-collapsible=true] .roadmap-changelog-toggle").on("click", function() {
                    var $version = $(this).closest(".roadmap-changelog-version");
                    var isExpanded = $(this).attr("aria-expanded") === "true";

                    $(this).attr("aria-expanded", !isExpanded);
                    $version.toggleClass("collapsed");
                });
            });
        '
		);
	}

	public function maybe_enqueue_assets() {
		global $post;

		if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'roadmap' ) ) {
			$this->enqueue_assets();
		}
	}

	public function enqueue_assets() {
		$version = ROADMAP_VERSION;
		$is_dev  = defined( 'WP_DEBUG' ) && WP_DEBUG;

		if ( $is_dev && file_exists( ROADMAP_PLUGIN_PATH . 'vue-app/dist/assets/index.js' ) ) {
			// Development mode - use Vite build
			wp_enqueue_script(
				'roadmap-vue-app',
				ROADMAP_PLUGIN_URL . 'vue-app/dist/assets/index.js',
				array(),
				$version,
				true
			);

			wp_enqueue_style(
				'roadmap-vue-app',
				ROADMAP_PLUGIN_URL . 'vue-app/dist/assets/index.css',
				array(),
				$version
			);

			// Enqueue RTL styles if needed
			if ( is_rtl() && file_exists( ROADMAP_PLUGIN_PATH . 'vue-app/dist/assets/index-rtl.css' ) ) {
				wp_enqueue_style(
					'roadmap-vue-app-rtl',
					ROADMAP_PLUGIN_URL . 'vue-app/dist/assets/index-rtl.css',
					array( 'roadmap-vue-app' ),
					$version
				);
			}
		} else {
			// Production mode - use built assets.
			// No 'wp-api' dependency: the Vue app talks to REST via fetch(), and
			// depending on wp-api loads wp-includes/js/wp-api.min.js, which throws
			// "_.each is not a function" on newer WordPress/Underscore builds.
			wp_enqueue_script(
				'roadmap-vue-app',
				ROADMAP_PLUGIN_URL . 'assets/js/roadmap-app.js',
				array(),
				$version,
				true
			);

			wp_enqueue_style(
				'roadmap-vue-app',
				ROADMAP_PLUGIN_URL . 'assets/css/roadmap-app.css',
				array(),
				$version
			);

			// Enqueue RTL styles if needed
			if ( is_rtl() && file_exists( ROADMAP_PLUGIN_PATH . 'assets/css/roadmap-app-rtl.css' ) ) {
				wp_enqueue_style(
					'roadmap-vue-app-rtl',
					ROADMAP_PLUGIN_URL . 'assets/css/roadmap-app-rtl.css',
					array( 'roadmap-vue-app' ),
					$version
				);
			}
		}

		// Add inline CSS to hide product filter for single product roadmaps
		wp_add_inline_style(
			'roadmap-vue-app',
			'
            /* Hide the third select (product filter) when viewing single product roadmap */
            .roadmap-single-product .roadmap-filters select:nth-child(3) {
                display: none !important;
            }
        '
		);

		// Add inline script to initialize Vue apps
		wp_add_inline_script(
			'roadmap-vue-app',
			'
            if (!window.roadmapInitialized) {
                window.roadmapInitialized = true;
                
                document.addEventListener("DOMContentLoaded", function() {
                    const roadmapApps = document.querySelectorAll(".roadmap-app");
                    
                    roadmapApps.forEach(function(container, index) {
                        // Skip if already mounted
                        if (container.__vue_app__) {
                            return;
                        }
                        
                        if (window.RoadmapApp && container.dataset.config) {
                            try {
                                const config = JSON.parse(container.dataset.config);
                                window.RoadmapApp.mount(container, config);
                            } catch (e) {
                                // Silent error handling
                            }
                        }
                    });
                });
            }
        '
		);

		// Allow pro addon to enqueue additional assets
		do_action( 'roadmap_enqueue_assets' );
	}

	public function get_user_data() {
		if ( ! is_user_logged_in() ) {
			return null;
		}

		$user = wp_get_current_user();

		return array(
			'ID'          => $user->ID,  // Changed to uppercase to match Vue component check
			'id'          => $user->ID,  // Keep lowercase for backward compatibility
			'name'        => $user->display_name,
			'email'       => $user->user_email,
			'avatar'      => get_avatar_url( $user->ID ),
			'can_vote'    => $this->can_user_vote(),
			'can_comment' => $this->can_user_comment(),
			'can_suggest' => $this->can_user_suggest(),
			'can_edit'    => current_user_can( 'edit_posts' ),
			'can_manage'  => current_user_can( 'manage_options' ),
		);
	}

	public function get_translations() {
		// Get custom labels
		$item_singular = get_option( 'roadmap_item_singular_label', 'Feature' );
		$item_plural   = get_option( 'roadmap_item_plural_label', 'Features' );
		$product_name  = get_option( 'roadmap_product_name', 'Product' );

		return array(
			'loading'              => __( 'Loading...', 'product-roadmap' ),
			'error'                => __( 'An error occurred', 'product-roadmap' ),
			'no_items'             => sprintf(
				/* translators: %s: Item plural label */
				__( 'No %s found', 'product-roadmap' ),
				strtolower( $item_plural )
			),
			'vote'                 => __( 'Vote', 'product-roadmap' ),
			'votes'                => __( 'votes', 'product-roadmap' ),
			'comment'              => __( 'Comment', 'product-roadmap' ),
			'comments'             => __( 'comments', 'product-roadmap' ),
			'suggest'              => sprintf(
				/* translators: %s: Item singular label */
				__( 'Suggest %s', 'product-roadmap' ),
				$item_singular
			),
			'search'               => __( 'Search...', 'product-roadmap' ),
			'filter_by_status'     => __( 'Filter by Status', 'product-roadmap' ),
			'filter_by_category'   => __( 'Filter by Category', 'product-roadmap' ),
			'all_statuses'         => __( 'All Statuses', 'product-roadmap' ),
			'all_categories'       => __( 'All Categories', 'product-roadmap' ),
			'view_mode'            => __( 'View Mode', 'product-roadmap' ),
			'manage_mode'          => __( 'Manage Mode', 'product-roadmap' ),
			'add_item'             => sprintf(
				/* translators: %s: Item singular label */
				__( 'Add %s', 'product-roadmap' ),
				$item_singular
			),
			'edit_item'            => sprintf(
				/* translators: %s: Item singular label */
				__( 'Edit %s', 'product-roadmap' ),
				$item_singular
			),
			'delete_item'          => sprintf(
				/* translators: %s: Item singular label */
				__( 'Delete %s', 'product-roadmap' ),
				$item_singular
			),
			'save'                 => __( 'Save', 'product-roadmap' ),
			'cancel'               => __( 'Cancel', 'product-roadmap' ),
			'confirm_delete'       => sprintf(
				/* translators: %s: Item singular label */
				__( 'Are you sure you want to delete this %s?', 'product-roadmap' ),
				strtolower( $item_singular )
			),
			'item_added'           => sprintf(
				/* translators: %s: Item singular label */
				__( '%s added successfully', 'product-roadmap' ),
				$item_singular
			),
			'item_updated'         => sprintf(
				/* translators: %s: Item singular label */
				__( '%s updated successfully', 'product-roadmap' ),
				$item_singular
			),
			'item_deleted'         => sprintf(
				/* translators: %s: Item singular label */
				__( '%s deleted successfully', 'product-roadmap' ),
				$item_singular
			),
			'vote_submitted'       => __( 'Your vote has been submitted', 'product-roadmap' ),
			'comment_submitted'    => __( 'Your comment has been submitted', 'product-roadmap' ),
			'suggestion_submitted' => __( 'Your suggestion has been submitted', 'product-roadmap' ),
			'login_required'       => __( 'Please login to perform this action', 'product-roadmap' ),
			'title'                => __( 'Title', 'product-roadmap' ),
			'description'          => __( 'Description', 'product-roadmap' ),
			'status'               => __( 'Status', 'product-roadmap' ),
			'category'             => __( 'Category', 'product-roadmap' ),
			'priority'             => __( 'Priority', 'product-roadmap' ),
			'target_date'          => __( 'Target Date', 'product-roadmap' ),
			'low'                  => __( 'Low', 'product-roadmap' ),
			'medium'               => __( 'Medium', 'product-roadmap' ),
			'high'                 => __( 'High', 'product-roadmap' ),
			'critical'             => __( 'Critical', 'product-roadmap' ),
			'suggest_feature'      => __( 'Suggest a Feature', 'product-roadmap' ),
			'submit'               => __( 'Submit', 'product-roadmap' ),
			'submit_comment'       => __( 'Submit Comment', 'product-roadmap' ),
			'your_comment'         => __( 'Your Comment', 'product-roadmap' ),
			'share_thoughts'       => __( 'Share your thoughts...', 'product-roadmap' ),
			'feature_title'        => __( 'Feature Title', 'product-roadmap' ),
			'brief_title'          => __( 'Brief title for your feature idea', 'product-roadmap' ),
			'describe_detail'      => __( 'Describe your feature idea in detail...', 'product-roadmap' ),
			'external_link'        => __( 'External Link', 'product-roadmap' ),
			'link_details'         => __( 'Link to more details or examples', 'product-roadmap' ),
			'optional'             => __( 'Optional', 'product-roadmap' ),
			'edit'                 => __( 'Edit', 'product-roadmap' ),
			'delete'               => __( 'Delete', 'product-roadmap' ),
			'vote_for_item'        => __( 'Vote for this item', 'product-roadmap' ),
			'view_comments'        => __( 'View comments', 'product-roadmap' ),
			'remove_vote'          => __( 'Remove Vote', 'product-roadmap' ),
			'vote_for_this'        => __( 'Vote for This', 'product-roadmap' ),
			'search_roadmap'       => __( 'Search roadmap...', 'product-roadmap' ),
			'all_items'            => __( 'All Items', 'product-roadmap' ),
			'other'                => __( 'Other', 'product-roadmap' ),
			'unknown'              => __( 'Unknown', 'product-roadmap' ),
			'future_later'         => __( 'Future - Later', 'product-roadmap' ),
			'months'               => array(
				__( 'January', 'product-roadmap' ),
				__( 'February', 'product-roadmap' ),
				__( 'March', 'product-roadmap' ),
				__( 'April', 'product-roadmap' ),
				__( 'May', 'product-roadmap' ),
				__( 'June', 'product-roadmap' ),
				__( 'July', 'product-roadmap' ),
				__( 'August', 'product-roadmap' ),
				__( 'September', 'product-roadmap' ),
				__( 'October', 'product-roadmap' ),
				__( 'November', 'product-roadmap' ),
				__( 'December', 'product-roadmap' ),
			),
			'progress'             => __( 'Progress', 'product-roadmap' ),
			'start_date'           => __( 'Start Date', 'product-roadmap' ),
			'end_date'             => __( 'End Date', 'product-roadmap' ),
			'effort_estimate'      => __( 'Effort Estimate', 'product-roadmap' ),
			'github_issue'         => __( 'GitHub Issue', 'product-roadmap' ),
			'assignee'             => __( 'Assignee', 'product-roadmap' ),
			'select_status'        => __( 'Select Status', 'product-roadmap' ),
			'select_category'      => __( 'Select Category', 'product-roadmap' ),
			'select_priority'      => __( 'Select Priority', 'product-roadmap' ),
			'drag_drop_enabled'    => __( 'Drag items to change status', 'product-roadmap' ),
			'pro_feature'          => __( 'Pro Feature', 'product-roadmap' ),
			'upgrade_pro'          => __( 'Upgrade to Pro', 'product-roadmap' ),
			'all_products'         => __( 'All Products', 'product-roadmap' ),
			'sort_by'              => __( 'Sort By', 'product-roadmap' ),
			'most_voted'           => __( 'Most Voted', 'product-roadmap' ),
			'most_recent'          => __( 'Most Recent', 'product-roadmap' ),
			'most_discussed'       => __( 'Most Discussed', 'product-roadmap' ),
			'highest_priority'     => __( 'Highest Priority', 'product-roadmap' ),
			// Custom labels
			'item_singular'        => $item_singular,
			'item_plural'          => $item_plural,
			'product_name'         => $product_name,
		);
	}

	private function can_user_vote() {
		$global_settings      = get_option( 'roadmap_global_settings', array() );
		$voting_enabled       = $global_settings['enable_voting'] ?? ( get_option( 'roadmap_enable_voting', 'yes' ) === 'yes' );
		$guest_voting_allowed = $global_settings['allow_guest_voting'] ?? ( get_option( 'roadmap_allow_guest_voting', 'yes' ) === 'yes' );

		return $voting_enabled && ( $guest_voting_allowed || is_user_logged_in() );
	}

	private function can_user_comment() {
		$global_settings  = get_option( 'roadmap_global_settings', array() );
		$comments_enabled = $global_settings['enable_comments'] ?? ( get_option( 'roadmap_enable_comments', 'yes' ) === 'yes' );

		return is_user_logged_in() && $comments_enabled;
	}

	private function can_user_suggest() {
		$global_settings     = get_option( 'roadmap_global_settings', array() );
		$suggestions_enabled = $global_settings['enable_suggestions'] ?? ( get_option( 'roadmap_enable_suggestions', 'yes' ) === 'yes' );
		$requires_login      = $global_settings['suggestion_requires_login'] ?? true;

		return $suggestions_enabled && ( $requires_login ? is_user_logged_in() : true );
	}
}