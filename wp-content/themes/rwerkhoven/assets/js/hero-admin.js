/**
 * Admin screen Weergave → Homepage-carousel: pick photos from the media
 * library, reorder them by dragging, remove them. The order of the list is
 * written to a hidden field as comma-separated attachment IDs.
 */
( function ( $ ) {
	'use strict';

	var $list = $( '#rwerkhoven-hero-list' );
	var $ids = $( '#rwerkhoven-hero-ids' );
	var frame;

	function sync() {
		var ids = $list
			.children()
			.map( function () {
				return $( this ).data( 'id' );
			} )
			.get();

		$ids.val( ids.join( ',' ) );
		$( '#rwerkhoven-hero-add' ).prop( 'disabled', ids.length >= window.rwerkhovenHero.max );
	}

	function item( attachment ) {
		var size = attachment.sizes && ( attachment.sizes.medium || attachment.sizes.full );
		var $li = $( '<li class="rwerkhoven-hero-item"></li>' ).attr( 'data-id', attachment.id );

		$( '<img alt="">' ).attr( 'src', size ? size.url : attachment.url ).appendTo( $li );

		if ( attachment.height >= attachment.width ) {
			$( '<p class="rwerkhoven-hero-warning"></p>' )
				.text( 'Staande foto: wordt bijgesneden. Kies liever een liggende foto.' )
				.appendTo( $li );
		}

		$( '<button type="button" class="button-link rwerkhoven-hero-remove"></button>' )
			.text( 'Verwijderen' )
			.appendTo( $li );

		return $li;
	}

	$list.sortable( { update: sync } );

	$list.on( 'click', '.rwerkhoven-hero-remove', function () {
		$( this ).closest( 'li' ).remove();
		sync();
	} );

	$( '#rwerkhoven-hero-add' ).on( 'click', function () {
		if ( ! frame ) {
			frame = wp.media( {
				title: 'Foto\'s voor de homepage-carousel',
				button: { text: 'Toevoegen' },
				library: { type: 'image' },
				multiple: true,
			} );

			frame.on( 'select', function () {
				var present = $ids.val().split( ',' );

				frame
					.state()
					.get( 'selection' )
					.each( function ( model ) {
						var attachment = model.toJSON();

						if ( $list.children().length >= window.rwerkhovenHero.max ) {
							return;
						}
						if ( present.indexOf( String( attachment.id ) ) !== -1 ) {
							return;
						}
						$list.append( item( attachment ) );
					} );

				sync();
			} );
		}

		frame.open();
	} );

	sync();
} )( jQuery );
