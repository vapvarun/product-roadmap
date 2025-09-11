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

<style>
.roadmap-archive {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.roadmap-archive-header {
    text-align: center;
    margin-bottom: 40px;
}

.roadmap-archive-header h1 {
    font-size: 2.5em;
    margin-bottom: 10px;
}

.roadmap-product-description {
    font-size: 1.2em;
    color: #666;
}

.roadmap-archive-footer {
    margin-top: 40px;
    text-align: center;
}
</style>

<?php
get_footer();