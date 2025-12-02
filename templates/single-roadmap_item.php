<?php
/**
 * Single Roadmap Item Template
 *
 * StackOverflow-style layout with prominent voting on the left
 *
 * @package ProductRoadmap
 * @since 1.3.0
 */

get_header();

while (have_posts()) : the_post();
    $item_id = get_the_ID();
    $votes = get_post_meta($item_id, '_roadmap_votes', true) ?: 0;
    $user_id = get_current_user_id();
    $user_vote = 0; // 0 = no vote, 1 = upvoted, -1 = downvoted

    if ($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';
        $vote_value = $wpdb->get_var($wpdb->prepare(
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            "SELECT vote_value FROM {$table} WHERE item_id = %d AND user_id = %d",
            $item_id,
            $user_id
        )); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

        if ($vote_value !== null) {
            $user_vote = intval($vote_value);
        }
    }

    // Get taxonomies
    $statuses = wp_get_post_terms($item_id, 'roadmap_status');
    $categories = wp_get_post_terms($item_id, 'roadmap_category');
    $products = wp_get_post_terms($item_id, 'roadmap_product');
    $product = !empty($products) ? $products[0] : null;

    // Get status color if available
    $status_color = '';
    $status_bg = '';
    if (!empty($statuses)) {
        $status_color = get_term_meta($statuses[0]->term_id, '_roadmap_status_color', true);
        if ($status_color) {
            // Create lighter background from status color
            $status_bg = $status_color . '15'; // 15% opacity hex
        }
    }

    // Get comment count
    $comment_count = get_comments_number($item_id);

    // Get post date
    $post_date = get_the_date();
    $author_id = get_post_field('post_author', $item_id);
    $author_name = get_the_author_meta('display_name', $author_id);

    // Enqueue styles and scripts
    wp_enqueue_style('dashicons');
    wp_enqueue_style('roadmap-single', ROADMAP_PLUGIN_URL . 'assets/css/roadmap-single.css', [], ROADMAP_VERSION);
    wp_enqueue_script('roadmap-single-vote', ROADMAP_PLUGIN_URL . 'assets/js/single-vote.js', ['jquery'], ROADMAP_VERSION, true);
    wp_localize_script('roadmap-single-vote', 'roadmap_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'api_url' => rest_url('roadmap/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
        'item_id' => $item_id,
        'user_vote' => $user_vote,
        'is_logged_in' => is_user_logged_in(),
        'login_url' => wp_login_url(get_permalink()),
        'i18n' => [
            'vote_submitted' => __('Your vote has been submitted!', 'product-roadmap'),
            'vote_removed' => __('Your vote has been removed.', 'product-roadmap'),
            'error' => __('An error occurred. Please try again.', 'product-roadmap'),
            'login_required' => __('Please login to vote.', 'product-roadmap'),
        ]
    ]);
?>

<div class="roadmap-single-wrapper">
    <article id="post-<?php the_ID(); ?>" <?php post_class('roadmap-single-article'); ?>>

        <!-- Breadcrumb Navigation -->
        <?php if ($product): ?>
        <nav class="roadmap-single-breadcrumb">
            <a href="<?php echo esc_url(home_url('/roadmap/')); ?>">
                <span class="dashicons dashicons-chart-line"></span>
                <?php esc_html_e('Roadmap', 'product-roadmap'); ?>
            </a>
            <span class="separator">/</span>
            <a href="<?php echo esc_url(home_url('/roadmap/' . $product->slug . '/')); ?>">
                <?php echo esc_html($product->name); ?>
            </a>
        </nav>
        <?php endif; ?>

        <!-- Main Content Layout: Voting | Content -->
        <div class="roadmap-single-layout">

            <!-- Left: Voting Section -->
            <div class="roadmap-single-voting">
                <button class="roadmap-vote-btn roadmap-vote-up <?php echo $user_vote > 0 ? 'active' : ''; ?>"
                        data-action="upvote"
                        title="<?php esc_attr_e('Upvote this idea', 'product-roadmap'); ?>">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="18 15 12 9 6 15"></polyline>
                    </svg>
                </button>

                <span class="roadmap-vote-count" id="vote-count"><?php echo intval($votes); ?></span>
                <span class="roadmap-vote-label"><?php echo esc_html(_n('vote', 'votes', intval($votes), 'product-roadmap')); ?></span>

                <button class="roadmap-vote-btn roadmap-vote-down <?php echo $user_vote < 0 ? 'active' : ''; ?>"
                        data-action="downvote"
                        title="<?php esc_attr_e('Downvote this idea', 'product-roadmap'); ?>">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </button>
            </div>

            <!-- Right: Content Section -->
            <div class="roadmap-single-main">

                <!-- Header with Title and Badges -->
                <header class="roadmap-single-header">
                    <h1 class="roadmap-single-title"><?php the_title(); ?></h1>

                    <div class="roadmap-single-badges">
                        <?php foreach ($statuses as $status):
                            $s_color = get_term_meta($status->term_id, '_roadmap_status_color', true) ?: '#2196f3';
                        ?>
                            <span class="roadmap-badge roadmap-badge-status"
                                  style="background-color: <?php echo esc_attr($s_color); ?>15; color: <?php echo esc_attr($s_color); ?>; border-color: <?php echo esc_attr($s_color); ?>40;">
                                <?php echo esc_html($status->name); ?>
                            </span>
                        <?php endforeach; ?>

                        <?php foreach ($categories as $category): ?>
                            <span class="roadmap-badge roadmap-badge-category">
                                <?php echo esc_html($category->name); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <div class="roadmap-single-meta-info">
                        <span class="meta-item">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php echo esc_html($post_date); ?>
                        </span>
                        <?php if ($author_name): ?>
                        <span class="meta-item">
                            <span class="dashicons dashicons-admin-users"></span>
                            <?php echo esc_html($author_name); ?>
                        </span>
                        <?php endif; ?>
                        <span class="meta-item">
                            <span class="dashicons dashicons-admin-comments"></span>
                            <?php
                            /* translators: %d: number of comments */
                            printf(esc_html(_n('%d comment', '%d comments', $comment_count, 'product-roadmap')), intval($comment_count));
                            ?>
                        </span>
                    </div>
                </header>

                <!-- Content -->
                <div class="roadmap-single-content">
                    <?php the_content(); ?>
                </div>

                <!-- Tags/Categories Footer -->
                <?php if (!empty($categories) || !empty($products)): ?>
                <footer class="roadmap-single-footer">
                    <?php if ($product): ?>
                    <div class="roadmap-single-product">
                        <span class="dashicons dashicons-portfolio"></span>
                        <a href="<?php echo esc_url(home_url('/roadmap/' . $product->slug . '/')); ?>">
                            <?php echo esc_html($product->name); ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </footer>
                <?php endif; ?>

                <!-- Comments Section -->
                <?php if (comments_open() || get_comments_number()) : ?>
                    <div class="roadmap-single-comments">
                        <?php comments_template(); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </article>
</div>

<!-- Notification Toast -->
<div class="roadmap-notification" id="roadmap-notification"></div>

<?php endwhile;

get_footer();
?>
