<?php
/**
 * Creates (or updates) the site's pages: Contact, Bedankt, Diensten (+ six
 * child pages), Het bureau and Werkwijze.
 *
 * Texts are based on the original site's content, edited for clarity and
 * brought up to date (the permit pages now describe the situation under the
 * Omgevingswet, in force since 1 January 2024 — flagged in
 * docs/content-questions.md for the owner to verify).
 *
 * Run inside the wpcli container:
 *   docker compose cp scripts/create-pages.php wpcli:/tmp/create-pages.php
 *   docker compose exec -T wpcli wp eval-file /tmp/create-pages.php
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via wp eval-file.\n" );
}

$cta = <<<'HTML'
<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--40)">Heeft u bouw- of verbouwplannen? <a href="/contact/">Neem vrijblijvend contact op</a>.</p>
<!-- /wp:paragraph -->
HTML;

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

		<!-- wp:social-links -->
		<ul class="wp-block-social-links"><!-- wp:social-link {"url":"https://www.linkedin.com/in/ron-werkhoven-2594881a","service":"linkedin","label":"LinkedIn"} /--></ul>
		<!-- /wp:social-links -->

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

$diensten_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Ron Werkhoven verzorgt bouwkundig ontwerp, tekenwerk en vergunningaanvragen — van eerste schets tot en met de uitvoering. Hieronder staat per dienst wat u kunt verwachten.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--40)">
	<!-- wp:pattern {"slug":"rwerkhoven/service-list"} /-->
</div>
<!-- /wp:group -->
HTML;

$ontwerp_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Elk bouwplan begint met een ontwerp dat klopt: ruimtelijk, technisch en binnen de regels. Ron Werkhoven vertaalt uw wensen naar een concreet bouwkundig ontwerp voor verbouw, uitbreiding of nieuwbouw.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Het ontwerp wordt in nauw overleg met u gemaakt en stap voor stap aangescherpt, tot er een plan ligt dat uitvoerbaar is en past bij de bestaande situatie.</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li>Schetsontwerp en varianten om de mogelijkheden te verkennen</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Ontwerp voor verbouw, aanbouw, uitbouw of nieuwbouw</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Herindeling van bestaande woningen en gebouwen</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li>Uitwerking tot vergunnings- en werktekeningen</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
HTML;

$tekeningen_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Heldere, nauwkeurige tekeningen vormen de basis van elk bouwproject: voor de vergunningaanvraag, het overleg met aannemer en adviseurs, en de uitvoering op de bouwplaats.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Bouwaanvraagtekeningen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Tekeningen voor de aanvraag van een omgevingsvergunning: van dakkapel, erker of woninguitbreiding tot herindeling van een bedrijfspand, gevelreclame, woningsplitsing of herbestemming.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Werktekeningen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Gedetailleerde tekeningen op grote schaal, met volledige maatvoering. Bij grotere of complexere projecten voorkomen goede werktekeningen vertraging en fouten in de uitvoering.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Splitsingstekeningen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Voor de kadastrale splitsing van een gebouw met meerdere koopwoningen. Op de splitsingstekening zijn de grenzen tussen de appartementen en de gemeenschappelijke ruimten exact aangegeven.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Inmeten en digitaliseren</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Bestaande gebouwen worden ingemeten en digitaal uitgewerkt naar de actuele situatie. Het detail- en materialisatieniveau bepalen we samen, op basis van het doel van de tekeningen.</p>
<!-- /wp:paragraph -->
HTML;

$verbouwing_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Bij een verbouwing of renovatie komt veel samen: de bestaande constructie, de nieuwe indeling, de regelgeving en de uitvoering. Ron Werkhoven begeleidt dit van eerste schets tot een uitvoeringsgereed plan.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Het begint met een goede opname: de bestaande situatie wordt ingemeten en digitaal vastgelegd. Daarna volgt het ontwerp voor de nieuwe situatie, uitgewerkt in duidelijke tekeningen — en waar nodig de aanvraag van de omgevingsvergunning.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Voorbeelden van verbouwingen staan bij de <a href="/projecten/">projecten</a>.</p>
<!-- /wp:paragraph -->
HTML;

$aanbouw_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Meer ruimte in of om uw woning: een aanbouw, uitbouw, dakkapel, erker, garage of carport. Ron Werkhoven ontwerpt en tekent uitbreidingen die passen bij de bestaande woning.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Niet elke uitbreiding is vergunningplichtig. Wat vergunningvrij kan en waarvoor een omgevingsvergunning nodig is, wordt vooraf voor u uitgezocht — zodat u niet voor verrassingen komt te staan.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Voorbeelden van aan- en uitbouwen staan bij de <a href="/projecten/">projecten</a>.</p>
<!-- /wp:paragraph -->
HTML;

$vergunning_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Voor veel bouw- en verbouwplannen is een omgevingsvergunning nodig. Ron Werkhoven maakt de benodigde tekeningen en stukken en verzorgt de volledige aanvraag, inclusief de afstemming met de gemeente.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Sinds 1 januari 2024 geldt de Omgevingswet. Aanvragen verlopen via het digitale Omgevingsloket, en een bouwplan wordt apart beoordeeld op het omgevingsplan van de gemeente en op de landelijke technische bouwregels. Voor bepaalde nieuwbouwprojecten geldt daarnaast de Wet kwaliteitsborging voor het bouwen, waarbij een onafhankelijke kwaliteitsborger de technische toets uitvoert.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Voor constructieberekeningen en energieprestatieberekeningen (BENG) wordt samengewerkt met externe adviseurs.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Brandveilig gebruik</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Voor sommige gebouwen is voor het gebruik een aparte vergunning of melding nodig. Ook die stukken worden verzorgd:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list"><!-- wp:list-item -->
<li><strong>Omgevingsvergunning brandveilig gebruik</strong> — onder meer bij nachtverblijf voor meer dan tien personen (zoals hotels en zorggebouwen) of dagverblijf voor jonge kinderen of mensen met een beperking.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Gebruiksmelding</strong> — bij gebouwen waar meer dan vijftig personen tegelijk verblijven, zoals scholen, kantoren, horeca en winkels.</li>
<!-- /wp:list-item -->

<!-- wp:list-item -->
<li><strong>Ontruimingsplattegronden en vluchtplannen</strong> — per verdieping, met blusmiddelen, vluchtroutes en nooduitgangen.</li>
<!-- /wp:list-item --></ul>
<!-- /wp:list -->
HTML;

$bedrijfspanden_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Voor winkels, horeca en bedrijfsruimten verzorgt Ron Werkhoven het bouwkundig tekenwerk en de bijbehorende aanvragen: herindelingen, verbouwingen, herbestemmingen en gevelreclame.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Ook de stukken rond brandveilig gebruik — de vergunning, de gebruiksmelding en ontruimingsplattegronden — worden verzorgd.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Voorbeelden staan bij de <a href="/projecten/">projecten</a>: van winkelketens tot de herbestemming van een bankgebouw.</p>
<!-- /wp:paragraph -->
HTML;

$bureau_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau is in 2010 opgericht en gevestigd in Soest.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Het bureau verzorgt bouwkundig ontwerp, tekenwerk en omgevingsvergunningaanvragen voor particulieren, bedrijven, aannemers, vastgoedbeheerders en architecten- en adviesbureaus. Het werk loopt uiteen van verbouw, uitbreiding en herinrichting van woningen tot winkels en bedrijfsruimten.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>De werkzaamheden worden in nauw overleg met de opdrachtgever uitgevoerd en afgestemd op diens wensen. De dienstverlening kenmerkt zich door creativiteit, snelheid en betrouwbaarheid — en van ontwerp tot realisatie heeft u één aanspreekpunt.</p>
<!-- /wp:paragraph -->

<!-- wp:social-links {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<ul class="wp-block-social-links" style="margin-top:var(--wp--preset--spacing--30)"><!-- wp:social-link {"url":"https://www.linkedin.com/in/ron-werkhoven-2594881a","service":"linkedin","label":"LinkedIn"} /--></ul>
<!-- /wp:social-links -->
HTML;

$werkwijze_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Elk project verloopt in overzichtelijke stappen. Zo weet u vooraf waar u aan toe bent en ziet u steeds waar het project staat.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="margin-top:var(--wp--preset--spacing--40)">
	<!-- wp:pattern {"slug":"rwerkhoven/process-steps"} /-->
</div>
<!-- /wp:group -->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--40)">Afhankelijk van het project kunnen stappen samenvallen of vervallen — bij een klein plan is soms alleen tekenwerk nodig. Wat er in uw situatie nodig is, bespreken we in het eerste gesprek.</p>
<!-- /wp:paragraph -->
HTML;

// Order matters: parents before children.
$pages = array(
	'contact'                        => array( 'title' => 'Contact', 'content' => $contact_content, 'cta' => false ),
	'bedankt'                        => array( 'title' => 'Bedankt', 'content' => $bedankt_content, 'cta' => false ),
	'diensten'                       => array( 'title' => 'Diensten', 'content' => $diensten_content, 'cta' => true ),
	'diensten/bouwkundig-ontwerp'    => array( 'title' => 'Bouwkundig ontwerp', 'content' => $ontwerp_content, 'cta' => true ),
	'diensten/bouwkundige-tekeningen' => array( 'title' => 'Bouwkundige tekeningen', 'content' => $tekeningen_content, 'cta' => true ),
	'diensten/verbouwing-renovatie'  => array( 'title' => 'Verbouwing & renovatie', 'content' => $verbouwing_content, 'cta' => true ),
	'diensten/aanbouw-uitbouw'       => array( 'title' => 'Aanbouw & uitbouw', 'content' => $aanbouw_content, 'cta' => true ),
	'diensten/omgevingsvergunning'   => array( 'title' => 'Omgevingsvergunning', 'content' => $vergunning_content, 'cta' => true ),
	'diensten/bedrijfspanden'        => array( 'title' => 'Bedrijfspanden', 'content' => $bedrijfspanden_content, 'cta' => true ),
	'het-bureau'                     => array( 'title' => 'Het bureau', 'content' => $bureau_content, 'cta' => true ),
	'werkwijze'                      => array( 'title' => 'Werkwijze', 'content' => $werkwijze_content, 'cta' => true ),
);

foreach ( $pages as $path => $page ) {
	$parts     = explode( '/', $path );
	$slug      = end( $parts );
	$parent_id = 0;

	if ( count( $parts ) > 1 ) {
		$parent = get_page_by_path( $parts[0], OBJECT, 'page' );
		if ( ! $parent ) {
			echo "FOUT: ouderpagina '{$parts[0]}' ontbreekt voor {$path}\n";
			continue;
		}
		$parent_id = $parent->ID;
	}

	$content = $page['content'];
	if ( $page['cta'] ) {
		$content .= "\n\n" . $cta;
	}

	$existing = get_page_by_path( $path, OBJECT, 'page' );

	$postarr = array(
		'post_type'    => 'page',
		'post_status'  => 'publish',
		'post_title'   => $page['title'],
		'post_name'    => $slug,
		'post_parent'  => $parent_id,
		'post_content' => $content,
	);

	if ( $existing ) {
		$postarr['ID'] = $existing->ID;
		$post_id       = wp_update_post( $postarr, true );
	} else {
		$post_id = wp_insert_post( $postarr, true );
	}

	if ( is_wp_error( $post_id ) ) {
		echo "FOUT bij {$path}: " . $post_id->get_error_message() . "\n";
		continue;
	}

	echo "ok: /{$path}/ (post {$post_id})\n";
}
