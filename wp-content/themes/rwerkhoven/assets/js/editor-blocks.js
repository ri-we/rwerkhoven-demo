/**
 * Editor-side stubs for the theme's server-rendered blocks, so they show up
 * cleanly in the block editor instead of as "unsupported block".
 */
( function ( blocks, element ) {
	'use strict';

	var el = element.createElement;

	function placeholder( label ) {
		return function () {
			return el(
				'p',
				{
					style: {
						padding: '0.75rem 1rem',
						border: '1px dashed #949494',
						color: '#555',
						fontFamily: 'monospace',
						fontSize: '13px',
					},
				},
				label
			);
		};
	}

	blocks.registerBlockType( 'rwerkhoven/project-meta', {
		title: 'Projectgegevens',
		icon: 'info-outline',
		category: 'widgets',
		edit: placeholder( 'Projectgegevens (type · locatie · jaar) - automatisch ingevuld' ),
		save: function () {
			return null;
		},
	} );

	blocks.registerBlockType( 'rwerkhoven/hero-carousel', {
		title: 'Homepage-carousel',
		icon: 'images-alt2',
		category: 'widgets',
		edit: placeholder( 'Homepage-carousel - foto's kiezen via Weergave → Homepage-carousel' ),
		save: function () {
			return null;
		},
	} );

	blocks.registerBlockType( 'rwerkhoven/contact-form', {
		title: 'Contactformulier',
		icon: 'email',
		category: 'widgets',
		edit: placeholder( 'Contactformulier - wordt op de site weergegeven' ),
		save: function () {
			return null;
		},
	} );
} )( window.wp.blocks, window.wp.element );
