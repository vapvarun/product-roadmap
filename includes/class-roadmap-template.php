<?php
/**
 * Template handler for Roadmap plugin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Roadmap_Template {

	private static $instance = null;

	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Page header for the plugin's own templates.
	 *
	 * Classic themes: get_header(). Block themes have no header.php (core falls
	 * back to a deprecated compat header and the page loses the site header), so
	 * print the document head and the theme's header template part instead.
	 */
	public static function get_header() {
		if ( ! wp_is_block_theme() ) {
			get_header();
			return;
		}
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
<div class="wp-site-blocks">
		<?php
		block_template_part( 'header' );
	}

	/**
	 * Comment list and form. Classic themes: comments_template(). Block themes
	 * have no comments.php (core would print a deprecation and use its compat
	 * file), so render core's Comments block for the current post instead.
	 */
	public static function comments() {
		if ( ! wp_is_block_theme() ) {
			comments_template();
			return;
		}
		echo do_blocks( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core block output.
			'<!-- wp:comments --><div class="wp-block-comments">'
			. '<!-- wp:comments-title /-->'
			. '<!-- wp:comment-template --><!-- wp:comment-author-name /--><!-- wp:comment-date /--><!-- wp:comment-content /--><!-- wp:comment-reply-link /--><!-- /wp:comment-template -->'
			. '<!-- wp:comments-pagination /-->'
			. '<!-- wp:post-comments-form /-->'
			. '</div><!-- /wp:comments -->'
		);
	}

	/**
	 * Page footer for the plugin's own templates; see get_header().
	 */
	public static function get_footer() {
		if ( ! wp_is_block_theme() ) {
			get_footer();
			return;
		}
		block_template_part( 'footer' );
		?>
</div>
		<?php wp_footer(); ?>
</body>
</html>
		<?php
	}

	private function __construct() {
		add_filter( 'single_template', array( $this, 'load_single_template' ) );
		add_filter( 'archive_template', array( $this, 'load_archive_template' ) );
	}

	public function load_single_template( $template ) {
		global $post;

		if ( $post instanceof WP_Post && 'roadmap_item' === $post->post_type ) {
			/**
			 * Fires when a single roadmap item is viewed on the frontend.
			 *
			 * @param int $item_id The roadmap item post ID.
			 */
			do_action( 'roadmap_item_viewed', $post->ID );

			$plugin_template = ROADMAP_PLUGIN_PATH . 'templates/single-roadmap_item.php';

			// Check if template exists in theme first
			$theme_template = locate_template( array( 'single-roadmap_item.php' ) );

			if ( $theme_template ) {
				return $theme_template;
			} elseif ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}

	public function load_archive_template( $template ) {
		// Handle roadmap_product taxonomy archives
		if ( is_tax( 'roadmap_product' ) ) {
			$plugin_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap_product.php';

			// Check if template exists in theme first
			$theme_template = locate_template( array( 'archive-roadmap_product.php', 'taxonomy-roadmap_product.php' ) );

			if ( $theme_template ) {
				return $theme_template;
			} elseif ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		// Handle roadmap_item post type archive (All Products view)
		if ( is_post_type_archive( 'roadmap_item' ) ) {
			$plugin_template = ROADMAP_PLUGIN_PATH . 'templates/archive-roadmap_item.php';

			// Check if template exists in theme first
			$theme_template = locate_template( array( 'archive-roadmap_item.php' ) );

			if ( $theme_template ) {
				return $theme_template;
			} elseif ( file_exists( $plugin_template ) ) {
				return $plugin_template;
			}
		}

		return $template;
	}
}
