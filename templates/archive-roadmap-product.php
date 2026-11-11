<?php
/**
 * Product Roadmap Archive Template
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

Roadmap_Template::get_header();

// Support both URL patterns:
// 1. /roadmap/{product}/ - custom rewrite with query var
// 2. /roadmap-product/{product}/ - WordPress taxonomy archive
$roadmap_product_slug = get_query_var('roadmap_product');

if (!$roadmap_product_slug && is_tax('roadmap_product')) {
    $term = get_queried_object();
    if ($term && !is_wp_error($term)) {
        $roadmap_product_slug = $term->slug;
    }
}

$roadmap_product_term = get_term_by('slug', $roadmap_product_slug, 'roadmap_product');

if (!$roadmap_product_term) {
    wp_safe_redirect(home_url());
    exit;
}

// Enqueue archive styles
wp_enqueue_style('roadmap-archive', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-archive.css', [ 'roadmap-tokens' ], ROADMAP_VERSION);

// Get default template from Pro settings (stored in roadmap_pro_templates array)
$pro_templates = get_option('roadmap_pro_templates', []);
$default_template = isset($pro_templates['default_template']) ? $pro_templates['default_template'] : 'board';
?>

<div class="roadmap-archive">
    <div class="roadmap-archive-header">
        <h1><?php echo esc_html($roadmap_product_term->name); ?> Roadmap</h1>
        <?php if ($roadmap_product_term->description) : ?>
            <p class="roadmap-product-description"><?php echo esc_html($roadmap_product_term->description); ?></p>
        <?php endif; ?>
    </div>

    <div class="roadmap-archive-content">
        <?php echo do_shortcode('[roadmap product="' . esc_attr($roadmap_product_slug) . '" template="' . esc_attr($default_template) . '"]'); ?>
    </div>
    
    <div class="roadmap-archive-footer">
        <p><a href="<?php echo esc_url(home_url()); ?>">&laquo; Back to Home</a></p>
    </div>
</div>

<?php
Roadmap_Template::get_footer();