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
