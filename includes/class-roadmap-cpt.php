<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Roadmap_CPT {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'register_block_meta' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_roadmap_item', array( $this, 'save_meta_boxes' ), 10, 2 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
		add_action( 'wp_insert_post', array( $this, 'set_default_product_if_missing' ), 10, 2 );

		// Custom permalink structure
		add_filter( 'post_type_link', array( $this, 'custom_roadmap_permalinks' ), 10, 2 );
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_action( 'wp_loaded', array( $this, 'maybe_flush_pending_rules' ) );
		add_action( 'transition_post_status', array( $this, 'maybe_flush_for_page' ), 10, 3 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_roadmap_by_product' ) );

		// Custom template
		add_filter( 'template_include', array( $this, 'custom_template' ) );

		// Pro hooks
		add_filter( 'roadmap_item_meta_fields', array( $this, 'filter_meta_fields' ) );

		// Admin post status views
		add_filter( 'views_edit-roadmap_item', array( $this, 'add_pending_post_status_view' ) );

		// Quick approve/reject actions
		// Show a Source column so admin-created and user-suggested items are easy to tell apart.
		add_filter( 'manage_roadmap_item_posts_columns', array( $this, 'add_source_column' ) );
		add_action( 'manage_roadmap_item_posts_custom_column', array( $this, 'render_source_column' ), 10, 2 );

		add_filter( 'post_row_actions', array( $this, 'add_quick_approve_actions' ), 10, 2 );
		add_action( 'admin_action_approve_roadmap_item', array( $this, 'handle_approve_action' ) );
		add_action( 'admin_action_reject_roadmap_item', array( $this, 'handle_reject_action' ) );
		add_action( 'admin_notices', array( $this, 'show_action_notices' ) );

		// Restoring a trashed item (board delete, rejected suggestion) puts it
		// back where it was, not into Draft (the core default since 5.6).
		add_filter( 'wp_untrash_post_status', array( $this, 'untrash_to_previous_status' ), 10, 3 );
	}

	/**
	 * Restore roadmap items to their status before they were trashed.
	 *
	 * @param string $new_status      Status core would restore to.
	 * @param int    $post_id         Post ID.
	 * @param string $previous_status Status before trashing.
	 * @return string
	 */
	public function untrash_to_previous_status( $new_status, $post_id, $previous_status ) {
		return 'roadmap_item' === get_post_type( $post_id ) && $previous_status ? $previous_status : $new_status;
	}

	public function register_post_types() {
		// Get custom labels
		$item_labels   = Roadmap_Settings::item_labels();
		$item_singular = $item_labels['singular'];
		$item_plural   = $item_labels['plural'];
		$product_name  = get_option( 'roadmap_product_name', 'Product' );

		$labels = array(
			'name'               => sprintf(
				/* translators: %s: Product name */
				__( '%s Roadmap', 'product-roadmap' ),
				$product_name
			),
			'singular_name'      => $item_singular,
			'menu_name'          => sprintf(
				/* translators: %s: Product name */
				__( '%s Roadmap', 'product-roadmap' ),
				$product_name
			),
			'add_new'            => __( 'Add New', 'product-roadmap' ),
			'add_new_item'       => sprintf(
				/* translators: %s: Singular item label */
				__( 'Add New %s', 'product-roadmap' ),
				$item_singular
			),
			'edit_item'          => sprintf(
				/* translators: %s: Singular item label */
				__( 'Edit %s', 'product-roadmap' ),
				$item_singular
			),
			'new_item'           => sprintf(
				/* translators: %s: Singular item label */
				__( 'New %s', 'product-roadmap' ),
				$item_singular
			),
			'view_item'          => sprintf(
				/* translators: %s: Singular item label */
				__( 'View %s', 'product-roadmap' ),
				$item_singular
			),
			'search_items'       => sprintf(
				/* translators: %s: Plural item label */
				__( 'Search %s', 'product-roadmap' ),
				$item_plural
			),
			'not_found'          => sprintf(
				/* translators: %s: Plural item label (lowercase) */
				__( 'No %s found', 'product-roadmap' ),
				strtolower( $item_plural )
			),
			'not_found_in_trash' => sprintf(
				/* translators: %s: Plural item label (lowercase) */
				__( 'No %s found in trash', 'product-roadmap' ),
				strtolower( $item_plural )
			),
			'all_items'          => sprintf(
				/* translators: %s: Plural item label */
				__( 'All %s', 'product-roadmap' ),
				$item_plural
			),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => false, // We'll handle this with custom rules
			'capability_type'    => 'post',
			'has_archive'        => false, // Archive will be product-specific
			'hierarchical'       => false,
			'menu_position'      => 25,
			'menu_icon'          => 'dashicons-chart-line',
			'supports'           => array( 'title', 'editor', 'author', 'comments' ),
			'show_in_rest'       => true,
			'rest_base'          => 'roadmap-items',
		);

		$args = apply_filters( 'roadmap_item_post_type_args', $args );

		register_post_type( 'roadmap_item', $args );
	}

	public function register_taxonomies() {
		// Status taxonomy
		$status_labels = array(
			'name'          => __( 'Statuses', 'product-roadmap' ),
			'singular_name' => __( 'Status', 'product-roadmap' ),
			'search_items'  => __( 'Search Statuses', 'product-roadmap' ),
			'all_items'     => __( 'All Statuses', 'product-roadmap' ),
			'edit_item'     => __( 'Edit Status', 'product-roadmap' ),
			'update_item'   => __( 'Update Status', 'product-roadmap' ),
			'add_new_item'  => __( 'Add New Status', 'product-roadmap' ),
			'new_item_name' => __( 'New Status Name', 'product-roadmap' ),
			'menu_name'     => __( 'Statuses', 'product-roadmap' ),
		);

		$status_args = array(
			'hierarchical'      => true,
			'labels'            => $status_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'roadmap-status' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'roadmap_status', array( 'roadmap_item' ), $status_args );

		// Category taxonomy
		$category_labels = array(
			'name'          => __( 'Categories', 'product-roadmap' ),
			'singular_name' => __( 'Category', 'product-roadmap' ),
			'search_items'  => __( 'Search Categories', 'product-roadmap' ),
			'all_items'     => __( 'All Categories', 'product-roadmap' ),
			'edit_item'     => __( 'Edit Category', 'product-roadmap' ),
			'update_item'   => __( 'Update Category', 'product-roadmap' ),
			'add_new_item'  => __( 'Add New Category', 'product-roadmap' ),
			'new_item_name' => __( 'New Category Name', 'product-roadmap' ),
			'menu_name'     => __( 'Categories', 'product-roadmap' ),
		);

		$category_args = array(
			'hierarchical'      => true,
			'labels'            => $category_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'roadmap-category' ),
			'show_in_rest'      => true,
		);

		register_taxonomy( 'roadmap_category', array( 'roadmap_item' ), $category_args );

		// Product taxonomy (even in free version, but limited to one)
		$product_labels = array(
			'name'          => __( 'Products', 'product-roadmap' ),
			'singular_name' => __( 'Product', 'product-roadmap' ),
			'search_items'  => __( 'Search Products', 'product-roadmap' ),
			'all_items'     => __( 'All Products', 'product-roadmap' ),
			'edit_item'     => __( 'Edit Product', 'product-roadmap' ),
			'update_item'   => __( 'Update Product', 'product-roadmap' ),
			'add_new_item'  => __( 'Add New Product', 'product-roadmap' ),
			'new_item_name' => __( 'New Product Name', 'product-roadmap' ),
			'menu_name'     => __( 'Products', 'product-roadmap' ),
		);

		$product_args = array(
			'hierarchical'      => true,
			'labels'            => $product_labels,
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'roadmap-product' ),
			'show_in_rest'      => true,
			'capabilities'      => array(
				'manage_terms' => ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) ? 'manage_categories' : 'do_not_allow',
				'edit_terms'   => ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) ? 'manage_categories' : 'do_not_allow',
				'delete_terms' => ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) ? 'manage_categories' : 'do_not_allow',
				'assign_terms' => 'edit_posts',
			),
		);

		register_taxonomy( 'roadmap_product', array( 'roadmap_item' ), $product_args );

		// Insert default terms only on activation or version upgrade (not every page load)
		$this->maybe_insert_default_terms();
	}

	/**
	 * Check if default terms need to be inserted
	 *
	 * This prevents database queries on every page load.
	 */
	private function maybe_insert_default_terms() {
		$terms_version = get_option( 'roadmap_terms_version', '0' );

		// Only insert terms on first install or version upgrade
		if ( version_compare( $terms_version, ROADMAP_VERSION, '<' ) ) {
			$this->insert_default_terms();
			update_option( 'roadmap_terms_version', ROADMAP_VERSION, false );
		}
	}

	/**
	 * Insert default taxonomy terms
	 *
	 * Called during activation or version upgrade only.
	 */
	private function insert_default_terms() {
		$default_statuses = array(
			'planned'      => __( 'Planned', 'product-roadmap' ),
			'in-progress'  => __( 'In Progress', 'product-roadmap' ),
			'completed'    => __( 'Completed', 'product-roadmap' ),
			'on-hold'      => __( 'On Hold', 'product-roadmap' ),
			'under-review' => __( 'Under Review', 'product-roadmap' ),
		);

		foreach ( $default_statuses as $slug => $name ) {
			if ( ! term_exists( $slug, 'roadmap_status' ) ) {
				wp_insert_term( $name, 'roadmap_status', array( 'slug' => $slug ) );
			}
		}

		// Insert default product term
		$default_product = get_option( 'roadmap_default_product', 'main' );
		if ( ! term_exists( $default_product, 'roadmap_product' ) ) {
			// Use the product name from settings if available
			$product_name = get_option( 'roadmap_product_name', __( 'Main Product', 'product-roadmap' ) );
			$term         = wp_insert_term(
				$product_name,
				'roadmap_product',
				array( 'slug' => $default_product )
			);

			if ( ! is_wp_error( $term ) ) {
				update_option( 'roadmap_default_product_id', $term['term_id'] );
			}
		}
	}

	public function add_meta_boxes() {
		add_meta_box(
			'roadmap_item_details',
			__( 'Roadmap Item Details', 'product-roadmap' ),
			array( $this, 'render_meta_box' ),
			'roadmap_item',
			'normal',
			'high'
		);
	}

	public function render_meta_box( $post ) {
		wp_nonce_field( 'roadmap_item_meta_box', 'roadmap_item_meta_box_nonce' );

		$fields       = $this->get_meta_fields();
		$basic_fields = array_filter(
			$fields,
			function ( $field ) {
				return empty( $field['pro'] );
			}
		);
		$pro_fields   = array_filter(
			$fields,
			function ( $field ) {
				return ! empty( $field['pro'] );
			}
		);

		// Basic Fields Section
		if ( ! empty( $basic_fields ) ) {
			?>
			<div class="roadmap-meta-section">
				<?php
				foreach ( $basic_fields as $field ) :
					$value = get_post_meta( $post->ID, $field['id'], true );

					// Use default value if empty
					if ( empty( $value ) && isset( $field['default'] ) ) {
						$value = $field['default'];
					}
					?>
					<p>
						<label for="<?php echo esc_attr( $field['id'] ); ?>">
							<?php echo esc_html( $field['label'] ); ?>:
						</label>
						<br>
						<?php $this->render_field( $field, $value ); ?>
						<?php if ( ! empty( $field['description'] ) ) : ?>
							<span class="description"><?php echo esc_html( $field['description'] ); ?></span>
						<?php endif; ?>
					</p>
				<?php endforeach; ?>
			</div>
			<?php
		}

		// Pro Fields Section
		if ( ! empty( $pro_fields ) ) {
			?>
			<div class="roadmap-meta-section">
				<h3><?php esc_html_e( 'Pro Features', 'product-roadmap' ); ?></h3>
				<?php
				foreach ( $pro_fields as $field ) :
					$value = get_post_meta( $post->ID, $field['id'], true );

					// Use default value if empty
					if ( empty( $value ) && isset( $field['default'] ) ) {
						$value = $field['default'];
					}
					?>
					<p>
						<label for="<?php echo esc_attr( $field['id'] ); ?>">
							<?php echo esc_html( $field['label'] ); ?>:
						</label>
						<br>
						<?php $this->render_field( $field, $value ); ?>
						<?php if ( ! empty( $field['description'] ) ) : ?>
							<span class="description"><?php echo esc_html( $field['description'] ); ?></span>
						<?php endif; ?>
					</p>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}

	private function get_meta_fields() {
		$fields = array(
			array(
				'id'          => '_roadmap_priority',
				'label'       => __( 'Priority', 'product-roadmap' ),
				'type'        => 'select',
				'options'     => array(
					''         => __( '-- Select Priority --', 'product-roadmap' ),
					'low'      => __( 'Low', 'product-roadmap' ),
					'medium'   => __( 'Medium', 'product-roadmap' ),
					'high'     => __( 'High', 'product-roadmap' ),
					'critical' => __( 'Critical', 'product-roadmap' ),
				),
				'description' => __( 'Set the priority level for this item.', 'product-roadmap' ),
				'free'        => true,
			),
			array(
				'id'          => '_roadmap_progress',
				'label'       => __( 'Progress', 'product-roadmap' ),
				'type'        => 'range',
				'min'         => 0,
				'max'         => 100,
				'step'        => 5,
				'default'     => 0,
				'description' => __( 'Current completion percentage (0-100%)', 'product-roadmap' ),
				'free'        => true,
			),
			array(
				'id'          => '_roadmap_completion_notes',
				'label'       => __( 'Completion Notes', 'product-roadmap' ),
				'type'        => 'textarea',
				'description' => __( 'Notes about what was completed (shown to customers on completed items)', 'product-roadmap' ),
				'free'        => true,
			),
			array(
				'id'          => '_roadmap_target_date',
				'label'       => __( 'Target Date', 'product-roadmap' ),
				'type'        => 'date',
				'description' => __( 'Expected completion date', 'product-roadmap' ),
				'free'        => true,
			),
			array(
				'id'          => '_roadmap_start_date',
				'label'       => __( 'Start Date', 'product-roadmap' ),
				'type'        => 'date',
				'description' => __( 'When work begins on this item', 'product-roadmap' ),
				'pro'         => true,
			),
			array(
				'id'          => '_roadmap_end_date',
				'label'       => __( 'End Date', 'product-roadmap' ),
				'type'        => 'date',
				'description' => __( 'When work was completed', 'product-roadmap' ),
				'pro'         => true,
			),
			array(
				'id'          => '_roadmap_effort_estimate',
				'label'       => __( 'Effort Estimate', 'product-roadmap' ),
				'type'        => 'select',
				'options'     => array(
					''    => __( '-- Select Effort --', 'product-roadmap' ),
					'xs'  => __( 'XS - Very Small (1-2 days)', 'product-roadmap' ),
					's'   => __( 'S - Small (3-5 days)', 'product-roadmap' ),
					'm'   => __( 'M - Medium (1-2 weeks)', 'product-roadmap' ),
					'l'   => __( 'L - Large (2-4 weeks)', 'product-roadmap' ),
					'xl'  => __( 'XL - Extra Large (1-2 months)', 'product-roadmap' ),
					'xxl' => __( 'XXL - Huge (2+ months)', 'product-roadmap' ),
				),
				'description' => __( 'Estimated effort required', 'product-roadmap' ),
				'pro'         => true,
			),
			array(
				'id'          => '_roadmap_github_issue',
				'label'       => __( 'GitHub Issue URL', 'product-roadmap' ),
				'type'        => 'url',
				'placeholder' => '',
				'description' => __( 'Link to related GitHub issue', 'product-roadmap' ),
				'pro'         => true,
			),
			array(
				'id'          => '_roadmap_external_link',
				'label'       => __( 'External Link', 'product-roadmap' ),
				'type'        => 'url',
				'placeholder' => '',
				'description' => __( 'Link to external documentation or reference', 'product-roadmap' ),
				'free'        => true,
			),
			array(
				'id'          => '_roadmap_votes',
				'label'       => __( 'Vote Count', 'product-roadmap' ),
				'type'        => 'number',
				'readonly'    => true,
				'description' => __( 'Total number of votes (automatically calculated)', 'product-roadmap' ),
				'free'        => true,
			),
		);

		return apply_filters( 'roadmap_item_meta_fields', $fields );
	}

	private function render_field( $field, $value ) {
		$readonly    = isset( $field['readonly'] ) && $field['readonly'] ? 'readonly' : '';
		$is_pro      = isset( $field['pro'] ) && $field['pro'];
		$is_disabled = $is_pro && ! defined( 'ROADMAP_PRO_VERSION' );
		$placeholder = isset( $field['placeholder'] ) ? $field['placeholder'] : '';

		// Check if field should be disabled for free version
		if ( $is_disabled ) {
			?>
			<div class="roadmap-pro-field-wrapper">
				<input type="text" 
						value="<?php esc_attr_e( 'Pro Feature', 'product-roadmap' ); ?>" 
						class="widefat" 
						disabled>
				<span class="roadmap-pro-badge">PRO</span>
			</div>
			<?php
			return;
		}

		switch ( $field['type'] ) {
			case 'text':
			case 'date':
			case 'number':
			case 'url':
				?>
				<input type="<?php echo esc_attr( $field['type'] ); ?>" 
						id="<?php echo esc_attr( $field['id'] ); ?>" 
						name="<?php echo esc_attr( $field['id'] ); ?>" 
						value="<?php echo esc_attr( $value ); ?>" 
						placeholder="<?php echo esc_attr( $placeholder ); ?>"
						class="widefat"
						<?php echo esc_attr( $readonly ); ?>>
				<?php
				break;

			case 'range':
				$min  = isset( $field['min'] ) ? $field['min'] : 0;
				$max  = isset( $field['max'] ) ? $field['max'] : 100;
				$step = isset( $field['step'] ) ? $field['step'] : 1;
				?>
				<div class="roadmap-range-wrapper">
					<input type="range" 
							id="<?php echo esc_attr( $field['id'] ); ?>" 
							name="<?php echo esc_attr( $field['id'] ); ?>" 
							value="<?php echo esc_attr( $value ); ?>" 
							min="<?php echo esc_attr( $min ); ?>"
							max="<?php echo esc_attr( $max ); ?>"
							step="<?php echo esc_attr( $step ); ?>"
							class="roadmap-range-input"
							<?php echo esc_attr( $readonly ); ?>>
					<span class="roadmap-range-value"><?php echo esc_html( $value ); ?>%</span>
				</div>
				<?php
				break;

			case 'select':
				$is_multiple     = isset( $field['multiple'] ) && $field['multiple'];
				$name_attr       = $is_multiple ? $field['id'] . '[]' : $field['id'];
				$selected_values = $is_multiple && is_array( $value ) ? $value : array( $value );
				// Option keys arrive as ints (e.g. $options[ $user->ID ]) while post
				// meta always reads back as a string, so a strict in_array() below
				// never matches and the field re-renders as unselected. That is not
				// just cosmetic: the blank control then posts an empty value on the
				// next save and destroys the stored one. Compare as strings.
				$selected_values = array_map( 'strval', $selected_values );
				?>
				<select id="<?php echo esc_attr( $field['id'] ); ?>"
						name="<?php echo esc_attr( $name_attr ); ?>"
						class="widefat"
						<?php echo $is_multiple ? 'multiple' : ''; ?>
						<?php echo esc_attr( $readonly ); ?>>
					<?php foreach ( $field['options'] as $option_value => $option_label ) : ?>
						<option value="<?php echo esc_attr( $option_value ); ?>"
								<?php echo in_array( (string) $option_value, $selected_values, true ) ? 'selected' : ''; ?>>
							<?php echo esc_html( $option_label ); ?>
						</option>
					<?php endforeach; ?>
				</select>
				<?php
				break;

			case 'textarea':
				?>
				<textarea id="<?php echo esc_attr( $field['id'] ); ?>" 
							name="<?php echo esc_attr( $field['id'] ); ?>" 
							rows="4" 
							class="widefat"><?php echo esc_textarea( $value ); ?></textarea>
				<?php
				break;
		}
	}

	public function save_meta_boxes( $post_id, $post ) {
		if ( ! isset( $_POST['roadmap_item_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['roadmap_item_meta_box_nonce'] ) ), 'roadmap_item_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = $this->get_meta_fields();

		foreach ( $fields as $field ) {
			if ( isset( $field['readonly'] ) && $field['readonly'] ) {
				continue;
			}

			if ( isset( $_POST[ $field['id'] ] ) ) {
				// Handle array values for multiple select fields
				if ( is_array( $_POST[ $field['id'] ] ) ) {
					$value = array_map( 'sanitize_text_field', wp_unslash( $_POST[ $field['id'] ] ) );
					// Filter out empty values (blank options)
					$value = array_filter(
						$value,
						function ( $item ) {
							return ! empty( $item );
						}
					);
					// Re-index array after filtering
					$value = array_values( $value );
				} else {
					$value = sanitize_text_field( wp_unslash( $_POST[ $field['id'] ] ) );
				}
				update_post_meta( $post_id, $field['id'], $value );
			} else {
				// Clear meta if field not in POST (e.g., unchecked multiselect)
				$is_multiple = isset( $field['multiple'] ) && $field['multiple'];
				if ( $is_multiple ) {
					update_post_meta( $post_id, $field['id'], array() );
				}
			}
		}
	}

	public function filter_meta_fields( $fields ) {
		// This filter allows pro addon to add additional fields
		return $fields;
	}

	/**
	 * Register block editor meta fields
	 */
	public function register_block_meta() {
		$fields = $this->get_meta_fields();

		foreach ( $fields as $field ) {
			// Skip readonly fields
			if ( isset( $field['readonly'] ) && $field['readonly'] ) {
				continue;
			}

			// Register meta field for block editor
			register_post_meta(
				'roadmap_item',
				$field['id'],
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => $this->get_field_rest_type( $field ),
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}

	/**
	 * Get REST API type for field
	 */
	private function get_field_rest_type( $field ) {
		switch ( $field['type'] ) {
			case 'number':
			case 'range':
				return 'integer';
			case 'date':
			case 'text':
			case 'select':
			case 'url':
			case 'textarea':
			default:
				return 'string';
		}
	}

	/**
	 * Enqueue block editor assets
	 */
	public function enqueue_block_editor_assets() {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'roadmap_item' ) {
			return;
		}

		// Enqueue block editor scripts
		wp_enqueue_script(
			'roadmap-block-editor',
			ROADMAP_PLUGIN_URL . 'assets/js/block-editor.js',
			array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data' ),
			ROADMAP_VERSION,
			true
		);

		// Enqueue meta box scripts and styles
		wp_enqueue_script(
			'roadmap-meta-box',
			ROADMAP_PLUGIN_URL . 'assets/js/roadmap-meta-box.js',
			array( 'jquery' ),
			ROADMAP_VERSION,
			true
		);

		wp_enqueue_style(
			'roadmap-meta-box',
			ROADMAP_PLUGIN_URL . 'assets/css/meta-box.css',
			array(),
			ROADMAP_VERSION
		);

		// Enqueue RTL styles if needed
		if ( is_rtl() && file_exists( ROADMAP_PLUGIN_PATH . 'assets/css/meta-box-rtl.css' ) ) {
			wp_enqueue_style(
				'roadmap-meta-box-rtl',
				ROADMAP_PLUGIN_URL . 'assets/css/meta-box-rtl.css',
				array( 'roadmap-meta-box' ),
				ROADMAP_VERSION
			);
		}

		// Localize script with field data
		wp_localize_script(
			'roadmap-block-editor',
			'roadmapMeta',
			array(
				'fields' => $this->get_meta_fields(),
				'nonce'  => wp_create_nonce( 'roadmap_meta' ),
				'isPro'  => defined( 'ROADMAP_PRO_VERSION' ),
			)
		);
	}

	/**
	 * Give a saved roadmap item the default product when it has none.
	 *
	 * Runs on real saves only, never on the auto-draft WordPress creates when
	 * "Add New" opens. Assigning it there pre-ticked the default product in the
	 * block editor's Products panel, so every new item also landed on the
	 * default board unless the owner noticed and unticked it. On a REST save
	 * the chosen terms are applied after this hook and replace the default.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 */
	public function set_default_product_if_missing( $post_id, $post ) {
		if ( 'roadmap_item' !== $post->post_type || 'auto-draft' === $post->post_status ) {
			return;
		}

		// Set default product term if not already set
		$product_terms = wp_get_object_terms( $post_id, 'roadmap_product' );
		if ( empty( $product_terms ) ) {
			$default_product_id = get_option( 'roadmap_default_product_id' );
			if ( ! $default_product_id ) {
				// Try to get the default product term
				$default_product = get_option( 'roadmap_default_product', 'main' );
				$term            = get_term_by( 'slug', $default_product, 'roadmap_product' );
				if ( $term ) {
					$default_product_id = $term->term_id;
				}
			}

			if ( $default_product_id ) {
				wp_set_object_terms( $post_id, (int) $default_product_id, 'roadmap_product' );
			}
		}
	}

	/**
	 * Add pending post status view in admin
	 */
	public function add_pending_post_status_view( $views ) {
		global $wpdb;

		$post_type     = 'roadmap_item';
		$pending_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'pending'",
				$post_type
			)
		);

		if ( $pending_count > 0 ) {
			$class       = ( isset( $_GET['post_status'] ) && $_GET['post_status'] === 'pending' ) ? ' class="current"' : '';
			$pending_url = admin_url( 'edit.php?post_status=pending&post_type=' . $post_type );

			$views['pending'] = sprintf(
				'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
				esc_url( $pending_url ),
				$class,
				__( 'Pending', 'product-roadmap' ),
				$pending_count
			);
		}

		return $views;
	}

	/**
	 * Whether a published Page already owns this top-level slug.
	 *
	 * @param string $slug Top-level slug to check.
	 * @return bool
	 */
	private function slug_is_taken_by_a_page( $slug ) {
		$page = get_page_by_path( $slug );

		return ( $page instanceof WP_Post ) && 'publish' === $page->post_status;
	}

	/**
	 * Add custom rewrite rules for product-specific URLs
	 *
	 * These rules are registered 'top', so they win over WordPress's own page
	 * rules. When the site owner has a published Page at /roadmap/ (or a child
	 * page under it) the rules that could shadow that Page stand down. The
	 * single-item rule is two segments deep, which no Page at /roadmap/ can
	 * claim, so it is always registered - custom_roadmap_permalinks() always
	 * advertises that URL and it must always resolve.
	 */
	public function add_rewrite_rules() {
		$page_owns_base = $this->slug_is_taken_by_a_page( 'roadmap' );

		if ( ! $page_owns_base ) {
			// All products index: /roadmap/
			add_rewrite_rule(
				'^roadmap/?$',
				'index.php?post_type=roadmap_item',
				'top'
			);
		}

		// Single roadmap item: /roadmap/{product}/{item-slug}/
		add_rewrite_rule(
			'^roadmap/([^/]+)/([^/]+)/?$',
			'index.php?roadmap_item=$matches[2]&roadmap_product=$matches[1]',
			'top'
		);

		// Product archive: /roadmap/{product}/
		if ( ! $page_owns_base ) {
			add_rewrite_rule(
				'^roadmap/([^/]+)/?$',
				'index.php?roadmap_product=$matches[1]&post_type=roadmap_item',
				'top'
			);
			return;
		}

		// A Page owns /roadmap/: serve only the product archives that no child
		// page already claims.
		$products = get_terms(
			array(
				'taxonomy'   => 'roadmap_product',
				'hide_empty' => false,
				'fields'     => 'slugs',
			)
		);
		foreach ( is_wp_error( $products ) ? array() : $products as $product_slug ) {
			if ( $this->slug_is_taken_by_a_page( 'roadmap/' . $product_slug ) ) {
				continue;
			}
			add_rewrite_rule(
				'^roadmap/(' . preg_quote( $product_slug, '/' ) . ')/?$',
				'index.php?roadmap_product=$matches[1]&post_type=roadmap_item',
				'top'
			);
		}
	}

	/**
	 * Flush rewrite rules when a Page under /roadmap/ is published, unpublished,
	 * renamed or trashed - the rules above depend on which Pages exist.
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       The post.
	 */
	public function maybe_flush_for_page( $new_status, $old_status, $post ) {
		if ( 'page' !== $post->post_type || ( 'publish' !== $new_status && 'publish' !== $old_status ) ) {
			return;
		}
		$uri = get_page_uri( $post );
		if ( 'roadmap' === $uri || 0 === strpos( $uri, 'roadmap/' ) ) {
			// Rules are registered at init, before this change was saved, so
			// rebuild them on the next request rather than now.
			update_option( 'roadmap_flush_rewrite_rules', 1, false );
		}
	}

	/**
	 * Rebuild rewrite rules once after a Page under /roadmap/ changed.
	 */
	public function maybe_flush_pending_rules() {
		if ( get_option( 'roadmap_flush_rewrite_rules' ) ) {
			delete_option( 'roadmap_flush_rewrite_rules' );
			flush_rewrite_rules();
		}
	}

	/**
	 * Add custom query vars
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'roadmap_product';
		return $vars;
	}

	/**
	 * Customize permalinks for roadmap items
	 */
	public function custom_roadmap_permalinks( $permalink, $post ) {
		if ( ! ( $post instanceof WP_Post ) || 'roadmap_item' !== $post->post_type ) {
			return $permalink;
		}

		// Only rewrite the permalink for a post that actually has one. A
		// pending or draft item has an empty post_name, so the pretty URL
		// collapsed to /roadmap/{product}/ - the product archive - and the
		// Preview button silently showed the board instead of the item.
		// Leaving core's link alone keeps the working ?p=ID&preview=true form.
		if ( empty( $post->post_name ) || ! in_array( $post->post_status, array( 'publish', 'private', 'future' ), true ) ) {
			return $permalink;
		}

		// get_the_terms() reads the object term cache, which the REST layer
		// primes for the whole page. wp_get_object_terms() bypasses it, so this
		// filter alone cost one term query per item on every board request.
		$product_terms = get_the_terms( $post->ID, 'roadmap_product' );
		$product_slug  = ( ! is_wp_error( $product_terms ) && ! empty( $product_terms ) )
			? $product_terms[0]->slug
			: 'main';

		return home_url( '/roadmap/' . $product_slug . '/' . $post->post_name . '/' );
	}

	/**
	 * Filter roadmap items by product in single views
	 */
	public function filter_roadmap_by_product( $query ) {
		// Only on frontend single roadmap item pages
		if ( ! is_admin() && $query->is_main_query() && is_singular( 'roadmap_item' ) ) {
			$product_slug = get_query_var( 'roadmap_product' );

			if ( $product_slug ) {
				// Verify the item belongs to this product
				$post_id = $query->get( 'p' );
				if ( ! $post_id ) {
					$post_name = $query->get( 'roadmap_item' );
					if ( $post_name ) {
						$post = get_page_by_path( $post_name, OBJECT, 'roadmap_item' );
						if ( $post ) {
							$post_id = $post->ID;
						}
					}
				}

				if ( $post_id ) {
					$item_products = wp_get_object_terms( $post_id, 'roadmap_product', array( 'fields' => 'slugs' ) );
					if ( ! in_array( $product_slug, $item_products ) ) {
						// Item doesn't belong to this product, show 404
						$query->set_404();
					}
				}
			}
		}
	}

	/**
	 * Load custom templates
	 */
	public function custom_template( $template ) {
		global $wp_query;

		// Single roadmap item templates are owned by Roadmap_Template, which
		// also handles the theme override via locate_template(). This branch
		// pointed at templates/single-roadmap-item.php - with a hyphen - while
		// the shipped file uses an underscore, so file_exists() always failed
		// and it silently did nothing. Two loaders for one job is how that goes
		// unnoticed, so the dead one is removed rather than corrected.

		// Product-specific archive template: /roadmap/{product}/
		if ( get_query_var( 'roadmap_product' ) && ! is_singular() ) {
			$custom_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap-product.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}

		// Taxonomy archive template: /roadmap-product/{product}/
		if ( is_tax( 'roadmap_product' ) ) {
			$custom_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap-product.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}

		// Main roadmap archive template: /roadmap/ (all products)
		// Check if we're on the roadmap_item post type archive without a specific product
		if (
			isset( $wp_query->query['post_type'] ) &&
			$wp_query->query['post_type'] === 'roadmap_item' &&
			! get_query_var( 'roadmap_product' ) &&
			! is_singular()
		) {
			$custom_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap_item.php';
			if ( file_exists( $custom_template ) ) {
				return $custom_template;
			}
		}

		return $template;
	}

	/**
	 * Add a "Source" column to the roadmap item list table.
	 *
	 * Inserted after the title so it's the first thing scanned.
	 *
	 * @param array $columns Existing columns.
	 * @return array
	 */
	public function add_source_column( $columns ) {
		$new = array();
		foreach ( $columns as $key => $label ) {
			$new[ $key ] = $label;
			if ( 'title' === $key ) {
				$new['roadmap_source'] = __( 'Source', 'product-roadmap' );
			}
		}
		// Fallback if there was no title column for some reason.
		if ( ! isset( $new['roadmap_source'] ) ) {
			$new['roadmap_source'] = __( 'Source', 'product-roadmap' );
		}
		return $new;
	}

	/**
	 * Render the Source column: user-suggested vs admin-created.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 * @return void
	 */
	public function render_source_column( $column, $post_id ) {
		if ( 'roadmap_source' !== $column ) {
			return;
		}

		$is_suggestion = ( 'suggestion' === get_post_meta( $post_id, '_roadmap_source', true ) );

		if ( $is_suggestion ) {
			printf(
				'<span class="roadmap-source-badge roadmap-source-badge--suggested" style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;color:#7c3aed;background:#f3e8ff;">%s</span>',
				esc_html__( 'Suggested', 'product-roadmap' )
			);
		} else {
			printf(
				'<span class="roadmap-source-badge roadmap-source-badge--created" style="display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:600;color:#334155;background:#e2e8f0;">%s</span>',
				esc_html__( 'Created', 'product-roadmap' )
			);
		}
	}

	/**
	 * Add quick approve/reject actions to pending items
	 */
	public function add_quick_approve_actions( $actions, $post ) {
		if ( $post->post_type !== 'roadmap_item' || $post->post_status !== 'pending' ) {
			return $actions;
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			return $actions;
		}

		$approve_url = wp_nonce_url(
			admin_url( 'admin.php?action=approve_roadmap_item&post=' . $post->ID ),
			'approve_roadmap_item_' . $post->ID
		);

		$reject_url = wp_nonce_url(
			admin_url( 'admin.php?action=reject_roadmap_item&post=' . $post->ID ),
			'reject_roadmap_item_' . $post->ID
		);

		// Add approve action (with green color). Keyed 'roadmap_approve', not
		// 'approve' - WP_List_Table::row_actions() wraps each key in
		// <span class="$key">, and wp-admin/css/common.css has an unscoped
		// `.approve { display: none; }` rule written for the Comments screen
		// (only re-shown inside .unapproved/.spam/.trash rows). Our pending
		// rows carry class status-pending, not unapproved, so that rule
		// always won and the link was invisible everywhere, for everyone -
		// the handler itself worked fine if you knew the URL. Found on the
		// 1.8.0 smoke re-run.
		$actions['roadmap_approve'] = sprintf(
			'<a href="%s" style="color: #46b450;">%s</a>',
			esc_url( $approve_url ),
			__( 'Approve', 'product-roadmap' )
		);

		// Add reject action (with red color). Two-step confirm, no browser dialog.
		wp_enqueue_script( 'roadmap-admin-feedback' );
		$actions['reject'] = sprintf(
			'<a href="%s" style="color: #dc3232;" data-roadmap-confirm="%s" data-roadmap-confirm-label="%s">%s</a>',
			esc_url( $reject_url ),
			esc_attr__( 'Rejects this submission.', 'product-roadmap' ),
			esc_attr__( 'Confirm reject', 'product-roadmap' ),
			esc_html__( 'Reject', 'product-roadmap' )
		);

		return $actions;
	}

	/**
	 * Handle approve action
	 */
	public function handle_approve_action() {
		if ( ! isset( $_GET['post'] ) ) {
			wp_die( esc_html__( 'No submission specified', 'product-roadmap' ) );
		}

		$post_id = intval( $_GET['post'] );

		// Security check
		check_admin_referer( 'approve_roadmap_item_' . $post_id );

		if ( ! current_user_can( 'publish_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to approve submissions', 'product-roadmap' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'roadmap_item' ) {
			wp_die( esc_html__( 'Invalid submission', 'product-roadmap' ) );
		}

		// Update post status to publish
		wp_update_post(
			array(
				'ID'          => $post_id,
				'post_status' => 'publish',
			)
		);

		// Set a transient for the success message
		set_transient(
			'roadmap_action_notice_' . get_current_user_id(),
			array(
				'type'    => 'success',
				/* translators: %s: Post title */
				'message' => sprintf( __( '"%s" has been approved and published!', 'product-roadmap' ), $post->post_title ),
			),
			30
		);

		// Redirect back to the list
		wp_redirect( admin_url( 'edit.php?post_type=roadmap_item&approved=1' ) );
		exit;
	}

	/**
	 * Handle reject action
	 */
	public function handle_reject_action() {
		if ( ! isset( $_GET['post'] ) ) {
			wp_die( esc_html__( 'No submission specified', 'product-roadmap' ) );
		}

		$post_id = intval( $_GET['post'] );

		// Security check
		check_admin_referer( 'reject_roadmap_item_' . $post_id );

		if ( ! current_user_can( 'delete_posts' ) ) {
			wp_die( esc_html__( 'You do not have permission to reject submissions', 'product-roadmap' ) );
		}

		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'roadmap_item' ) {
			wp_die( esc_html__( 'Invalid submission', 'product-roadmap' ) );
		}

		// Move to trash
		wp_trash_post( $post_id );

		// Set a transient for the success message
		set_transient(
			'roadmap_action_notice_' . get_current_user_id(),
			array(
				'type'    => 'updated',
				/* translators: %s: Post title */
				'message' => sprintf( __( '"%s" has been rejected and moved to trash.', 'product-roadmap' ), $post->post_title ),
			),
			30
		);

		// Redirect back to the list
		wp_redirect( admin_url( 'edit.php?post_type=roadmap_item&rejected=1' ) );
		exit;
	}

	/**
	 * Show admin notices after approve/reject actions
	 */
	public function show_action_notices() {
		$screen = get_current_screen();
		if ( ! $screen || $screen->post_type !== 'roadmap_item' ) {
			return;
		}

		// Check for transient message
		$notice = get_transient( 'roadmap_action_notice_' . get_current_user_id() );
		if ( $notice ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $notice['type'] ),
				esc_html( $notice['message'] )
			);
			delete_transient( 'roadmap_action_notice_' . get_current_user_id() );
		}
	}
}