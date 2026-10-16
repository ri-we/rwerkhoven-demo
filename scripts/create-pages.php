<?php
/**
 * Creates (or updates) the site's pages: Contact, Bedankt, Diensten, Het bureau
 * and Werkwijze — including per-page SEO meta (rwerkhoven_seo_title /
 * rwerkhoven_seo_description, see inc/seo.php).
 *
 * All six services are described on the single Diensten page, one anchored
 * section each (/diensten/#bouwkundig-ontwerp etc.). Former child pages under
 * /diensten/ are deleted at the end of the run.
 *
 * Copy follows the tone-of-voice brief (2026-09-04): professional,
 * approachable, active voice, scannable, offer-oriented CTAs. Facts come
 * from the owner's existing site; permit texts follow the Omgevingswet
 * situation since 1-1-2024 (verification flagged in docs/content-questions.md).
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
<p style="margin-top:var(--wp--preset--spacing--40)">Benieuwd wat dit voor uw plannen betekent? <a href="/contact/">Vraag een vrijblijvende offerte aan</a> — u ontvangt snel een reactie.</p>
<!-- /wp:paragraph -->
HTML;

$contact_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Wilt u weten wat er mogelijk is met uw woning of bedrijfspand? Vertel kort iets over uw plannen — u krijgt snel een reactie en desgewenst een vrijblijvende offerte.</p>
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

		<!-- wp:paragraph {"fontSize":"small"} -->
		<p class="has-small-font-size"><a href="tel:0613645507">06-13645507</a><br><a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a></p>
		<!-- /wp:paragraph -->

		<!-- wp:social-links -->
		<ul class="wp-block-social-links">
			<!-- wp:social-link {"url":"https://www.linkedin.com/in/ron-werkhoven-2594881a","service":"linkedin","label":"LinkedIn"} /-->
		</ul>
		<!-- /wp:social-links -->

		<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
		<p style="margin-top:var(--wp--preset--spacing--30)">Benieuwd naar ervaringen van eerdere opdrachtgevers? <a href="https://g.page/r/Cf3-IzqPxu_8EAc" rel="noopener" target="_blank">Lees de reviews op Google</a> — of laat er zelf een achter.</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:column -->

	<!-- wp:column {"width":"60%"} -->
	<div class="wp-block-column" style="flex-basis:60%">
		<!-- wp:heading {"level":2,"fontSize":"large"} -->
		<h2 class="wp-block-heading has-large-font-size">Vraag een vrijblijvende offerte aan</h2>
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
<p>Bekijk in de tussentijd gerust de <a href="/projecten/">projecten</a>, of ga <a href="/">terug naar de homepage</a>.</p>
<!-- /wp:paragraph -->
HTML;

$diensten_intro = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Alles wat er nodig is om van een idee een gerealiseerd bouwplan te maken: ontwerp, tekenwerk en de complete vergunningaanvraag. Voor particulieren én zakelijke opdrachtgevers, altijd met één aanspreekpunt.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"rwerkhoven-service-index","textColor":"muted","fontSize":"small"} -->
<p class="rwerkhoven-service-index has-muted-color has-text-color has-small-font-size"><a href="#bouwkundig-ontwerp">Bouwkundig ontwerp</a> · <a href="#bouwkundige-tekeningen">Bouwkundige tekeningen</a> · <a href="#verbouwing-renovatie">Verbouwing &amp; renovatie</a> · <a href="#aanbouw-uitbouw">Aanbouw &amp; uitbouw</a> · <a href="#omgevingsvergunning">Omgevingsvergunning</a> · <a href="#bedrijfspanden">Bedrijfspanden</a></p>
<!-- /wp:paragraph -->
HTML;

$ontwerp_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Elk goed bouwplan begint op papier. Ron Werkhoven vertaalt uw wensen naar een ontwerp dat klopt: ruimtelijk aantrekkelijk, technisch doordacht en uitvoerbaar binnen de regels én uw budget.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Schetsontwerp: eerst verkennen, dan beslissen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Het begint met een schetsontwerp. In een of meer varianten wordt zichtbaar wat er kan met uw woning, kavel of pand — zodat u keuzes maakt op basis van beeld, niet op basis van aannames. Pas als de richting staat, wordt het ontwerp technisch uitgewerkt.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Wat u kunt verwachten</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>Schetsontwerp met varianten om de mogelijkheden te verkennen</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Ontwerp voor verbouw, aanbouw, uitbouw of nieuwbouw</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Herindeling van bestaande woningen en gebouwen</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Doorwerking tot vergunnings- en werktekeningen — alles in één hand</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
HTML;

$tekeningen_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Goede tekeningen zijn de taal van de bouw: de gemeente toetst erop, de aannemer bouwt ermee en u weet precies wat u krijgt. Ron Werkhoven levert tekenwerk dat nauwkeurig, compleet en direct bruikbaar is.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Bouwaanvraagtekeningen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Tekeningen voor de aanvraag van een omgevingsvergunning: van dakkapel, erker of woninguitbreiding tot herindeling van een bedrijfspand, gevelreclame, woningsplitsing of herbestemming.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Werk- en bestektekeningen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Gedetailleerde tekeningen op grote schaal, met volledige maatvoering en materiaalgegevens. Bij grotere of complexere projecten voorkomen goede werktekeningen discussie, vertraging en fouten in de uitvoering.</p>
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
<p>Van bestaande gebouwen worden de actuele maten opgenomen en digitaal uitgewerkt. Het detailniveau stemmen we vooraf af op het doel van de tekeningen — van globale plattegrond tot volledig gedetailleerd model.</p>
<!-- /wp:paragraph -->
HTML;

$verbouwing_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Bij een verbouwing of renovatie komt alles samen: de bestaande constructie, de nieuwe indeling, de regelgeving en de uitvoering. Ron Werkhoven begeleidt dat traject van eerste schets tot een plan waarmee de aannemer direct aan de slag kan.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Zo pakt het aan</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li><strong>Opname</strong> — de bestaande situatie wordt ingemeten en digitaal vastgelegd</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Ontwerp</strong> — de nieuwe indeling, in overleg met u aangescherpt</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Uitwerking</strong> — duidelijke tekeningen voor vergunning en uitvoering</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Vergunning</strong> — waar nodig wordt de omgevingsvergunning volledig verzorgd</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Voorbeelden van verbouwingen staan bij de <a href="/projecten/">projecten</a>.</p>
<!-- /wp:paragraph -->
HTML;

$aanbouw_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Meer ruimte zonder te verhuizen: een aanbouw, uitbouw, dakkapel, erker, garage of carport. Ron Werkhoven ontwerpt en tekent uitbreidingen die aanvoelen als een vanzelfsprekend deel van uw woning — geen aangeplakte doos.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Vergunningvrij of vergunningplichtig?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Niet elke uitbreiding heeft een vergunning nodig. Wat vergunningvrij kan en waarvoor een omgevingsvergunning vereist is, wordt vooraf voor u uitgezocht — zodat u niet halverwege voor verrassingen komt te staan.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Voorbeelden van aan- en uitbouwen staan bij de <a href="/projecten/">projecten</a>.</p>
<!-- /wp:paragraph -->
HTML;

$vergunning_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Voor veel bouw- en verbouwplannen is een omgevingsvergunning nodig. Ron Werkhoven verzorgt de complete aanvraag: de tekeningen, de bijbehorende stukken, de indiening via het Omgevingsloket en de afstemming met de gemeente. U hoeft er niet zelf achteraan.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Hoe werkt het sinds de Omgevingswet?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Sinds 1 januari 2024 geldt de Omgevingswet. Aanvragen verlopen via het digitale <a href="https://omgevingswet.overheid.nl/" rel="noopener" target="_blank">Omgevingsloket</a>, en een bouwplan wordt apart beoordeeld op het omgevingsplan van de gemeente en op de landelijke technische bouwregels. Voor bepaalde nieuwbouwprojecten geldt daarnaast de Wet kwaliteitsborging voor het bouwen, waarbij een onafhankelijke kwaliteitsborger de technische toets uitvoert.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Voor constructieberekeningen en energieprestatieberekeningen (BENG) wordt samengewerkt met vaste externe adviseurs — ook dat loopt via één aanspreekpunt.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Brandveilig gebruik</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Voor sommige gebouwen is voor het gebruik een aparte vergunning of melding nodig. Ook die stukken worden verzorgd:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li><strong>Omgevingsvergunning brandveilig gebruik</strong> — onder meer bij nachtverblijf voor meer dan tien personen (zoals hotels en zorggebouwen) of dagverblijf voor jonge kinderen of mensen met een beperking.</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Gebruiksmelding</strong> — bij gebouwen waar meer dan vijftig personen tegelijk verblijven, zoals scholen, kantoren, horeca en winkels.</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Ontruimingsplattegronden en vluchtplannen</strong> — per verdieping, met blusmiddelen, vluchtroutes en nooduitgangen.</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
HTML;

$bedrijfspanden_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Voor winkels, horeca en bedrijfsruimten telt maar één ding: dat de verbouwing snel, goed en volgens de regels gebeurt — elke week vertraging kost omzet. Ron Werkhoven verzorgt het bouwkundig tekenwerk en de bijbehorende aanvragen, van eenmalige verbouwing tot landelijke uitrol.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Wat wordt er verzorgd</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>Herindelingen, verbouwingen en herbestemmingen van bedrijfspanden</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Vergunningaanvragen, inclusief gevelreclame</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Stukken rond brandveilig gebruik: vergunning, gebruiksmelding en ontruimingsplattegronden</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Inmeten en digitaliseren van bestaande panden</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Voorbeelden staan bij de <a href="/projecten/">projecten</a>: van winkelketens tot de herbestemming van een bankgebouw.</p>
<!-- /wp:paragraph -->
HTML;

/**
 * The six services, in the order of patterns/service-list.php. The key is the
 * section anchor the home page's service list links to (/diensten/#anchor).
 */
