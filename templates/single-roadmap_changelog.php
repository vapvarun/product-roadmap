<?php
/**
 * Template for displaying single changelog entries
 *
 * @package ProductRoadmap
 * @since 1.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();

while (have_posts()) :
    the_post();

    $changelog = Roadmap_Changelog::get_changelog_data(get_the_ID());
    $products = $changelog['product'] ?? [];
    $product = !empty($products) ? $products[0] : null;

    // Apply the product's saved Theme Color to the changelog accent, scoped to
    // this instance. The accent token is declared on .roadmap-changelog-wrapper,
    // so overriding it via the id (higher specificity) cascades to every shade.
    $rm_settings = $product ? get_term_meta($product->term_id, 'roadmap_product_settings', true) : '';
    $rm_accent   = !empty($rm_settings['theme_color']) ? sanitize_hex_color($rm_settings['theme_color']) : '';
    $rm_wrap_id  = 'roadmap-changelog-wrapper-' . get_the_ID();
    ?>

    <?php if ($rm_accent): ?>
        <style>#<?php echo esc_attr($rm_wrap_id); ?>{--roadmap-accent:<?php echo esc_attr($rm_accent); ?>;}</style>
    <?php endif; ?>
    <div id="<?php echo esc_attr($rm_wrap_id); ?>" class="roadmap-changelog-wrapper">
        <article id="changelog-<?php the_ID(); ?>" <?php post_class('roadmap-changelog-article'); ?>>

            <header class="changelog-entry-header">
                <?php if ($product): ?>
                    <nav class="changelog-breadcrumb">
                        <a href="<?php echo esc_url(home_url('/changelog/')); ?>"><?php esc_html_e('Changelog', 'product-roadmap'); ?></a>
                        <span class="separator">/</span>
                        <a href="<?php echo esc_url(home_url('/changelog/' . $product->slug . '/')); ?>"><?php echo esc_html($product->name); ?></a>
                    </nav>
                <?php endif; ?>

                <h1 class="changelog-entry-title"><?php the_title(); ?></h1>

                <div class="changelog-meta">
                    <?php if (!empty($changelog['version'])): ?>
                        <span class="changelog-version-badge">
                            <span class="version-label"><?php esc_html_e('Version', 'product-roadmap'); ?></span>
                            <span class="version-number"><?php echo esc_html($changelog['version']); ?></span>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($changelog['release_date'])): ?>
                        <span class="changelog-date">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($changelog['release_date']))); ?>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($changelog['release_type'])): ?>
                        <?php
                        $release_classes = [
                            'major' => 'release-major',
                            'minor' => 'release-minor',
                            'patch' => 'release-patch',
                        ];
                        $release_labels = [
                            'major' => __('Major Release', 'product-roadmap'),
                            'minor' => __('Minor Release', 'product-roadmap'),
                            'patch' => __('Patch', 'product-roadmap'),
                        ];
                        $release_class = $release_classes[$changelog['release_type']] ?? '';
                        $release_label = $release_labels[$changelog['release_type']] ?? $changelog['release_type'];
                        ?>
                        <span class="changelog-release-type <?php echo esc_attr($release_class); ?>">
                            <?php echo esc_html($release_label); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ($product): ?>
                        <span class="changelog-product-name">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            </svg>
                            <?php echo esc_html($product->name); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </header>

            <?php if (!empty($changelog['content']) || has_excerpt()): ?>
                <div class="changelog-description">
                    <?php
                    if (has_excerpt()) {
                        the_excerpt();
                    } else {
                        the_content();
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($changelog['items'])): ?>
                <div class="changelog-changes">
                    <h2 class="changelog-changes-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                        <?php esc_html_e('What\'s Changed', 'product-roadmap'); ?>
                    </h2>

                    <ul class="changelog-items-list">
                        <?php foreach ($changelog['items'] as $item):
                            $type_data = $item['type_data'] ?? null;
                            $bg_color = $type_data['background'] ?? '#e2e3e5';
                            $text_color = $type_data['color'] ?? '#383d41';
                            $type_name = $type_data['name'] ?? ucfirst($item['type'] ?? 'Update');
                            $icon = $type_data['icon'] ?? 'marker';
                            ?>
                            <li class="changelog-item">
                                <span class="changelog-badge" style="background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>;">
                                    <span class="dashicons dashicons-<?php echo esc_attr($icon); ?>"></span>
                                    <?php echo esc_html($type_name); ?>
                                </span>
                                <span class="changelog-item-text">
                                    <?php echo esc_html($item['description']); ?>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <footer class="changelog-entry-footer">
                <?php
                // Navigate by release date (matching the archive order), not the
                // post publish date, so "Previous/Next Release" reflect the actual
                // release chronology rather than the order entries were authored.
                $this_release_date = get_post_meta(get_the_ID(), '_changelog_release_date', true);
                if (empty($this_release_date)) {
                    $this_release_date = get_the_date('Y-m-d');
                }

                $nav_args = [
                    'post_type' => 'roadmap_changelog',
                    'posts_per_page' => 1,
                    'meta_key' => '_changelog_release_date',
                    'orderby' => 'meta_value',
                ];

                // Get previous changelog (the newest release dated before this one)
                $prev_args = array_merge($nav_args, [
                    'meta_query' => [
                        [
                            'key' => '_changelog_release_date',
                            'value' => $this_release_date,
                            'compare' => '<',
                            'type' => 'DATE',
                        ],
                    ],
                    'order' => 'DESC',
                ]);

                // Filter by same product if available
                if ($product) {
                    $prev_args['tax_query'] = [
                        [
                            'taxonomy' => 'roadmap_product',
                            'field' => 'term_id',
                            'terms' => $product->term_id,
                        ]
                    ];
                }

                $prev_query = new WP_Query($prev_args);
                $prev_post = $prev_query->have_posts() ? $prev_query->posts[0] : null;
                wp_reset_postdata();

                // Get next changelog (the oldest release dated after this one)
                $next_args = array_merge($nav_args, [
                    'meta_query' => [
                        [
                            'key' => '_changelog_release_date',
                            'value' => $this_release_date,
                            'compare' => '>',
                            'type' => 'DATE',
                        ],
                    ],
                    'order' => 'ASC',
                ]);

                if ($product) {
                    $next_args['tax_query'] = [
                        [
                            'taxonomy' => 'roadmap_product',
                            'field' => 'term_id',
                            'terms' => $product->term_id,
                        ]
                    ];
                }

                $next_query = new WP_Query($next_args);
                $next_post = $next_query->have_posts() ? $next_query->posts[0] : null;
                wp_reset_postdata();
                ?>

                <nav class="changelog-navigation">
                    <?php if ($prev_post): ?>
                        <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="changelog-nav-link changelog-nav-prev">
                            <span class="nav-label"><?php esc_html_e('Previous Release', 'product-roadmap'); ?></span>
                            <span class="nav-title"><?php echo esc_html($prev_post->post_title); ?></span>
                        </a>
                    <?php else: ?>
                        <span class="changelog-nav-link changelog-nav-prev disabled"></span>
                    <?php endif; ?>

                    <?php if ($next_post): ?>
                        <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="changelog-nav-link changelog-nav-next">
                            <span class="nav-label"><?php esc_html_e('Next Release', 'product-roadmap'); ?></span>
                            <span class="nav-title"><?php echo esc_html($next_post->post_title); ?></span>
                        </a>
                    <?php else: ?>
                        <span class="changelog-nav-link changelog-nav-next disabled"></span>
                    <?php endif; ?>
                </nav>

                <?php if ($product): ?>
                    <div class="changelog-all-releases">
                        <a href="<?php echo esc_url(home_url('/changelog/' . $product->slug . '/')); ?>" class="view-all-link">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="8" y1="6" x2="21" y2="6"></line>
                                <line x1="8" y1="12" x2="21" y2="12"></line>
                                <line x1="8" y1="18" x2="21" y2="18"></line>
                                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                <line x1="3" y1="18" x2="3.01" y2="18"></line>
                            </svg>
                            <?php
                            /* translators: %s: product name */
                            printf(esc_html__('View all %s releases', 'product-roadmap'), esc_html($product->name));
                            ?>
                        </a>
                    </div>
                <?php endif; ?>
            </footer>

        </article>
    </div>

    <?php
endwhile;

get_footer();
