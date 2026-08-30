<?php
/**
 * `project` custom post type.
 *
 * Slug and rewrite base match the CPT already live on rwerkhoven.nl
 * (see docs/site-audit.md) so existing /project/{slug}/ URLs keep working.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_post_type(
			'project',
			array(
				'labels'       => array(
					'name'                  => __( 'Projecten', 'rwerkhoven' ),
					'singular_name'         => __( 'Project', 'rwerkhoven' ),
					'add_new_item'          => __( 'Nieuw project toevoegen', 'rwerkhoven' ),
					'edit_item'             => __( 'Project bewerken', 'rwerkhoven' ),
					'all_items'             => __( 'Alle projecten', 'rwerkhoven' ),
					'search_items'          => __( 'Projecten zoeken', 'rwerkhoven' ),
					'not_found'             => __( 'Geen projecten gevonden', 'rwerkhoven' ),
					'featured_image'        => __( 'Hoofdafbeelding project', 'rwerkhoven' ),
					'set_featured_image'    => __( 'Hoofdafbeelding instellen', 'rwerkhoven' ),
					'remove_featured_image' => __( 'Hoofdafbeelding verwijderen', 'rwerkhoven' ),
				),
				'public'       => true,
				'has_archive'  => 'projecten',
				'rewrite'      => array(
					'slug'       => 'project',
					'with_front' => false,
				),
				'menu_icon'    => 'dashicons-building',
				'supports'     => array( 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ),
				'show_in_rest' => true,
				'template'     => array(
					array( 'core/paragraph', array( 'placeholder' => __( 'Korte omschrijving van het project…', 'rwerkhoven' ) ) ),
					array( 'core/gallery' ),
				),
			)
		);
	}
);

/**
 * Structured project fields (location, year, project type, size) as post meta.
 * Plain post meta rather than a plugin dependency (ACF etc.) — kept to a small,
 * explicit field set per docs/site-architecture.md.
 */
add_action(
	'init',
	function () {
		$fields = array(
			'rwerkhoven_project_location' => 'string',
			'rwerkhoven_project_year'     => 'string',
			'rwerkhoven_project_type'     => 'string',
			'rwerkhoven_project_size'     => 'string',
		);

		foreach ( $fields as $meta_key => $type ) {
			register_post_meta(
				'project',
				$meta_key,
				array(
					'type'              => $type,
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
);
