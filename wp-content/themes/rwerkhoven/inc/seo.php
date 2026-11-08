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

		rwerkhoven_print_social_meta( $description );
		rwerkhoven_print_structured_data();
	},
	1
);

/**
 * Shorter brand in the default title ("Didi - Ron Werkhoven"), so project
 * titles stay within what search results show.
 */
add_filter(
	'document_title_parts',
	function ( $parts ) {
		if ( isset( $parts['site'] ) ) {
			$parts['site'] = 'Ron Werkhoven';
		}
		return $parts;
	}
);

/**
 * The image that represents the current page when it is shared or shown in
 * search: the project's featured image, else the first homepage slide, else
 * the theme's own hero photo.
 */
function rwerkhoven_seo_image() {
	$id = is_singular() ? (int) get_post_thumbnail_id( get_queried_object_id() ) : 0;

	if ( ! $id && function_exists( 'rwerkhoven_hero_slide_ids' ) ) {
		$slides = rwerkhoven_hero_slide_ids();
		$id     = $slides ? $slides[0] : 0;
	}

	if ( $id ) {
		$src = wp_get_attachment_image_src( $id, 'large' );
		if ( $src ) {
			return array(
				'url'    => $src[0],
				'width'  => $src[1],
				'height' => $src[2],
				'alt'    => get_post_meta( $id, '_wp_attachment_image_alt', true ),
			);
		}
	}

	return array(
		'url'    => get_theme_file_uri( 'assets/img/hero-uitbouw-vrijstaande-woning.jpg' ),
		'width'  => 1440,
		'height' => 960,
		'alt'    => '',
	);
}

/**
 * Open Graph and Twitter tags: title, description and image when a page is
 * shared on LinkedIn, WhatsApp, Facebook and the like.
 */
function rwerkhoven_print_social_meta( $description ) {
	$image = rwerkhoven_seo_image();
	$url   = is_singular() ? get_permalink( get_queried_object_id() ) : home_url( add_query_arg( array() ) );
	if ( is_front_page() ) {
		$url = home_url( '/' );
	} elseif ( is_post_type_archive( 'project' ) ) {
		$url = get_post_type_archive_link( 'project' );
	}

	$tags = array(
		'og:locale'      => 'nl_NL',
		'og:type'        => is_singular( 'project' ) ? 'article' : 'website',
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:title'       => wp_get_document_title(),
		'og:description' => $description,
		'og:url'         => $url,
		'og:image'       => $image['url'],
		'og:image:width' => (string) $image['width'],
		'og:image:height' => (string) $image['height'],
		'og:image:alt'   => $image['alt'],
	);

	foreach ( $tags as $property => $content ) {
		if ( '' !== (string) $content ) {
			printf( '<meta property="%s" content="%s" />' . "\n", esc_attr( $property ), esc_attr( $content ) );
		}
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
}

/**
 * Structured data (schema.org JSON-LD): the business details on the pages
 * that are about the business, and a breadcrumb trail on project pages.
 */
function rwerkhoven_print_structured_data() {
	$graph = array();

	if ( is_front_page() || is_page( array( 'contact', 'het-bureau' ) ) ) {
		$image   = rwerkhoven_seo_image();
		$graph[] = array(
			'@type'        => 'ProfessionalService',
			'@id'          => home_url( '/#bureau' ),
			'name'         => get_bloginfo( 'name' ),
			'description'  => RWERKHOVEN_HOME_DESCRIPTION,
			'url'          => home_url( '/' ),
			'image'        => $image['url'],
			'telephone'    => '+31613645507',
			'email'        => 'info@rwerkhoven.nl',
			'foundingDate' => '2010',
			'founder'      => array(
				'@type' => 'Person',
				'name'  => 'Ron Werkhoven',
			),
			'address'      => array(
				'@type'           => 'PostalAddress',
				'streetAddress'   => 'Hofstedering 65',
				'postalCode'      => '3763 XB',
				'addressLocality' => 'Soest',
				'addressCountry'  => 'NL',
			),
			'geo'          => array(
				'@type'     => 'GeoCoordinates',
				'latitude'  => 52.1741646,
				'longitude' => 5.2752089,
			),
			'sameAs'       => array(
				'https://www.linkedin.com/in/ron-werkhoven-2594881a',
				'https://g.page/r/Cf3-IzqPxu_8EAc',
			),
		);
	}

	if ( is_singular( 'project' ) ) {
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array(
					'@type'    => 'ListItem',
					'position' => 1,
					'name'     => 'Home',
					'item'     => home_url( '/' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 2,
					'name'     => 'Projecten',
					'item'     => get_post_type_archive_link( 'project' ),
				),
				array(
					'@type'    => 'ListItem',
					'position' => 3,
					'name'     => wp_strip_all_tags( get_the_title( get_queried_object_id() ) ),
				),
			),
		);
	}

	if ( $graph ) {
		printf(
			'<script type="application/ld+json">%s</script>' . "\n",
			wp_json_encode(
				array(
					'@context' => 'https://schema.org',
					'@graph'   => $graph,
				),
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
			)
		);
	}
}

/**
 * Keep thin or private URLs out of search: the thank-you page after the
 * contact form is noindex and out of the sitemap, and the sitemap does not
 * list user archives (which would expose the admin login name).
 */
add_filter(
	'wp_robots',
	function ( $robots ) {
		if ( is_page( 'bedankt' ) ) {
			$robots['noindex'] = true;
			$robots['follow']  = true;
		}
		return $robots;
	}
);

add_filter(
	'wp_sitemaps_add_provider',
	function ( $provider, $name ) {
		return 'users' === $name ? false : $provider;
	},
	10,
	2
);

// The site has no blog, so category and tag archives are empty pages.
add_filter(
	'wp_sitemaps_taxonomies',
	function ( $taxonomies ) {
		unset( $taxonomies['category'], $taxonomies['post_tag'] );
		return $taxonomies;
	}
);

add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$bedankt = get_page_by_path( 'bedankt' );
			if ( $bedankt ) {
				$args['post__not_in'] = array_merge( $args['post__not_in'] ?? array(), array( $bedankt->ID ) );
			}
		}
		return $args;
	},
	10,
	2
);

// Author archives only repeat other pages and reveal the login name.
add_action(
	'template_redirect',
	function () {
		if ( is_author() ) {
			wp_safe_redirect( home_url( '/' ), 301 );
			exit;
		}
	}
);
