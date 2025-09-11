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
    
    // Enqueue voting script
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

<style>
.roadmap-single {
    max-width: 800px;
    margin: 0 auto;
    padding: 40px 20px;
}

.roadmap-single-header {
    margin-bottom: 30px;
}

.roadmap-single-title {
    margin: 0 0 20px 0;
    font-size: 32px;
    line-height: 1.3;
}

.roadmap-single-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
}

.roadmap-single-badges {
    display: flex;
    gap: 8px;
}

.roadmap-badge {
    display: inline-block;
    padding: 4px 12px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 4px;
}

.roadmap-badge-status {
    background: #e3f2fd;
    color: #1976d2;
}

.roadmap-badge-category {
    background: #f3e5f5;
    color: #7b1fa2;
}

.roadmap-single-vote {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    background: white;
    border: 2px solid #ddd;
    border-radius: 25px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.roadmap-single-vote:hover {
    border-color: #2196f3;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.2);
}

.roadmap-single-vote.voted {
    background: #2196f3;
    color: white;
    border-color: #2196f3;
}

.roadmap-single-vote.voted:hover {
    background: #1976d2;
    border-color: #1976d2;
}

.roadmap-single-vote:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.roadmap-single-vote-icon {
    font-size: 20px;
    transition: transform 0.3s ease;
}

.roadmap-single-vote:hover .roadmap-single-vote-icon {
    transform: scale(1.2);
}

.roadmap-single-vote.voting .roadmap-single-vote-icon {
    animation: bounce 0.5s ease;
}

@keyframes bounce {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.3); }
}

.roadmap-single-vote-count {
    font-size: 18px;
    font-weight: 600;
}

.roadmap-single-content {
    margin: 40px 0;
    font-size: 18px;
    line-height: 1.7;
}

.roadmap-single-content h2 { margin-top: 30px; margin-bottom: 15px; }
.roadmap-single-content h3 { margin-top: 25px; margin-bottom: 12px; }
.roadmap-single-content p { margin-bottom: 20px; }
.roadmap-single-content ul, .roadmap-single-content ol { margin-bottom: 20px; padding-left: 30px; }

/* Notification */
.roadmap-notification {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.8);
    padding: 20px 30px;
    background: #fff;
    color: #333;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    min-width: 250px;
    z-index: 99999;
    opacity: 0;
    transition: all 0.3s ease;
}

.roadmap-notification.show {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}

.roadmap-notification.success {
    color: #4caf50;
    border: 2px solid #4caf50;
}

.roadmap-notification.success::before {
    content: "✓ ";
    font-size: 20px;
    font-weight: bold;
    margin-right: 8px;
}

.roadmap-notification.error {
    color: #f44336;
    border: 2px solid #f44336;
}

.roadmap-notification.error::before {
    content: "✕ ";
    font-size: 20px;
    font-weight: bold;
    margin-right: 8px;
}
</style>

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