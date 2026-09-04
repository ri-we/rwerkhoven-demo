<?php
/**
 * Imports the 15 projects of the live site (rwerkhoven.nl) into the local
 * WordPress install: title, description, photos (sideloaded), featured image
 * and a core Gallery block per project (rendered as a carousel by the theme).
 *
 * Run inside the wpcli container:
 *   docker compose cp scripts/import-projects.php wpcli:/tmp/import-projects.php
 *   docker compose exec -T wpcli wp eval-file /tmp/import-projects.php
 *
 * Idempotent: existing slugs are updated, already-imported photos (tracked via
 * the _rwerkhoven_source_url attachment meta) are not downloaded again.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via wp eval-file.\n" );
}

require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

// Original listing order (top to bottom on https://rwerkhoven.nl/projecten/).
// Location is only set where the original title names one.
$projects = array(
	array( 'slug' => 'verbouw-woning-abcoude', 'location' => 'Abcoude' ),
	array( 'slug' => 'diverse-projecten-2', 'location' => '' ),
	array( 'slug' => 'polderhuis-abcoude', 'location' => 'Abcoude' ),
	array( 'slug' => 'woning-oisterwijk', 'location' => 'Oisterwijk' ),
	array( 'slug' => 'uitbreiding-villa-bilthoven', 'location' => 'Bilthoven' ),
	array( 'slug' => 'herbestemming-abn-amro-bank', 'location' => '' ),
	array( 'slug' => 'verbouw-woning-soesterberg', 'location' => 'Soesterberg' ),
	array( 'slug' => 'nieuwbouw-woning', 'location' => '' ),
	array( 'slug' => 'verbouw-steps-winkels', 'location' => '' ),
	array( 'slug' => 'aanbouw-abcoude', 'location' => 'Abcoude' ),
	array( 'slug' => 'didi', 'location' => '' ),
	array( 'slug' => 'kapsalon', 'location' => '' ),
	array( 'slug' => 'diverse-projecten', 'location' => '' ),
	array( 'slug' => 'coop', 'location' => '' ),
	array( 'slug' => 'didi-utrecht', 'location' => 'Utrecht' ),
);

/**
 * Finds an existing attachment for a source URL (so re-runs do not re-download).
 */
function rwerkhoven_find_attachment_by_source( $source_url ) {
	$found = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_rwerkhoven_source_url',
			'meta_value'     => $source_url,
		)
	);

	return $found ? (int) $found[0] : 0;
}

$base_date = strtotime( '2024-01-15 12:00:00' );
$index     = 0;

foreach ( $projects as $project ) {
	$slug = $project['slug'];
	$url  = 'https://rwerkhoven.nl/project/' . $slug . '/';

	echo "== {$slug}\n";

	$response = wp_remote_get( $url, array( 'timeout' => 30 ) );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		echo "   FOUT: pagina niet opgehaald\n";
		$index++;
		continue;
	}

	$html = wp_remote_retrieve_body( $response );

	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML( '<?xml encoding="utf-8"?>' . $html );
	libxml_clear_errors();
	$xpath = new DOMXPath( $dom );

	// Title + description from the columnblock section.
	$title_node = $xpath->query( '//div[contains(@class,"columnblock")]//h2' )->item( 0 );
	$title      = $title_node ? trim( $title_node->textContent ) : $slug;

	$paragraphs = array();
	foreach ( $xpath->query( '//div[contains(@class,"columnblock")]//div[@class="text"]/p' ) as $p ) {
		$text = trim( preg_replace( '/\s+/u', ' ', $p->textContent ) );

		// The old site lists photo captions ("Foto 1 …") as text; skip those.
		if ( '' === $text || preg_match( '/^foto\s*\d+/i', $text ) ) {
			continue;
		}

		$paragraphs[] = $text;
	}

	// Slider images; strip the -WxH size suffix to get the original file.
	$image_urls = array();
	foreach ( $xpath->query( '//div[contains(@class,"image_slider")]//img/@src' ) as $src_attr ) {
		$src = trim( $src_attr->value );
		if ( '' === $src ) {
			continue;
		}
		if ( 0 === strpos( $src, '/' ) ) {
			$src = 'https://rwerkhoven.nl' . $src;
		}
		$src = preg_replace( '/-\d+x\d+(\.[a-z]+)$/i', '$1', $src );
		if ( ! in_array( $src, $image_urls, true ) ) {
			$image_urls[] = $src;
		}
	}

	// Create or update the post.
	$existing = get_page_by_path( $slug, OBJECT, 'project' );
	$postarr  = array(
		'post_type'   => 'project',
		'post_status' => 'publish',
		'post_title'  => $title,
		'post_name'   => $slug,
		'post_date'   => gmdate( 'Y-m-d H:i:s', $base_date - $index * DAY_IN_SECONDS ),
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id       = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		echo '   FOUT: ' . $post_id->get_error_message() . "\n";
		$index++;
		continue;
	}

	if ( '' !== $project['location'] ) {
		update_post_meta( $post_id, 'rwerkhoven_project_location', $project['location'] );
	}

	// Sideload the photos.
	$attachment_ids = array();
	foreach ( $image_urls as $i => $image_url ) {
		$attachment_id = rwerkhoven_find_attachment_by_source( $image_url );

		if ( ! $attachment_id ) {
			$attachment_id = media_sideload_image( $image_url, $post_id, $title . ' — foto ' . ( $i + 1 ), 'id' );
			if ( is_wp_error( $attachment_id ) ) {
				echo '   foto overgeslagen: ' . $image_url . "\n";
				continue;
			}
			update_post_meta( $attachment_id, '_rwerkhoven_source_url', $image_url );
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $title );
		}

		$attachment_ids[] = (int) $attachment_id;
	}

	// Featured image: keep an existing one, otherwise the first photo.
	if ( ! has_post_thumbnail( $post_id ) && $attachment_ids ) {
		set_post_thumbnail( $post_id, $attachment_ids[0] );
	}

	// Compose the content: description paragraphs + one Gallery block.
	$blocks = '';
	foreach ( $paragraphs as $text ) {
		$blocks .= "<!-- wp:paragraph -->\n<p>" . esc_html( $text ) . "</p>\n<!-- /wp:paragraph -->\n\n";
	}

	if ( $attachment_ids ) {
		$blocks .= "<!-- wp:gallery {\"linkTo\":\"none\",\"sizeSlug\":\"large\"} -->\n";
		$blocks .= "<figure class=\"wp-block-gallery has-nested-images columns-default is-cropped\">";
		foreach ( $attachment_ids as $attachment_id ) {
			$src = wp_get_attachment_image_url( $attachment_id, 'large' );
			$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );

			$blocks .= sprintf(
				"<!-- wp:image {\"id\":%1\$d,\"sizeSlug\":\"large\",\"linkTo\":\"none\"} -->\n<figure class=\"wp-block-image size-large\"><img src=\"%2\$s\" alt=\"%3\$s\" class=\"wp-image-%1\$d\"/></figure>\n<!-- /wp:image -->\n",
				$attachment_id,
				esc_url( $src ),
				esc_attr( $alt )
			);
		}
		$blocks .= "</figure>\n<!-- /wp:gallery -->\n";
	}

	wp_update_post(
		array(
			'ID'           => $post_id,
			'post_content' => $blocks,
		)
	);

	printf( "   ok: post %d, %d alinea's, %d foto's\n", $post_id, count( $paragraphs ), count( $attachment_ids ) );
	$index++;
}

echo "Klaar.\n";
