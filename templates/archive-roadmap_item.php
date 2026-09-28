<?php
/**
 * Template for displaying roadmap item archives (All Products)
 *
 * @package ProductRoadmap
 * @since 1.4.0
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

// Enqueue shared frontend styles (includes Select2 theme styles). Resolved
// through the same helper the changelog templates use, so a theme's
// roadmap/changelog-frontend.css override applies here too instead of being
// silently skipped in favour of the plugin's own file. Bugs 10344482685.
wp_enqueue_style(
	'roadmap-changelog-frontend',
	Roadmap_Changelog::get_frontend_css_url(),
	array( 'roadmap-select2', 'roadmap-tokens' ),
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

// Get products that have roadmap items (not just any post type)
$products     = array();
$all_products = get_terms(
	array(
		'taxonomy'   => 'roadmap_product',
		'hide_empty' => false,
	)
);

// Counts for every product and every status in two grouped queries, rather
// than a WP_Query per product here and an unbounded WP_Query plus a term query
// per item further down. On ten products with 2000 items each that was roughly
// 20,000 queries on a public page.
$roadmap_counts = Roadmap_API::get_product_item_counts();

if ( ! is_wp_error( $all_products ) ) {
	foreach ( $all_products as $product ) {
		$product_total = $roadmap_counts['totals'][ $product->term_id ] ?? 0;

		if ( $product_total > 0 ) {
			$product->count = $product_total;
			$products[]     = $product;
		}
	}
}

// Get all statuses
$statuses = get_terms(
	array(
		'taxonomy'   => 'roadmap_status',
		'hide_empty' => true,
	)
);

// Current filter
$current_status = isset( $_GET['status'] ) ? sanitize_text_field( $_GET['status'] ) : '';
?>

<div class="roadmap-archive-wrapper">
	<header class="roadmap-archive-header">
		<h1 class="roadmap-archive-title">
			<?php esc_html_e( 'Product Roadmap', 'product-roadmap' ); ?>
		</h1>
		<p class="roadmap-archive-description">
			<?php esc_html_e( 'Explore our product roadmaps and see what we\'re working on.', 'product-roadmap' ); ?>
		</p>

		<?php if ( ! empty( $products ) && count( $products ) > 1 ) : ?>
			<div class="roadmap-plugin-product-filter">
				<label for="roadmap-items-product-select" class="roadmap-plugin-filter-label">
					<?php esc_html_e( 'Jump to product:', 'product-roadmap' ); ?>
				</label>
				<select id="roadmap-items-product-select" class="roadmap-plugin-select2">
					<option value=""><?php esc_html_e( 'Search products...', 'product-roadmap' ); ?></option>
					<?php foreach ( $products as $product ) : ?>
						<option value="<?php echo esc_url( home_url( '/roadmap/' . $product->slug . '/' ) ); ?>" data-count="<?php echo esc_attr( (string) $product->count ); ?>">
							<?php echo esc_html( $product->name ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>
	</header>

	<div class="roadmap-products-grid">
		<?php if ( ! empty( $products ) ) : ?>
			<?php
			foreach ( $products as $product ) :
				// Both numbers come from the grouped queries above.
				$total_items   = $roadmap_counts['totals'][ $product->term_id ] ?? 0;
				$status_counts = $roadmap_counts['statuses'][ $product->term_id ] ?? array();
				?>

				<article class="roadmap-product-card">
					<a href="<?php echo esc_url( home_url( '/roadmap/' . $product->slug . '/' ) ); ?>" class="roadmap-product-card-link">
						<header class="roadmap-product-card-header">
							<h2 class="roadmap-product-card-title"><?php echo esc_html( $product->name ); ?></h2>
							<?php if ( ! empty( $product->description ) ) : ?>
								<p class="roadmap-product-card-description">
									<?php echo esc_html( wp_trim_words( $product->description, 20, '...' ) ); ?>
								</p>
							<?php endif; ?>
						</header>

						<div class="roadmap-product-card-stats">
							<span class="roadmap-product-stat total">
								<strong><?php echo esc_html( $total_items ); ?></strong>
								<?php echo esc_html( _n( 'item', 'items', $total_items, 'product-roadmap' ) ); ?>
							</span>

							<?php if ( ! empty( $status_counts ) ) : ?>
								<div class="roadmap-product-status-summary">
									<?php
									foreach ( $status_counts as $status_slug => $count ) :
										$status_term = get_term_by( 'slug', $status_slug, 'roadmap_status' );
										?>
										<span class="roadmap-status-badge roadmap-status-<?php echo esc_attr( $status_slug ); ?>">
											<?php echo esc_html( $count ); ?> <?php echo esc_html( $status_term ? $status_term->name : ucfirst( $status_slug ) ); ?>
										</span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>

						<footer class="roadmap-product-card-footer">
							<span class="roadmap-view-link">
								<?php esc_html_e( 'View Roadmap', 'product-roadmap' ); ?>
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
									<line x1="5" y1="12" x2="19" y2="12"></line>
									<polyline points="12 5 19 12 12 19"></polyline>
								</svg>
							</span>
						</footer>
					</a>
				</article>

			<?php endforeach; ?>
		<?php else : ?>
			<div class="roadmap-no-products">
				<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M3 3v18h18"></path>
					<path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path>
				</svg>
				<h2><?php esc_html_e( 'No Products Yet', 'product-roadmap' ); ?></h2>
				<p><?php esc_html_e( 'Check back soon for product roadmaps.', 'product-roadmap' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</div>

<style>
/* Roadmap Archive Page Styles */
.roadmap-archive-wrapper {
	max-width: 1200px;
	margin: 0 auto;
	padding: 40px 20px;
}

