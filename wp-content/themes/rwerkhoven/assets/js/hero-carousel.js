/**
 * Homepage hero carousel: cross-fades between the slides rendered by
 * inc/hero-carousel.php and adds previous/next buttons and dots.
 *
 * Autoplay pauses while the pointer or keyboard focus is on the carousel and
 * stays off for visitors who prefer reduced motion. Without JavaScript the
 * first photo simply stays put.
 */
( function () {
	'use strict';

	var INTERVAL = 6000;

	var carousel = document.querySelector( '.rwerkhoven-hero-carousel' );
	if ( ! carousel ) {
		return;
	}

	var slides = carousel.querySelectorAll( '.rwerkhoven-hero-slide' );
	if ( slides.length < 2 ) {
		return;
	}

	var current = 0;
	var timer = null;
	var paused = false;
	var motionOk = window.matchMedia( '(prefers-reduced-motion: no-preference)' ).matches;

	var controls = document.createElement( 'div' );
	controls.className = 'rwerkhoven-hero-controls';

	function button( label, text ) {
		var b = document.createElement( 'button' );
		b.type = 'button';
		b.className = 'rwerkhoven-hero-btn';
		b.setAttribute( 'aria-label', label );
		b.textContent = text;
		return b;
	}

	var prev = button( 'Vorige foto', '←' );
	var next = button( 'Volgende foto', '→' );

	var dots = document.createElement( 'div' );
	dots.className = 'rwerkhoven-hero-dots';

	Array.prototype.forEach.call( slides, function ( slide, index ) {
		var dot = button( 'Foto ' + ( index + 1 ) + ' van ' + slides.length, '' );
		dot.className = 'rwerkhoven-hero-dot';
		dot.addEventListener( 'click', function () {
			goTo( index );
			restart();
		} );
		dots.appendChild( dot );

		// The lazy-loaded slides sit behind the first one; load them now so
		// the first fade doesn't reveal a half-loaded image.
		var img = slide.querySelector( 'img' );
		if ( img && index > 0 ) {
			img.loading = 'eager';
		}
	} );

	function goTo( index ) {
		slides[ current ].classList.remove( 'is-active' );
		dots.children[ current ].removeAttribute( 'aria-current' );

		current = ( index + slides.length ) % slides.length;

		slides[ current ].classList.add( 'is-active' );
		dots.children[ current ].setAttribute( 'aria-current', 'true' );
	}

	function restart() {
		window.clearInterval( timer );
		timer = null;
		if ( motionOk && ! paused ) {
			timer = window.setInterval( function () {
				goTo( current + 1 );
			}, INTERVAL );
		}
	}

	function pause( state ) {
		paused = state;
		restart();
	}

	prev.addEventListener( 'click', function () {
		goTo( current - 1 );
		restart();
	} );

	next.addEventListener( 'click', function () {
		goTo( current + 1 );
		restart();
	} );

	carousel.addEventListener( 'mouseenter', function () {
		pause( true );
	} );
	carousel.addEventListener( 'mouseleave', function () {
		pause( false );
	} );
	carousel.addEventListener( 'focusin', function () {
		pause( true );
	} );
	carousel.addEventListener( 'focusout', function () {
		pause( false );
	} );

	controls.appendChild( prev );
	controls.appendChild( dots );
	controls.appendChild( next );
	carousel.appendChild( controls );

	goTo( 0 );
	restart();
} )();
