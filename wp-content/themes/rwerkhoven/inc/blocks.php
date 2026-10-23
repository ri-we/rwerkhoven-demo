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
