<?php
/**
 * Changelog Custom Post Type
 *
 * @package ProductRoadmap
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Roadmap_Changelog {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'init', array( $this, 'register_post_meta' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'add_rewrite_rules' ), 25 );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post_roadmap_changelog', array( $this, 'save_meta_boxes' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_menu', array( $this, 'add_changelog_menu' ), 26 );

		// AJAX handler for saving change-type colours (Settings > Changelog Types).
		// Note: item rows are added client-side by cloning a hidden template, so
		// there is no server-side add-item AJAX handler.
		add_action( 'wp_ajax_roadmap_save_changelog_type_color', array( $this, 'ajax_save_changelog_type_color' ) );

		// Share product taxonomy with changelog
		add_action( 'init', array( $this, 'register_product_taxonomy_for_changelog' ), 20 );

		// Menu highlighting
		add_filter( 'parent_file', array( $this, 'set_changelog_parent_menu' ) );
		add_filter( 'submenu_file', array( $this, 'set_changelog_submenu_active' ) );

		// Custom permalink structure with product
		add_filter( 'post_type_link', array( $this, 'changelog_permalink' ), 10, 2 );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );

		// Frontend template
		add_filter( 'single_template', array( $this, 'changelog_single_template' ) );
		add_filter( 'archive_template', array( $this, 'changelog_archive_template' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );

		// Filter changelog archives by product
		add_action( 'pre_get_posts', array( $this, 'filter_changelog_by_product' ) );

		// Auto-generate slug with product prefix
		add_filter( 'wp_insert_post_data', array( $this, 'auto_generate_slug' ), 10, 2 );

		// The block editor's REST save sets post fields (and runs the filter
		// above) before it attaches the roadmap_product term via handle_terms()
		// later in the same request, so a non-default product's first save can
		// only see "no product yet" and falls back to the general- slug. Correct
		// it once REST has actually finished attaching terms.
		add_action( 'rest_after_insert_roadmap_changelog', array( $this, 'fix_slug_after_rest_terms' ), 10, 2 );
	}

	/**
	 * Add changelog menu
	 */
	public function add_changelog_menu() {
		// Add main Changelog menu
		add_menu_page(
			__( 'Changelog', 'product-roadmap' ),
			__( 'Changelog', 'product-roadmap' ),
			'edit_posts',
			'roadmap-changelog',
			array( $this, 'redirect_to_changelogs' ),
			'dashicons-list-view',
			26
		);

		// Add All Changelogs submenu
		add_submenu_page(
			'roadmap-changelog',
			__( 'All Changelogs', 'product-roadmap' ),
			__( 'All Changelogs', 'product-roadmap' ),
			'edit_posts',
			'edit.php?post_type=roadmap_changelog'
		);

		// Add New Changelog submenu
		add_submenu_page(
			'roadmap-changelog',
			__( 'Add New', 'product-roadmap' ),
			__( 'Add New', 'product-roadmap' ),
			'edit_posts',
			'post-new.php?post_type=roadmap_changelog'
		);

		// Add Changelog Types submenu
		add_submenu_page(
			'roadmap-changelog',
			__( 'Change Types', 'product-roadmap' ),
			__( 'Change Types', 'product-roadmap' ),
			'manage_options',
			'edit-tags.php?taxonomy=changelog_type&post_type=roadmap_changelog'
		);

		// Remove the duplicate first submenu item
		global $submenu;
		if ( isset( $submenu['roadmap-changelog'] ) ) {
			unset( $submenu['roadmap-changelog'][0] );
		}
	}

	/**
	 * Redirect to changelogs list
	 */
	public function redirect_to_changelogs() {
		wp_redirect( admin_url( 'edit.php?post_type=roadmap_changelog' ) );
		exit;
	}

	/**
	 * Set parent menu for changelog pages
	 */
	public function set_changelog_parent_menu( $parent_file ) {
		global $current_screen;

		if ( $current_screen && isset( $current_screen->post_type ) ) {
			if ( $current_screen->post_type === 'roadmap_changelog' ) {
				return 'roadmap-changelog';
			}
		}

		return $parent_file;
	}

	/**
	 * Set active submenu for changelog pages
	 */
	public function set_changelog_submenu_active( $submenu_file ) {
		global $current_screen, $pagenow;

		if ( ! $current_screen || ! isset( $current_screen->post_type ) ) {
			return $submenu_file;
		}

		if ( $current_screen->post_type === 'roadmap_changelog' ) {
			// Changelog types taxonomy
			if ( isset( $current_screen->taxonomy ) && $current_screen->taxonomy === 'changelog_type' ) {
				return 'edit-tags.php?taxonomy=changelog_type&post_type=roadmap_changelog';
			}

			// Add new changelog
			if ( $pagenow === 'post-new.php' ) {
				return 'post-new.php?post_type=roadmap_changelog';
			}

			// Edit/list changelogs
			return 'edit.php?post_type=roadmap_changelog';
		}

		return $submenu_file;
	}

	/**
	 * AJAX handler to save changelog type colors
	 */
	public function ajax_save_changelog_type_color() {
		check_ajax_referer( 'roadmap_changelog_type_color', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		$term_id = isset( $_POST['term_id'] ) ? absint( $_POST['term_id'] ) : 0;
		$field   = isset( $_POST['field'] ) ? sanitize_text_field( wp_unslash( $_POST['field'] ) ) : '';
		$value   = isset( $_POST['value'] ) ? sanitize_hex_color( wp_unslash( $_POST['value'] ) ) : '';

		if ( ! $term_id || ! $field || ! $value ) {
			wp_send_json_error( 'Invalid parameters' );
		}

		$meta_key = '_changelog_type_' . $field;
		update_term_meta( $term_id, $meta_key, $value );

		wp_send_json_success();
	}

	/**
	 * Register the changelog custom post type
	 */
	public function register_post_type() {
		$labels = array(
			'name'               => __( 'Changelogs', 'product-roadmap' ),
			'singular_name'      => __( 'Changelog', 'product-roadmap' ),
			'menu_name'          => __( 'Changelogs', 'product-roadmap' ),
			'add_new'            => __( 'Add New', 'product-roadmap' ),
			'add_new_item'       => __( 'Add New Changelog', 'product-roadmap' ),
			'edit_item'          => __( 'Edit Changelog', 'product-roadmap' ),
			'new_item'           => __( 'New Changelog', 'product-roadmap' ),
			'view_item'          => __( 'View Changelog', 'product-roadmap' ),
			'search_items'       => __( 'Search Changelogs', 'product-roadmap' ),
			'not_found'          => __( 'No changelogs found', 'product-roadmap' ),
			'not_found_in_trash' => __( 'No changelogs found in trash', 'product-roadmap' ),
			'all_items'          => __( 'All Changelogs', 'product-roadmap' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false, // We add menu manually
			'query_var'          => true,
			'rewrite'            => array(
				'slug'       => 'changelog',
				'with_front' => false,
			),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'menu_icon'          => 'dashicons-list-view',
			'supports'           => array( 'title', 'editor', 'author', 'custom-fields' ),
			'show_in_rest'       => true,
			'rest_base'          => 'changelogs',
		);

		$args = apply_filters( 'roadmap_changelog_post_type_args', $args );

		register_post_type( 'roadmap_changelog', $args );
	}

	/**
	 * Register meta fields for REST API support
	 *
	 * This enables the meta fields to be readable/writable via REST API,
	 * which is required for MCP tools to create changelogs with proper meta.
	 */
	public function register_post_meta() {
		// Version number (e.g., "2.5.0")
		register_post_meta(
			'roadmap_changelog',
			'_changelog_version',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Release date (YYYY-MM-DD format)
		register_post_meta(
			'roadmap_changelog',
			'_changelog_release_date',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Release type (major/minor/patch)
		register_post_meta(
			'roadmap_changelog',
			'_changelog_release_type',
			array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);

		// Changelog items array
		register_post_meta(
			'roadmap_changelog',
			'_changelog_items',
			array(
				'show_in_rest'  => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type'       => 'object',
							'properties' => array(
								'type'        => array(
									'type' => 'string',
								),
								'description' => array(
									'type' => 'string',
								),
							),
						),
					),
				),
				'single'            => true,
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_changelog_items' ),
				'auth_callback'     => function () {
					return current_user_can( 'edit_posts' );
				},
			)
		);
	}

	/**
	 * Sanitize the _changelog_items meta (array of {type, description}).
	 *
	 * REST writes were previously stored unsanitized; this normalizes each row.
	 *
	 * @param mixed $items Raw meta value.
	 * @return array Sanitized list of changelog items.
	 */
	public function sanitize_changelog_items( $items ) {
		if ( ! is_array( $items ) ) {
			return array();
		}

		$clean = array();
		foreach ( $items as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}
			$clean[] = array(
				'type'        => isset( $item['type'] ) ? sanitize_text_field( $item['type'] ) : '',
				'description' => isset( $item['description'] ) ? sanitize_textarea_field( $item['description'] ) : '',
			);
		}

		return $clean;
	}

	/**
	 * Register changelog type taxonomy (for badge categories)
	 */
	public function register_taxonomies() {
		$labels = array(
			'name'          => __( 'Change Types', 'product-roadmap' ),
			'singular_name' => __( 'Change Type', 'product-roadmap' ),
			'search_items'  => __( 'Search Change Types', 'product-roadmap' ),
			'all_items'     => __( 'All Change Types', 'product-roadmap' ),
			'edit_item'     => __( 'Edit Change Type', 'product-roadmap' ),
			'update_item'   => __( 'Update Change Type', 'product-roadmap' ),
			'add_new_item'  => __( 'Add New Change Type', 'product-roadmap' ),
			'new_item_name' => __( 'New Change Type Name', 'product-roadmap' ),
			'menu_name'     => __( 'Change Types', 'product-roadmap' ),
		);

		$args = array(
			'hierarchical'      => false, // Like tags
			'labels'            => $labels,
			'show_ui'           => true,
			'show_admin_column' => false, // Items have multiple types
			'query_var'         => true,
			'rewrite'           => array( 'slug' => 'changelog-type' ),
			'show_in_rest'      => true,
			'meta_box_cb'       => false, // We'll use our own UI
		);

		register_taxonomy( 'changelog_type', array( 'roadmap_changelog' ), $args );

		// Insert default change types
		$this->insert_default_change_types();
	}

	/**
	 * Share roadmap_product taxonomy with changelog CPT
	 */
	public function register_product_taxonomy_for_changelog() {
		// Only in Pro version, allow multiple products
		// In free version, auto-assign to default product
		if ( taxonomy_exists( 'roadmap_product' ) ) {
			register_taxonomy_for_object_type( 'roadmap_product', 'roadmap_changelog' );
		}
	}

	/**
	 * Insert default change type terms with colors
	 */
	private function insert_default_change_types() {
		$default_types = array(
			'new-feature'     => array(
				'name'       => __( 'New Feature', 'product-roadmap' ),
				'color'      => '#155724',
				'background' => '#d4edda',
				'icon'       => 'star-filled',
			),
			'enhancement'     => array(
				'name'       => __( 'Enhancement', 'product-roadmap' ),
				'color'      => '#0c5460',
				'background' => '#d1ecf1',
				'icon'       => 'arrow-up-alt',
			),
			'fix'             => array(
				'name'       => __( 'Fix', 'product-roadmap' ),
				'color'      => '#856404',
				'background' => '#fff3cd',
				'icon'       => 'admin-tools',
			),
			'update'          => array(
				'name'       => __( 'Update', 'product-roadmap' ),
				'color'      => '#383d41',
				'background' => '#e2e3e5',
				'icon'       => 'update',
			),
			'deprecated'      => array(
				'name'       => __( 'Deprecated', 'product-roadmap' ),
				'color'      => '#721c24',
				'background' => '#f8d7da',
				'icon'       => 'warning',
			),
			'security'        => array(
				'name'       => __( 'Security', 'product-roadmap' ),
				'color'      => '#721c24',
				'background' => '#f8d7da',
				'icon'       => 'shield',
			),
			'breaking-change' => array(
				'name'       => __( 'Breaking Change', 'product-roadmap' ),
				'color'      => '#721c24',
				'background' => '#f5c6cb',
				'icon'       => 'dismiss',
			),
		);

		foreach ( $default_types as $slug => $data ) {
			if ( ! term_exists( $slug, 'changelog_type' ) ) {
				$term = wp_insert_term( $data['name'], 'changelog_type', array( 'slug' => $slug ) );

				if ( ! is_wp_error( $term ) ) {
					// Store color and icon as term meta
					update_term_meta( $term['term_id'], '_changelog_type_color', $data['color'] );
					update_term_meta( $term['term_id'], '_changelog_type_background', $data['background'] );
					update_term_meta( $term['term_id'], '_changelog_type_icon', $data['icon'] );
				}
			}
		}
	}

	/**
	 * Add meta boxes for changelog
	 */
	public function add_meta_boxes() {
		add_meta_box(
			'roadmap_changelog_details',
			__( 'Release Details', 'product-roadmap' ),
			array( $this, 'render_details_meta_box' ),
			'roadmap_changelog',
			'side',
			'high'
		);

		add_meta_box(
			'roadmap_changelog_items',
			__( 'Changelog Items', 'product-roadmap' ),
			array( $this, 'render_items_meta_box' ),
			'roadmap_changelog',
			'normal',
			'high'
		);
	}

	/**
	 * Render release details meta box
	 */
	public function render_details_meta_box( $post ) {
		wp_nonce_field( 'roadmap_changelog_meta_box', 'roadmap_changelog_meta_box_nonce' );

		$version      = get_post_meta( $post->ID, '_changelog_version', true );
		$release_date = get_post_meta( $post->ID, '_changelog_release_date', true );
		$release_type = get_post_meta( $post->ID, '_changelog_release_type', true );
		?>
		<p>
			<label for="_changelog_version">
				<strong><?php esc_html_e( 'Version', 'product-roadmap' ); ?></strong>
			</label>
			<input type="text"
					id="_changelog_version"
					name="_changelog_version"
					value="<?php echo esc_attr( $version ); ?>"
					placeholder="e.g., 2.5.0"
					class="widefat">
		</p>

		<p>
			<label for="_changelog_release_date">
				<strong><?php esc_html_e( 'Release Date', 'product-roadmap' ); ?></strong>
			</label>
			<input type="date"
					id="_changelog_release_date"
					name="_changelog_release_date"
					value="<?php echo esc_attr( $release_date ); ?>"
					class="widefat">
		</p>

		<p>
			<label for="_changelog_release_type">
				<strong><?php esc_html_e( 'Release Type', 'product-roadmap' ); ?></strong>
			</label>
			<select id="_changelog_release_type" name="_changelog_release_type" class="widefat">
				<option value=""><?php esc_html_e( '-- Select Type --', 'product-roadmap' ); ?></option>
				<option value="major" <?php selected( $release_type, 'major' ); ?>>
					<?php esc_html_e( 'Major (Breaking changes)', 'product-roadmap' ); ?>
				</option>
				<option value="minor" <?php selected( $release_type, 'minor' ); ?>>
					<?php esc_html_e( 'Minor (New features)', 'product-roadmap' ); ?>
				</option>
				<option value="patch" <?php selected( $release_type, 'patch' ); ?>>
					<?php esc_html_e( 'Patch (Bug fixes)', 'product-roadmap' ); ?>
				</option>
			</select>
		</p>
		<?php
	}

	/**
	 * Render changelog items meta box (repeater)
	 */
	public function render_items_meta_box( $post ) {
		$items = get_post_meta( $post->ID, '_changelog_items', true );
		if ( ! is_array( $items ) ) {
			$items = array();
		}

		// Get all change types
		$change_types = get_terms(
			array(
				'taxonomy'   => 'changelog_type',
				'hide_empty' => false,
			)
		);
		?>
		<div class="roadmap-changelog-items-wrapper">
			<table class="widefat roadmap-changelog-items-table" id="changelog-items-table">
				<thead>
					<tr>
						<th class="sort-handle" style="width: 30px;"></th>
						<th style="width: 150px;"><?php esc_html_e( 'Type', 'product-roadmap' ); ?></th>
						<th><?php esc_html_e( 'Description', 'product-roadmap' ); ?></th>
						<th style="width: 50px;"></th>
					</tr>
				</thead>
				<tbody id="changelog-items-body">
					<?php if ( ! empty( $items ) ) : ?>
						<?php foreach ( $items as $index => $item ) : ?>
							<?php $this->render_item_row( $index, $item, $change_types ); ?>
						<?php endforeach; ?>
					<?php else : ?>
						<?php $this->render_item_row( 0, array(), $change_types ); ?>
					<?php endif; ?>
				</tbody>
			</table>

			<p>
				<button type="button" class="button button-secondary" id="add-changelog-item">
					<span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span>
					<?php esc_html_e( 'Add Item', 'product-roadmap' ); ?>
				</button>
			</p>
		</div>

		<script type="text/template" id="changelog-item-template">
			<?php $this->render_item_row( '{{INDEX}}', array(), $change_types ); ?>
		</script>
		<?php
	}

	/**
	 * Render a single changelog item row
	 */
	private function render_item_row( $index, $item, $change_types ) {
		$type        = isset( $item['type'] ) ? $item['type'] : '';
		$description = isset( $item['description'] ) ? $item['description'] : '';
		?>
		<tr class="changelog-item-row" data-index="<?php echo esc_attr( $index ); ?>">
			<td class="sort-handle">
				<span class="dashicons dashicons-menu" style="cursor: move; color: #999;"></span>
			</td>
			<td>
				<select name="_changelog_items[<?php echo esc_attr( $index ); ?>][type]" class="widefat changelog-type-select">
					<option value=""><?php esc_html_e( 'Select Type', 'product-roadmap' ); ?></option>
					<?php
					foreach ( $change_types as $term ) :
						$color = get_term_meta( $term->term_id, '_changelog_type_color', true );
						$bg    = get_term_meta( $term->term_id, '_changelog_type_background', true );
						?>
						<option value="<?php echo esc_attr( $term->slug ); ?>"
								data-color="<?php echo esc_attr( $color ); ?>"
								data-bg="<?php echo esc_attr( $bg ); ?>"
								<?php selected( $type, $term->slug ); ?>>
							<?php echo esc_html( $term->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</td>
			<td>
				<input type="text"
						name="_changelog_items[<?php echo esc_attr( $index ); ?>][description]"
						value="<?php echo esc_attr( $description ); ?>"
						placeholder="<?php esc_attr_e( 'Describe the change...', 'product-roadmap' ); ?>"
						class="widefat">
			</td>
			<td>
				<button type="button" class="button button-link-delete remove-changelog-item" title="<?php esc_attr_e( 'Remove', 'product-roadmap' ); ?>">
					<span class="dashicons dashicons-trash"></span>
				</button>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save changelog meta boxes
	 */
	/**
	 * The product Free assigns to an entry saved without one, or 0 (Pro, or unset).
	 *
	 * @return int
	 */
	private static function free_default_product_id() {
		if ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) {
			return 0;
		}
		return (int) get_option( 'roadmap_default_product_id' );
	}

	public function save_meta_boxes( $post_id, $post ) {
		if ( ! isset( $_POST['roadmap_changelog_meta_box_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['roadmap_changelog_meta_box_nonce'] ) ), 'roadmap_changelog_meta_box' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Save version
		if ( isset( $_POST['_changelog_version'] ) ) {
			update_post_meta( $post_id, '_changelog_version', sanitize_text_field( wp_unslash( $_POST['_changelog_version'] ) ) );
		}

		// Save release date
		if ( isset( $_POST['_changelog_release_date'] ) ) {
			update_post_meta( $post_id, '_changelog_release_date', sanitize_text_field( wp_unslash( $_POST['_changelog_release_date'] ) ) );
		}

		// Save release type
		if ( isset( $_POST['_changelog_release_type'] ) ) {
			update_post_meta( $post_id, '_changelog_release_type', sanitize_text_field( wp_unslash( $_POST['_changelog_release_type'] ) ) );
		}

		// Save changelog items
		if ( isset( $_POST['_changelog_items'] ) && is_array( $_POST['_changelog_items'] ) ) {
			// Unslash once at the boundary, then sanitize each field. WordPress
			// slashes superglobals, so sanitizing without unslashing stores the
			// escaped form and backslashes accumulate on every re-save.
			$posted_items = map_deep( wp_unslash( $_POST['_changelog_items'] ), 'sanitize_text_field' );
			$items        = array();

			foreach ( $posted_items as $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}

				if ( ! empty( $item['type'] ) || ! empty( $item['description'] ) ) {
					$items[] = array(
						'type'        => $item['type'] ?? '',
						'description' => $item['description'] ?? '',
					);
				}
			}
			update_post_meta( $post_id, '_changelog_items', $items );
		}

		// Auto-assign default product in free version
		$default_product_id = self::free_default_product_id();
		if ( $default_product_id && empty( wp_get_object_terms( $post_id, 'roadmap_product' ) ) ) {
			wp_set_object_terms( $post_id, $default_product_id, 'roadmap_product' );
		}
	}

	/**
	 * Enqueue admin assets
	 */
	public function enqueue_admin_assets( $hook ) {
		global $post_type;

		if ( $post_type !== 'roadmap_changelog' ) {
			return;
		}

		// Inline CSS for changelog meta box
		wp_add_inline_style(
			'wp-admin',
			'
            .roadmap-changelog-items-table { margin-bottom: 10px; }
            .roadmap-changelog-items-table th { font-weight: 600; }
            .roadmap-changelog-items-table .sort-handle { text-align: center; }
            .roadmap-changelog-items-table .remove-changelog-item { color: #b32d2e; display: flex; align-items: center; }
            .roadmap-changelog-items-table .remove-changelog-item:hover { color: #dc3232; }
            .changelog-type-select { min-width: 120px; }
            select#_changelog_release_type { width: calc(100% - 34px); }
        '
		);

		// Inline JS for changelog items repeater
		wp_add_inline_script(
			'jquery-ui-sortable',
			'
            jQuery(document).ready(function($) {
                var itemIndex = ' . ( count( get_post_meta( get_the_ID(), '_changelog_items', true ) ?: array() ) + 1 ) . ';

                // Add new item
                $("#add-changelog-item").on("click", function() {
                    var template = $("#changelog-item-template").html();
                    template = template.replace(/\{\{INDEX\}\}/g, itemIndex);
                    $("#changelog-items-body").append(template);
                    itemIndex++;
                });

                // Remove item
                $(document).on("click", ".remove-changelog-item", function() {
                    var $row = $(this).closest("tr");
                    if ($("#changelog-items-body tr").length > 1) {
                        $row.remove();
                    } else {
                        // Clear values instead of removing last row
                        $row.find("input, select").val("");
                    }
                });

                // Sortable
                if ($.fn.sortable) {
                    $("#changelog-items-body").sortable({
                        handle: ".sort-handle",
                        placeholder: "ui-sortable-placeholder",
                        update: function(event, ui) {
                            // Re-index items after sort
                            $("#changelog-items-body tr").each(function(index) {
                                $(this).find("select, input").each(function() {
                                    var name = $(this).attr("name");
                                    if (name) {
                                        name = name.replace(/\[\d+\]/, "[" + index + "]");
                                        $(this).attr("name", name);
                                    }
                                });
                            });
                        }
                    });
                }
            });
        '
		);

		// Enqueue sortable
		wp_enqueue_script( 'jquery-ui-sortable' );
	}

	/**
	 * Get changelog types with their styling
	 */
	public static function get_change_types() {
		$terms = get_terms(
			array(
				'taxonomy'   => 'changelog_type',
				'hide_empty' => false,
			)
		);

		$types = array();
		foreach ( $terms as $term ) {
			$types[ $term->slug ] = array(
				'name'       => $term->name,
				'slug'       => $term->slug,
				'color'      => get_term_meta( $term->term_id, '_changelog_type_color', true ) ?: '#383d41',
				'background' => get_term_meta( $term->term_id, '_changelog_type_background', true ) ?: '#e2e3e5',
				'icon'       => get_term_meta( $term->term_id, '_changelog_type_icon', true ) ?: 'marker',
			);
		}

		return $types;
	}

	/**
	 * Get formatted changelog data for a post
	 */
	public static function get_changelog_data( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post || $post->post_type !== 'roadmap_changelog' ) {
			return null;
		}

		$items = get_post_meta( $post_id, '_changelog_items', true );
		$types = self::get_change_types();

		// Enhance items with type data
		if ( is_array( $items ) ) {
			foreach ( $items as &$item ) {
				if ( isset( $item['type'] ) && isset( $types[ $item['type'] ] ) ) {
					$item['type_data'] = $types[ $item['type'] ];
				}
			}
		}

		return array(
			'id'           => $post_id,
			'title'        => $post->post_title,
			'content'      => $post->post_content,
			'version'      => get_post_meta( $post_id, '_changelog_version', true ),
			'release_date' => get_post_meta( $post_id, '_changelog_release_date', true ),
			'release_type' => get_post_meta( $post_id, '_changelog_release_type', true ),
			'items'        => $items ?: array(),
			'product'      => wp_get_object_terms( $post_id, 'roadmap_product', array( 'fields' => 'all' ) ),
			'link'         => get_permalink( $post_id ),
		);
	}

	/**
	 * Add custom rewrite rules for changelog with product
	 */
	public function add_rewrite_rules() {
		// Single changelog: /changelog/{product-slug}/{post-slug}/
		add_rewrite_rule(
			'changelog/([^/]+)/([^/]+)/?$',
			'index.php?post_type=roadmap_changelog&changelog_product=$matches[1]&name=$matches[2]',
			'top'
		);

		// Product changelog archive: /changelog/{product-slug}/
		// Only match if it's not a changelog post name
		add_rewrite_rule(
			'changelog/([^/]+)/?$',
			'index.php?post_type=roadmap_changelog&changelog_product=$matches[1]',
			'top'
		);

		// Paginated product changelog archive
		add_rewrite_rule(
			'changelog/([^/]+)/page/([0-9]+)/?$',
			'index.php?post_type=roadmap_changelog&changelog_product=$matches[1]&paged=$matches[2]',
			'top'
		);
	}

	/**
	 * Add custom query vars
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'roadmap_changelog';
		$vars[] = 'changelog_product';
		return $vars;
	}

	/**
	 * Filter changelog archive by product
	 */
	public function filter_changelog_by_product( \WP_Query $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		// Only apply to changelog queries
		if ( ! isset( $query->query_vars['post_type'] ) || $query->query_vars['post_type'] !== 'roadmap_changelog' ) {
			return;
		}

		// Check if we have a changelog_product filter
		$changelog_product = get_query_var( 'changelog_product' );
		if ( ! empty( $changelog_product ) ) {
			// Verify it's a valid product slug
			$product_term = get_term_by( 'slug', $changelog_product, 'roadmap_product' );
			if ( $product_term ) {
				// Add tax query to filter by product
				$tax_query   = $query->get( 'tax_query' ) ?: array();
				$tax_query[] = array(
					'taxonomy' => 'roadmap_product',
					'field'    => 'slug',
					'terms'    => $changelog_product,
				);
				$query->set( 'tax_query', $tax_query );
			}
		}

		// Order by release date (stored in meta), newest first. Ordering by a
		// meta_key alone forces an INNER JOIN that silently drops undated entries
		// from the archive; instead a posts_clauses filter LEFT JOINs the meta and
		// sorts by COALESCE( release date, post date ) so undated entries stay and
		// fall back to their publish date.
		add_filter( 'posts_clauses', array( $this, 'order_changelog_by_release_date' ), 10, 2 );
	}

	/**
	 * Order the changelog archive by release-date meta with a post-date fallback.
	 *
	 * Registered from filter_changelog_by_product() so it only affects that
	 * query, and removes itself after running.
	 *
	 * @param array    $clauses SQL clauses.
	 * @param WP_Query $query   The query.
	 * @return array
	 */
	public function order_changelog_by_release_date( $clauses, $query ) {
		global $wpdb;

		remove_filter( 'posts_clauses', array( $this, 'order_changelog_by_release_date' ), 10 );

		if ( is_admin() || ! $query->is_main_query() || $query->get( 'post_type' ) !== 'roadmap_changelog' ) {
			return $clauses;
		}

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS rc_rd ON ( {$wpdb->posts}.ID = rc_rd.post_id AND rc_rd.meta_key = '_changelog_release_date' )";

		// COALESCE lets undated entries fall back to their publish date instead of
		// being excluded; NULLIF treats an empty-string meta the same as missing.
		$clauses['orderby'] = "COALESCE( NULLIF( rc_rd.meta_value, '' ), {$wpdb->posts}.post_date ) DESC";

		// LEFT JOIN on a single-value meta cannot duplicate rows, but keep the
		// query DISTINCT-safe in case other filters add joins.
		$clauses['groupby'] = "{$wpdb->posts}.ID";

		return $clauses;
	}

	/**
	 * Generate custom permalink for changelog with product
	 */
	public function changelog_permalink( $permalink, $post ) {
		if ( $post->post_type !== 'roadmap_changelog' ) {
			return $permalink;
		}

		// Get product term
		$products = wp_get_object_terms( $post->ID, 'roadmap_product' );
		if ( ! empty( $products ) && ! is_wp_error( $products ) ) {
			$product_slug = $products[0]->slug;
		} else {
			// Fallback to 'general' if no product assigned
			$product_slug = 'general';
		}

		return home_url( '/changelog/' . $product_slug . '/' . $post->post_name . '/' );
	}

	/**
	 * Auto-generate unique slug with product prefix
	 */
	public function auto_generate_slug( $data, $postarr ) {
		if ( $data['post_type'] !== 'roadmap_changelog' ) {
			return $data;
		}

		// Never derive a slug while WordPress is creating the auto-draft: at
		// that point the title is "Auto Draft" and no product is assigned, so
		// this produced "general-auto-draft" and wrote it to post_name. Being
		// non-empty from then on, the guard below never fired again and the
		// slug was stuck for the life of the entry.
		if ( 'auto-draft' === ( $data['post_status'] ?? '' ) ) {
			return $data;
		}

		if ( empty( $data['post_title'] ) || 'Auto Draft' === $data['post_title'] ) {
			return $data;
		}

		// The version arrives in the POST body, not in $postarr - reading
		// $postarr['_changelog_version'] meant the product-vX.Y.Z branch
		// below never ran.
		$stored_version = (string) get_post_meta( $postarr['ID'] ?? 0, '_changelog_version', true );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- slug derivation only; the save itself is nonce-checked
		$version = isset( $_POST['_changelog_version'] )
			? sanitize_text_field( wp_unslash( $_POST['_changelog_version'] ) )
			: $stored_version;

		// Re-derive when the slug is missing, or when it is the artefact a
		// previous version of this code left behind.
		$needs_slug = empty( $data['post_name'] )
			|| 'auto-draft' === $data['post_name']
			|| (bool) preg_match( '/-auto-draft(-\d+)?$/', $data['post_name'] );

		// Block editor: its REST save publishes first, and WordPress fills
		// post_name from the title; the version only arrives with the meta-box
		// save that follows. Re-derive on that save - the slug is still the
		// title default and this request brings the first version. Entries that
		// already have a stored version, or a slug the owner edited, keep their
		// URL.
		if ( ! $needs_slug && '' === $stored_version && '' !== $version ) {
			$needs_slug = (bool) preg_match( '/^' . preg_quote( sanitize_title( $data['post_title'] ), '/' ) . '(-\d+)?$/', $data['post_name'] );
		}

		if ( $needs_slug ) {
			// Get product slug if available
			$product_slug = 'general';
			if ( ! empty( $postarr['tax_input']['roadmap_product'] ) ) {
				$product_terms = $postarr['tax_input']['roadmap_product'];
				if ( is_array( $product_terms ) && ! empty( $product_terms[0] ) ) {
					$term = get_term( $product_terms[0], 'roadmap_product' );
					if ( $term && ! is_wp_error( $term ) ) {
						$product_slug = $term->slug;
					}
				}
			} elseif ( ! empty( $postarr['ID'] ) ) {
				$existing_products = wp_get_object_terms( $postarr['ID'], 'roadmap_product' );
				if ( ! empty( $existing_products ) && ! is_wp_error( $existing_products ) ) {
					$product_slug = $existing_products[0]->slug;
				}
			}

			// Free assigns its default product in save_meta_boxes(), which runs
			// after this filter: use that product now, or the slug says
			// "general" for an entry that lives under the default product.
			if ( 'general' === $product_slug && self::free_default_product_id() ) {
				$default_term = get_term( self::free_default_product_id(), 'roadmap_product' );
				if ( $default_term && ! is_wp_error( $default_term ) ) {
					$product_slug = $default_term->slug;
				}
			}

			// Generate slug from version or title
			if ( ! empty( $version ) ) {
				$slug_base = $product_slug . '-v' . sanitize_title( $version );
			} else {
				$slug_base = $product_slug . '-' . sanitize_title( $data['post_title'] );
			}

			$data['post_name'] = wp_unique_post_slug(
				$slug_base,
				$postarr['ID'] ?? 0,
				$data['post_status'],
				$data['post_type'],
				$postarr['post_parent'] ?? 0
			);
		}

		return $data;
	}

	/**
	 * Re-derive a changelog slug that was forced to the "general-" fallback
	 * because auto_generate_slug() ran before the block editor's REST request
	 * had attached the roadmap_product term (see the hook registration in the
	 * constructor) - it can land as either "general-v{version}" or, when the
	 * very first autosave ran before a version existed yet too, "general-
	 * {title}". Once REST has actually finished attaching terms and saving
	 * meta, replace just the wrong "general-" prefix with the real product's
	 * slug. Only that exact fallback prefix is touched - a slug the owner
	 * edited, or one that already derived the right product, is left alone.
	 * (Bugs 10326920444.)
	 */
	public function fix_slug_after_rest_terms( $post, $request ) {
		if ( 'roadmap_changelog' !== $post->post_type ) {
			return;
		}

		if ( 0 !== strpos( $post->post_name, 'general-' ) ) {
			return;
		}

		$products = wp_get_object_terms( $post->ID, 'roadmap_product' );
		if ( empty( $products ) || is_wp_error( $products ) ) {
			return;
		}
		$product_slug = $products[0]->slug;

		// The fallback was already correct - this entry really is "general".
		if ( 'general' === $product_slug ) {
			return;
		}

		$version = (string) get_post_meta( $post->ID, '_changelog_version', true );
		$slug_base = '' !== $version
			? $product_slug . '-v' . sanitize_title( $version )
			: $product_slug . '-' . substr( $post->post_name, strlen( 'general-' ) );

		$new_slug = wp_unique_post_slug( $slug_base, $post->ID, $post->post_status, $post->post_type, $post->post_parent );

		if ( $new_slug !== $post->post_name ) {
			wp_update_post(
				array(
					'ID'        => $post->ID,
					'post_name' => $new_slug,
				)
			);
		}
	}

	/**
	 * Load custom single template for changelog
	 */
	public function changelog_single_template( $template ) {
		global $post;

		if ( $post && $post->post_type === 'roadmap_changelog' ) {
			// Check theme first
			$theme_template = locate_template( array( 'single-roadmap_changelog.php' ) );
			if ( $theme_template ) {
				return $theme_template;
			}

			// Use plugin template
			$plugin_template = ROADMAP_PLUGIN_PATH . 'templates/single-roadmap_changelog.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Load custom archive template for changelog
	 */
	public function changelog_archive_template( $template ) {
		if ( is_post_type_archive( 'roadmap_changelog' ) ) {
			// Check theme first
			$theme_template = locate_template( array( 'archive-roadmap_changelog.php' ) );
			if ( $theme_template ) {
				return $theme_template;
			}

			// Use plugin template
			$plugin_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap_changelog.php';
			if ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	/**
	 * Enqueue frontend assets for changelog
	 */
	public function enqueue_frontend_assets() {
		if ( ! is_singular( 'roadmap_changelog' ) && ! is_post_type_archive( 'roadmap_changelog' ) ) {
			return;
		}

		/**
		 * Allow a theme to take over changelog styling entirely.
		 *
		 * Return false (e.g. add_filter( 'roadmap_changelog_enqueue_frontend_css', '__return_false' ))
		 * to stop the plugin from loading its own changelog CSS so the theme can
		 * provide its own.
		 *
		 * @param bool $load Whether to enqueue the plugin's changelog CSS.
		 */
		if ( ! apply_filters( 'roadmap_changelog_enqueue_frontend_css', true ) ) {
			return;
		}

		wp_enqueue_style(
			'roadmap-changelog-frontend',
			self::get_frontend_css_url(),
			array( 'roadmap-tokens' ),
			ROADMAP_VERSION
		);
	}

	/**
	 * Resolve the changelog stylesheet URL: a theme override
	 * (roadmap/changelog-frontend.css in the child or parent theme) if one
	 * exists, else the plugin's own file. Shared by every surface that
	 * enqueues this stylesheet, so a theme override applies everywhere at
	 * once instead of per-template. The item archive template used to
	 * hardcode the plugin's own URL directly, so a theme's override never
	 * reached it. Bugs 10344482685.
	 *
	 * @since 1.8.0
	 * @return string
	 */
	public static function get_frontend_css_url() {
		// Prefer a theme override if the active (child or parent) theme ships
		// `roadmap/changelog-frontend.css`, so themes can style the changelog
		// without a filter. Fall back to the plugin's stylesheet otherwise.
		$relative = 'roadmap/changelog-frontend.css';
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $relative ) ) {
			$css_url = trailingslashit( get_stylesheet_directory_uri() ) . $relative;
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . $relative ) ) {
			$css_url = trailingslashit( get_template_directory_uri() ) . $relative;
		} else {
			$css_url = ROADMAP_PLUGIN_URL . 'assets/css/changelog-frontend.css';
		}

		/**
		 * Filter the final changelog CSS URL (theme override already applied).
		 *
		 * @param string $css_url Stylesheet URL that will be enqueued.
		 */
		return apply_filters( 'roadmap_changelog_frontend_css_url', $css_url );
	}

	/**
	 * Render changelog content for frontend
	 */
	public static function render_changelog_content( $post_id = null ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		$changelog = self::get_changelog_data( $post_id );
		if ( ! $changelog ) {
			return '';
		}

		ob_start();
		?>
		<div class="roadmap-changelog-single">
			<div class="changelog-header">
				<?php if ( ! empty( $changelog['version'] ) ) : ?>
					<span class="changelog-version">v<?php echo esc_html( $changelog['version'] ); ?></span>
				<?php endif; ?>

				<?php if ( ! empty( $changelog['release_date'] ) ) : ?>
					<span class="changelog-date">
						<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $changelog['release_date'] ) ) ); ?>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $changelog['release_type'] ) ) : ?>
					<span class="changelog-release-type changelog-release-type--<?php echo esc_attr( $changelog['release_type'] ); ?>">
						<?php
						$release_types = array(
							'major' => __( 'Major Release', 'product-roadmap' ),
							'minor' => __( 'Minor Release', 'product-roadmap' ),
							'patch' => __( 'Patch', 'product-roadmap' ),
						);
						echo esc_html( $release_types[ $changelog['release_type'] ] ?? $changelog['release_type'] );
						?>
					</span>
				<?php endif; ?>

				<?php if ( ! empty( $changelog['product'] ) ) : ?>
					<span class="changelog-product">
						<?php echo esc_html( $changelog['product'][0]->name ); ?>
					</span>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $changelog['content'] ) ) : ?>
				<div class="changelog-description">
					<?php echo wp_kses_post( wpautop( $changelog['content'] ) ); ?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $changelog['items'] ) ) : ?>
				<div class="changelog-items">
					<h3><?php esc_html_e( 'Changes', 'product-roadmap' ); ?></h3>
					<ul class="changelog-items-list">
						<?php
						foreach ( $changelog['items'] as $item ) :
							$type_data  = $item['type_data'] ?? null;
							$bg_color   = $type_data['background'] ?? '#e2e3e5';
							$text_color = $type_data['color'] ?? '#383d41';
							$type_name  = $type_data['name'] ?? ucfirst( $item['type'] );
							?>
							<li class="changelog-item">
								<?php if ( ! empty( $item['type'] ) ) : ?>
									<span class="changelog-badge"
											style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
										<?php echo esc_html( $type_name ); ?>
									</span>
								<?php endif; ?>
								<span class="changelog-item-description">
									<?php echo esc_html( $item['description'] ); ?>
								</span>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}
}
