<?php
/**
 * Single Roadmap Item Template
 */

// Enqueue voting script
wp_enqueue_script('jquery');
wp_enqueue_script(
    'roadmap-single',
    ROADMAP_PLUGIN_URL . 'assets/js/roadmap-single.js',
    ['jquery'],
    ROADMAP_VERSION,
    true
);

// Localize script
wp_localize_script('roadmap-single', 'roadmapAjax', [
    'apiUrl' => rest_url('roadmap/v1'),
    'nonce' => wp_create_nonce('wp_rest')
]);

// Get the shortcode instance to enqueue Vue assets
$shortcode = Roadmap_Shortcode::get_instance();
$shortcode->enqueue_assets();

get_header();

while (have_posts()) : the_post();
    $item_id = get_the_ID();
    $product_terms = wp_get_object_terms($item_id, 'roadmap_product');
    $status_terms = wp_get_object_terms($item_id, 'roadmap_status');
    $category_terms = wp_get_object_terms($item_id, 'roadmap_category');
    
    $votes = get_post_meta($item_id, '_roadmap_votes', true) ?: 0;
    $priority = get_post_meta($item_id, '_roadmap_priority', true) ?: 'medium';
    $target_date = get_post_meta($item_id, '_roadmap_target_date', true);
    
    // Check if user has voted
    $user_has_voted = false;
    if (is_user_logged_in()) {
        global $wpdb;
        $table = $wpdb->prefix . 'roadmap_votes';
        $user_id = get_current_user_id();
        $vote = $wpdb->get_var($wpdb->prepare(
            "SELECT vote_value FROM $table WHERE user_id = %d AND item_id = %d",
            $user_id,
            $item_id
        ));
        $user_has_voted = !empty($vote);
    }
?>

<div class="roadmap-single-item">
    <div class="roadmap-breadcrumb">
        <a href="<?php echo home_url(); ?>">Home</a> &raquo; 
        <?php if (!empty($product_terms)) : ?>
            <a href="<?php echo home_url('/roadmap/' . $product_terms[0]->slug . '/'); ?>">
                <?php echo esc_html($product_terms[0]->name); ?> Roadmap
            </a> &raquo; 
        <?php endif; ?>
        <?php the_title(); ?>
    </div>
    
    <article id="post-<?php the_ID(); ?>" <?php post_class('roadmap-item-single'); ?>>
        <header class="roadmap-item-header">
            <h1 class="roadmap-item-title"><?php the_title(); ?></h1>
            
            <div class="roadmap-item-meta">
                <?php if (!empty($status_terms)) : ?>
                    <span class="roadmap-status roadmap-status-<?php echo esc_attr($status_terms[0]->slug); ?>">
                        <?php echo esc_html($status_terms[0]->name); ?>
                    </span>
                <?php endif; ?>
                
                <span class="roadmap-priority roadmap-priority-<?php echo esc_attr($priority); ?>">
                    <?php echo esc_html(ucfirst($priority)); ?> Priority
                </span>
                
                <?php if ($target_date) : ?>
                    <span class="roadmap-target-date">
                        Target: <?php echo esc_html(date('F Y', strtotime($target_date))); ?>
                    </span>
                <?php endif; ?>
                
                <span class="roadmap-votes">
                    <?php echo esc_html($votes); ?> votes
                </span>
            </div>
        </header>
        
        <div class="roadmap-item-content">
            <?php the_content(); ?>
        </div>
        
        <?php if (!empty($category_terms)) : ?>
            <div class="roadmap-item-categories">
                <strong>Categories:</strong>
                <?php foreach ($category_terms as $category) : ?>
                    <span class="roadmap-category"><?php echo esc_html($category->name); ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($product_terms)) : ?>
            <div class="roadmap-item-product">
                <strong>Product:</strong> 
                <a href="<?php echo home_url('/roadmap/' . $product_terms[0]->slug . '/'); ?>">
                    <?php echo esc_html($product_terms[0]->name); ?>
                </a>
            </div>
        <?php endif; ?>
        
        <div class="roadmap-item-actions">
            <?php if (is_user_logged_in()) : ?>
                <button class="roadmap-vote-btn<?php echo $user_has_voted ? ' voted' : ''; ?>" data-item-id="<?php echo $item_id; ?>">
                    <?php echo $user_has_voted ? 'Remove vote' : 'Vote for this feature'; ?>
                </button>
            <?php else : ?>
                <a href="<?php echo wp_login_url(get_permalink()); ?>" class="roadmap-vote-btn">
                    Login to vote
                </a>
            <?php endif; ?>
            
            <?php if (current_user_can('edit_posts')) : ?>
                <button class="roadmap-edit-btn" data-item-id="<?php echo $item_id; ?>">
                    Edit this item
                </button>
            <?php endif; ?>
        </div>
        
        <?php if (comments_open() || get_comments_number()) : ?>
            <div class="roadmap-item-comments">
                <?php comments_template(); ?>
            </div>
        <?php endif; ?>
    </article>
