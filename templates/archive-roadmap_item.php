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
									$status_colors = array(
										'planned'      => '#6366f1',
										'in-progress'  => '#f59e0b',
										'completed'    => '#10b981',
										'on-hold'      => '#6b7280',
										'under-review' => '#8b5cf6',
									);

									foreach ( $status_counts as $status_slug => $count ) :
										$status_term = get_term_by( 'slug', $status_slug, 'roadmap_status' );
										$color       = $status_colors[ $status_slug ] ?? '#6b7280';
										?>
										<span class="roadmap-status-badge" style="background-color: <?php echo esc_attr( $color ); ?>20; color: <?php echo esc_attr( $color ); ?>; border: 1px solid <?php echo esc_attr( $color ); ?>40;">
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
	color: #1e293b;
	margin-bottom: 12px;
}

.roadmap-archive-description {
	font-size: 1.125rem;
	color: #64748b;
	margin-bottom: 24px;
}

.roadmap-products-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
	gap: 24px;
}

.roadmap-product-card {
	background: #fff;
	border-radius: 12px;
	border: 1px solid #e2e8f0;
	overflow: hidden;
	transition: transform 0.2s, box-shadow 0.2s;
}

.roadmap-product-card:hover {
	transform: translateY(-4px);
	box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
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
	color: #1e293b;
	margin: 0 0 8px 0;
}

.roadmap-product-card-description {
	font-size: 0.9375rem;
	color: #64748b;
	margin: 0;
	line-height: 1.5;
}

.roadmap-product-card-stats {
	margin-bottom: 16px;
}

.roadmap-product-stat.total {
	display: block;
	font-size: 0.9375rem;
	color: #475569;
	margin-bottom: 12px;
}

.roadmap-product-stat.total strong {
	font-size: 1.5rem;
	font-weight: 700;
	color: #1e293b;
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
}

.roadmap-product-card-footer {
	padding-top: 16px;
	border-top: 1px solid #f1f5f9;
}

.roadmap-view-link {
	display: inline-flex;
	align-items: center;
	gap: 8px;
	font-size: 0.9375rem;
	font-weight: 500;
	color: #6366f1;
	transition: gap 0.2s;
}

.roadmap-product-card:hover .roadmap-view-link {
	gap: 12px;
}

.roadmap-no-products {
	grid-column: 1 / -1;
	text-align: center;
	padding: 60px 20px;
	background: #f8fafc;
	border-radius: 12px;
}

.roadmap-no-products svg {
	color: #94a3b8;
	margin-bottom: 16px;
}

.roadmap-no-products h2 {
	font-size: 1.5rem;
	color: #475569;
	margin: 0 0 8px 0;
}

.roadmap-no-products p {
	color: #64748b;
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

/*
 * Dark mode. This page hardcoded near-black text on a white card, so on a
 * dark theme the heading rendered invisible against the page background. The
 * 1.7.0 dark-mode work covered the changelog and the Vue board but matched
 * only .roadmap-changelog-* wrappers, never this page's .roadmap-archive-wrapper.
 *
 * A previous fix here used `@media (prefers-color-scheme: dark)`, which
 * tracks the OS setting rather than the theme's own dark-mode toggle - the
 * rest of this plugin's frontend is dark-mode-aware via theme toggle only
 * (see the same selector list in assets/css/changelog-frontend.css), so an
 * OS-dark + theme-light visitor got an unreadable heading on a light page.
 * Matching that selector list keeps this page consistent with every other
 * dark-mode-aware surface in the plugin, instead of tracking the OS setting.
 */
html[data-bx-mode="dark"] .roadmap-archive-title,
html[data-mode="dark"] .roadmap-archive-title,
body.buddyx-dark-theme .roadmap-archive-title,
body.buddyx-dark-style-variation .roadmap-archive-title,
body.dark-scheme .roadmap-archive-title,
body.reign-ld-dark-theme .roadmap-archive-title {
	color: #f1f5f9;
}

html[data-bx-mode="dark"] :is(.roadmap-archive-description, .roadmap-no-products h2),
html[data-mode="dark"] :is(.roadmap-archive-description, .roadmap-no-products h2),
body.buddyx-dark-theme :is(.roadmap-archive-description, .roadmap-no-products h2),
body.buddyx-dark-style-variation :is(.roadmap-archive-description, .roadmap-no-products h2),
body.dark-scheme :is(.roadmap-archive-description, .roadmap-no-products h2),
body.reign-ld-dark-theme :is(.roadmap-archive-description, .roadmap-no-products h2) {
	color: #cbd5e1;
}

html[data-bx-mode="dark"] .roadmap-no-products p,
html[data-mode="dark"] .roadmap-no-products p,
body.buddyx-dark-theme .roadmap-no-products p,
body.buddyx-dark-style-variation .roadmap-no-products p,
body.dark-scheme .roadmap-no-products p,
body.reign-ld-dark-theme .roadmap-no-products p {
	color: #94a3b8;
}

html[data-bx-mode="dark"] .roadmap-product-card,
html[data-mode="dark"] .roadmap-product-card,
body.buddyx-dark-theme .roadmap-product-card,
body.buddyx-dark-style-variation .roadmap-product-card,
body.dark-scheme .roadmap-product-card,
body.reign-ld-dark-theme .roadmap-product-card {
	background: #1e293b;
	border-color: #334155;
}

html[data-bx-mode="dark"] .roadmap-product-card-title,
html[data-mode="dark"] .roadmap-product-card-title,
body.buddyx-dark-theme .roadmap-product-card-title,
body.buddyx-dark-style-variation .roadmap-product-card-title,
body.dark-scheme .roadmap-product-card-title,
body.reign-ld-dark-theme .roadmap-product-card-title {
	color: #f1f5f9;
}

html[data-bx-mode="dark"] .roadmap-product-card-description,
html[data-mode="dark"] .roadmap-product-card-description,
body.buddyx-dark-theme .roadmap-product-card-description,
body.buddyx-dark-style-variation .roadmap-product-card-description,
body.dark-scheme .roadmap-product-card-description,
body.reign-ld-dark-theme .roadmap-product-card-description {
	color: #cbd5e1;
}
</style>

<?php get_footer(); ?>
