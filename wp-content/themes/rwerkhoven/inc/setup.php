<?php
/**
 * Theme support and core setup.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'rwerkhoven', RWERKHOVEN_DIR . '/languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

		// Block theme: no classic menus/widgets, no custom-header/-background.
		remove_theme_support( 'custom-header' );
		remove_theme_support( 'custom-background' );
	}
);

/**
 * The old thank-you URL of the live site redirects to the new one
 * (docs/site-architecture.md).
 */
add_action(
	'template_redirect',
	function () {
		if ( ! is_404() ) {
			return;
		}

		$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		if ( '/bedankt-voor-je-contact-bericht/' === trailingslashit( $path ) ) {
			wp_safe_redirect( home_url( '/bedankt/' ), 301 );
			exit;
		}
	}
);

/**
 * Skip link (brief §17 accessibility) — templates wrap their main content
 * in <main id="main-content">.
 */
add_action(
	'wp_body_open',
	function () {
		printf(
			'<a class="skip-link" href="#main-content">%s</a>',
			esc_html__( 'Direct naar de inhoud', 'rwerkhoven' )
		);
	}
);

/**
 * Keep typed characters as typed. wptexturize() turns " - " into an en dash
 * and straight quotes into curly ones; the owner wants plain hyphens.
 */
add_filter( 'run_wptexturize', '__return_false' );