</div>

<style>
.roadmap-single-item {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 20px;
}

.roadmap-breadcrumb {
    margin-bottom: 20px;
    color: #666;
}

.roadmap-breadcrumb a {
    color: #2271b1;
    text-decoration: none;
}

.roadmap-item-header {
    margin-bottom: 30px;
}

.roadmap-item-title {
    font-size: 2.5em;
    margin-bottom: 15px;
}

.roadmap-item-meta {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    align-items: center;
}

.roadmap-status {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 600;
}

.roadmap-status-planned { background: #e3f2fd; color: #1976d2; }
.roadmap-status-in-progress { background: #fff3e0; color: #f57c00; }
.roadmap-status-completed { background: #e8f5e9; color: #388e3c; }
.roadmap-status-on-hold { background: #fce4ec; color: #c2185b; }

.roadmap-priority {
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 14px;
}

.roadmap-priority-low { background: #f5f5f5; color: #666; }
.roadmap-priority-medium { background: #e3f2fd; color: #1976d2; }
.roadmap-priority-high { background: #fff3e0; color: #f57c00; }
.roadmap-priority-critical { background: #ffebee; color: #d32f2f; }

.roadmap-item-content {
    font-size: 1.1em;
    line-height: 1.7;
    margin-bottom: 30px;
}

.roadmap-item-categories,
.roadmap-item-product {
    margin-bottom: 20px;
}

.roadmap-category {
    background: #f0f0f0;
    padding: 4px 10px;
    border-radius: 4px;
    margin-left: 5px;
}

.roadmap-item-actions {
    margin: 40px 0;
    padding: 30px 0;
    border-top: 1px solid #ddd;
    border-bottom: 1px solid #ddd;
}

.roadmap-vote-btn {
    background: #2271b1;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.roadmap-vote-btn:hover {
    background: #135e96;
}

.roadmap-edit-btn {
    margin-left: 15px;
    background: #666;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.roadmap-edit-btn:hover {
    background: #555;
}
</style>

<?php
    // Prepare data for Vue app (for editing functionality)
    $product_slug = !empty($product_terms) ? $product_terms[0]->slug : 'main';
    
    $app_data = [
        'api_url' => rest_url('roadmap/v1'),
        'nonce' => wp_create_nonce('wp_rest'),
        'mode' => 'view',
        'allow_frontend_edit' => true,
        'product' => $product_slug,
        'is_pro' => (defined('ROADMAP_PRO_ACTIVE') && ROADMAP_PRO_ACTIVE),
        'settings' => [
            'enable_voting' => get_option('roadmap_enable_voting', 'yes') === 'yes',
            'enable_comments' => get_option('roadmap_enable_comments', 'yes') === 'yes',
            'enable_suggestions' => get_option('roadmap_enable_suggestions', 'yes') === 'yes',
            'items_per_page' => (int) get_option('roadmap_items_per_page', 20),
        ],
        'user' => $shortcode->get_user_data(),
        'i18n' => $shortcode->get_translations(),
        'single_edit_mode' => true,
        'edit_item_id' => $item_id,
    ];
    ?>
    
    <!-- Hidden Vue app container for edit modal -->
    <div id="roadmap-edit-app" 
         class="roadmap-app roadmap-single-edit-app" 
         data-config='<?php echo esc_attr(json_encode($app_data)); ?>'
         style="display: none;">
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle edit button click
        $('.roadmap-edit-btn').on('click', function(e) {
            e.preventDefault();
            var itemId = $(this).data('item-id');
            
            // Initialize Vue app if not already done
            var container = document.getElementById('roadmap-edit-app');
            if (container && window.RoadmapApp && !container.__vue_app__) {
                try {
                    var config = JSON.parse(container.dataset.config);
                    window.RoadmapApp.mount(container, config);
                } catch (e) {
                    console.error('Failed to initialize edit modal:', e);
                }
            }
            
            // Call the exposed method
            if (container && container.__vue_app__) {
                container.__vue_app__.openSingleItemModal(itemId);
            }
        });
    });
    </script>
    
<?php
endwhile;

get_footer();