$services = array(
	'bouwkundig-ontwerp'     => array( 'Bouwkundig ontwerp', $ontwerp_content ),
	'bouwkundige-tekeningen' => array( 'Bouwkundige tekeningen', $tekeningen_content ),
	'verbouwing-renovatie'   => array( 'Verbouwing &amp; renovatie', $verbouwing_content ),
	'aanbouw-uitbouw'        => array( 'Aanbouw &amp; uitbouw', $aanbouw_content ),
	'omgevingsvergunning'    => array( 'Omgevingsvergunning', $vergunning_content ),
	'bedrijfspanden'         => array( 'Bedrijfspanden', $bedrijfspanden_content ),
);

$diensten_content = $diensten_intro;

foreach ( $services as $anchor => $service ) {
	list( $title, $body ) = $service;

	// The texts were written as stand-alone pages: a large lead paragraph and
	// H2 sub-headings. As sections of one page they get a normal lead and H3s.
	$body = str_replace(
		array(
			"<!-- wp:paragraph {\"fontSize\":\"large\"} -->\n<p class=\"has-large-font-size\">",
			'<!-- wp:heading {"level":2,"fontSize":"large"} -->',
			'<h2 class="wp-block-heading has-large-font-size">',
			'</h2>',
		),
		array(
			"<!-- wp:paragraph -->\n<p>",
			'<!-- wp:heading {"level":3} -->',
			'<h3 class="wp-block-heading">',
			'</h3>',
		),
		$body
	);

	// Indent the section's blocks one level, so the generated markup nests the
	// way the hand-written block markup in this file does. Blank lines stay
	// blank; no paragraph text spans more than one line, so nothing inside a
	// <p> picks up a stray tab.
	$body = preg_replace( '/^(?=.)/m', "\t", $body );

	$diensten_content .= <<<HTML


<!-- wp:group {"className":"rwerkhoven-service-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group rwerkhoven-service-section">
	<!-- wp:heading {"level":2,"anchor":"{$anchor}"} -->
	<h2 class="wp-block-heading" id="{$anchor}">{$title}</h2>
	<!-- /wp:heading -->

{$body}
</div>
<!-- /wp:group -->
HTML;
}

$bureau_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Achter het bureau staat één man: Ron Werkhoven. Sinds 2010 verzorgt hij vanuit Soest bouwkundig ontwerp, tekenwerk en vergunningaanvragen — voor particulieren die willen verbouwen én voor aannemers, vastgoedbeheerders en architectenbureaus die op zijn tekenwerk bouwen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Eén aanspreekpunt, van schets tot oplevering</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Bij veel bouwtrajecten raken opdrachtgevers verstrikt tussen ontwerper, tekenaar, adviseurs en gemeente. Hier niet: u heeft van begin tot eind met dezelfde persoon te maken. Dat betekent korte lijnen, geen informatie die tussen partijen verloren gaat, en iemand die het hele plan kent — van het eerste schetsje tot het laatste detail op de werktekening.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Werkwijze in het kort</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>De werkzaamheden worden in nauw overleg met u uitgevoerd en afgestemd op uw wensen. De dienstverlening kenmerkt zich door creativiteit, snelheid en betrouwbaarheid: afspraken worden nagekomen en tekenwerk wordt op tijd geleverd. Hoe een project stap voor stap verloopt, leest u bij de <a href="/werkwijze/">werkwijze</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Voor wie</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li><strong>Particulieren</strong> — aanbouw, uitbouw, dakkapel, complete woningverbouwing of nieuwbouw</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Zakelijke opdrachtgevers</strong> — aannemers, vastgoedbeheerders, winkelketens en architecten- en adviesbureaus die technisch tekenwerk uitbesteden</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Wat opdrachtgevers zeggen</h2>
<!-- /wp:heading -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
	<!-- wp:paragraph -->
	<p>„Kan zeer zelfstandig en accuraat werken. Komt zijn afspraken na. Coop Supermarkten maakt al vele jaren gebruik van zijn diensten."</p>
	<!-- /wp:paragraph -->
	<cite>Gerard van Elswijk — Hoofd Bouwbureau, Coop Supermarkten</cite>
</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
	<!-- wp:paragraph -->
	<p>„Van vergunningen tot complete lay-outs: als u iemand zoekt voor tekenwerk, bent u bij Ron aan het goede adres."</p>
	<!-- /wp:paragraph -->
	<cite>Jeroen Vonck — JEZZ Projectmanagement</cite>
</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>Meer ervaringen leest u bij de <a href="https://www.linkedin.com/in/ron-werkhoven-2594881a" rel="noopener" target="_blank">aanbevelingen op LinkedIn</a> en de <a href="https://g.page/r/Cf3-IzqPxu_8EAc" rel="noopener" target="_blank">reviews op Google</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:social-links {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<ul class="wp-block-social-links" style="margin-top:var(--wp--preset--spacing--30)">
	<!-- wp:social-link {"url":"https://www.linkedin.com/in/ron-werkhoven-2594881a","service":"linkedin","label":"LinkedIn"} /-->
</ul>
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
<p style="margin-top:var(--wp--preset--spacing--40)">Afhankelijk van het project kunnen stappen samenvallen of vervallen — bij een klein plan is soms alleen tekenwerk nodig. Wat er in uw situatie nodig is, hoort u direct in het eerste gesprek, inclusief een heldere prijsopgave.</p>
<!-- /wp:paragraph -->
HTML;

// Order matters: parents before children.
$pages = array(
	'contact'                        => array(
		'title'    => 'Contact',
		'content'  => $contact_content,
		'cta'      => false,
		'seo_t'    => 'Contact & vrijblijvende offerte | Ron Werkhoven Soest',
		'seo_d'    => 'Neem contact op met Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau in Soest. Vraag een vrijblijvende offerte aan voor ontwerp, tekenwerk of een omgevingsvergunning.',
	),
	'bedankt'                        => array(
		'title'    => 'Bedankt',
		'content'  => $bedankt_content,
		'cta'      => false,
		'seo_t'    => '',
		'seo_d'    => '',
	),
	'diensten'                       => array(
		'title'    => 'Diensten',
		'content'  => $diensten_content,
		'cta'      => true,
		'seo_t'    => 'Diensten: ontwerp, bouwtekeningen & vergunning | Ron Werkhoven',
		'seo_d'    => 'Bouwkundig ontwerp, bouwtekeningen en omgevingsvergunningen — voor particulieren en bedrijven in Soest en regio Utrecht. Eén aanspreekpunt, van schets tot realisatie.',
	),
	'het-bureau'                     => array(
		'title'    => 'Het bureau',
		'content'  => $bureau_content,
		'cta'      => true,
		'seo_t'    => 'Het bureau: één aanspreekpunt voor uw bouwplan | Ron Werkhoven',
		'seo_d'    => 'Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau, sinds 2010 in Soest. Creatief, snel en betrouwbaar — met één aanspreekpunt van schets tot oplevering.',
	),
	'werkwijze'                      => array(
		'title'    => 'Werkwijze',
		'content'  => $werkwijze_content,
		'cta'      => true,
		'seo_t'    => 'Werkwijze: in vijf stappen van idee naar realisatie | Ron Werkhoven',
		'seo_d'    => 'Zo verloopt uw project: kennismaken, ontwerpen, uitwerken, vergunning en realisatie. Overzichtelijke stappen en heldere afspraken, van begin tot eind.',
	),
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

	if ( '' !== $page['seo_t'] ) {
		update_post_meta( $post_id, 'rwerkhoven_seo_title', $page['seo_t'] );
	}
	if ( '' !== $page['seo_d'] ) {
		update_post_meta( $post_id, 'rwerkhoven_seo_description', $page['seo_d'] );
	}

	echo "ok: /{$path}/ (post {$post_id})\n";
}

// The services used to be child pages of /diensten/; they are now sections on
// that page. Remove any that still exist so there are no duplicate URLs.
$diensten_page = get_page_by_path( 'diensten', OBJECT, 'page' );
if ( $diensten_page ) {
	$children = get_pages(
		array(
			'child_of'    => $diensten_page->ID,
			'post_status' => 'publish,draft,private,pending,future',
		)
	);
	foreach ( $children as $child ) {
		wp_delete_post( $child->ID, true );
		echo "verwijderd: /diensten/{$child->post_name}/ (post {$child->ID})\n";
	}
}
