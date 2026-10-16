<?php
/**
 * Rebuilds the rwerkhoven.nl demo content from content.json.
 *
 * Runs inside WordPress Playground (see blueprint.json), where there is no
 * network access to the origin site: every page, project and photo is created
 * from the JSON next to this file plus the files already unpacked into
 * wp-content/uploads. Nothing is downloaded.
 *
 * Attachment IDs differ from the exported site, so post content is rewritten
 * after the attachments exist: the old host is swapped for the new one and
 * every wp-image-<id> class is re-pointed at the new attachment.
 *
 * Also runnable outside Playground for a local round-trip check:
 *   docker compose exec -T wpcli wp eval-file /tmp/seed.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via wp eval-file or a Playground runPHP step.\n" );
}

$seed_dir  = __DIR__;
$json_path = $seed_dir . '/content.json';

if ( ! is_file( $json_path ) ) {
	exit( "content.json niet gevonden naast seed.php ({$json_path})\n" );
}

$data = json_decode( file_get_contents( $json_path ), true );
if ( ! is_array( $data ) ) {
	exit( "content.json kon niet gelezen worden: " . json_last_error_msg() . "\n" );
}

$uploads     = wp_upload_dir();
$uploads_dir = untrailingslashit( $uploads['basedir'] );
$uploads_url = untrailingslashit( $uploads['baseurl'] );
$old_home    = isset( $data['site']['home_url'] ) ? untrailingslashit( $data['site']['home_url'] ) : '';

$posts_by_slug = array();   // "type:slug" => new ID
$attach_by_old = array();   // old attachment ID => new ID
$attach_by_file = array();  // "2024/01/foo.jpg" => new ID

/* ------------------------------------------------------------------ posts */

// Pages first: a child page needs its parent to exist.
$ordered = array_merge(
	array_values( array_filter( $data['posts'], static fn( $p ) => 'page' === $p['type'] ) ),
	array_values( array_filter( $data['posts'], static fn( $p ) => 'page' !== $p['type'] ) )
);

foreach ( $ordered as $item ) {
	$parent_id = 0;
	if ( ! empty( $item['parent_slug'] ) ) {
		$parent_id = $posts_by_slug[ $item['type'] . ':' . $item['parent_slug'] ] ?? 0;
	}

	$existing = get_page_by_path( $item['slug'], OBJECT, $item['type'] );

	$postarr = array(
		'post_type'    => $item['type'],
		'post_status'  => $item['status'],
		'post_title'   => wp_slash( $item['title'] ),
		'post_name'    => $item['slug'],
		'post_content' => wp_slash( $item['content'] ),
		'post_excerpt' => wp_slash( $item['excerpt'] ),
		'post_parent'  => $parent_id,
		'menu_order'   => $item['menu_order'],
	);
	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
	}

	$post_id = $existing ? wp_update_post( $postarr, true ) : wp_insert_post( $postarr, true );
	if ( is_wp_error( $post_id ) ) {
		echo "FOUT bij {$item['slug']}: " . $post_id->get_error_message() . "\n";
		continue;
	}

	foreach ( (array) $item['meta'] as $key => $value ) {
		update_post_meta( $post_id, $key, $value );
	}

	foreach ( (array) ( $item['terms'] ?? array() ) as $taxonomy => $terms ) {
		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}
		$slugs = array();
		foreach ( $terms as $term ) {
			$existing_term = get_term_by( 'slug', $term['slug'], $taxonomy );
			if ( ! $existing_term ) {
				$created = wp_insert_term( $term['name'], $taxonomy, array( 'slug' => $term['slug'], 'description' => $term['description'] ) );
				if ( is_wp_error( $created ) ) {
					continue;
				}
			}
			$slugs[] = $term['slug'];
		}
		if ( $slugs ) {
			wp_set_object_terms( $post_id, $slugs, $taxonomy, false );
		}
	}

	$posts_by_slug[ $item['type'] . ':' . $item['slug'] ] = $post_id;
}

/* ------------------------------------------------------------ attachments */

$missing_files = 0;

