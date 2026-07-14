<?php
/**
 * Template for displaying changelog archives
 *
 * @package ProductRoadmap
 * @since 1.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Enqueue Select2 from plugin assets
wp_enqueue_style(
	'roadmap-select2',
	ROADMAP_PLUGIN_URL . 'assets/vendor/select2/select2.min.css',
	array(),
	'4.1.0'
);

// Enqueue shared frontend styles (includes Select2 theme styles)
wp_enqueue_style(
	'roadmap-changelog-frontend',
	ROADMAP_PLUGIN_URL . 'assets/css/changelog-frontend.css',
	array( 'roadmap-select2' ),
	ROADMAP_VERSION
);

wp_enqueue_script(
	'roadmap-select2',
	ROADMAP_PLUGIN_URL . 'assets/vendor/select2/select2.min.js',
	array( 'jquery' ),
	'4.1.0',
	true
);

wp_enqueue_script(
	'roadmap-select2-init',
	ROADMAP_PLUGIN_URL . 'assets/js/changelog-select2.js',
	array( 'jquery', 'roadmap-select2' ),
	ROADMAP_VERSION,
	true
);

wp_localize_script(
	'roadmap-select2-init',
	'roadmapSelect2',
	array(
		'placeholder' => __( 'Search products...', 'product-roadmap' ),
	)
);

get_header();

// Get current product filter if any
$current_product      = get_query_var( 'changelog_product' );
$current_product_term = null;

if ( $current_product ) {
	$current_product_term = get_term_by( 'slug', $current_product, 'roadmap_product' );
}

// Get products that have changelogs (not just any post type)
$products     = array();
$all_products = get_terms(
	array(
		'taxonomy'   => 'roadmap_product',
		'hide_empty' => false,
	)
);

if ( ! is_wp_error( $all_products ) ) {
	foreach ( $all_products as $product ) {
		// Check if this product has any changelog posts
		$changelog_count = new WP_Query(
			array(
				'post_type'      => 'roadmap_changelog',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'tax_query'      => array(
					array(
						'taxonomy' => 'roadmap_product',
						'field'    => 'term_id',
						'terms'    => $product->term_id,
					),
				),
			)
		);

		if ( $changelog_count->found_posts > 0 ) {
			// Update count to reflect changelog count, not all posts
			$product->count = $changelog_count->found_posts;
			$products[]     = $product;
		}
		wp_reset_postdata();
	}
}

// When the archive is filtered to one product, apply that product's saved Theme
// Color to the changelog accent, scoped to this wrapper. An unfiltered (mixed
// products) archive has no single accent, so it keeps the theme default.
$rm_accent = '';
if ( $current_product_term && ! is_wp_error( $current_product_term ) ) {
	$rm_settings = get_term_meta( $current_product_term->term_id, 'roadmap_product_settings', true );
	$rm_accent   = ! empty( $rm_settings['theme_color'] ) ? sanitize_hex_color( $rm_settings['theme_color'] ) : '';
}
?>

<?php if ( $rm_accent ) : ?>
	<style>#roadmap-changelog-archive-wrapper{--roadmap-accent:<?php echo esc_attr( $rm_accent ); ?>;}</style>
<?php endif; ?>
<div id="roadmap-changelog-archive-wrapper" class="roadmap-changelog-archive-wrapper">
	<header class="changelog-archive-header">
		<h1 class="changelog-archive-title">
			<?php
			if ( $current_product_term ) {
				/* translators: %s: product name */
				printf( esc_html__( '%s Changelog', 'product-roadmap' ), esc_html( $current_product_term->name ) );
			} else {
				esc_html_e( 'Changelog', 'product-roadmap' );
			}
			?>
		</h1>

		<?php if ( $current_product_term && ! empty( $current_product_term->description ) ) : ?>
			<p class="changelog-archive-description">
				<?php echo esc_html( $current_product_term->description ); ?>
			</p>
		<?php else : ?>
			<p class="changelog-archive-description">
				<?php esc_html_e( 'Stay up to date with the latest changes and improvements.', 'product-roadmap' ); ?>
			</p>
		<?php endif; ?>

		<?php
		// Only show product filter on the main changelog page (not product-specific pages)
		if ( ! $current_product && ! empty( $products ) && count( $products ) > 1 ) :
			?>
			<div class="roadmap-plugin-product-filter">
				<label for="roadmap-changelog-product-select" class="roadmap-plugin-filter-label">
					<?php esc_html_e( 'Jump to product:', 'product-roadmap' ); ?>
				</label>
				<select id="roadmap-changelog-product-select" class="roadmap-plugin-select2">
					<option value=""><?php esc_html_e( 'Search products...', 'product-roadmap' ); ?></option>
					<?php foreach ( $products as $product ) : ?>
						<option value="<?php echo esc_url( home_url( '/changelog/' . $product->slug . '/' ) ); ?>" data-count="<?php echo esc_attr( $product->count ); ?>">
							<?php echo esc_html( $product->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php elseif ( $current_product_term ) : ?>
			<a href="<?php echo esc_url( home_url( '/changelog/' ) ); ?>" class="changelog-back-link">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<line x1="19" y1="12" x2="5" y2="12"></line>
					<polyline points="12 19 5 12 12 5"></polyline>
				</svg>
				<?php esc_html_e( 'View all product changelogs', 'product-roadmap' ); ?>
			</a>
		<?php endif; ?>
	</header>

	<div class="changelog-archive-list">
		<?php if ( have_posts() ) : ?>
			<?php
			while ( have_posts() ) :
				the_post();
				$changelog          = Roadmap_Changelog::get_changelog_data( get_the_ID() );
				$changelog_products = $changelog['product'] ?? array();
				$changelog_product  = ! empty( $changelog_products ) ? $changelog_products[0] : null;
				?>

				<article class="changelog-archive-item">
					<a href="<?php the_permalink(); ?>">
						<header class="changelog-archive-item-header">
							<h2><?php the_title(); ?></h2>

							<div class="changelog-archive-meta">
								<?php if ( ! empty( $changelog['version'] ) ) : ?>
									<span class="changelog-version">v<?php echo esc_html( $changelog['version'] ); ?></span>
								<?php endif; ?>

								<?php if ( ! empty( $changelog['release_date'] ) ) : ?>
									<span class="changelog-date">
										<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $changelog['release_date'] ) ) ); ?>
									</span>
								<?php endif; ?>

								<?php if ( ! empty( $changelog['release_type'] ) ) : ?>
									<?php
									$release_labels = array(
										'major' => __( 'Major', 'product-roadmap' ),
										'minor' => __( 'Minor', 'product-roadmap' ),
										'patch' => __( 'Patch', 'product-roadmap' ),
									);
									?>
									<span class="changelog-release-type release-<?php echo esc_attr( $changelog['release_type'] ); ?>">
										<?php echo esc_html( $release_labels[ $changelog['release_type'] ] ?? $changelog['release_type'] ); ?>
									</span>
								<?php endif; ?>

								<?php if ( $changelog_product && ! $current_product ) : ?>
									<span class="changelog-product-badge">
										<?php echo esc_html( $changelog_product->name ); ?>
									</span>
								<?php endif; ?>
							</div>
						</header>

						<?php if ( ! empty( $changelog['items'] ) ) : ?>
							<div class="changelog-archive-summary">
								<?php
								$item_count = count( $changelog['items'] );
								/* translators: %d: number of changes */
								printf( esc_html( _n( '%d change', '%d changes', $item_count, 'product-roadmap' ) ), intval( $item_count ) );
								?>

								<?php
								// Group by type for summary
								$type_counts = array();
								foreach ( $changelog['items'] as $item ) {
									$type = $item['type'] ?? 'update';
									if ( ! isset( $type_counts[ $type ] ) ) {
										$type_counts[ $type ] = 0;
									}
									++$type_counts[ $type ];
								}

								$summary_parts = array();
								$types         = Roadmap_Changelog::get_change_types();
								foreach ( $type_counts as $type => $count ) {
									$type_name       = $types[ $type ]['name'] ?? ucfirst( $type );
									$summary_parts[] = $count . ' ' . strtolower( $type_name );
								}

								if ( ! empty( $summary_parts ) ) :
									?>
									<span class="changelog-type-summary">
										(<?php echo esc_html( implode( ', ', array_slice( $summary_parts, 0, 3 ) ) ); ?>)
									</span>
								<?php endif; ?>
							</div>

							<ul class="changelog-preview-items">
								<?php
								$preview_items = array_slice( $changelog['items'], 0, 3 );
								foreach ( $preview_items as $item ) :
									$type_data  = $item['type_data'] ?? null;
									$bg_color   = $type_data['background'] ?? '#e2e3e5';
									$text_color = $type_data['color'] ?? '#383d41';
									$type_name  = $type_data['name'] ?? ucfirst( $item['type'] ?? 'Update' );
									?>
									<li class="changelog-preview-item">
										<span class="changelog-badge-small"
												style="background-color: <?php echo esc_attr( $bg_color ); ?>; color: <?php echo esc_attr( $text_color ); ?>;">
											<?php echo esc_html( $type_name ); ?>
										</span>
										<span class="changelog-preview-text">
											<?php echo esc_html( wp_trim_words( $item['description'], 12, '...' ) ); ?>
										</span>
									</li>
								<?php endforeach; ?>

								<?php if ( count( $changelog['items'] ) > 3 ) : ?>
									<li class="changelog-preview-more">
										<?php
										/* translators: %d: number of more changes */
										printf( esc_html__( '+ %d more changes', 'product-roadmap' ), count( $changelog['items'] ) - 3 );
										?>
									</li>
								<?php endif; ?>
							</ul>
						<?php endif; ?>
					</a>
				</article>

			<?php endwhile; ?>

			<?php
			// Pagination
			the_posts_pagination(
				array(
					'mid_size'  => 2,
					'prev_text' => '&larr; ' . __( 'Previous', 'product-roadmap' ),
					'next_text' => __( 'Next', 'product-roadmap' ) . ' &rarr;',
					'class'     => 'changelog-pagination',
				)
			);
			?>

		<?php else : ?>
			<div class="changelog-no-results">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
					<polyline points="14 2 14 8 20 8"></polyline>
					<line x1="16" y1="13" x2="8" y2="13"></line>
					<line x1="16" y1="17" x2="8" y2="17"></line>
					<polyline points="10 9 9 9 8 9"></polyline>
				</svg>
				<h2><?php esc_html_e( 'No Releases Yet', 'product-roadmap' ); ?></h2>
				<p><?php esc_html_e( 'Check back soon for updates and release notes.', 'product-roadmap' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

<?php get_footer(); ?>
