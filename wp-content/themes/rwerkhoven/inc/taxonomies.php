<?php
/**
 * `dienst` taxonomy — attached to `project` only.
 *
 * Term slugs are expected to match the service page slugs under /diensten/
 * (see docs/site-architecture.md) so a service page template can query
 * projects by `dienst` = its own slug without an extra mapping table.
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_taxonomy(
			'dienst',
			array( 'project' ),
			array(
				'labels'            => array(
					'name'          => __( 'Diensten', 'rwerkhoven' ),
					'singular_name' => __( 'Dienst', 'rwerkhoven' ),
					'search_items'  => __( 'Diensten zoeken', 'rwerkhoven' ),
					'all_items'     => __( 'Alle diensten', 'rwerkhoven' ),
					'edit_item'     => __( 'Dienst bewerken', 'rwerkhoven' ),
					'update_item'   => __( 'Dienst bijwerken', 'rwerkhoven' ),
					'add_new_item'  => __( 'Nieuwe dienst toevoegen', 'rwerkhoven' ),
					'new_item_name' => __( 'Naam nieuwe dienst', 'rwerkhoven' ),
				),
				'hierarchical'      => true,
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
				'rewrite'           => array( 'slug' => 'dienst' ),
			)
		);
	}
);
