<?php
/**
 * Pattern category. The patterns themselves live as standard header-annotated
 * files under patterns/ (WordPress auto-registers those); this just gives
 * them a proper category label instead of falling back to "Uncategorized".
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	function () {
		register_block_pattern_category(
			'rwerkhoven',
			array( 'label' => __( 'Ron Werkhoven', 'rwerkhoven' ) )
		);
	}
);
