<?php
/**
 * Front-end and editor asset loading.
 *
 * Fonts are loaded from Google Fonts for now (Phase 4 scaffolding). Self-hosting
 * the specific weights used is tracked as a Phase 14 (performance) follow-up —
 * see docs/design-decision.md.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style(
			'rwerkhoven-fonts',
			'https://fonts.googleapis.com/css2?family=Archivo+Narrow:wght@600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap',
			array(),
			null
		);

		// Version by file modification time so browsers pick up every edit
		// immediately; the static theme version alone let stale CSS linger.
		wp_enqueue_style(
			'rwerkhoven-style',
			RWERKHOVEN_URI . '/assets/css/style.css',
			array( 'rwerkhoven-fonts' ),
			(string) filemtime( RWERKHOVEN_DIR . '/assets/css/style.css' )
		);

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
				'https://fonts.googleapis.com/css2?family=Archivo+Narrow:wght@600;700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap',
				'assets/css/style.css',
			)
		);
	}
);
