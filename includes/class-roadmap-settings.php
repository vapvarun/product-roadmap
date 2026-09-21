<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Roadmap_Settings {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_pages' ), 99 );
		add_action( 'admin_menu', array( $this, 'reorganize_menu' ), 100 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_ajax_roadmap_save_product_settings', array( $this, 'ajax_save_product_settings' ) );
		add_action( 'wp_ajax_roadmap_get_product_settings', array( $this, 'ajax_get_product_settings' ) );

		// Add admin notice for pending submissions
		add_action( 'admin_notices', array( $this, 'show_pending_submissions_notice' ) );
		add_action( 'wp_ajax_roadmap_dismiss_pending_notice', array( $this, 'ajax_dismiss_pending_notice' ) );

		// Add shortcode column to product taxonomy
		add_filter( 'manage_edit-roadmap_product_columns', array( $this, 'add_product_columns' ) );
		add_filter( 'manage_roadmap_product_custom_column', array( $this, 'fill_product_columns' ), 10, 3 );

		// Menu highlighting for taxonomies
		add_filter( 'parent_file', array( $this, 'set_taxonomy_parent_menu' ) );
		add_filter( 'submenu_file', array( $this, 'set_taxonomy_submenu_active' ) );
	}

	public function add_menu_pages() {
		// Add main Roadmap menu
		add_menu_page(
			__( 'Roadmap', 'product-roadmap' ),
			__( 'Roadmap', 'product-roadmap' ),
			'edit_posts',
			'roadmap-dashboard',
			array( $this, 'render_dashboard_page' ),
			'dashicons-chart-line',
			25
		);

		// Add Dashboard submenu (rename the first item)
		add_submenu_page(
			'roadmap-dashboard',
			__( 'Dashboard', 'product-roadmap' ),
			__( 'Dashboard', 'product-roadmap' ),
			'edit_posts',
			'roadmap-dashboard',
			array( $this, 'render_dashboard_page' )
		);

		// Add Products submenu only if Pro is active
		if ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) {
			add_submenu_page(
				'roadmap-dashboard',
				__( 'Products', 'product-roadmap' ),
				__( 'Products', 'product-roadmap' ),
				'manage_options',
				'roadmap-products',
				array( $this, 'render_products_page' )
			);
		}

		// Add All Items submenu
		add_submenu_page(
			'roadmap-dashboard',
			__( 'All Items', 'product-roadmap' ),
			__( 'All Items', 'product-roadmap' ),
			'edit_posts',
			'edit.php?post_type=roadmap_item'
		);

		// Add New Item submenu
		add_submenu_page(
			'roadmap-dashboard',
			__( 'Add New Item', 'product-roadmap' ),
			__( 'Add New Item', 'product-roadmap' ),
			'edit_posts',
			'post-new.php?post_type=roadmap_item'
		);

		// Add Statuses submenu
		add_submenu_page(
			'roadmap-dashboard',
			__( 'Statuses', 'product-roadmap' ),
			__( 'Statuses', 'product-roadmap' ),
			'manage_options',
			'edit-tags.php?taxonomy=roadmap_status&post_type=roadmap_item'
		);

		// Add Categories submenu
		add_submenu_page(
			'roadmap-dashboard',
			__( 'Categories', 'product-roadmap' ),
			__( 'Categories', 'product-roadmap' ),
			'manage_options',
			'edit-tags.php?taxonomy=roadmap_category&post_type=roadmap_item'
		);

		// Add Settings submenu
		add_submenu_page(
			'roadmap-dashboard',
			__( 'Settings', 'product-roadmap' ),
			__( 'Settings', 'product-roadmap' ),
			'manage_options',
			'roadmap-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function reorganize_menu() {
		global $menu, $submenu;

		// Remove the default roadmap_item menu
		remove_menu_page( 'edit.php?post_type=roadmap_item' );

		// Remove default taxonomy submenus if they still exist
		if ( isset( $submenu['edit.php?post_type=roadmap_item'] ) ) {
			unset( $submenu['edit.php?post_type=roadmap_item'] );
		}
	}

	public function register_settings() {
		// Global settings
		register_setting(
			'roadmap_global',
			'roadmap_global_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_global_settings' ),
				'default'           => $this->get_default_global_settings(),
			)
		);

		// Product name and labels
		register_setting(
			'roadmap_global',
			'roadmap_product_name',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '',
			)
		);
		register_setting(
			'roadmap_global',
			'roadmap_item_singular_label',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'Feature',
			)
		);
		register_setting(
			'roadmap_global',
			'roadmap_item_plural_label',
			array(
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => 'Features',
			)
		);

		// Advanced settings (legacy - now handled in global settings)
		register_setting(
			'roadmap_global',
			'roadmap_cache_expiry',
			array(
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 3600,
			)
		);

		// Product-specific settings
		register_setting(
			'roadmap_products',
			'roadmap_product_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_product_settings' ),
				'default'           => array(),
			)
		);
	}

	private function get_default_global_settings() {
		return array(
			'default_status'            => 'planned',
			'items_per_page'            => 100,
			'enable_voting'             => true,
			'allow_guest_voting'        => true,
			'enable_comments'           => true,
			'enable_suggestions'        => true,
			'suggestion_requires_login' => true,
			'auto_approve_suggestions'  => false,
			'suggestion_default_status' => 'under-review',
			'enable_notifications'      => true,
			'notification_email'        => get_option( 'admin_email' ),
			'notify_on_vote'            => false,
			'notify_on_comment'         => true,
			'notify_on_suggestion'      => true,
		);
	}

	private function get_default_product_settings() {
		return array(
			'default_status'   => 'planned',
			'show_in_nav_menu' => true,
			'custom_slug'      => '',
			'theme_color'      => '#2271b1',
			'dark_mode'        => false,
		);
	}

	public function render_dashboard_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		$products     = get_terms(
			array(
				'taxonomy'   => 'roadmap_product',
				'hide_empty' => false,
			)
		);
		$total_items  = wp_count_posts( 'roadmap_item' );
		$recent_items = get_posts(
			array(
				'post_type'      => 'roadmap_item',
				'posts_per_page' => 5,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'post_status'    => array( 'publish', 'pending' ),
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Roadmap Dashboard', 'product-roadmap' ); ?></h1>
			
			<div class="roadmap-dashboard-grid">
				<!-- Stats Cards -->
				<div class="roadmap-stat-card">
					<h3><?php esc_html_e( 'Total Items', 'product-roadmap' ); ?></h3>
					<p class="roadmap-stat-number"><?php echo esc_html( $total_items->publish ); ?></p>
				</div>
				
				<div class="roadmap-stat-card">
					<h3><?php esc_html_e( 'Products', 'product-roadmap' ); ?></h3>
					<p class="roadmap-stat-number"><?php echo count( $products ); ?></p>
					<?php if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) : ?>
						<p style="font-size: 12px; color: #666; margin-top: 10px;">
							<?php esc_html_e( 'Pro feature', 'product-roadmap' ); ?>
						</p>
					<?php endif; ?>
				</div>
				
				<div class="roadmap-stat-card">
					<h3><?php esc_html_e( 'In Progress', 'product-roadmap' ); ?></h3>
					<p class="roadmap-stat-number">
						<?php
						// Use WP_Query with fields=ids for efficient counting
						$in_progress_query = new WP_Query(
							array(
								'post_type'      => 'roadmap_item',
								'posts_per_page' => 1,
								'post_status'    => array( 'publish', 'pending' ),
								'fields'         => 'ids',
                                // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Dashboard statistics query
								'tax_query'      => array(
									array(
										'taxonomy' => 'roadmap_status',
										'field'    => 'slug',
										'terms'    => 'in-progress',
									),
								),
							)
						);
						echo esc_html( (string) $in_progress_query->found_posts );
						wp_reset_postdata();
						?>
					</p>
				</div>
			</div>
			
			<div class="roadmap-dashboard-sections">
				<div class="roadmap-dashboard-section">
					<h2><?php esc_html_e( 'Recent Items', 'product-roadmap' ); ?></h2>
					<?php if ( $recent_items ) : ?>
						<ul class="roadmap-recent-list">
							<?php foreach ( $recent_items as $item ) : ?>
								<li>
									<a href="<?php echo esc_url( get_edit_post_link( $item->ID ) ); ?>">
										<?php echo esc_html( $item->post_title ); ?>
									</a>
									<span class="roadmap-item-date">
										<?php echo esc_html( human_time_diff( strtotime( $item->post_date ), current_time( 'timestamp' ) ) . ' ago' ); ?>
									</span>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php else : ?>
						<p><?php esc_html_e( 'No items yet.', 'product-roadmap' ); ?></p>
					<?php endif; ?>
				</div>
				
				<div class="roadmap-dashboard-section">
					<h2><?php esc_html_e( 'Quick Actions', 'product-roadmap' ); ?></h2>
					<p>
						<a href="<?php echo esc_url( admin_url( 'post-new.php?post_type=roadmap_item' ) ); ?>" 
							class="button button-primary"><?php esc_html_e( 'Add New Item', 'product-roadmap' ); ?></a>
						<?php if ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=roadmap-products' ) ); ?>" 
								class="button"><?php esc_html_e( 'Manage Products', 'product-roadmap' ); ?></a>
						<?php else : ?>
							<a href="#" class="button" disabled style="opacity: 0.6; cursor: not-allowed;" 
								title="<?php esc_html_e( 'Upgrade to Pro to manage multiple products', 'product-roadmap' ); ?>">
								<?php esc_html_e( 'Manage Products (Pro)', 'product-roadmap' ); ?>
							</a>
						<?php endif; ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=roadmap-settings' ) ); ?>" 
							class="button"><?php esc_html_e( 'Settings', 'product-roadmap' ); ?></a>
					</p>
				</div>
			</div>
			
			<?php
			// Allow pro addon to add content to dashboard
			do_action( 'roadmap_dashboard_after_sections' );
			?>
		</div>
		<?php
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading tab parameter for display only
		$active_tab      = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
		$global_settings = get_option( 'roadmap_global_settings', $this->get_default_global_settings() );
		?>
		<div class="wrap roadmap-settings-wrap">
			<div class="roadmap-settings-header">
				<h1><?php esc_html_e( 'Roadmap Settings', 'product-roadmap' ); ?></h1>
				<p class="roadmap-settings-description">
					<?php esc_html_e( 'Configure your roadmap settings to match your workflow and requirements.', 'product-roadmap' ); ?>
				</p>
			</div>
			
			<div class="roadmap-settings-container">
				<div class="roadmap-settings-sidebar">
					<ul class="roadmap-settings-nav">
						<li>
							<a href="?page=roadmap-settings&tab=general" 
								class="roadmap-nav-item <?php echo esc_attr( $active_tab === 'general' ? 'active' : '' ); ?>">
								<i class="dashicons dashicons-admin-settings"></i>
								<span><?php esc_html_e( 'General', 'product-roadmap' ); ?></span>
							</a>
						</li>
						<li>
							<a href="?page=roadmap-settings&tab=notifications"
								class="roadmap-nav-item <?php echo esc_attr( $active_tab === 'notifications' ? 'active' : '' ); ?>">
								<i class="dashicons dashicons-email"></i>
								<span><?php esc_html_e( 'Notifications', 'product-roadmap' ); ?></span>
							</a>
						</li>
						<li>
							<a href="?page=roadmap-settings&tab=changelog-types"
								class="roadmap-nav-item <?php echo esc_attr( $active_tab === 'changelog-types' ? 'active' : '' ); ?>">
								<i class="dashicons dashicons-tag"></i>
								<span><?php esc_html_e( 'Changelog Types', 'product-roadmap' ); ?></span>
							</a>
						</li>
						<?php
						// Allow pro addon to add tabs
						$tabs = apply_filters( 'roadmap_settings_tabs', array() );
						foreach ( $tabs as $tab_key => $tab_data ) {
							$icon  = isset( $tab_data['icon'] ) ? $tab_data['icon'] : 'dashicons-admin-generic';
							$label = isset( $tab_data['label'] ) ? $tab_data['label'] : $tab_data;
							?>
							<li>
								<a href="?page=roadmap-settings&tab=<?php echo esc_attr( $tab_key ); ?>" 
									class="roadmap-nav-item <?php echo esc_attr( $active_tab === $tab_key ? 'active' : '' ); ?>">
									<i class="dashicons <?php echo esc_attr( $icon ); ?>"></i>
									<span><?php echo esc_html( $label ); ?></span>
								</a>
							</li>
							<?php
						}
						?>
					</ul>
					
					<?php if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) : ?>
					<div class="roadmap-pro-cta">
						<h3><?php esc_html_e( 'Upgrade to Pro', 'product-roadmap' ); ?></h3>
						<p><?php esc_html_e( 'Unlock advanced features like multiple products, timeline view, and more.', 'product-roadmap' ); ?></p>
						<a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
							<?php esc_html_e( 'Get Pro Version', 'product-roadmap' ); ?>
						</a>
					</div>
					<?php endif; ?>
				</div>
				
				<div class="roadmap-settings-content">
					<?php
					// Check if this is a tab that needs form wrapper
					$form_tabs = array( 'general', 'notifications', 'advanced' );
					// Allow pro addon to add tabs that need forms
					$form_tabs  = apply_filters( 'roadmap_settings_form_tabs', $form_tabs );
					$needs_form = in_array( $active_tab, $form_tabs );

					if ( $needs_form ) {
						// Determine settings group based on active tab
						$settings_group = 'roadmap_global';
						// Allow pro addon to specify different settings groups for pro tabs
						$settings_group = apply_filters( 'roadmap_settings_group', $settings_group, $active_tab );

						echo '<form method="post" action="options.php" class="roadmap-settings-form">';
						settings_fields( $settings_group );
						// Tell the sanitizer which tab is posting. Every tab shares
						// one option, so without this it cannot tell "unticked on
						// this tab" from "not rendered by this tab" and wipes the
						// other tab's values. See sanitize_global_settings().
						printf(
							'<input type="hidden" name="roadmap_global_settings[_tab]" value="%s">',
							esc_attr( $active_tab )
						);
					}

					switch ( $active_tab ) {
						case 'general':
							$this->render_general_settings_modern( $global_settings );
							break;
						case 'notifications':
							$this->render_notifications_settings_modern( $global_settings );
							break;
						case 'changelog-types':
							$this->render_changelog_types_settings();
							break;
						default:
							// Allow pro addon to render custom tabs
							$this->render_pro_tab_content( $active_tab, $global_settings );
							break;
					}

					if ( $needs_form ) {
						?>
						<div class="roadmap-settings-footer">
							<?php submit_button( __( 'Save Settings', 'product-roadmap' ), 'primary', 'submit', false ); ?>
							<span class="roadmap-settings-saved" style="display: none;">
								<i class="dashicons dashicons-yes-alt"></i> <?php esc_html_e( 'Settings saved', 'product-roadmap' ); ?>
							</span>
						</div>
						
						<?php
						// Settings saved notification handled in roadmap-admin.js
						?>
						</form>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_general_settings_modern( $settings ) {
		$statuses      = get_terms(
			array(
				'taxonomy'   => 'roadmap_status',
				'hide_empty' => false,
			)
		);
		$product_name  = get_option( 'roadmap_product_name', 'Product' );
		$item_singular = get_option( 'roadmap_item_singular_label', 'Feature' );
		$item_plural   = get_option( 'roadmap_item_plural_label', 'Features' );
		?>
		<div class="roadmap-settings-section">
			<h2><?php esc_html_e( 'General Settings', 'product-roadmap' ); ?></h2>
			<p class="section-description">
				<?php esc_html_e( 'Configure the basic settings for your roadmap.', 'product-roadmap' ); ?>
			</p>
			
			<!-- Product Configuration Card -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-portfolio"></i> <?php esc_html_e( 'Product Configuration', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<div class="form-row">
						<label for="product_name"><?php esc_html_e( 'Product Name', 'product-roadmap' ); ?></label>
						<input type="text" name="roadmap_product_name" id="product_name" 
								value="<?php echo esc_attr( $product_name ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'The name of your product or project.', 'product-roadmap' ); ?></p>
					</div>
					
					<div class="form-row-group">
						<div class="form-row half">
							<label for="item_singular"><?php esc_html_e( 'Item Label (Singular)', 'product-roadmap' ); ?></label>
							<input type="text" name="roadmap_item_singular_label" id="item_singular" 
									value="<?php echo esc_attr( $item_singular ); ?>" />
							<p class="description"><?php esc_html_e( 'e.g., Feature, Task, Story', 'product-roadmap' ); ?></p>
						</div>
						<div class="form-row half">
							<label for="item_plural"><?php esc_html_e( 'Item Label (Plural)', 'product-roadmap' ); ?></label>
							<input type="text" name="roadmap_item_plural_label" id="item_plural" 
									value="<?php echo esc_attr( $item_plural ); ?>" />
							<p class="description"><?php esc_html_e( 'e.g., Features, Tasks, Stories', 'product-roadmap' ); ?></p>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Display Settings Card -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-layout"></i> <?php esc_html_e( 'Display Settings', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<div class="form-row-group">
						<div class="form-row half">
							<label for="default_status"><?php esc_html_e( 'Default Status', 'product-roadmap' ); ?></label>
							<select name="roadmap_global_settings[default_status]" id="default_status">
								<?php foreach ( $statuses as $status ) : ?>
									<option value="<?php echo esc_attr( $status->slug ); ?>" 
										<?php selected( $settings['default_status'], $status->slug ); ?>>
										<?php echo esc_html( $status->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Default status for new items.', 'product-roadmap' ); ?></p>
						</div>
						
						<div class="form-row half">
							<label for="items_per_page"><?php esc_html_e( 'Items Per Page', 'product-roadmap' ); ?></label>
							<input type="number" name="roadmap_global_settings[items_per_page]" 
									id="items_per_page" value="<?php echo esc_attr( $settings['items_per_page'] ); ?>" 
									min="5" max="100" />
							<p class="description"><?php esc_html_e( 'Items to show per page.', 'product-roadmap' ); ?></p>
						</div>
					</div>
				</div>
			</div>
			
			<!-- Permissions & Access Card -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-admin-users"></i> <?php esc_html_e( 'Permissions & Access', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<div class="form-row">
						<label for="who_can_vote"><?php esc_html_e( 'Who Can Vote', 'product-roadmap' ); ?></label>
						<select name="roadmap_global_settings[allow_guest_voting]" id="who_can_vote">
							<option value="1" <?php selected( $settings['allow_guest_voting'] ?? true, true ); ?>>
								<?php esc_html_e( 'Anyone (including guests)', 'product-roadmap' ); ?>
							</option>
							<option value="0" <?php selected( $settings['allow_guest_voting'] ?? true, false ); ?>>
								<?php esc_html_e( 'Logged in users only', 'product-roadmap' ); ?>
							</option>
						</select>
						<p class="description"><?php esc_html_e( 'Control who can vote on roadmap items.', 'product-roadmap' ); ?></p>
					</div>
					
					<div class="form-row">
						<label for="who_can_suggest"><?php esc_html_e( 'Who Can Suggest Ideas', 'product-roadmap' ); ?></label>
						<?php $is_pro = defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE; ?>
						<select name="roadmap_global_settings[suggestion_requires_login]" id="who_can_suggest">
							<option value="0" <?php selected( $settings['suggestion_requires_login'] ?? true, false ); ?> <?php echo ! $is_pro ? 'disabled' : ''; ?>>
								<?php esc_html_e( 'Anyone (including guests)', 'product-roadmap' ); ?>
								<?php if ( ! $is_pro ) : ?>
									<?php esc_html_e( '(Pro)', 'product-roadmap' ); ?>
								<?php endif; ?>
							</option>
							<option value="1" <?php selected( $settings['suggestion_requires_login'] ?? true, true ); ?>>
								<?php esc_html_e( 'Logged in users only', 'product-roadmap' ); ?>
							</option>
						</select>
						<?php if ( ! $is_pro ) : ?>
							<p class="description">
								<span style="color: #d63638;"><?php esc_html_e( 'Guest suggestions require Pro addon.', 'product-roadmap' ); ?></span>
								<?php esc_html_e( 'Currently only logged-in users can submit ideas.', 'product-roadmap' ); ?>
							</p>
						<?php else : ?>
							<p class="description"><?php esc_html_e( 'Control who can submit new ideas.', 'product-roadmap' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			
			<!-- Features Card -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-admin-generic"></i> <?php esc_html_e( 'Features', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<div class="features-grid">
						<div class="feature-toggle">
							<label class="toggle-switch">
								<input type="checkbox" name="roadmap_global_settings[enable_voting]" value="1" 
										<?php checked( $settings['enable_voting'], true ); ?> />
								<span class="toggle-slider"></span>
							</label>
							<div class="feature-info">
								<h4><?php esc_html_e( 'Enable Voting', 'product-roadmap' ); ?></h4>
								<p><?php esc_html_e( 'Allow users to vote on roadmap items.', 'product-roadmap' ); ?></p>
							</div>
						</div>
						
						<div class="feature-toggle">
							<label class="toggle-switch">
								<input type="checkbox" name="roadmap_global_settings[enable_comments]" value="1" 
										<?php checked( $settings['enable_comments'], true ); ?> />
								<span class="toggle-slider"></span>
							</label>
							<div class="feature-info">
								<h4><?php esc_html_e( 'Enable Comments', 'product-roadmap' ); ?></h4>
								<p><?php esc_html_e( 'Allow users to comment on items.', 'product-roadmap' ); ?></p>
							</div>
						</div>
						
						<div class="feature-toggle">
							<label class="toggle-switch">
								<input type="checkbox" name="roadmap_global_settings[enable_suggestions]" value="1" 
										<?php checked( $settings['enable_suggestions'], true ); ?> />
								<span class="toggle-slider"></span>
							</label>
							<div class="feature-info">
								<h4><?php esc_html_e( 'User Suggestions', 'product-roadmap' ); ?></h4>
								<p><?php esc_html_e( 'Let users submit feature ideas.', 'product-roadmap' ); ?></p>
							</div>
						</div>
					</div>
					
					<?php if ( $settings['enable_suggestions'] ) : ?>
					<div class="form-row" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
						<label>
							<input type="checkbox" name="roadmap_global_settings[auto_approve_suggestions]" value="1"
									<?php checked( $settings['auto_approve_suggestions'] ?? false, true ); ?> />
							<?php esc_html_e( 'Auto-approve user suggestions', 'product-roadmap' ); ?>
						</label>
						<p class="description"><?php esc_html_e( 'Automatically publish user suggestions without manual review. Use with caution.', 'product-roadmap' ); ?></p>
					</div>

					<div class="form-row" style="margin-top: 15px;">
						<label for="suggestion_default_status"><?php esc_html_e( 'Default Status for Suggestions', 'product-roadmap' ); ?></label>
						<select name="roadmap_global_settings[suggestion_default_status]" id="suggestion_default_status">
							<?php foreach ( $statuses as $status ) : ?>
								<option value="<?php echo esc_attr( $status->slug ); ?>"
									<?php selected( $settings['suggestion_default_status'] ?? 'under-review', $status->slug ); ?>>
									<?php echo esc_html( $status->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Status assigned to user-submitted suggestions (when auto-approve is enabled).', 'product-roadmap' ); ?></p>
					</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private function render_notifications_settings_modern( $settings ) {
		?>
		<div class="roadmap-settings-section">
			<h2><?php esc_html_e( 'Email Notifications', 'product-roadmap' ); ?></h2>
			<p class="section-description">
				<?php esc_html_e( 'Configure email notifications to stay informed about roadmap activity.', 'product-roadmap' ); ?>
			</p>
			
			<!-- Email Configuration Card -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-email-alt"></i> <?php esc_html_e( 'Email Configuration', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<div class="feature-toggle large">
						<label class="toggle-switch">
							<input type="checkbox" name="roadmap_global_settings[enable_notifications]" 
									id="enable_notifications" value="1" 
									<?php checked( $settings['enable_notifications'], true ); ?> />
							<span class="toggle-slider"></span>
						</label>
						<div class="feature-info">
							<h4><?php esc_html_e( 'Enable Email Notifications', 'product-roadmap' ); ?></h4>
							<p><?php esc_html_e( 'Receive email alerts for roadmap activity.', 'product-roadmap' ); ?></p>
						</div>
					</div>
					
					<div class="form-row" style="margin-top: 20px;">
						<label for="notification_email"><?php esc_html_e( 'Notification Email Addresses', 'product-roadmap' ); ?></label>
						<textarea name="roadmap_global_settings[notification_email]"
									id="notification_email"
									rows="5"
									class="large-text code"
									placeholder="admin@example.com&#10;manager@example.com&#10;team@example.com"><?php echo esc_textarea( $settings['notification_email'] ); ?></textarea>
						<p class="description"><?php esc_html_e( 'Enter one email address per line. All addresses will receive notification emails. Leave empty to use the admin email.', 'product-roadmap' ); ?></p>
					</div>
				</div>
			</div>
			
			<!-- Notification Events Card -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-bell"></i> <?php esc_html_e( 'Notification Events', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<p class="card-description"><?php esc_html_e( 'Choose which events trigger email notifications.', 'product-roadmap' ); ?></p>
					
					<div class="notification-events">
						<div class="notification-event">
							<label class="checkbox-label">
								<input type="checkbox" name="roadmap_global_settings[notify_on_vote]" value="1" 
										<?php checked( $settings['notify_on_vote'] ?? false, true ); ?> />
								<span class="checkbox-text">
									<strong><?php esc_html_e( 'New Votes', 'product-roadmap' ); ?></strong>
									<small><?php esc_html_e( 'When users vote on roadmap items', 'product-roadmap' ); ?></small>
								</span>
							</label>
						</div>
						
						<div class="notification-event">
							<label class="checkbox-label">
								<input type="checkbox" name="roadmap_global_settings[notify_on_comment]" value="1" 
										<?php checked( $settings['notify_on_comment'], true ); ?> />
								<span class="checkbox-text">
									<strong><?php esc_html_e( 'New Comments', 'product-roadmap' ); ?></strong>
									<small><?php esc_html_e( 'When users comment on items', 'product-roadmap' ); ?></small>
								</span>
							</label>
						</div>
						
						<div class="notification-event">
							<label class="checkbox-label">
								<input type="checkbox" name="roadmap_global_settings[notify_on_suggestion]" value="1" 
										<?php checked( $settings['notify_on_suggestion'], true ); ?> />
								<span class="checkbox-text">
									<strong><?php esc_html_e( 'New Suggestions', 'product-roadmap' ); ?></strong>
									<small><?php esc_html_e( 'When users submit feature suggestions', 'product-roadmap' ); ?></small>
								</span>
							</label>
						</div>
					</div>
				</div>
			</div>
			
			<?php if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) : ?>
			<!-- Pro Features Preview -->
			<div class="roadmap-settings-card pro-preview">
				<div class="card-header">
					<h3><i class="dashicons dashicons-star-filled"></i> <?php esc_html_e( 'Pro Notification Features', 'product-roadmap' ); ?></h3>
					<span class="pro-badge">PRO</span>
				</div>
				<div class="card-body">
					<ul class="pro-features-list">
						<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Custom email templates', 'product-roadmap' ); ?></li>
						<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Multiple notification recipients', 'product-roadmap' ); ?></li>
						<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Digest emails (daily/weekly)', 'product-roadmap' ); ?></li>
						<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'User notification preferences', 'product-roadmap' ); ?></li>
					</ul>
					<a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
						<?php esc_html_e( 'Upgrade to Pro', 'product-roadmap' ); ?>
					</a>
				</div>
			</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function render_products_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$products = get_terms(
			array(
				'taxonomy'   => 'roadmap_product',
				'hide_empty' => false,
			)
		);
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Reading product parameter for display only
		$selected_product = isset( $_GET['product'] ) ? sanitize_text_field( wp_unslash( $_GET['product'] ) ) : '';

		// Get total votes across all products
		global $wpdb;
		$votes_table = $wpdb->prefix . 'roadmap_votes';
		?>
		<div class="roadmap-settings-wrap">
			<div class="roadmap-settings-header">
				<h1><?php esc_html_e( 'Products', 'product-roadmap' ); ?></h1>
				<p class="roadmap-settings-description">
					<?php esc_html_e( 'Manage your product roadmaps. Each product has its own roadmap that can be displayed using shortcodes.', 'product-roadmap' ); ?>
				</p>
			</div>

			<?php if ( empty( $products ) ) : ?>
				<div class="roadmap-empty-state">
					<span class="dashicons dashicons-products"></span>
					<h3><?php esc_html_e( 'No Products Yet', 'product-roadmap' ); ?></h3>
					<p><?php esc_html_e( 'Create your first product to start building roadmaps your users will love.', 'product-roadmap' ); ?></p>
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=roadmap_product&post_type=roadmap_item' ) ); ?>"
						class="button button-primary button-hero">
						<span class="dashicons dashicons-plus-alt2"></span>
						<?php esc_html_e( 'Create Your First Product', 'product-roadmap' ); ?>
					</a>
				</div>
			<?php else : ?>
				<div class="products-header-actions">
					<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=roadmap_product&post_type=roadmap_item' ) ); ?>"
						class="button button-primary">
						<span class="dashicons dashicons-plus-alt2"></span>
						<?php esc_html_e( 'Add New Product', 'product-roadmap' ); ?>
					</a>
				</div>

				<div class="roadmap-products-grid">
					<?php
					foreach ( $products as $product ) :
						$product_settings = $this->get_product_settings( $product->term_id );

						// Count only roadmap_item posts for this product (not changelogs).
						// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
						$item_count = (int) $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COUNT(DISTINCT p.ID)
								FROM {$wpdb->posts} p
								INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
								INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
								WHERE tt.term_id = %d
								AND tt.taxonomy = 'roadmap_product'
								AND p.post_type = 'roadmap_item'
								AND p.post_status = 'publish'",
								$product->term_id
							)
						);

						$theme_color = ! empty( $product_settings['theme_color'] ) ? $product_settings['theme_color'] : '#2271b1';

						// Get vote count for this product
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Simple count query for display
						$vote_count = $wpdb->get_var(
							$wpdb->prepare(
								"SELECT COALESCE(SUM(v.vote_value), 0)
                             FROM {$votes_table} v
                             INNER JOIN {$wpdb->posts} p ON v.item_id = p.ID
                             INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                             INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                             WHERE tt.term_id = %d AND tt.taxonomy = 'roadmap_product'",
								$product->term_id
							)
						);
						$vote_count = intval( $vote_count );
						?>
						<div class="roadmap-product-card" style="--product-color: <?php echo esc_attr( $theme_color ); ?>;">
							<div class="product-card-header">
								<div class="product-icon" style="background: <?php echo esc_attr( $theme_color ); ?>;">
									<span class="dashicons dashicons-clipboard"></span>
								</div>
								<div class="product-title-wrap">
									<h3><?php echo esc_html( $product->name ); ?></h3>
									<span class="product-slug">/<?php echo esc_html( $product->slug ); ?></span>
								</div>
							</div>

							<?php if ( ! empty( $product->description ) ) : ?>
								<p class="product-description"><?php echo esc_html( $product->description ); ?></p>
							<?php endif; ?>

							<div class="product-stats">
								<div class="stat-item">
									<span class="stat-icon dashicons dashicons-list-view"></span>
									<div class="stat-content">
										<span class="stat-number"><?php echo esc_html( (string) $item_count ); ?></span>
										<span class="stat-label"><?php echo esc_html( _n( 'Item', 'Items', $item_count, 'product-roadmap' ) ); ?></span>
									</div>
								</div>
								<div class="stat-item">
									<span class="stat-icon dashicons dashicons-thumbs-up"></span>
									<div class="stat-content">
										<span class="stat-number"><?php echo esc_html( (string) $vote_count ); ?></span>
										<span class="stat-label"><?php echo esc_html( _n( 'Vote', 'Votes', $vote_count, 'product-roadmap' ) ); ?></span>
									</div>
								</div>
							</div>

							<div class="product-shortcode">
								<label><?php esc_html_e( 'Shortcode', 'product-roadmap' ); ?></label>
								<div class="shortcode-copy-wrapper">
									<code class="shortcode-text">[roadmap product="<?php echo esc_attr( $product->slug ); ?>"]</code>
									<button type="button" class="copy-shortcode-btn"
											data-shortcode='[roadmap product="<?php echo esc_attr( $product->slug ); ?>"]'
											title="<?php esc_attr_e( 'Copy to clipboard', 'product-roadmap' ); ?>">
										<span class="dashicons dashicons-admin-page"></span>
									</button>
								</div>
							</div>

							<div class="product-actions">
								<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=roadmap_item&roadmap_product=' . $product->slug ) ); ?>"
									class="button button-primary">
									<span class="dashicons dashicons-visibility"></span>
									<?php esc_html_e( 'View Items', 'product-roadmap' ); ?>
								</a>
								<a href="<?php echo esc_url( get_term_link( $product, 'roadmap_product' ) ); ?>"
									class="button" target="_blank">
									<span class="dashicons dashicons-external"></span>
									<?php esc_html_e( 'View Roadmap', 'product-roadmap' ); ?>
								</a>
								<a href="#" class="button" onclick="editProductSettings('<?php echo esc_attr( (string) $product->term_id ); ?>'); return false;">
									<span class="dashicons dashicons-admin-generic"></span>
									<?php esc_html_e( 'Settings', 'product-roadmap' ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<!-- Product Settings Modal -->
				<div id="product-settings-modal" class="roadmap-modal" style="display: none;">
					<div class="roadmap-modal-content">
						<span class="roadmap-modal-close">&times;</span>
						<h2><?php esc_html_e( 'Product Settings', 'product-roadmap' ); ?></h2>
						<form id="product-settings-form">
							<input type="hidden" id="product_id" name="product_id" value="">
							<?php wp_nonce_field( 'roadmap_product_settings', 'roadmap_nonce' ); ?>
							
							<table class="form-table">
								<tr>
									<th scope="row">
										<label for="product_default_status"><?php esc_html_e( 'Default Status', 'product-roadmap' ); ?></label>
									</th>
									<td>
										<select name="default_status" id="product_default_status">
											<?php
											$statuses = get_terms(
												array(
													'taxonomy' => 'roadmap_status',
													'hide_empty' => false,
												)
											);
											foreach ( $statuses as $status ) :
												?>
												<option value="<?php echo esc_attr( $status->slug ); ?>">
													<?php echo esc_html( $status->name ); ?>
												</option>
											<?php endforeach; ?>
										</select>
									</td>
								</tr>
								
								<tr>
									<th scope="row"><?php esc_html_e( 'Features', 'product-roadmap' ); ?></th>
									<td>
										<p class="description">
											<?php esc_html_e( 'Feature settings are controlled globally. Use the main settings page to enable/disable voting, comments, and suggestions.', 'product-roadmap' ); ?>
										</p>
									</td>
								</tr>
								
								<tr>
									<th scope="row">
										<label for="product_theme_color"><?php esc_html_e( 'Theme Color', 'product-roadmap' ); ?></label>
									</th>
									<td>
										<input type="color" name="theme_color" id="product_theme_color" value="#2271b1">
									</td>
								</tr>
							</table>
							
							<p class="submit">
								<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Settings', 'product-roadmap' ); ?></button>
							</p>
						</form>
					</div>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	public function ajax_save_product_settings() {
		check_ajax_referer( 'roadmap_product_settings', 'roadmap_nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$settings   = array(
			'default_status' => isset( $_POST['default_status'] ) ? sanitize_text_field( wp_unslash( $_POST['default_status'] ) ) : '',
			'theme_color'    => isset( $_POST['theme_color'] ) ? sanitize_hex_color( wp_unslash( $_POST['theme_color'] ) ) : '',
		);

		update_term_meta( $product_id, 'roadmap_product_settings', $settings );

		wp_send_json_success();
	}

	public function get_product_settings( $product_id ) {
		$settings = get_term_meta( $product_id, 'roadmap_product_settings', true );
		if ( empty( $settings ) ) {
			$settings = $this->get_default_product_settings();
		}
		return $settings;
	}

	public function ajax_get_product_settings() {
		check_ajax_referer( 'roadmap_product_settings', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die();
		}

		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$settings   = $this->get_product_settings( $product_id );

		wp_send_json_success( $settings );
	}

	/**
	 * Sanitize yes/no option
	 */
	public function sanitize_yes_no( $input ) {
		return ( $input === 'yes' ) ? 'yes' : 'no';
	}

	/**
	 * Which settings keys each tab is allowed to write.
	 *
	 * The Settings screen splits one option across several tabs, and a browser
	 * only posts the fields it rendered. Rebuilding the whole option from a
	 * single tab's POST therefore reset every field the other tabs own - saving
	 * Notifications silently turned voting, comments, suggestions and guest
	 * voting off and reset items_per_page. This map is what makes a partial
	 * save safe.
	 *
	 * @return array<string, string[]> Tab slug => keys that tab owns.
	 */
	private function get_tab_setting_keys() {
		return apply_filters(
			'roadmap_tab_setting_keys',
			array(
				'general'       => array(
					'default_status',
					'items_per_page',
					'enable_voting',
					'allow_guest_voting',
					'enable_comments',
					'enable_suggestions',
					'suggestion_requires_login',
					'auto_approve_suggestions',
					'suggestion_default_status',
				),
				'notifications' => array(
					'enable_notifications',
					'notification_email',
					'notify_on_vote',
					'notify_on_comment',
					'notify_on_suggestion',
				),
			)
		);
	}

	public function sanitize_global_settings( $input ) {
		// Start from what is already stored so keys this tab does not render
		// survive the save untouched.
		$stored    = get_option( 'roadmap_global_settings', array() );
		$sanitized = is_array( $stored ) ? $stored : array();

		$posting_tab = isset( $input['_tab'] ) ? sanitize_key( $input['_tab'] ) : '';
		$tab_keys    = $this->get_tab_setting_keys();

		// An unknown tab (or a programmatic save with no marker) keeps the old
		// whole-option behaviour, so nothing that writes this option directly
		// silently stops working.
		$owned = $tab_keys[ $posting_tab ] ?? null;

		unset( $input['_tab'] );

		$candidate = array();

		$candidate['default_status']            = sanitize_text_field( $input['default_status'] ?? 'planned' );
		$candidate['items_per_page']            = absint( $input['items_per_page'] ?? 100 );
		$candidate['enable_voting']             = ! empty( $input['enable_voting'] );
		$candidate['allow_guest_voting']        = ! empty( $input['allow_guest_voting'] );
		$candidate['enable_comments']           = ! empty( $input['enable_comments'] );
		$candidate['enable_suggestions']        = ! empty( $input['enable_suggestions'] );
		$candidate['suggestion_requires_login'] = ! empty( $input['suggestion_requires_login'] );
		$candidate['auto_approve_suggestions']  = ! empty( $input['auto_approve_suggestions'] );
		$candidate['suggestion_default_status'] = sanitize_text_field( $input['suggestion_default_status'] ?? 'under-review' );
		$candidate['enable_notifications']      = ! empty( $input['enable_notifications'] );

		// Sanitize notification email(s) - supports multiple emails (one per line)
		if ( ! empty( $input['notification_email'] ) ) {
			$emails                          = array_map( 'trim', explode( "\n", $input['notification_email'] ) );
			$emails                          = array_filter( $emails ); // Remove empty lines
			$emails                          = array_map( 'sanitize_email', $emails );
			$emails                          = array_filter( $emails ); // Remove invalid emails
			$candidate['notification_email'] = implode( "\n", $emails );
		} else {
			$candidate['notification_email'] = '';
		}

		$candidate['notify_on_vote']       = ! empty( $input['notify_on_vote'] );
		$candidate['notify_on_comment']    = ! empty( $input['notify_on_comment'] );
		$candidate['notify_on_suggestion'] = ! empty( $input['notify_on_suggestion'] );

		if ( null === $owned ) {
			return $candidate;
		}

		foreach ( $owned as $key ) {
			if ( array_key_exists( $key, $candidate ) ) {
				$sanitized[ $key ] = $candidate[ $key ];
			}
		}

		return $sanitized;
	}

	public function sanitize_product_settings( $input ) {
		// Sanitize product-specific settings
		return $input;
	}

	public function enqueue_admin_assets( $hook ) {
		// Check if we're on a roadmap admin page
		$is_roadmap_page = (
			strpos( $hook, 'roadmap' ) !== false ||
			strpos( $hook, 'roadmap_item' ) !== false ||
			( isset( $_GET['page'] ) && strpos( sanitize_text_field( wp_unslash( $_GET['page'] ) ), 'roadmap' ) !== false )
		);

		if ( ! $is_roadmap_page ) {
			return;
		}

		// Enqueue modern admin CSS
		wp_enqueue_style( 'roadmap-admin', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-admin.css', array(), ROADMAP_VERSION );

		// Enqueue admin JavaScript
		wp_enqueue_script( 'roadmap-admin', ROADMAP_PLUGIN_URL . 'assets/js/roadmap-admin.js', array( 'jquery' ), ROADMAP_VERSION, true );

		// Localize script for AJAX and translations
		wp_localize_script(
			'roadmap-admin',
			'roadmap_admin',
			array(
				'ajax_url'            => admin_url( 'admin-ajax.php' ),
				'nonce'               => wp_create_nonce( 'roadmap_admin_nonce' ),
				'save_error'          => __( 'Error saving settings. Please try again.', 'product-roadmap' ),
			)
		);

		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
	}

	public function add_product_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $key => $value ) {
			$new_columns[ $key ] = $value;

			// Add shortcode column after name
			if ( $key === 'name' ) {
				$new_columns['shortcode'] = __( 'Shortcode', 'product-roadmap' );
			}
		}

		return $new_columns;
	}

	public function fill_product_columns( $content, $column_name, $term_id ) {
		if ( $column_name === 'shortcode' ) {
			$term = get_term( $term_id, 'roadmap_product' );
			if ( $term && ! is_wp_error( $term ) ) {
				$shortcode = '[roadmap product="' . esc_attr( $term->slug ) . '"]';
				$content   = '<div class="shortcode-wrapper inline-shortcode">';
				$content  .= '<code class="shortcode-text" style="user-select: all;">' . $shortcode . '</code>';
				$content  .= '<button type="button" class="button button-small copy-shortcode" ';
				$content  .= 'data-shortcode="' . esc_attr( $shortcode ) . '" ';
				$content  .= 'title="' . esc_attr__( 'Copy to clipboard', 'product-roadmap' ) . '">';
				$content  .= '<span class="dashicons dashicons-clipboard"></span>';
				$content  .= '</button>';
				$content  .= '</div>';
			}
		}

		return $content;
	}

	public function set_taxonomy_parent_menu( $parent_file ) {
		global $current_screen;

		// Check if we're on a roadmap taxonomy page
		if ( $current_screen && isset( $current_screen->post_type ) ) {
			if ( $current_screen->post_type === 'roadmap_item' ) {
				// Force the parent menu to be roadmap-dashboard
				return 'roadmap-dashboard';
			}
		}

		return $parent_file;
	}

	public function set_taxonomy_submenu_active( $submenu_file ) {
		global $current_screen, $taxonomy;

		// Check if we're on a roadmap taxonomy page
		if ( $current_screen && isset( $current_screen->taxonomy ) && isset( $current_screen->post_type ) ) {
			if ( $current_screen->post_type === 'roadmap_item' ) {
				// Highlight the appropriate submenu based on taxonomy
				switch ( $current_screen->taxonomy ) {
					case 'roadmap_status':
						return 'edit-tags.php?taxonomy=roadmap_status&post_type=roadmap_item';
					case 'roadmap_category':
						return 'edit-tags.php?taxonomy=roadmap_category&post_type=roadmap_item';
					case 'roadmap_product':
						if ( defined( 'ROADMAP_PRO_ACTIVE' ) && ROADMAP_PRO_ACTIVE ) {
							return 'roadmap-products';
						}
						break;
				}
			}
		}

		return $submenu_file;
	}

	private function render_pro_tab_content( $tab, $settings ) {
		// Provide card-based wrapper for pro tabs
		switch ( $tab ) {
			case 'templates':
				$this->render_templates_tab( $settings );
				break;
			case 'advanced':
				$this->render_advanced_tab( $settings );
				break;
			case 'license':
				// Let pro addon handle license tab
				do_action( 'roadmap_render_license_tab' );
				break;
			default:
				// Allow other pro tabs
				do_action( 'roadmap_settings_tab_content', $tab );
				break;
		}
	}

	private function render_templates_tab( $settings ) {
		?>
		<div class="roadmap-settings-section">
			<h2><?php esc_html_e( 'Template Settings', 'product-roadmap' ); ?></h2>
			<p class="section-description">
				<?php esc_html_e( 'Customize the appearance and layout of your roadmap.', 'product-roadmap' ); ?>
			</p>
			
			<?php if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) : ?>
			<!-- Pro Feature Notice -->
			<div class="roadmap-settings-card pro-preview">
				<div class="card-header">
					<h3><i class="dashicons dashicons-layout"></i> <?php esc_html_e( 'Template Options', 'product-roadmap' ); ?></h3>
					<span class="pro-badge">PRO</span>
				</div>
				<div class="card-body">
					<div class="roadmap-empty-state">
						<i class="dashicons dashicons-star-filled"></i>
						<h3><?php esc_html_e( 'Pro Feature', 'product-roadmap' ); ?></h3>
						<p><?php esc_html_e( 'Unlock multiple template layouts and customization options with the Pro version.', 'product-roadmap' ); ?></p>
						<ul class="pro-features-list" style="text-align: left; max-width: 400px; margin: 20px auto;">
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Timeline view template', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Grid view template', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Kanban board template', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Compact list template', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Custom CSS editor', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Dark mode support', 'product-roadmap' ); ?></li>
						</ul>
						<a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
							<?php esc_html_e( 'Get Pro Version', 'product-roadmap' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php else : ?>
				<?php // Pro version would render actual settings here ?>
				<?php do_action( 'roadmap_render_templates_settings', $settings ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	private function render_advanced_tab( $settings ) {
		?>
		<div class="roadmap-settings-section">
			<h2><?php esc_html_e( 'Advanced Settings', 'product-roadmap' ); ?></h2>
			<p class="section-description">
				<?php esc_html_e( 'Fine-tune your roadmap with advanced configuration options.', 'product-roadmap' ); ?>
			</p>
			
			<!-- Cache Settings Card -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-performance"></i> <?php esc_html_e( 'Performance Settings', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<div class="form-row">
						<label for="cache_expiry"><?php esc_html_e( 'Cache Expiry (seconds)', 'product-roadmap' ); ?></label>
						<input type="number" name="roadmap_cache_expiry" id="cache_expiry" 
								value="<?php echo esc_attr( get_option( 'roadmap_cache_expiry', '3600' ) ); ?>" 
								min="0" max="86400" />
						<p class="description"><?php esc_html_e( 'How long to cache roadmap data. Set to 0 to disable caching.', 'product-roadmap' ); ?></p>
					</div>
				</div>
			</div>
			
			<?php if ( ! defined( 'ROADMAP_PRO_ACTIVE' ) || ! ROADMAP_PRO_ACTIVE ) : ?>
			<!-- Pro Feature Notice -->
			<div class="roadmap-settings-card pro-preview">
				<div class="card-header">
					<h3><i class="dashicons dashicons-admin-tools"></i> <?php esc_html_e( 'More Advanced Options', 'product-roadmap' ); ?></h3>
					<span class="pro-badge">PRO</span>
				</div>
				<div class="card-body">
					<div class="roadmap-empty-state">
						<i class="dashicons dashicons-star-filled"></i>
						<h3><?php esc_html_e( 'Pro Features', 'product-roadmap' ); ?></h3>
						<p><?php esc_html_e( 'Access more advanced settings and customization options with the Pro version.', 'product-roadmap' ); ?></p>
						<ul class="pro-features-list" style="text-align: left; max-width: 400px; margin: 20px auto;">
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Custom post type slug', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Advanced permissions', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'API access control', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Export/Import settings', 'product-roadmap' ); ?></li>
							<li><i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Database optimization', 'product-roadmap' ); ?></li>
						</ul>
						<a href="https://wbcomdesigns.com/downloads/product-roadmap-pro/" class="button button-primary" target="_blank">
							<?php esc_html_e( 'Get Pro Version', 'product-roadmap' ); ?>
						</a>
					</div>
				</div>
			</div>
			<?php else : ?>
				<?php // Pro version would render actual settings here ?>
				<?php do_action( 'roadmap_render_advanced_settings', $settings ); ?>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render changelog types settings tab
	 */
	private function render_changelog_types_settings() {
		$change_types = get_terms(
			array(
				'taxonomy'   => 'changelog_type',
				'hide_empty' => false,
			)
		);
		?>
		<div class="roadmap-settings-section">
			<h2><?php esc_html_e( 'Changelog Badge Types', 'product-roadmap' ); ?></h2>
			<p class="section-description">
				<?php esc_html_e( 'Customize the badge types used in your changelogs. Each type can have its own color scheme.', 'product-roadmap' ); ?>
			</p>

			<!-- Existing Badge Types -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-tag"></i> <?php esc_html_e( 'Badge Types', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<?php if ( ! empty( $change_types ) && ! is_wp_error( $change_types ) ) : ?>
						<table class="widefat changelog-types-table">
							<thead>
								<tr>
									<th><?php esc_html_e( 'Name', 'product-roadmap' ); ?></th>
									<th><?php esc_html_e( 'Slug', 'product-roadmap' ); ?></th>
									<th style="width: 150px;"><?php esc_html_e( 'Preview', 'product-roadmap' ); ?></th>
									<th style="width: 100px;"><?php esc_html_e( 'Text Color', 'product-roadmap' ); ?></th>
									<th style="width: 100px;"><?php esc_html_e( 'Background', 'product-roadmap' ); ?></th>
									<th style="width: 100px;"><?php esc_html_e( 'Actions', 'product-roadmap' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								foreach ( $change_types as $type ) :
									$color      = get_term_meta( $type->term_id, '_changelog_type_color', true ) ?: '#383d41';
									$background = get_term_meta( $type->term_id, '_changelog_type_background', true ) ?: '#e2e3e5';
									?>
									<tr data-term-id="<?php echo esc_attr( (string) $type->term_id ); ?>">
										<td>
											<strong><?php echo esc_html( $type->name ); ?></strong>
										</td>
										<td>
											<code><?php echo esc_html( $type->slug ); ?></code>
										</td>
										<td>
											<span class="changelog-badge-preview" style="display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 500; color: <?php echo esc_attr( $color ); ?>; background: <?php echo esc_attr( $background ); ?>;">
												<?php echo esc_html( $type->name ); ?>
											</span>
										</td>
										<td>
											<input type="color"
													class="changelog-type-color"
													value="<?php echo esc_attr( $color ); ?>"
													data-term-id="<?php echo esc_attr( (string) $type->term_id ); ?>"
													data-field="color">
										</td>
										<td>
											<input type="color"
													class="changelog-type-background"
													value="<?php echo esc_attr( $background ); ?>"
													data-term-id="<?php echo esc_attr( (string) $type->term_id ); ?>"
													data-field="background">
										</td>
										<td>
											<a href="<?php echo esc_url( admin_url( 'edit-tags.php?action=edit&taxonomy=changelog_type&tag_ID=' . $type->term_id . '&post_type=roadmap_changelog' ) ); ?>" class="button button-small">
												<?php esc_html_e( 'Edit', 'product-roadmap' ); ?>
											</a>
										</td>
									</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					<?php else : ?>
						<p><?php esc_html_e( 'No changelog types found. They will be created automatically when you add your first changelog.', 'product-roadmap' ); ?></p>
					<?php endif; ?>

					<p style="margin-top: 20px;">
						<a href="<?php echo esc_url( admin_url( 'edit-tags.php?taxonomy=changelog_type&post_type=roadmap_changelog' ) ); ?>" class="button">
							<span class="dashicons dashicons-plus-alt2" style="vertical-align: middle;"></span>
							<?php esc_html_e( 'Manage All Types', 'product-roadmap' ); ?>
						</a>
					</p>
				</div>
			</div>

			<!-- Usage Info -->
			<div class="roadmap-settings-card">
				<div class="card-header">
					<h3><i class="dashicons dashicons-info"></i> <?php esc_html_e( 'How to Use Changelogs', 'product-roadmap' ); ?></h3>
				</div>
				<div class="card-body">
					<p><?php esc_html_e( 'To display your changelog on your site, use this shortcode:', 'product-roadmap' ); ?></p>
					<div class="shortcode-wrapper" style="margin: 15px 0;">
						<code class="shortcode-text" style="padding: 10px 15px; display: inline-block; background: #f0f0f1; border-radius: 4px;">[roadmap_changelog]</code>
						<button type="button" class="button button-small copy-shortcode" data-shortcode="[roadmap_changelog]" style="margin-left: 10px;">
							<span class="dashicons dashicons-clipboard"></span>
							<?php esc_html_e( 'Copy', 'product-roadmap' ); ?>
						</button>
					</div>

					<h4><?php esc_html_e( 'Shortcode Options', 'product-roadmap' ); ?></h4>
					<table class="widefat" style="margin-top: 10px;">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Attribute', 'product-roadmap' ); ?></th>
								<th><?php esc_html_e( 'Description', 'product-roadmap' ); ?></th>
								<th><?php esc_html_e( 'Example', 'product-roadmap' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><code>limit</code></td>
								<td><?php esc_html_e( 'Number of changelog entries to show', 'product-roadmap' ); ?></td>
								<td><code>[roadmap_changelog limit="5"]</code></td>
							</tr>
							<tr>
								<td><code>product</code></td>
								<td><?php esc_html_e( 'Filter by product slug (Pro)', 'product-roadmap' ); ?></td>
								<td><code>[roadmap_changelog product="my-app"]</code></td>
							</tr>
							<tr>
								<td><code>collapsible</code></td>
								<td><?php esc_html_e( 'Make versions expandable', 'product-roadmap' ); ?></td>
								<td><code>[roadmap_changelog collapsible="true"]</code></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			// Update badge preview on color change
			$('.changelog-type-color, .changelog-type-background').on('change', function() {
				var $row = $(this).closest('tr');
				var termId = $(this).data('term-id');
				var field = $(this).data('field');
				var value = $(this).val();

				// Update preview
				var $preview = $row.find('.changelog-badge-preview');
				if (field === 'color') {
					$preview.css('color', value);
				} else {
					$preview.css('background', value);
				}

				// Save via AJAX
				$.post(ajaxurl, {
					action: 'roadmap_save_changelog_type_color',
					term_id: termId,
					field: field,
					value: value,
					nonce: '<?php echo esc_js( wp_create_nonce( 'roadmap_changelog_type_color' ) ); ?>'
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Show admin notice for pending submissions
	 */
	public function show_pending_submissions_notice() {
		// Only show on roadmap-related admin pages
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'roadmap' ) === false ) {
			return;
		}

		// Check if user can edit posts
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		// Check if user has dismissed this notice
		$user_id   = get_current_user_id();
		$dismissed = get_user_meta( $user_id, 'roadmap_pending_notice_dismissed', true );

		// If dismissed, check if the count changed (show again if new submissions)
		$pending_count   = wp_count_posts( 'roadmap_item' )->pending;
		$dismissed_count = get_user_meta( $user_id, 'roadmap_pending_notice_count', true );

		// If dismissed and count is same or lower, don't show
		if ( $dismissed && $pending_count <= (int) $dismissed_count ) {
			return;
		}

		if ( $pending_count > 0 ) {
			$review_link = admin_url( 'edit.php?post_status=pending&post_type=roadmap_item' );
			?>
			<div class="notice notice-warning is-dismissible" id="roadmap-pending-notice" data-nonce="<?php echo esc_attr( wp_create_nonce( 'roadmap_dismiss_pending_notice' ) ); ?>">
				<p>
					<strong><?php esc_html_e( 'Roadmap Submissions:', 'product-roadmap' ); ?></strong>
					<?php
					printf(
												esc_html(
							/* translators: %s: Number of pending submissions */
							_n(
								'You have %s pending submission awaiting review.',
								'You have %s pending submissions awaiting review.',
								$pending_count,
								'product-roadmap'
							)
						),
						'<strong>' . esc_html( $pending_count ) . '</strong>'
					);
					?>
					<a href="<?php echo esc_url( $review_link ); ?>" class="button">
						<?php esc_html_e( 'Review Now', 'product-roadmap' ); ?>
					</a>
				</p>
			</div>
			<script>
			jQuery(document).ready(function($) {
				$('#roadmap-pending-notice').on('click', '.notice-dismiss', function() {
					$.post(ajaxurl, {
						action: 'roadmap_dismiss_pending_notice',
						nonce: $('#roadmap-pending-notice').data('nonce')
					});
				});
			});
			</script>
			<?php
		}
	}

	/**
	 * AJAX handler for dismissing pending submissions notice
	 */
	public function ajax_dismiss_pending_notice() {
		check_ajax_referer( 'roadmap_dismiss_pending_notice', 'nonce' );

		// The notice is only ever shown to users who can act on it, so the
		// dismissal needs the same gate. Matches the sibling AJAX handlers.
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error( array( 'message' => __( 'You are not allowed to do that.', 'product-roadmap' ) ), 403 );
		}

		$user_id       = get_current_user_id();
		$pending_count = wp_count_posts( 'roadmap_item' )->pending;

		update_user_meta( $user_id, 'roadmap_pending_notice_dismissed', true );
		update_user_meta( $user_id, 'roadmap_pending_notice_count', $pending_count );

		wp_send_json_success();
	}
}

// Initialize
Roadmap_Settings::get_instance();
