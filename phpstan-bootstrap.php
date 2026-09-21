<?php
/**
 * PHPStan bootstrap: declare plugin constants that are define()'d at runtime
 * in the main plugin file, so static analysis resolves them.
 */

// Never executed by WordPress - this file exists only so static analysis
// knows about the plugin's constants. Guarded anyway: a PHP file that ships
// or is served must not run on a direct request.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
define( 'ROADMAP_VERSION', '1.7.0' );
define( 'ROADMAP_PLUGIN_FILE', __FILE__ );
define( 'ROADMAP_PLUGIN_PATH', __DIR__ . '/' );
define( 'ROADMAP_PLUGIN_URL', 'https://example.test/' );
define( 'ROADMAP_PLUGIN_BASENAME', 'product-roadmap/product-roadmap.php' );
define( 'ROADMAP_PRO_ACTIVE', true );
