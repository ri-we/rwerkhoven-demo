/**
 * Turns the core Gallery block on single project pages into a carousel.
 *
 * Progressive enhancement: editors keep working with a plain Gallery block in
 * the admin, and without JavaScript the front end still shows a normal gallery.
 */
( function () {
	'use strict';

	var galleries = document.querySelectorAll(
		'body.single-project .wp-block-post-content .wp-block-gallery'
	);

	Array.prototype.forEach.call( galleries, function ( gallery ) {
		var slides = gallery.querySelectorAll( ':scope > figure, :scope > .wp-block-image' );

		if ( slides.length < 2 ) {
			return;
		}

		gallery.classList.add( 'rwerkhoven-carousel' );

		var wrap = document.createElement( 'div' );
		wrap.className = 'rwerkhoven-carousel-wrap';
		gallery.parentNode.insertBefore( wrap, gallery );
		wrap.appendChild( gallery );

		var controls = document.createElement( 'div' );
		controls.className = 'rwerkhoven-carousel-controls';

		var prev = document.createElement( 'button' );
		prev.type = 'button';
		prev.className = 'rwerkhoven-carousel-btn';
		prev.setAttribute( 'aria-label', 'Vorige foto' );
		prev.textContent = '←';

		var next = document.createElement( 'button' );
		next.type = 'button';
		next.className = 'rwerkhoven-carousel-btn';
		next.setAttribute( 'aria-label', 'Volgende foto' );
		next.textContent = '→';

		var counter = document.createElement( 'span' );
		counter.className = 'rwerkhoven-carousel-counter';
		counter.setAttribute( 'aria-live', 'polite' );

		var current = 0;

		function update() {
			counter.textContent = current + 1 + ' / ' + slides.length;
			prev.disabled = 0 === current;
			next.disabled = current === slides.length - 1;
		}

		function goTo( index ) {
			var motionOk = window.matchMedia( '(prefers-reduced-motion: no-preference)' ).matches;

			current = Math.max( 0, Math.min( slides.length - 1, index ) );
			gallery.scrollTo( {
				left: current * gallery.clientWidth,
				behavior: motionOk ? 'smooth' : 'auto',
			} );
			update();
		}

		prev.addEventListener( 'click', function () {
			goTo( current - 1 );
		} );

		next.addEventListener( 'click', function () {
			goTo( current + 1 );
		} );

		// Keep the counter in sync with touch/trackpad swiping.
		gallery.addEventListener(
			'scroll',
			function () {
				var index = Math.round( gallery.scrollLeft / gallery.clientWidth );

				if ( index !== current ) {
					current = index;
					update();
				}
			},
			{ passive: true }
		);

		controls.appendChild( prev );
		controls.appendChild( counter );
		controls.appendChild( next );
		wrap.appendChild( controls );

		update();
	} );
} )();
