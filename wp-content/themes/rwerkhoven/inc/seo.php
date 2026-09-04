<?php
/**
 * Lightweight native SEO: per-page meta title and description via post meta,
 * with sensible defaults for the front page and the project archive.
 * No plugin dependency; fields are editable in the editor's custom fields
 * panel and set by scripts/create-pages.php.
 */

defined( 'ABSPATH' ) || exit;

const RWERKHOVEN_HOME_DESCRIPTION = 'Bouwkundig ontwerp- en tekenbureau in Soest: ontwerp, bouwtekeningen en omgevingsvergunningen voor particulieren en bedrijven. Eén aanspreekpunt, vrijblijvende offerte.';

add_action(
	'init',
	function () {
		foreach ( array( 'page', 'project' ) as $post_type ) {
			foreach ( array( 'rwerkhoven_seo_title', 'rwerkhoven_seo_description' ) as $meta_key ) {
				register_post_meta(
					$post_type,
					$meta_key,
					array(
						'type'              => 'string',
						'single'            => true,
						'show_in_rest'      => true,
						'sanitize_callback' => 'sanitize_text_field',
						'auth_callback'     => function () {
							return current_user_can( 'edit_posts' );
						},
					)
				);
			}
		}
	}
);

/**
 * Custom meta title, when set. A custom title replaces the whole
 * "Title – Site" pattern, so it can carry its own branding.
 */
add_filter(
	'pre_get_document_title',
	function ( $title ) {
		if ( is_front_page() ) {
			return 'Bouwkundig Ontwerp- en Tekenbureau Soest | Ron Werkhoven';
		}

		if ( is_singular() ) {
			$custom = get_post_meta( get_queried_object_id(), 'rwerkhoven_seo_title', true );
			if ( $custom ) {
				return $custom;
			}
		}

		return $title;
	}
);

/**
 * Meta description.
 */
add_action(
	'wp_head',
	function () {
		$description = '';

		if ( is_front_page() ) {
			$description = RWERKHOVEN_HOME_DESCRIPTION;
		} elseif ( is_post_type_archive( 'project' ) ) {
			$description = 'Gerealiseerde projecten van Ron Werkhoven: aanbouwen, verbouwingen en nieuwbouw voor particulieren, en winkels, herbestemmingen en bedrijfsruimten voor zakelijke opdrachtgevers.';
		} elseif ( is_singular() ) {
			$description = get_post_meta( get_queried_object_id(), 'rwerkhoven_seo_description', true );

			if ( '' === $description && is_singular( 'project' ) ) {
				$description = wp_strip_all_tags( get_the_excerpt( get_queried_object_id() ) );
				$description = wp_html_excerpt( $description, 155, '…' );
			}
		}

		if ( '' !== $description ) {
			printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
		}
	},
	1
);