.roadmap-archive-header {
	text-align: center;
	margin-bottom: 40px;
}

.roadmap-archive-title {
	font-size: 2.5rem;
	font-weight: 700;
	color: var(--rm-heading);
	margin-bottom: 12px;
}

.roadmap-archive-description {
	font-size: 1.125rem;
	color: var(--rm-muted);
	margin-bottom: 24px;
}

.roadmap-products-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
	gap: 24px;
}

.roadmap-product-card {
	background: var(--rm-surface);
	border-radius: 12px;
	border: 1px solid var(--rm-border);
	overflow: hidden;
	transition: transform 0.2s, box-shadow 0.2s;
}

.roadmap-product-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 12px 24px var(--rm-shadow-strong);
}

.roadmap-product-card-link {
	display: block;
	padding: 24px;
	text-decoration: none;
	color: inherit;
}

.roadmap-product-card-header {
	margin-bottom: 16px;
}

.roadmap-product-card-title {
	font-size: 1.375rem;
	font-weight: 600;
	color: var(--rm-heading);
	margin: 0 0 8px 0;
}

.roadmap-product-card-description {
	font-size: 0.9375rem;
	color: var(--rm-muted);
	margin: 0;
	line-height: 1.5;
}

.roadmap-product-card-stats {
	margin-bottom: 16px;
}

.roadmap-product-stat.total {
	display: block;
	font-size: 0.9375rem;
	color: var(--rm-text);
	margin-bottom: 12px;
}

.roadmap-product-stat.total strong {
	font-size: 1.5rem;
	font-weight: 700;
	color: var(--rm-heading);
	margin-right: 4px;
}

.roadmap-product-status-summary {
	display: flex;
	flex-wrap: wrap;
	gap: 8px;
}

.roadmap-status-badge {
	display: inline-flex;
	align-items: center;
	padding: 4px 10px;
	font-size: 0.8125rem;
	font-weight: 500;
	border-radius: 9999px;
	background-color: var(--rm-status-tint);
	color: var(--rm-status);
	border: 1px solid color-mix(in srgb, var(--rm-status) 25%, transparent);
}

.roadmap-product-card-footer {
	padding-top: 16px;
	border-top: 1px solid var(--rm-border-soft);
}

.roadmap-view-link {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	font-size: 0.9375rem;
	font-weight: 500;
	color: var(--roadmap-accent-text);
	transition: gap 0.2s;
}

.roadmap-product-card:hover .roadmap-view-link {
	gap: 12px;
}

.roadmap-no-products {
	grid-column: 1 / -1;
	text-align: center;
	padding: 60px 20px;
	background: var(--rm-surface-alt);
	border-radius: 12px;
}

.roadmap-no-products svg {
	color: var(--rm-muted);
	margin-bottom: 16px;
}

.roadmap-no-products h2 {
	font-size: 1.5rem;
	color: var(--rm-heading);
	margin: 0 0 8px 0;
}

.roadmap-no-products p {
	color: var(--rm-muted);
	margin: 0;
}

@media (max-width: 768px) {
	.roadmap-archive-title {
		font-size: 2rem;
	}

	.roadmap-products-grid {
		grid-template-columns: 1fr;
	}
}
</style>

<?php get_footer(); ?>
