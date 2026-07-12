<?php
/**
 * PHPStan bootstrap: declare plugin constants that are define()'d at runtime
 * in the main plugin file, so static analysis resolves them.
 */
define( 'ROADMAP_VERSION', '1.7.0' );
define( 'ROADMAP_PLUGIN_FILE', __FILE__ );
define( 'ROADMAP_PLUGIN_PATH', __DIR__ . '/' );
define( 'ROADMAP_PLUGIN_URL', 'https://example.test/' );
define( 'ROADMAP_PLUGIN_BASENAME', 'product-roadmap/product-roadmap.php' );
define( 'ROADMAP_PRO_ACTIVE', true );
