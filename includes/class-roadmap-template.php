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
	 * Sidebar layout for the plugin's own pages (GeneratePress and Astra filters).
	 *
	 * @return string
	 */
	public static function no_sidebar_layout() {
		return 'no-sidebar';
	}

	/** Footer template part, rendered with the header so its scripts are known before wp_head(). */
	private static $block_footer = '';

	/**
	 * Page header for the plugin's own templates.
	 *
	 * Classic themes: get_header(). Block themes have no header.php (core falls
	 * back to a deprecated compat header and the page loses the site header), so
	 * print the document head and the theme's header template part instead.
	 *
	 * The theme's header and footer parts are rendered BEFORE wp_head(), as core's
	 * own template canvas does: an interactive block in them (the Navigation
	 * block's overlay menu, search, a mini cart) enqueues its script module while
	 * it renders, and a module enqueued after the head is printed cannot resolve
	 * its @wordpress/interactivity import, which leaves the mobile menu dead.
	 */
	public static function get_header() {
		if ( ! wp_is_block_theme() ) {
			// The plugin's own pages print no sidebar, so tell the themes that reserve
			// room for one (GeneratePress, Astra) to give the content the full width.
			add_filter( 'generate_sidebar_layout', array( __CLASS__, 'no_sidebar_layout' ) );
			add_filter( 'astra_page_layout', array( __CLASS__, 'no_sidebar_layout' ) );
			get_header();
			// Classic themes lay out and style their content area by #primary and
			// main.site-main (Astra and GeneratePress make the parent a flex row). A
			// bare wrapper right after get_header() becomes a flex item that shrinks
			// to its content, so give the page the standard content markup.
			echo '<div id="primary" class="content-area"><main id="main" class="site-main">';
			return;
		}
		ob_start();
		block_template_part( 'header' );
		$header = ob_get_clean();
		ob_start();
		block_template_part( 'footer' );
		self::$block_footer = ob_get_clean();
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
		echo $header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core block output.
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
			echo '</main></div>';
			get_footer();
			return;
		}
		echo self::$block_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Core block output, rendered in get_header().
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
