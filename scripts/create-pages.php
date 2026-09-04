<?php
/**
 * Creates (or updates) the Contact and Bedankt pages.
 *
 * Run inside the wpcli container:
 *   docker compose cp scripts/create-pages.php wpcli:/tmp/create-pages.php
 *   docker compose exec -T wpcli wp eval-file /tmp/create-pages.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via wp eval-file.\n" );
}

$contact_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Heeft u vragen, of wilt u vrijblijvend een afspraak maken? Neem gerust contact op via e-mail of telefoon, of stuur een bericht via het formulier.</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<div class="wp-block-columns" style="margin-top:var(--wp--preset--spacing--40)">

	<!-- wp:column {"width":"40%"} -->
	<div class="wp-block-column" style="flex-basis:40%">
		<!-- wp:heading {"level":2,"fontSize":"large"} -->
		<h2 class="wp-block-heading has-large-font-size">Contactgegevens</h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau<br>Hofstedering 65<br>3763 XB Soest</p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"fontFamily":"mono","fontSize":"small"} -->
		<p class="has-mono-font-family has-small-font-size"><a href="tel:0613645507">06-13645507</a><br><a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph -->
		<p><a href="https://www.linkedin.com/in/ron-werkhoven-2594881a" rel="noopener" target="_blank">LinkedIn</a></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
		<p style="margin-top:var(--wp--preset--spacing--30)">Wilt u een review geven? <a href="https://g.page/r/Cf3-IzqPxu_8EAc" rel="noopener" target="_blank">Klik hier</a>.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"width":"60%"} -->
	<div class="wp-block-column" style="flex-basis:60%">
		<!-- wp:heading {"level":2,"fontSize":"large"} -->
		<h2 class="wp-block-heading has-large-font-size">Stuur een bericht</h2>
		<!-- /wp:heading -->

		<!-- wp:rwerkhoven/contact-form /-->
	</div>
	<!-- /wp:column -->

</div>
<!-- /wp:columns -->
HTML;

$bedankt_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Bedankt voor uw bericht. U ontvangt zo snel mogelijk een reactie.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p><a href="/">Terug naar de homepage</a></p>
<!-- /wp:paragraph -->
HTML;

$pages = array(
	'contact' => array( 'title' => 'Contact', 'content' => $contact_content ),
	'bedankt' => array( 'title' => 'Bedankt', 'content' => $bedankt_content ),
);

foreach ( $pages as $slug => $page ) {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );

	$postarr = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $page['title'],
		'post_name'    => $slug,
		'post_content' => $page['content'],
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id       = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		echo "FOUT bij {$slug}: " . $post_id->get_error_message() . "\n";
		continue;
	}

	echo "ok: /{$slug}/ (post {$post_id})\n";
}
