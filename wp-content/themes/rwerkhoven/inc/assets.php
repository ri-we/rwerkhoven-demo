<?php
/**
 * Front-end and editor asset loading.
 *
 * Fonts are self-hosted (assets/fonts, declared in assets/css/fonts.css): a
 * request to Google Fonts would hand every visitor's IP address to Google,
 * which the privacy statement (/privacy/) says does not happen.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'rwerkhoven-fonts',
			RWERKHOVEN_URI . '/assets/css/fonts.css',
			array(),
			(string) filemtime( RWERKHOVEN_DIR . '/assets/css/fonts.css' )
		);

		// Version by file modification time so browsers pick up every edit
		// immediately; the static theme version alone let stale CSS linger.
		wp_enqueue_style(
			'rwerkhoven-style',
			RWERKHOVEN_URI . '/assets/css/style.css',
			array( 'rwerkhoven-fonts' ),
			(string) filemtime( RWERKHOVEN_DIR . '/assets/css/style.css' )
		);

		if ( is_front_page() ) {
			wp_enqueue_script(
				'rwerkhoven-hero-carousel',
				RWERKHOVEN_URI . '/assets/js/hero-carousel.js',
				array(),
				(string) filemtime( RWERKHOVEN_DIR . '/assets/js/hero-carousel.js' ),
				array( 'in_footer' => true )
			);
		}

		if ( is_singular( 'project' ) ) {
			wp_enqueue_script(
				'rwerkhoven-gallery-carousel',
				RWERKHOVEN_URI . '/assets/js/gallery-carousel.js',
				array(),
				(string) filemtime( RWERKHOVEN_DIR . '/assets/js/gallery-carousel.js' ),
				array( 'in_footer' => true )
			);
		}
	}
);

add_action(
	'enqueue_block_editor_assets',
	function () {
		wp_enqueue_script(
			'rwerkhoven-editor-blocks',
			RWERKHOVEN_URI . '/assets/js/editor-blocks.js',
			array( 'wp-blocks', 'wp-element' ),
			(string) filemtime( RWERKHOVEN_DIR . '/assets/js/editor-blocks.js' ),
			true
		);
	}
);

add_action(
	'admin_init',
	function () {
		add_editor_style(
			array(
				'assets/css/fonts.css',
				'assets/css/style.css',
			)
		);
	}
);

// WordPress' emoji fallback loads images from s.w.org; current browsers draw
// emoji themselves, so drop it and keep every request on this domain.
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
