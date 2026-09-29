<?php
/**
 * Parity check: every [roadmap] option reaches the board, from every entry point.
 *
 * Run from the site root:  wp eval-file wp-content/plugins/product-roadmap/bin/check-board-options.php
 * Exits non-zero on any failure. Covers the shortcode, the Pro block (when Pro is
 * active) and the Pro block's pre-1.8.0 attribute names.
 *
 * @package product-roadmap
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'Roadmap_Shortcode' ) ) {
	fwrite( STDERR, "Run with: wp eval-file <this file>\n" );
	exit( 1 );
}

wp_set_current_user( 1 ); // Manage mode needs edit_posts.

/**
 * Pick the first real (non-empty) choice for a term-based option.
 *
 * @param string $token Choices token.
 * @return string
 */
function roadmap_check_first_choice( $token ) {
	$keys = array_values( array_filter( array_keys( Roadmap_Shortcode::option_choices( $token ) ), static fn( $key ) => '' !== (string) $key ) );
	return $keys ? (string) $keys[0] : '';
}

/**
 * The board config (data-config) from rendered HTML.
 *
 * @param string $html Rendered output.
 * @return array
 */
function roadmap_check_config( $html ) {
	return preg_match( "/data-config='([^']*)'/", $html, $m ) ? (array) json_decode( html_entity_decode( $m[1] ), true ) : array();
}

$settings  = Roadmap_Settings::get();
$templates = array_keys( Roadmap_Shortcode::option_choices( 'templates' ) );
$default_t = Roadmap_Shortcode::options()['template']['default'];
$other_t   = current( array_diff( $templates, array( $default_t ) ) );

// option => [ value to set, config path, expected value ]. null value = skip (not testable here).
$cases = array(
	'product'             => array( roadmap_check_first_choice( 'products' ), 'product' ),
	'template'            => array( $other_t ? $other_t : null, 'template' ),
	'status'              => array( roadmap_check_first_choice( 'statuses' ), 'status' ),
	'category'            => array( roadmap_check_first_choice( 'categories' ), 'category' ),
	'priority'            => array( 'high', 'priority' ),
	'date_from'           => array( '2026-01-01', 'date_from' ),
	'date_to'             => array( '2026-12-31', 'date_to' ),
	'sort'                => array( 'votes', 'default_sort' ),
	'limit'               => array( 5, 'limit' ),
	'show_title'          => array( false, 'show_title' ),
	'show_filters'        => array( false, 'show_filters' ),
	'show_search'         => array( false, 'show_search' ),
	'show_voting'         => array( $settings['enable_voting'] ? 'hide' : null, 'settings.enable_voting', false ),
	'show_comments'       => array( $settings['enable_comments'] ? 'hide' : null, 'settings.enable_comments', false ),
	'mode'                => array( 'manage', 'mode' ),
	'allow_frontend_edit' => array( true, 'allow_frontend_edit' ),
	'class'               => array( 'qa-extra-class', null ),
);

$failures = 0;
$report   = function ( $label, $ok, $detail = '' ) use ( &$failures ) {
	if ( ! $ok ) {
		++$failures;
	}
	printf( "%s  %s%s\n", $ok ? 'PASS' : 'FAIL', $label, $detail ? "  ($detail)" : '' );
};

// Every option in the schema must have a case, so a new option cannot skip this check.
foreach ( array_keys( Roadmap_Shortcode::options() ) as $name ) {
	if ( ! isset( $cases[ $name ] ) ) {
		$report( "schema option '$name' has a parity case", false, 'add it to $cases' );
	}
}

$read = function ( $config, $path ) {
	foreach ( explode( '.', $path ) as $key ) {
		$config = is_array( $config ) && array_key_exists( $key, $config ) ? $config[ $key ] : '__missing__';
	}
	return $config;
};

$block_available = WP_Block_Type_Registry::get_instance()->is_registered( 'roadmap-pro/roadmap' );

foreach ( $cases as $name => $case ) {
	list( $value, $path ) = $case;
	$expected             = array_key_exists( 2, $case ) ? $case[2] : $value;
	if ( null === $value || '' === $value ) {
		printf( "SKIP  %s (no value to test on this site)\n", $name );
		continue;
	}

	// Shortcode.
	$html = do_shortcode( sprintf( '[roadmap %s="%s"]', $name, is_bool( $value ) ? ( $value ? 'true' : 'false' ) : $value ) );
	if ( null === $path ) {
		$report( "shortcode $name", false !== strpos( $html, esc_attr( $value ) ), 'class on the container' );
	} else {
		$got = $read( roadmap_check_config( $html ), $path );
		$report( "shortcode $name -> $path", $got === $expected, 'got ' . wp_json_encode( $got ) );
	}

	// Block.
	$option = Roadmap_Shortcode::options()[ $name ];
	if ( $block_available && ( ! isset( $option['block'] ) || false !== $option['block'] ) ) {
		$html = do_blocks( '<!-- wp:roadmap-pro/roadmap ' . wp_json_encode( array( $name => $value ) ) . ' /-->' );
		$got  = $read( roadmap_check_config( $html ), $path );
		$report( "block     $name -> $path", $got === $expected, 'got ' . wp_json_encode( $got ) );
	}
}

// Posts saved before 1.8.0 carry the old block attribute names.
if ( $block_available ) {
	$legacy = roadmap_check_config( do_blocks( '<!-- wp:roadmap-pro/roadmap {"order":"votes","showFilters":false,"showSearch":false,"showVoting":false,"showComments":false} /-->' ) );
	$report( 'legacy order -> default_sort', 'votes' === $legacy['default_sort'] );
	$report( 'legacy showFilters -> show_filters', false === $legacy['show_filters'] );
	$report( 'legacy showSearch -> show_search', false === $legacy['show_search'] );
	$report( 'legacy showVoting -> settings.enable_voting', ! $settings['enable_voting'] || false === $legacy['settings']['enable_voting'] );
	$report( 'legacy showComments -> settings.enable_comments', ! $settings['enable_comments'] || false === $legacy['settings']['enable_comments'] );
}

// Defaults: a bare shortcode renders exactly the global settings.
$bare = roadmap_check_config( do_shortcode( '[roadmap]' ) );
$report( 'defaults: voting follows Settings', (bool) $settings['enable_voting'] === $bare['settings']['enable_voting'] );
$report( 'defaults: comments follow Settings', (bool) $settings['enable_comments'] === $bare['settings']['enable_comments'] );
$report( 'defaults: no limit, template = owner default', 0 === $bare['limit'] && $default_t === $bare['template'] );

printf( "\n%s: %d failure(s)\n", $failures ? 'FAILED' : 'OK', $failures );
exit( $failures ? 1 : 0 );
