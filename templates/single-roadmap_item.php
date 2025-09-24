<?php
/**
 * Single Roadmap Item Template
 */

get_header();

while (have_posts()) : the_post();
    $item_id = get_the_ID();
    $votes = get_post_meta($item_id, '_roadmap_votes', true) ?: 0;
    $user_id = get_current_user_id();
    $user_voted = false;
    
    if ($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';
        $user_voted = $wpdb->get_var($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT vote_value FROM {$table} WHERE item_id = %d AND user_id = %d",
            $item_id,
            $user_id
        )) > 0; // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- User vote check, table name is safe
    }
    
    // Get taxonomies
    $statuses = wp_get_post_terms($item_id, 'roadmap_status');
    $categories = wp_get_post_terms($item_id, 'roadmap_category');
    $products = wp_get_post_terms($item_id, 'roadmap_product');
    
    // Enqueue styles and scripts
    wp_enqueue_style('roadmap-single', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-single.css', [], ROADMAP_VERSION);
    wp_enqueue_script('roadmap-single-vote', ROADMAP_PLUGIN_URL . 'assets/js/single-vote.js', ['jquery'], ROADMAP_VERSION, true);
    wp_localize_script('roadmap-single-vote', 'roadmap_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'api_url' => rest_url('roadmap/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
        'item_id' => $item_id,
        'user_voted' => $user_voted,
        'i18n' => [
            'vote_submitted' => __('Your vote has been submitted!', 'product-roadmap'),
            'vote_removed' => __('Your vote has been removed!', 'product-roadmap'),
            'error' => __('An error occurred. Please try again.', 'product-roadmap'),
            'login_required' => __('Please login to vote.', 'product-roadmap'),
        ]
    ]);
?>

<div class="roadmap-single">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <header class="roadmap-single-header">
            <h1 class="roadmap-single-title"><?php the_title(); ?></h1>
            
            <div class="roadmap-single-meta">
                <div class="roadmap-single-badges">
                    <?php foreach ($statuses as $status) : ?>
                        <span class="roadmap-badge roadmap-badge-status"><?php echo esc_html($status->name); ?></span>
                    <?php endforeach; ?>
                    
                    <?php foreach ($categories as $category) : ?>
                        <span class="roadmap-badge roadmap-badge-category"><?php echo esc_html($category->name); ?></span>
                    <?php endforeach; ?>
                </div>
                
                <button class="roadmap-single-vote <?php echo esc_attr($user_voted ? 'voted' : ''); ?>" id="roadmap-vote-btn">
                    <span class="roadmap-single-vote-icon">👍</span>
                    <span class="roadmap-single-vote-count"><?php echo intval($votes); ?></span>
                    <span class="roadmap-single-vote-text"><?php echo esc_html($user_voted ? __('Voted', 'product-roadmap') : __('Vote', 'product-roadmap')); ?></span>
                </button>
            </div>
        </header>
        
        <div class="roadmap-single-content">
            <?php the_content(); ?>
        </div>
        
        <?php if (comments_open() || get_comments_number()) : ?>
            <div class="roadmap-single-comments">
                <?php comments_template(); ?>
            </div>
        <?php endif; ?>
    </article>
</div>

<div class="roadmap-notification" id="roadmap-notification"></div>

<?php endwhile;

get_footer();
?>