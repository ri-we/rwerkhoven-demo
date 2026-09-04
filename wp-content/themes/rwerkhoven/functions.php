<?php
/**
 * Ron Werkhoven theme bootstrap.
 *
 * Keeps this file to includes only; actual logic lives in inc/.
 */

defined( 'ABSPATH' ) || exit;

define( 'RWERKHOVEN_VERSION', '0.1.0' );
define( 'RWERKHOVEN_DIR', get_template_directory() );
define( 'RWERKHOVEN_URI', get_template_directory_uri() );

require RWERKHOVEN_DIR . '/inc/setup.php';
require RWERKHOVEN_DIR . '/inc/assets.php';
require RWERKHOVEN_DIR . '/inc/post-types.php';
require RWERKHOVEN_DIR . '/inc/taxonomies.php';
require RWERKHOVEN_DIR . '/inc/blocks.php';
require RWERKHOVEN_DIR . '/inc/contact-form.php';
require RWERKHOVEN_DIR . '/inc/seo.php';
require RWERKHOVEN_DIR . '/inc/patterns.php';
