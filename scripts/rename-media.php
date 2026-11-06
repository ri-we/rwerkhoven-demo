<?php
/**
 * Gives every project image a descriptive file name and alt text, for image
 * search and accessibility: "147_1440.jpg" becomes
 * "uitbouw-vrijstaande-woning-abcoude-1.jpg", "Begane-grond.jpg" becomes
 * "uitbouw-villa-bilthoven-plattegrond-begane-grond.jpg".
 *
 * Renames the main file and every generated size on disk, updates the
 * attachment metadata, and rewrites the image URLs and alt attributes in all
 * post content. Images whose name already starts with their project's base
 * name are left alone, so the script can run again safely.
 *
 * Run inside the wpcli container:
 *   docker compose cp scripts/rename-media.php wpcli:/tmp/rename-media.php
 *   docker compose exec -T wpcli wp eval 'require "/tmp/rename-media.php";'
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via wp eval.\n" );
}

// File-name base per project (post slug => base).
$bases = array(
	'verbouw-woning-abcoude'      => 'uitbouw-vrijstaande-woning-abcoude',
	'diverse-projecten-2'         => 'aanbouw-woning-diverse-projecten',
	'polderhuis-abcoude'          => 'herbestemming-gemeentehuis-abcoude',
	'woning-oisterwijk'           => 'verbouw-woning-oisterwijk',
	'uitbreiding-villa-bilthoven' => 'uitbouw-villa-bilthoven',
	'herbestemming-abn-amro-bank' => 'herbestemming-abn-amro-bank',
	'verbouw-woning-soesterberg'  => 'verbouw-woning-soesterberg',
	'nieuwbouw-woning'            => 'nieuwbouw-woning',
	'verbouw-steps-winkels'       => 'verbouw-steps-winkel',
	'aanbouw-abcoude'             => 'aanbouw-woning-abcoude',
	'didi'                        => 'didi-winkel-hoogeveen',
	'kapsalon'                    => 'scissors-hairdesign-kapsalon',
	'diverse-projecten'           => 'bouwtekeningen-diverse-projecten',
	'coop'                        => 'coop-supermarkt',
	'didi-utrecht'                => 'didi-winkel-utrecht',
	'herbestemming-sportschool'   => 'herbestemming-sportschool',
	'fedex-sorteercentrum'        => 'fedex',
);

// Old-name keywords that say what an image shows (checked in this order).
$descriptors = array(
	'/bestaande-toestand|bestaand/i' => array( 'bestaande-situatie', 'Bestaande situatie' ),
	'/nieuwe-toestand/i'             => array( 'nieuwe-situatie', 'Nieuwe situatie' ),
	'/^voorgevel/i'                  => array( 'voorgevel', 'Voorgevel' ),
	'/^achtergevel/i'                => array( 'achtergevel', 'Achtergevel' ),
	'/^begane-grond/i'               => array( 'plattegrond-begane-grond', 'Plattegrond begane grond' ),
	'/^1e-verdieping/i'              => array( 'plattegrond-1e-verdieping', 'Plattegrond 1e verdieping' ),
	'/^2e-verdieping/i'              => array( 'plattegrond-2e-verdieping', 'Plattegrond 2e verdieping' ),
	'/^verdieping/i'                 => array( 'plattegrond-verdieping', 'Plattegrond verdieping' ),
	'/^doorsnede/i'                  => array( 'doorsnede', 'Doorsnede' ),
	'/^hoogstraat/i'                 => array( 'hoogstraat', 'Gevel aan de Hoogstraat' ),
	'/^torenlaan/i'                  => array( 'torenlaan', 'Torenlaan' ),
	'/^baambrugge/i'                 => array( 'baambrugge', 'Tekening woning Baambrugge' ),
	'/^woonboerderij/i'              => array( 'woonboerderij', 'Tekening woonboerderij' ),
	'/^utrecht-winkelpand/i'         => array( 'winkelpand-utrecht', 'Tekening winkelpand Utrecht' ),
	'/^abcoude-nieuwbouw/i'          => array( 'nieuwbouw-abcoude', 'Tekening nieuwbouw Abcoude' ),
);

$uploads = wp_upload_dir();
$base_dir = trailingslashit( $uploads['basedir'] );
$map      = array(); // "2024/01/Old.jpg" => "2024/01/new.jpg", for every file.
$alts     = array(); // attachment ID => new alt text.
$renamed  = 0;

foreach ( $bases as $slug => $base ) {
	$project = get_page_by_path( $slug, OBJECT, 'project' );
	if ( ! $project ) {
		echo "overgeslagen, project ontbreekt: {$slug}\n";
		continue;
	}
	$title = wp_specialchars_decode( $project->post_title, ENT_QUOTES );

	$attachments = get_posts(
		array(
			'post_type'   => 'attachment',
			'post_parent' => $project->ID,
			'post_status' => 'inherit',
			'numberposts' => -1,
			'orderby'     => 'ID',
			'order'       => 'ASC',
		)
	);

	$n = 0;
	foreach ( $attachments as $attachment ) {
		$meta = wp_get_attachment_metadata( $attachment->ID );
		if ( empty( $meta['file'] ) ) {
			continue;
		}

		$dir      = dirname( $meta['file'] );
		$old_main = basename( $meta['file'] );
		$ext      = strtolower( pathinfo( $old_main, PATHINFO_EXTENSION ) );
		$old_stem = pathinfo( $old_main, PATHINFO_FILENAME );

		if ( 0 === strpos( $old_stem, $base ) ) {
			continue; // Already descriptive.
		}

		// Strip WordPress' "-scaled" and "-e<timestamp>" edit suffixes before matching.
		$clean = preg_replace( '/(-e\d{10,})?(-scaled)?$/', '', $old_stem );

		$descriptor = null;
		$label      = null;
		foreach ( $descriptors as $pattern => $d ) {
			if ( preg_match( $pattern, $clean ) ) {
				list( $descriptor, $label ) = $d;
				break;
			}
		}
		if ( null === $descriptor ) {
			$n++;
			$descriptor = (string) $n;
			$label      = null;
		}

		$new_stem = $base . '-' . $descriptor;
		$suffix   = 2;
		while ( file_exists( $base_dir . $dir . '/' . $new_stem . '.' . $ext ) ) {
			$new_stem = $base . '-' . $descriptor . '-' . $suffix++;
		}

		// Main file.
		$new_main = $new_stem . '.' . $ext;
		if ( ! rename( $base_dir . $dir . '/' . $old_main, $base_dir . $dir . '/' . $new_main ) ) {
			echo "FOUT bij hernoemen {$old_main}\n";
			continue;
		}
		$map[ $dir . '/' . $old_main ] = $dir . '/' . $new_main;

		// Generated sizes: "<old>-300x200.jpg" -> "<new>-300x200.jpg".
		foreach ( (array) ( $meta['sizes'] ?? array() ) as $size => $info ) {
			$old_size = $info['file'];
			$new_size = preg_replace( '/^' . preg_quote( $old_stem, '/' ) . '/', $new_stem, $old_size );
			if ( $new_size === $old_size ) {
				continue;
			}
			if ( file_exists( $base_dir . $dir . '/' . $old_size ) ) {
				rename( $base_dir . $dir . '/' . $old_size, $base_dir . $dir . '/' . $new_size );
			}
			$meta['sizes'][ $size ]['file'] = $new_size;
			$map[ $dir . '/' . $old_size ]  = $dir . '/' . $new_size;
		}

		// The unscaled original behind a "-scaled" image.
		if ( ! empty( $meta['original_image'] ) ) {
			$old_orig = $meta['original_image'];
			$new_orig = $new_stem . '-origineel.' . strtolower( pathinfo( $old_orig, PATHINFO_EXTENSION ) );
			if ( file_exists( $base_dir . $dir . '/' . $old_orig ) ) {
				rename( $base_dir . $dir . '/' . $old_orig, $base_dir . $dir . '/' . $new_orig );
				$meta['original_image'] = $new_orig;
				$map[ $dir . '/' . $old_orig ] = $dir . '/' . $new_orig;
			} else {
				unset( $meta['original_image'] );
			}
		}

		$meta['file'] = $dir . '/' . $new_main;
		wp_update_attachment_metadata( $attachment->ID, $meta );
		update_post_meta( $attachment->ID, '_wp_attached_file', $dir . '/' . $new_main );
		// Backups from old in-dashboard edits point at files that no longer exist.
		delete_post_meta( $attachment->ID, '_wp_attachment_backup_sizes' );

		$alt = $label ? $label . ' - ' . $title : $title . ', foto ' . $descriptor;
		update_post_meta( $attachment->ID, '_wp_attachment_image_alt', $alt );
		wp_update_post(
			array(
				'ID'         => $attachment->ID,
				'post_title' => wp_slash( $label ? $label . ' - ' . $title : $title . ' ' . $descriptor ),
				'guid'       => $uploads['baseurl'] . '/' . $dir . '/' . $new_main,
			)
		);
		$alts[ $attachment->ID ] = $alt;

		$renamed++;
		echo "{$old_main} -> {$new_main}\n";
	}
}

// Rewrite file URLs and alt attributes in all content. strtr() replaces the
// longest keys first in a single pass, so "Soesterberg.jpg" never matches
// inside "Soesterberg-1.jpg".
$url_map = array();
foreach ( $map as $old => $new ) {
	$url_map[ '/uploads/' . $old ] = '/uploads/' . $new;
}

$updated = 0;
foreach ( get_posts( array( 'post_type' => array( 'page', 'project' ), 'post_status' => 'any', 'numberposts' => -1 ) ) as $post ) {
	$content = strtr( $post->post_content, $url_map );

	$content = preg_replace_callback(
		'/<img\b[^>]*\bclass="[^"]*\bwp-image-(\d+)\b[^"]*"[^>]*>/',
		static function ( $m ) use ( $alts ) {
			$id = (int) $m[1];
			if ( ! isset( $alts[ $id ] ) ) {
				return $m[0];
			}
			$alt = 'alt="' . esc_attr( $alts[ $id ] ) . '"';
			return preg_match( '/\balt="[^"]*"/', $m[0] )
				? preg_replace( '/\balt="[^"]*"/', $alt, $m[0], 1 )
				: str_replace( '<img ', '<img ' . $alt . ' ', $m[0] );
		},
		$content
	);

	if ( $content !== $post->post_content ) {
		wp_update_post( array( 'ID' => $post->ID, 'post_content' => wp_slash( $content ) ) );
		$updated++;
	}
}

printf( "klaar: %d afbeeldingen hernoemd, %d bestanden, %d pagina's/projecten bijgewerkt\n", $renamed, count( $map ), $updated );
