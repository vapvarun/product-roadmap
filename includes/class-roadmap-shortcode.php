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

	/**
	 * The template an owner has configured as their default.
	 *
	 * Lives in the Pro templates option; free installs have no such setting and
	 * fall through to 'minimal', which is the only template free ships.
	 *
	 * @return string
	 */
	public static function get_default_template() {
		if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) {
			return 'minimal';
		}

		$pro_templates = get_option( 'roadmap_pro_templates', array() );
		$default       = is_array( $pro_templates ) && ! empty( $pro_templates['default_template'] )
			? sanitize_key( $pro_templates['default_template'] )
			: 'minimal';

		return $default;
	}

	/**
	 * Every [roadmap] option: the one list the shortcode, the Pro block and the
	 * docs share. Adding an option here makes it work everywhere.
	 *
	 * Keys: type (string|boolean|integer), default, label, help, group (block
	 * sidebar panel), choices (value => label, or a token resolved by
	 * option_choices() so a normal page render runs no term queries), min/max
	 * for integers, format => 'date' (YYYY-MM-DD), pro => true for options that
	 * need Pro's API filters, block => false for options the block gets natively.
	 *
	 * @return array
	 */
	public static function options() {
		return array(
			'product'             => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Product', 'product-roadmap' ),
				'help'    => __( 'Leave empty to show your default product.', 'product-roadmap' ),
				'group'   => 'content',
				'choices' => 'products',
			),
			'template'            => array(
				'type'    => 'string',
				'default' => self::get_default_template(),
				'label'   => __( 'Layout', 'product-roadmap' ),
				'group'   => 'content',
				'choices' => 'templates',
			),
			'status'              => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Show only this status', 'product-roadmap' ),
				'help'    => __( 'Visitors can still change it from the filter bar unless you hide the filters.', 'product-roadmap' ),
				'group'   => 'filter',
				'choices' => 'statuses',
			),
			'category'            => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Show only this category', 'product-roadmap' ),
				'group'   => 'filter',
				'choices' => 'categories',
			),
			'priority'            => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Show only this priority', 'product-roadmap' ),
				'group'   => 'filter',
				'pro'     => true,
				'choices' => array(
					''         => __( 'All priorities', 'product-roadmap' ),
					'low'      => __( 'Low', 'product-roadmap' ),
					'medium'   => __( 'Medium', 'product-roadmap' ),
					'high'     => __( 'High', 'product-roadmap' ),
					'critical' => __( 'Critical', 'product-roadmap' ),
				),
			),
			'date_from'           => array(
				'type'    => 'string',
				'format'  => 'date',
				'default' => '',
				'label'   => __( 'Target date from', 'product-roadmap' ),
				'help'    => __( 'Only items with a target date on or after this day.', 'product-roadmap' ),
				'group'   => 'filter',
				'pro'     => true,
			),
			'date_to'             => array(
				'type'    => 'string',
				'format'  => 'date',
				'default' => '',
				'label'   => __( 'Target date to', 'product-roadmap' ),
				'help'    => __( 'Only items with a target date on or before this day.', 'product-roadmap' ),
				'group'   => 'filter',
				'pro'     => true,
			),
			'sort'                => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Sort by', 'product-roadmap' ),
				'group'   => 'filter',
				'choices' => array(
					''         => __( 'Default', 'product-roadmap' ),
					'votes'    => __( 'Most votes', 'product-roadmap' ),
					'recent'   => __( 'Newest', 'product-roadmap' ),
					'comments' => __( 'Most comments', 'product-roadmap' ),
					'priority' => __( 'Priority', 'product-roadmap' ),
					'title'    => __( 'Title', 'product-roadmap' ),
				),
			),
			'limit'               => array(
				'type'    => 'integer',
				'default' => 0,
				'label'   => __( 'Number of items', 'product-roadmap' ),
				'help'    => __( '0 shows every item, with a Load more button.', 'product-roadmap' ),
				'group'   => 'filter',
				'min'     => 0,
				'max'     => 100,
			),
			'show_title'          => array(
				'type'    => 'boolean',
				'default' => true,
				'label'   => __( 'Show the title', 'product-roadmap' ),
				'group'   => 'display',
			),
			'show_filters'        => array(
				'type'    => 'boolean',
				'default' => true,
				'label'   => __( 'Show the filter bar', 'product-roadmap' ),
				'group'   => 'display',
			),
			'show_search'         => array(
				'type'    => 'boolean',
				'default' => true,
				'label'   => __( 'Show the search box', 'product-roadmap' ),
				'group'   => 'display',
			),
			'show_voting'         => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Voting on this board', 'product-roadmap' ),
				'help'    => __( 'Can hide voting here; it can only be on where voting is enabled in Settings.', 'product-roadmap' ),
				'group'   => 'display',
				'choices' => array(
					''     => __( 'Follow the global setting', 'product-roadmap' ),
					'hide' => __( 'Hide on this board', 'product-roadmap' ),
				),
			),
			'show_comments'       => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Comments on this board', 'product-roadmap' ),
				'help'    => __( 'Can hide comments here; they can only be on where comments are enabled in Settings.', 'product-roadmap' ),
				'group'   => 'display',
				'choices' => array(
					''     => __( 'Follow the global setting', 'product-roadmap' ),
					'hide' => __( 'Hide on this board', 'product-roadmap' ),
				),
			),
			'mode'                => array(
				'type'    => 'string',
				'default' => 'view',
				'label'   => __( 'Mode', 'product-roadmap' ),
				'help'    => __( 'Manage lets editors add and edit items on the page; visitors always see View.', 'product-roadmap' ),
				'group'   => 'advanced',
				'choices' => array(
					'view'   => __( 'View', 'product-roadmap' ),
					'manage' => __( 'Manage', 'product-roadmap' ),
				),
			),
			'allow_frontend_edit' => array(
				'type'    => 'boolean',
				'default' => false,
				'label'   => __( 'Allow editing items on the page', 'product-roadmap' ),
				'group'   => 'advanced',
			),
			'class'               => array(
				'type'    => 'string',
				'default' => '',
				'label'   => __( 'Extra CSS class', 'product-roadmap' ),
				'group'   => 'advanced',
				'block'   => false,
			),
		);
	}

	/**
	 * Resolve a choices token from options() into value => label.
	 *
	 * @param string $token products|templates|statuses|categories.
	 * @return array
	 */
	public static function option_choices( $token ) {
		$terms = array(
			'products'   => array( 'roadmap_product', __( 'Default product', 'product-roadmap' ) ),
			'statuses'   => array( 'roadmap_status', __( 'All statuses', 'product-roadmap' ) ),
			'categories' => array( 'roadmap_category', __( 'All categories', 'product-roadmap' ) ),
		);
		if ( isset( $terms[ $token ] ) ) {
			$choices = array( '' => $terms[ $token ][1] );
			$found   = get_terms(
				array(
					'taxonomy'   => $terms[ $token ][0],
					'hide_empty' => false,
				)
			);
			foreach ( is_wp_error( $found ) ? array() : $found as $term ) {
				$choices[ $term->slug ] = $term->name;
			}
			return $choices;
		}
		if ( 'templates' === $token ) {
			$templates = ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) ? (array) apply_filters( 'roadmap_available_templates', array() ) : array();
			$choices   = array();
			foreach ( $templates as $key => $template ) {
				$choices[ $key ] = is_array( $template ) && isset( $template['name'] ) ? $template['name'] : $key;
			}
			return $choices ? $choices : array( 'minimal' => __( 'Minimal', 'product-roadmap' ) );
		}
		return array();
	}

	/**
	 * Cast and constrain resolved [roadmap] attributes to their option types.
	 *
	 * @param array $atts Attributes after shortcode_atts().
	 * @return array
	 */
	private static function normalize_options( $atts ) {
		foreach ( self::options() as $name => $option ) {
			$value = $atts[ $name ] ?? $option['default'];
			if ( 'boolean' === $option['type'] ) {
				$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} elseif ( 'integer' === $option['type'] ) {
				$value = min( max( absint( $value ), $option['min'] ?? 0 ), $option['max'] ?? PHP_INT_MAX );
			} else {
				$value = sanitize_text_field( (string) $value );
				if ( isset( $option['format'] ) && 'date' === $option['format'] && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
					$value = '';
				}
				// Fixed choice lists are whitelisted; term-based ones stay free-form slugs.
				if ( isset( $option['choices'] ) && is_array( $option['choices'] ) && ! array_key_exists( $value, $option['choices'] ) ) {
					$value = $option['default'];
				}
			}
			$atts[ $name ] = $value;
		}
		return $atts;
	}

	public function render_shortcode( $atts ) {
		$atts = self::normalize_options( shortcode_atts( wp_list_pluck( self::options(), 'default' ), $atts, 'roadmap' ) );

		// Visitors always get a working board. A missing product, or one the
		// shortcode parser could not read (a curly quote pasted from an email),
		// shows the default product; configuration messages go to editors only.
		$is_pro = defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE;
		$notice = '';
		if ( empty( $atts['product'] ) ) {
			// roadmap_default_product is never written - the owner's actual
			// default lives in roadmap_default_product_id (a term ID). Resolve
			// it to a slug; fall back to 'main' when none has been configured.
			$atts['product'] = 'main';
			if ( $is_pro ) {
				$default_product_id = (int) get_option( 'roadmap_default_product_id' );
				if ( $default_product_id ) {
					$default_term = get_term( $default_product_id, 'roadmap_product' );
					if ( $default_term && ! is_wp_error( $default_term ) ) {
						$atts['product'] = $default_term->slug;
					}
				}
			}
		} elseif ( ! $is_pro && 'main' !== $atts['product'] ) {
			// Free has one board, 'main' (e.g. a shortcode left over from Pro).
			$notice          = $this->editor_notice( __( 'Multiple products require the Pro version. Upgrade to use different products.', 'product-roadmap' ) );
			$atts['product'] = 'main';
		}

		// A template that isn't offered (Pro inactive, a template the owner
		// switched off, a typo) renders the default one, and minimal if the
		// default itself isn't offered.
		$available = self::option_choices( 'templates' );
		if ( ! isset( $available[ $atts['template'] ] ) ) {
			$default          = self::get_default_template();
			$atts['template'] = isset( $available[ $default ] ) ? $default : 'minimal';
		}

		// Check permissions for manage mode
		if ( $atts['mode'] === 'manage' && ! current_user_can( 'edit_posts' ) ) {
			$atts['mode'] = 'view';
		}

		// Enqueue assets
		$this->enqueue_assets();

		// Prepare data for Vue app
		$global_settings = Roadmap_Settings::get();

		$app_data = array(
			'api_url'             => rest_url( 'roadmap/v1' ),
			'nonce'               => wp_create_nonce( 'wp_rest' ),
			'mode'                => $atts['mode'],
			'allow_frontend_edit' => $atts['allow_frontend_edit'],
			'product'             => $atts['product'],
			'template'            => $atts['template'],
			'default_sort'        => $atts['sort'],
			'status'              => $atts['status'],
			'category'            => $atts['category'],
			'priority'            => $atts['priority'],
			'date_from'           => $atts['date_from'],
			'date_to'             => $atts['date_to'],
			'limit'               => $atts['limit'],
			'show_filters'        => $atts['show_filters'],
			'show_title'          => $atts['show_title'],
			'show_search'         => $atts['show_search'],
			'is_pro'              => ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ),
			'settings'            => array(
				// A board can hide voting/comments, never turn on what Settings turned off
				// (the REST API would reject it).
				'enable_voting'      => $global_settings['enable_voting'] && 'hide' !== $atts['show_voting'],
				'allow_guest_voting' => (bool) $global_settings['allow_guest_voting'],
				'enable_comments'    => $global_settings['enable_comments'] && 'hide' !== $atts['show_comments'],
				'enable_suggestions' => (bool) $global_settings['enable_suggestions'],
				'items_per_page'     => (int) $global_settings['items_per_page'],
			),
			'user'                => $this->get_user_data(),
			'i18n'                => $this->get_translations(),
		);

		$app_data = apply_filters( 'roadmap_shortcode_data', $app_data, $atts );

		// Generate unique ID for multiple instances
		$app_id = 'roadmap-app-' . uniqid();

		ob_start();
		// Apply the site owner's saved per-product Theme Color, scoped to this instance.
		echo $this->accent_inline_style( $app_id, $this->get_product_accent_color( $atts['product'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- returns escaped, self-contained <style>.
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

		return $notice . ob_get_clean();
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

		// Free has one product: show its changelog to visitors, tell editors why.
		$notice = '';
		if ( ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) && ! empty( $atts['product'] ) && 'main' !== $atts['product'] ) {
			$notice          = $this->editor_notice( __( 'Multiple products require the Pro version.', 'product-roadmap' ) );
			$atts['product'] = 'main';
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
			return $notice . '<div class="roadmap-changelog-empty">' . __( 'No changelog entries found.', 'product-roadmap' ) . '</div>';
		}

		// Get change types for styling
		$change_types = Roadmap_Changelog::get_change_types();

		$show_date   = filter_var( $atts['show_date'], FILTER_VALIDATE_BOOLEAN );
		$collapsible = filter_var( $atts['collapsible'], FILTER_VALIDATE_BOOLEAN );

		// Enqueue changelog styles
		$this->enqueue_changelog_assets();

		$changelog_id = 'roadmap-changelog-' . uniqid();

		ob_start();
		// Apply the site owner's saved per-product Theme Color, scoped to this instance.
		echo $this->accent_inline_style( $changelog_id, $this->get_product_accent_color( $atts['product'] ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- returns escaped, self-contained <style>.
		?>
		<div id="<?php echo esc_attr( $changelog_id ); ?>" class="roadmap-changelog <?php echo esc_attr( $atts['class'] ); ?>" <?php echo $collapsible ? 'data-collapsible="true"' : ''; ?>>
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

		return $notice . ob_get_clean();
	}

	/**
	 * Resolve a product slug to its saved per-product accent (Theme Color) setting.
	 *
	 * Site owners set this under Product Roadmap > Products > Settings; it is stored
	 * in the `roadmap_product_settings` term meta on the roadmap_product taxonomy.
	 * Returns a sanitized hex color, or '' when none is set (the frontend then falls
	 * back to the site theme's --wp--preset--color--primary).
	 *
	 * @param string $product_slug Product slug from the shortcode `product` attribute.
	 * @return string Sanitized hex color string, or '' if unset/invalid.
	 */
	private function get_product_accent_color( $product_slug ) {
		if ( empty( $product_slug ) ) {
			return '';
		}

		$term = get_term_by( 'slug', $product_slug, 'roadmap_product' );
		if ( ! $term instanceof WP_Term ) {
			return '';
		}

		$settings = get_term_meta( $term->term_id, 'roadmap_product_settings', true );
		if ( empty( $settings['theme_color'] ) ) {
			return '';
		}

		return (string) sanitize_hex_color( $settings['theme_color'] );
	}

	/**
	 * Build a scoped inline <style> that overrides the roadmap accent for one instance.
	 *
	 * Only --roadmap-accent is set: roadmap-tokens.css recomputes the derived tokens
	 * (hover/tint/text/contrast) on .roadmap-app and .roadmap-changelog, so this single
	 * override reaches the whole instance. Returns '' when no color is given, leaving
	 * the theme-driven default.
	 *
	 * @param string $element_id Container element id to scope the override to.
	 * @param string $color      Sanitized hex color, or '' to skip.
	 * @return string Safe <style> markup, or ''.
	 */
	private function accent_inline_style( $element_id, $color ) {
		if ( empty( $color ) || empty( $element_id ) ) {
			return '';
		}

		$id    = esc_attr( $element_id );
		$color = esc_attr( $color );

		return sprintf(
			'<style>#%1$s{--roadmap-accent:%2$s;}</style>',
			$id,
			$color
		);
	}

	/**
	 * Enqueue changelog-specific assets
	 */
	private function enqueue_changelog_assets() {
		$version = ROADMAP_VERSION;

		// Inline CSS for changelog
		wp_register_style( 'roadmap-changelog', false, array( 'roadmap-tokens' ), $version );
		wp_enqueue_style( 'roadmap-changelog' );
		wp_add_inline_style(
			'roadmap-changelog',
			'
            .roadmap-changelog {
                max-width: 800px;
                margin: 0 auto;
            }
            .roadmap-changelog-version {
                background: var(--rm-surface);
                border: 1px solid var(--rm-border);
                border-radius: 8px;
                margin-bottom: 20px;
                overflow: hidden;
            }
            .roadmap-changelog-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 20px;
                background: var(--rm-surface-alt);
                border-bottom: 1px solid var(--rm-border);
            }
            .roadmap-changelog-title {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
                color: var(--rm-heading);
            }
            .roadmap-changelog-title .version-number {
                color: var(--rm-heading);
            }
            .roadmap-changelog-date {
                color: var(--rm-muted);
                font-size: 14px;
            }
            .roadmap-changelog-content {
                padding: 20px;
                background: var(--rm-surface);
            }
            .roadmap-changelog-description {
                margin-bottom: 16px;
                color: var(--rm-text);
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
                border-bottom: 1px solid var(--rm-border-soft);
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
                color: var(--rm-text);
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
                color: var(--rm-muted);
            }
            /* Release type accents, from the semantic status palette. */
            .changelog-major .roadmap-changelog-header {
                border-left: 4px solid var(--rm-cancelled);
            }
            .changelog-minor .roadmap-changelog-header {
                border-left: 4px solid var(--rm-planned);
            }
            .changelog-patch .roadmap-changelog-header {
                border-left: 4px solid var(--rm-done);
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
				array( 'roadmap-tokens' ),
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
			// Bust the browser cache on every rebuilt bundle, not just on a version
			// bump: a hotfix rebuild under the same tag otherwise serves the stale
			// asset to anyone who already cached it. filemtime() changes whenever the
			// file is rewritten and falls back to the plugin version if unreadable.
			$js_path  = ROADMAP_PLUGIN_PATH . 'assets/js/roadmap-app.js';
			$css_path = ROADMAP_PLUGIN_PATH . 'assets/css/roadmap-app.css';
			$js_ver   = file_exists( $js_path ) ? filemtime( $js_path ) : $version;
			$css_ver  = file_exists( $css_path ) ? filemtime( $css_path ) : $version;

			// No 'wp-api' dependency: the Vue app talks to REST via fetch(), and
			// depending on wp-api loads wp-includes/js/wp-api.min.js, which throws
			// "_.each is not a function" on newer WordPress/Underscore builds.
			wp_enqueue_script(
				'roadmap-vue-app',
				ROADMAP_PLUGIN_URL . 'assets/js/roadmap-app.js',
				array(),
				$js_ver,
				true
			);

			wp_enqueue_style(
				'roadmap-vue-app',
				ROADMAP_PLUGIN_URL . 'assets/css/roadmap-app.css',
				array( 'roadmap-tokens' ),
				$css_ver
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
		$item_labels   = Roadmap_Settings::item_labels();
		$item_singular = $item_labels['singular'];
		$item_plural   = $item_labels['plural'];
		$product_name  = get_option( 'roadmap_product_name', 'Product' );

		return array(
			'loading'              => __( 'Loading...', 'product-roadmap' ),
			'error'                => __( 'An error occurred', 'product-roadmap' ),
			'no_items'             => sprintf(
				/* translators: %s: Plural item label (lowercase) */
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
				/* translators: %s: Singular item label */
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
			'confirm_delete_label' => __( 'Confirm delete', 'product-roadmap' ),
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
				__( '%s moved to the trash.', 'product-roadmap' ),
				$item_singular
			),
			'vote_submitted'       => __( 'Your vote has been submitted', 'product-roadmap' ),
			'vote_removed'         => __( 'Your vote has been removed', 'product-roadmap' ),
			'suggested'            => __( 'Suggested', 'product-roadmap' ),
			'user_suggested_title' => __( 'Suggested by a user', 'product-roadmap' ),
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
			'title_az'             => __( 'Title (A-Z)', 'product-roadmap' ),
			// Frontend UI strings (1.7.0 translation completion).
			'roadmap' => __( 'Roadmap', 'product-roadmap' ),
			'roadmap_timeline' => __( 'Product Roadmap Timeline', 'product-roadmap' ),
			'current_quarter' => __( 'Current Quarter', 'product-roadmap' ),
			'next_6_months' => __( 'Next 6 Months', 'product-roadmap' ),
			'next_year' => __( 'Next Year', 'product-roadmap' ),
			'all_time' => __( 'All Time', 'product-roadmap' ),
			'now' => __( 'Now', 'product-roadmap' ),
			'next' => __( 'Next', 'product-roadmap' ),
			'later' => __( 'Later', 'product-roadmap' ),
			'done' => __( 'Done', 'product-roadmap' ),
			'past' => __( 'Past', 'product-roadmap' ),
			'and_beyond' => __( '& Beyond', 'product-roadmap' ),
			'in_progress' => __( 'in progress', 'product-roadmap' ),
			'completed' => __( 'completed', 'product-roadmap' ),
			'total_items' => __( 'total items', 'product-roadmap' ),
			'no_items_in' => __( 'No items in', 'product-roadmap' ),
			'no_items_in_progress' => __( 'No items in progress', 'product-roadmap' ),
			'no_upcoming_items' => __( 'No upcoming items', 'product-roadmap' ),
			'no_items_in_backlog' => __( 'No items in backlog', 'product-roadmap' ),
			'drag_items_hint' => __( 'Drag items here or create new ones', 'product-roadmap' ),
			'items_appear_hint' => __( 'Items will appear here when added', 'product-roadmap' ),
			'show_completed_items' => __( 'Show completed items', 'product-roadmap' ),
			'you_have_voted' => __( 'You have voted for this item', 'product-roadmap' ),
			'voted' => __( 'Voted', 'product-roadmap' ),
			'all' => __( 'All', 'product-roadmap' ),
			'add_comment' => __( 'Add Comment', 'product-roadmap' ),
			'post_comment' => __( 'Post Comment', 'product-roadmap' ),
			'add_new_item' => __( 'Add New Item', 'product-roadmap' ),
			'categories' => __( 'Categories', 'product-roadmap' ),
			'none' => __( 'None', 'product-roadmap' ),
			'close' => __( 'Close', 'product-roadmap' ),
			'views' => __( 'Views', 'product-roadmap' ),
			'assigned_to' => __( 'Assigned To', 'product-roadmap' ),
			'what_was_delivered' => __( 'What was delivered', 'product-roadmap' ),
			'github_issue_url' => __( 'GitHub Issue URL', 'product-roadmap' ),
			'view_details_comments' => __( 'View Details & Comments', 'product-roadmap' ),
			'select_effort' => __( '-- Select Effort --', 'product-roadmap' ),
			'effort_xs' => __( 'XS - Very Small (1-2 days)', 'product-roadmap' ),
			'effort_s' => __( 'S - Small (3-5 days)', 'product-roadmap' ),
			'effort_m' => __( 'M - Medium (1-2 weeks)', 'product-roadmap' ),
			'effort_l' => __( 'L - Large (2-4 weeks)', 'product-roadmap' ),
			'effort_xl' => __( 'XL - Extra Large (1-2 months)', 'product-roadmap' ),
			'effort_xxl' => __( 'XXL - Huge (2+ months)', 'product-roadmap' ),
			'your_name' => __( 'Your Name', 'product-roadmap' ),
			'enter_name' => __( 'Enter your name', 'product-roadmap' ),
			'your_email' => __( 'Your Email', 'product-roadmap' ),
			'enter_email' => __( 'Enter your email address', 'product-roadmap' ),
			'priority_low_hint' => __( 'Nice to have', 'product-roadmap' ),
			'priority_medium_hint' => __( 'Important', 'product-roadmap' ),
			'priority_high_hint' => __( 'Very important', 'product-roadmap' ),
			'priority_critical_hint' => __( 'Must have', 'product-roadmap' ),
			'submit_suggestion' => __( 'Submit Suggestion', 'product-roadmap' ),
			'auto_layout' => __( 'Auto Layout', 'product-roadmap' ),
			'columns_label' => __( 'Columns', 'product-roadmap' ),
			'created_label' => __( 'Created', 'product-roadmap' ),
			'idea' => __( 'idea', 'product-roadmap' ),
			'ideas' => __( 'ideas', 'product-roadmap' ),
			'no_ideas_yet' => __( 'No ideas yet', 'product-roadmap' ),
			'be_first_suggest' => __( 'Be the first to suggest a feature!', 'product-roadmap' ),
			'top_voted' => __( 'Top Voted', 'product-roadmap' ),
			'newest' => __( 'Newest', 'product-roadmap' ),
			'today' => __( 'Today', 'product-roadmap' ),
			'yesterday' => __( 'Yesterday', 'product-roadmap' ),
			'days_ago' => __( 'days ago', 'product-roadmap' ),
			'weeks_ago' => __( 'weeks ago', 'product-roadmap' ),
			'months_ago' => __( 'months ago', 'product-roadmap' ),
			'group_by_status' => __( 'Group by Status', 'product-roadmap' ),
			'group_by_category' => __( 'Group by Category', 'product-roadmap' ),
			'group_by_priority' => __( 'Group by Priority', 'product-roadmap' ),
			'no_grouping' => __( 'No Grouping', 'product-roadmap' ),
			'sort_by_created' => __( 'Sort by Created Date', 'product-roadmap' ),
			'sort_by_votes' => __( 'Sort by Votes', 'product-roadmap' ),
			'sort_by_priority' => __( 'Sort by Priority', 'product-roadmap' ),
			'sort_by_title' => __( 'Sort by Title', 'product-roadmap' ),
			'load_more' => __( 'Load More', 'product-roadmap' ),
			'loading_changelog' => __( 'Loading changelog...', 'product-roadmap' ),
			'retry' => __( 'Retry', 'product-roadmap' ),
			'no_changelog' => __( 'No changelog entries found.', 'product-roadmap' ),
			'change' => __( 'change', 'product-roadmap' ),
			'changes' => __( 'changes', 'product-roadmap' ),
			'network_error' => __( 'Network error', 'product-roadmap' ),
			'api_call_failed' => __( 'API call failed', 'product-roadmap' ),
			'failed_load_changelog' => __( 'Failed to load changelog', 'product-roadmap' ),
			'load_item_failed' => __( 'Failed to load item for editing', 'product-roadmap' ),
			// Custom labels
			'item_singular'        => $item_singular,
			'item_plural'          => $item_plural,
			'product_name'         => $product_name,
		);
	}

	private function can_user_vote() {
		$global_settings      = Roadmap_Settings::get();
		$voting_enabled       = (bool) $global_settings['enable_voting'];
		$guest_voting_allowed = (bool) $global_settings['allow_guest_voting'];

		return $voting_enabled && ( $guest_voting_allowed || is_user_logged_in() );
	}

	private function can_user_comment() {
		$global_settings  = Roadmap_Settings::get();
		$comments_enabled = (bool) $global_settings['enable_comments'];

		return is_user_logged_in() && $comments_enabled;
	}

	private function can_user_suggest() {
		$global_settings     = Roadmap_Settings::get();
		$suggestions_enabled = (bool) $global_settings['enable_suggestions'];
		$requires_login      = $global_settings['suggestion_requires_login'] ?? true;

		return $suggestions_enabled && ( $requires_login ? is_user_logged_in() : true );
	}

	/**
	 * A shortcode configuration message for people who can fix the page.
	 *
	 * Visitors never see it: they get the working fallback board instead.
	 *
	 * @param string $message Message text.
	 * @return string Notice HTML, or '' for visitors.
	 */
	private function editor_notice( $message ) {
		$post_id = get_the_ID();
		$can_fix = $post_id ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
		return $can_fix ? '<div class="roadmap-error">' . esc_html( $message ) . '</div>' : '';
	}
}
