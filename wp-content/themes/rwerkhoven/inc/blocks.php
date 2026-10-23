<?php
/**
 * Small dynamic blocks the theme needs that core doesn't provide.
 *
 * Core has no block for displaying post meta, and this theme deliberately
 * avoids an ACF/plugin dependency for four simple text fields (see
 * docs/site-architecture.md) — one small dynamic block is the plainer,
 * dependency-free alternative.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		// The logo is inlined rather than an <img>, so it takes the text colour
		// of the header (fill="currentColor") and matches the menu links.
		register_block_type(
			'rwerkhoven/logo',
			array(
				'render_callback' => function () {
					$svg = file_get_contents( RWERKHOVEN_DIR . '/assets/img/logo.svg' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
					if ( ! $svg ) {
						return '';
					}

					$name = get_bloginfo( 'name' );
					$svg  = str_replace(
						'<svg ',
						'<svg role="img" aria-label="' . esc_attr( $name ) . '" focusable="false" ',
						$svg
					);

					return sprintf(
						'<a class="rwerkhoven-logo" href="%s" rel="home">%s</a>',
						esc_url( home_url( '/' ) ),
						$svg
					);
				},
			)
		);

		register_block_type(
			'rwerkhoven/project-meta',
			array(
				'render_callback' => function () {
					$post_id = get_the_ID();
					if ( ! $post_id ) {
						return '';
					}

					$parts = array();

					$type = get_post_meta( $post_id, 'rwerkhoven_project_type', true );
					if ( $type ) {
						$parts[] = sprintf( 'TYPE - %s', mb_strtoupper( $type ) );
					}

					$location = get_post_meta( $post_id, 'rwerkhoven_project_location', true );
					if ( $location ) {
						$parts[] = sprintf( 'LOCATIE - %s', mb_strtoupper( $location ) );
					}

					$year = get_post_meta( $post_id, 'rwerkhoven_project_year', true );
					if ( $year ) {
						$parts[] = esc_html( $year );
					}

					$size = get_post_meta( $post_id, 'rwerkhoven_project_size', true );
					if ( $size ) {
						$parts[] = esc_html( $size );
					}

					if ( empty( $parts ) ) {
						return '';
					}

					return sprintf(
						'<p class="rwerkhoven-project-meta">%s</p>',
						esc_html( implode( '  ·  ', $parts ) )
					);
				},
			)
		);
	}
);
