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

		wp_enqueue_style(
			'rwerkhoven-style',
			RWERKHOVEN_URI . '/assets/css/style.css',
			array( 'rwerkhoven-fonts' ),
			RWERKHOVEN_VERSION
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