foreach ( $data['attachments'] as $item ) {
	$abs = $uploads_dir . '/' . $item['file'];
	if ( ! is_file( $abs ) ) {
		$missing_files++;
		continue;
	}

	$parent_id = 0;
	if ( ! empty( $item['parent_slug'] ) ) {
		foreach ( array( 'project', 'page' ) as $type ) {
			if ( isset( $posts_by_slug[ $type . ':' . $item['parent_slug'] ] ) ) {
				$parent_id = $posts_by_slug[ $type . ':' . $item['parent_slug'] ];
				break;
			}
		}
	}

	$attachment_id = wp_insert_attachment(
		array(
			'post_mime_type' => $item['mime'],
			'post_title'     => wp_slash( $item['title'] ),
			'post_name'      => $item['slug'],
			'post_excerpt'   => wp_slash( $item['caption'] ),
			'post_content'   => wp_slash( $item['description'] ),
			'post_status'    => 'inherit',
			'guid'           => $uploads_url . '/' . $item['file'],
		),
		$abs,
		$parent_id,
		true
	);

	if ( is_wp_error( $attachment_id ) ) {
		echo "FOUT bij bijlage {$item['file']}: " . $attachment_id->get_error_message() . "\n";
		continue;
	}

	// Use the exported metadata as-is: regenerating thumbnails here would
	// redo work the shipped files already contain, and is slow under wasm.
	wp_update_attachment_metadata( $attachment_id, $item['metadata'] );

	if ( ! empty( $item['alt'] ) ) {
		update_post_meta( $attachment_id, '_wp_attachment_image_alt', $item['alt'] );
	}
	if ( ! empty( $item['source_url'] ) ) {
		update_post_meta( $attachment_id, '_rwerkhoven_source_url', $item['source_url'] );
	}

	$attach_by_old[ (int) $item['old_id'] ]  = $attachment_id;
	$attach_by_file[ $item['file'] ]         = $attachment_id;
}

/* ------------------------------------- re-point content at the new IDs/URLs */

$rewritten = 0;

foreach ( $data['posts'] as $item ) {
	$post_id = $posts_by_slug[ $item['type'] . ':' . $item['slug'] ] ?? 0;
	if ( ! $post_id ) {
		continue;
	}

	$content = $item['content'];

	if ( $old_home && $old_home !== untrailingslashit( home_url() ) ) {
		$content = str_replace( $old_home, untrailingslashit( home_url() ), $content );
	}

	$content = preg_replace_callback(
		'/wp-image-(\d+)/',
		static function ( $m ) use ( $attach_by_old ) {
			$old = (int) $m[1];
			return isset( $attach_by_old[ $old ] ) ? 'wp-image-' . $attach_by_old[ $old ] : $m[0];
		},
		$content
	);

	if ( $content !== $item['content'] ) {
		wp_update_post(
			array(
				'ID'           => $post_id,
				'post_content' => wp_slash( $content ),
			)
		);
		$rewritten++;
	}

	if ( ! empty( $item['thumbnail_file'] ) && isset( $attach_by_file[ $item['thumbnail_file'] ] ) ) {
		set_post_thumbnail( $post_id, $attach_by_file[ $item['thumbnail_file'] ] );
	}
}

/* --------------------------------------------------------------- settings */

$site = $data['site'];

update_option( 'blogname', $site['blogname'] );
update_option( 'blogdescription', $site['blogdescription'] );
update_option( 'permalink_structure', $site['permalink_structure'] );
if ( ! empty( $site['timezone_string'] ) ) {
	update_option( 'timezone_string', $site['timezone_string'] );
}

if ( 'page' === $site['show_on_front'] && ! empty( $site['page_on_front_slug'] ) ) {
	$front = get_page_by_path( $site['page_on_front_slug'], OBJECT, 'page' );
	if ( $front ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front->ID );
	}
}

// WordPress' starter content would otherwise sit in the demo alongside the real pages.
foreach ( array( 'sample-page' => 'page', 'privacy-policy' => 'page', 'hello-world' => 'post' ) as $slug => $type ) {
	$stale = get_page_by_path( $slug, OBJECT, $type );
	if ( $stale ) {
		wp_delete_post( $stale->ID, true );
	}
}

if ( ! empty( $site['theme'] ) && get_stylesheet() !== $site['theme'] ) {
	switch_theme( $site['theme'] );
}

flush_rewrite_rules( false );

printf(
	"klaar: %d pagina's/projecten, %d bijlagen, %d herschreven, %d bestanden ontbraken\n",
	count( $posts_by_slug ),
	count( $attach_by_old ),
	$rewritten,
	$missing_files
);
