<?php
/**
 * Small hardening measures that belong with the theme. Login protection,
 * brute-force blocking and file-level rules are left to the security plugin
 * (Kadence Security) and WP Toolkit on the live server.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Security headers on every WordPress response. HSTS only over HTTPS and not
 * on local installs, so a dev site on http://localhost keeps working.
 */
add_action(
	'send_headers',
	function () {
		header( 'X-Content-Type-Options: nosniff' );
		header( 'X-Frame-Options: SAMEORIGIN' );
		header( 'Referrer-Policy: strict-origin-when-cross-origin' );
		header( 'Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()' );

		if ( is_ssl() && 'local' !== wp_get_environment_type() ) {
			header( 'Strict-Transport-Security: max-age=31536000' );
		}
	}
);

// Don't advertise the WordPress version in the page source or feeds.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// XML-RPC is not used by this site; it is a common brute-force target.
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter(
	'wp_headers',
	function ( $headers ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}
);

// No discovery links that only matter to blogs and API clients.
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
