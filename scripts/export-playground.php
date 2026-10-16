<?php
/**
 * Exports the site's content to playground/content.json — a self-contained,
 * network-free description of every page, project, attachment and setting.
 *
 * Why not a WXR export? The WordPress importer re-downloads every attachment
 * from the URL in the file. In WordPress Playground that runs in the visitor's
 * browser, where those requests are blocked by CORS, so every photo would be
 * missing. This export instead records each attachment's path inside the
 * uploads zip that ships alongside it, and playground/seed.php attaches the
 * files that are already on disk. Nothing is fetched at run time.
 *
 * Attachment dimensions are read from the slimmed demo set (/tmp/pg-final)
 * when it is present, so the metadata matches the files that actually ship
 * and no srcset entry points at a size that was left out.
 *
 * Run inside the wpcli container:
 *   docker compose cp scripts/export-playground.php wpcli:/tmp/export-playground.php
 *   docker compose exec -T wpcli wp eval-file /tmp/export-playground.php
 *   docker compose cp wpcli:/tmp/content.json playground/content.json
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via wp eval-file.\n" );
}

$demo_uploads = '/tmp/pg-final';
$out_path     = '/tmp/content.json';

// WordPress' own starter content has no place in a client demo.
$skip_slugs = array( 'sample-page', 'privacy-policy', 'hello-world' );

$export = array(
	'generated'   => gmdate( 'c' ),
	'site'        => array(),
	'posts'       => array(),
	'attachments' => array(),
);

/* ---------------------------------------------------------------- settings */

$front_page = (int) get_option( 'page_on_front' );

$export['site'] = array(
	// The seed rewrites absolute URLs in post content from this host to
	// whatever host the import ends up running on.
	'home_url'            => home_url(),
	'blogname'            => get_option( 'blogname' ),
	'blogdescription'     => get_option( 'blogdescription' ),
	'permalink_structure' => '/%postname%/',
	'timezone_string'     => get_option( 'timezone_string' ),
	'show_on_front'       => get_option( 'show_on_front' ),
	'page_on_front_slug'  => $front_page ? get_post_field( 'post_name', $front_page ) : '',
	'language'            => get_option( 'WPLANG' ),
	'theme'               => get_stylesheet(),
);

/* ------------------------------------------------------------------- posts */

$posts = get_posts(
	array(
		'post_type'   => array( 'page', 'project' ),
		'post_status' => array( 'publish', 'draft', 'private' ),
		'numberposts' => -1,
		'orderby'     => 'ID',
		'order'       => 'ASC',
	)
);

foreach ( $posts as $post ) {
	if ( in_array( $post->post_name, $skip_slugs, true ) ) {
		continue;
	}

	$meta = array();
	foreach ( get_post_meta( $post->ID ) as $key => $values ) {
		// Thumbnails are re-linked by file path below; IDs mean nothing after import.
		if ( '_thumbnail_id' === $key || '_edit_lock' === $key || '_edit_last' === $key ) {
			continue;
		}
		$meta[ $key ] = maybe_unserialize( $values[0] );
	}

	$thumb_id   = (int) get_post_thumbnail_id( $post->ID );
	$thumb_file = $thumb_id ? get_post_meta( $thumb_id, '_wp_attached_file', true ) : '';

	$terms = array();
	foreach ( get_object_taxonomies( $post->post_type ) as $tax ) {
		$assigned = wp_get_object_terms( $post->ID, $tax );
		if ( is_wp_error( $assigned ) || ! $assigned ) {
			continue;
		}
		foreach ( $assigned as $term ) {
			$terms[ $tax ][] = array(
				'slug'        => $term->slug,
				'name'        => $term->name,
				'description' => $term->description,
			);
		}
	}

	$export['posts'][] = array(
		'old_id'         => $post->ID,
		'type'           => $post->post_type,
		'slug'           => $post->post_name,
		'title'          => $post->post_title,
		'content'        => $post->post_content,
		'excerpt'        => $post->post_excerpt,
		'status'         => $post->post_status,
		'menu_order'     => (int) $post->menu_order,
		'parent_slug'    => $post->post_parent ? get_post_field( 'post_name', $post->post_parent ) : '',
		'meta'           => $meta,
		'thumbnail_file' => $thumb_file,
		'terms'          => $terms,
	);
}

/* ------------------------------------------------------------- attachments */

$attachments = get_posts(
	array(
		'post_type'   => 'attachment',
		'post_status' => 'any',
		'numberposts' => -1,
		'orderby'     => 'ID',
		'order'       => 'ASC',
	)
);

$dropped_sizes = 0;

foreach ( $attachments as $attachment ) {
	$file = get_post_meta( $attachment->ID, '_wp_attached_file', true );
	if ( ! $file ) {
		continue;
	}

	$metadata = wp_get_attachment_metadata( $attachment->ID );
	$metadata = is_array( $metadata ) ? $metadata : array();

	// Re-measure against the files that actually ship, and drop any registered
	// size whose file was left out of the demo set.
	if ( is_dir( $demo_uploads ) ) {
		$main = $demo_uploads . '/' . $file;
		if ( ! is_file( $main ) ) {
			continue;
		}
		$dim = @getimagesize( $main );
		if ( $dim ) {
			$metadata['width']  = $dim[0];
			$metadata['height'] = $dim[1];
		}
		$metadata['filesize'] = filesize( $main );

		if ( ! empty( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			$dir = dirname( $file );
			foreach ( $metadata['sizes'] as $name => $size ) {
				$size_path = $demo_uploads . '/' . ( '.' === $dir ? '' : $dir . '/' ) . $size['file'];
				if ( ! is_file( $size_path ) ) {
					unset( $metadata['sizes'][ $name ] );
					$dropped_sizes++;
					continue;
				}
				$size_dim = @getimagesize( $size_path );
				if ( $size_dim ) {
					$metadata['sizes'][ $name ]['width']  = $size_dim[0];
					$metadata['sizes'][ $name ]['height'] = $size_dim[1];
				}
				$metadata['sizes'][ $name ]['filesize'] = filesize( $size_path );
			}
		}
	}

	$export['attachments'][] = array(
		'old_id'      => $attachment->ID,
		'file'        => $file,
		'title'       => $attachment->post_title,
		'slug'        => $attachment->post_name,
		'caption'     => $attachment->post_excerpt,
		'description' => $attachment->post_content,
		'mime'        => $attachment->post_mime_type,
		'alt'         => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
		'parent_slug' => $attachment->post_parent ? get_post_field( 'post_name', $attachment->post_parent ) : '',
		'source_url'  => get_post_meta( $attachment->ID, '_rwerkhoven_source_url', true ),
		'metadata'    => $metadata,
	);
}

/* ------------------------------------------------------------------ write */

$json = wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
file_put_contents( $out_path, $json );

printf(
	"%s: %d pagina's/projecten, %d bijlagen, %d ongebruikte maten weggelaten, %.2f MB\n",
	basename( $out_path ),
	count( $export['posts'] ),
	count( $export['attachments'] ),
	$dropped_sizes,
	strlen( $json ) / 1048576
);
