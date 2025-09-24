<?php
/**
 * Product Roadmap Archive Template
 */

get_header();

$product_slug = get_query_var('roadmap_product');
$product_term = get_term_by('slug', $product_slug, 'roadmap_product');

if (!$product_term) {
    wp_redirect(home_url());
    exit;
}

// Enqueue archive styles
wp_enqueue_style('roadmap-archive', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-archive.css', [], ROADMAP_VERSION);
?>

<div class="roadmap-archive">
    <div class="roadmap-archive-header">
        <h1><?php echo esc_html($product_term->name); ?> Roadmap</h1>
        <?php if ($product_term->description) : ?>
            <p class="roadmap-product-description"><?php echo esc_html($product_term->description); ?></p>
        <?php endif; ?>
    </div>
    
    <div class="roadmap-archive-content">
        <?php echo do_shortcode('[roadmap product="' . esc_attr($product_slug) . '"]'); ?>
    </div>
    
    <div class="roadmap-archive-footer">
        <p><a href="<?php echo esc_url(home_url()); ?>">&laquo; Back to Home</a></p>
    </div>
</div>

<?php
get_footer();