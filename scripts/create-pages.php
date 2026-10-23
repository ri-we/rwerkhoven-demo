<?php
/**
 * Creates (or updates) the site's pages: Contact, Bedankt, Diensten, Het bureau
 * and Privacy - including per-page SEO meta (rwerkhoven_seo_title /
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
 *   docker compose exec -T wpcli wp eval 'require "/tmp/create-pages.php";'
 *
 * Not `wp eval-file`: since this file grew past ~25 KB, eval-file exits
 * silently without running it (WP-CLI 2.x in wordpress:cli-php8.3).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via wp eval-file.\n" );
}

$cta = <<<'HTML'
<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--40)">Benieuwd wat dit voor uw plannen betekent? <a href="/contact/">Vraag een vrijblijvende offerte aan</a> - u ontvangt snel een reactie.</p>
<!-- /wp:paragraph -->
HTML;

$contact_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Wilt u weten wat er mogelijk is met uw woning of bedrijfspand? Vertel kort iets over uw plannen - u krijgt snel een reactie en desgewenst een vrijblijvende offerte.</p>
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
		<p style="margin-top:var(--wp--preset--spacing--30)">Benieuwd naar ervaringen van eerdere opdrachtgevers? <a href="https://g.page/r/Cf3-IzqPxu_8EAc" rel="noopener" target="_blank">Lees de reviews op Google</a> - of laat er zelf een achter.</p>
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
<p class="has-large-font-size">Van eerste idee tot vergunning en uitvoering. Werkhoven Bouwkundig Ontwerp verzorgt ontwerp, tekenwerk en vergunningaanvragen voor particulieren en zakelijke opdrachtgevers. Altijd met één vast aanspreekpunt.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"rwerkhoven-service-index","textColor":"muted","fontSize":"small"} -->
<p class="rwerkhoven-service-index has-muted-color has-text-color has-small-font-size"><a href="#bouwkundig-ontwerp">Bouwkundig ontwerp</a> · <a href="#bouwkundige-tekeningen">Bouwkundige tekeningen</a> · <a href="#verbouwing-renovatie">Verbouwing &amp; renovatie</a> · <a href="#aanbouw-uitbouw">Aanbouw &amp; uitbouw</a> · <a href="#omgevingsvergunning">Omgevingsvergunning</a> · <a href="#bedrijfspanden">Bedrijfspanden</a></p>
<!-- /wp:paragraph -->
HTML;

/**
 * Builds a core List block; $ordered gives a numbered list.
 */
function rwerkhoven_list_block( array $items, $ordered = false, $class = '' ) {
	$tag   = $ordered ? 'ol' : 'ul';
	$attrs = array();
	if ( $ordered ) {
		$attrs['ordered'] = true;
	}
	if ( $class ) {
		$attrs['className'] = $class;
	}
	$json = $attrs ? ' ' . wp_json_encode( $attrs ) : '';
	$cls  = trim( 'wp-block-list ' . $class );

	$out = "<!-- wp:list{$json} -->\n<{$tag} class=\"{$cls}\">\n";
	foreach ( $items as $i => $item ) {
		$out .= ( $i ? "\n" : '' ) . "\t<!-- wp:list-item -->\n\t<li>{$item}</li>\n\t<!-- /wp:list-item -->\n";
	}
	return $out . "</{$tag}>\n<!-- /wp:list -->";
}

/**
 * Builds a core Paragraph block; $label styles it as the lead-in above a list.
 */
function rwerkhoven_paragraph_block( $text, $label = false ) {
	if ( $label ) {
		return "<!-- wp:paragraph {\"className\":\"rwerkhoven-list-label\"} -->\n<p class=\"rwerkhoven-list-label\">{$text}</p>\n<!-- /wp:paragraph -->";
	}
	return "<!-- wp:paragraph -->\n<p>{$text}</p>\n<!-- /wp:paragraph -->";
}

/**
 * The six services, in the order of patterns/service-list.php. The key is the
 * section anchor the home page's service list links to (/diensten/#anchor).
 * Text supplied by the owner (2026-10-23), supplemented with details from the
 * service descriptions on the original rwerkhoven.nl.
 */
$services = array(
	'bouwkundig-ontwerp'     => array(
		'Bouwkundig ontwerp',
		array(
			rwerkhoven_paragraph_block( 'Een goed bouwplan begint met een doordacht ontwerp. Uw wensen worden vertaald naar een praktisch, haalbaar en aantrekkelijk ontwerp dat past binnen de regelgeving en uw budget.' ),
			rwerkhoven_paragraph_block( 'Onder andere voor:', true ),
			rwerkhoven_list_block( array( 'Aan- en uitbouwen', 'Dakkapellen', 'Woningverbouwingen', 'Nieuwbouw', 'Herindeling van woningen en gebouwen' ) ),
		),
	),
	'bouwkundige-tekeningen' => array(
		'Bouwkundige tekeningen',
		array(
			rwerkhoven_paragraph_block( 'Duidelijke en nauwkeurige tekeningen vormen de basis van ieder bouwproject. Ze zijn geschikt voor de vergunningaanvraag, de uitvoering en de technische uitwerking.' ),
			rwerkhoven_paragraph_block( 'Mogelijkheden:', true ),
			rwerkhoven_list_block( array( 'Vergunningstekeningen', 'Werk- en bestektekeningen', 'Splitsingstekeningen', 'Ontruimingsplattegronden en vluchtplannen', 'Inmeten en digitaliseren van bestaande gebouwen' ) ),
			rwerkhoven_paragraph_block( 'Zijn er alleen oude, papieren tekeningen van een gebouw? Die worden digitaal nagetekend, zodat ze weer bruikbaar zijn voor een verbouwing of aanvraag.' ),
		),
	),
	'verbouwing-renovatie'   => array(
		'Verbouwing &amp; renovatie',
		array(
			rwerkhoven_paragraph_block( 'Van een nieuwe indeling tot een complete renovatie. Het hele traject wordt verzorgd, van de opname van de bestaande situatie tot de technische uitwerking.' ),
			rwerkhoven_paragraph_block( 'Werkwijze:', true ),
			rwerkhoven_list_block( array( 'Opnemen van de bestaande situatie', 'Ontwerp uitwerken', 'Technische tekeningen opstellen', 'Vergunning aanvragen, indien nodig' ), true ),
		),
	),
	'aanbouw-uitbouw'        => array(
		'Aanbouw &amp; uitbouw',
		array(
			rwerkhoven_paragraph_block( 'Meer ruimte zonder te verhuizen. Van dakkapel en erker tot een complete aanbouw of uitbouw.' ),
			rwerkhoven_paragraph_block( 'Voorbeelden:', true ),
			rwerkhoven_list_block( array( 'Aanbouw', 'Uitbouw', 'Dakkapel', 'Garage', 'Carport', 'Erker' ) ),
			rwerkhoven_paragraph_block( 'Vooraf wordt beoordeeld of er een vergunning nodig is en welke mogelijkheden er zijn.' ),
		),
	),
	'omgevingsvergunning'    => array(
		'Omgevingsvergunning',
		array(
			rwerkhoven_paragraph_block( 'Geen zorgen over procedures en regelgeving. De complete vergunningaanvraag wordt verzorgd, inclusief tekenwerk, benodigde documenten en indiening via het Omgevingsloket.' ),
			rwerkhoven_paragraph_block( 'Begeleiding bij:', true ),
			rwerkhoven_list_block( array( 'Vergunningaanvragen', 'Vooroverleg met gemeenten', 'Brandveiligheidsaanvragen', 'Gebruiksmeldingen', 'Afstemming met adviseurs' ) ),
			rwerkhoven_paragraph_block( 'Voor constructie- en energieprestatieberekeningen wordt samengewerkt met vaste externe adviseurs. Hun offertes worden voor u opgevraagd, zodat u ook daarvoor één aanspreekpunt houdt.' ),
		),
	),
	'bedrijfspanden'         => array(
		'Bedrijfspanden',
		array(
			rwerkhoven_paragraph_block( 'Voor winkels, kantoren, horeca en bedrijfsruimten. Praktische oplossingen waarbij snelheid, continuïteit en regelgeving centraal staan.' ),
			rwerkhoven_paragraph_block( 'Diensten voor bedrijven:', true ),
			rwerkhoven_list_block( array( 'Verbouwingen en herindelingen', 'Herbestemmingen', 'Vergunningaanvragen', 'Gevelreclame', 'Brandveiligheidsdocumentatie', 'Inmeten en digitaliseren' ) ),
			rwerkhoven_paragraph_block( 'Voorbeelden staan bij de <a href="/projecten/">projecten</a>: van winkelketens tot de herbestemming van een bankgebouw.' ),
		),
	),
);

$diensten_content = $diensten_intro;

foreach ( $services as $anchor => $service ) {
	list( $title, $blocks ) = $service;

	// Indent the section's blocks one level, so the generated markup nests the
	// way the hand-written block markup in this file does.
	$body = preg_replace( '/^(?=.)/m', "\t", implode( "\n\n", $blocks ) );

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

$diensten_why = rwerkhoven_list_block(
	array( 'Eén vast aanspreekpunt', 'Ruim 15 jaar ervaring', 'Ontwerp, tekenwerk en vergunningen onder één dak', 'Korte lijnen en heldere communicatie', 'Voor particulieren én zakelijke opdrachtgevers' ),
	false,
	'rwerkhoven-checklist'
);
$diensten_why = preg_replace( '/^(?=.)/m', "\t", $diensten_why );

$diensten_content .= <<<HTML


<!-- wp:group {"className":"rwerkhoven-service-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group rwerkhoven-service-section">
	<!-- wp:heading {"level":2,"anchor":"waarom"} -->
	<h2 class="wp-block-heading" id="waarom">Waarom kiezen voor Werkhoven Bouwkundig Ontwerp?</h2>
	<!-- /wp:heading -->

{$diensten_why}
</div>
<!-- /wp:group -->

<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} -->
<p style="margin-top:var(--wp--preset--spacing--40)">Benieuwd wat er mogelijk is voor uw project? <a href="/contact/">Neem vrijblijvend contact op</a> voor advies of een offerte.</p>
<!-- /wp:paragraph -->
HTML;

$bureau_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Achter het bureau staat één man: Ron Werkhoven. Sinds 2010 verzorgt hij vanuit Soest bouwkundig ontwerp, tekenwerk en vergunningaanvragen - voor particulieren die willen verbouwen én voor aannemers, vastgoedbeheerders en architectenbureaus die op zijn tekenwerk bouwen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Eén aanspreekpunt, van schets tot oplevering</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Bij veel bouwtrajecten raken opdrachtgevers verstrikt tussen ontwerper, tekenaar, adviseurs en gemeente. Hier niet: u heeft van begin tot eind met dezelfde persoon te maken. Dat betekent korte lijnen, geen informatie die tussen partijen verloren gaat, en iemand die het hele plan kent - van het eerste schetsje tot het laatste detail op de werktekening.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Creatief, snel en betrouwbaar</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>De werkzaamheden worden in nauw overleg met u uitgevoerd en afgestemd op uw wensen. De dienstverlening kenmerkt zich door creativiteit, snelheid en betrouwbaarheid: afspraken worden nagekomen en tekenwerk wordt op tijd geleverd.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Voor wie</h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"rwerkhoven-plain-list"} -->
<ul class="wp-block-list rwerkhoven-plain-list">
	<!-- wp:list-item -->
	<li>Particulieren - aanbouw, uitbouw, dakkapel, complete woningverbouwing of nieuwbouw</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Zakelijke opdrachtgevers - aannemers, vastgoedbeheerders, winkelketens en architecten- en adviesbureaus die technisch tekenwerk uitbesteden</li>
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
	<cite>Gerard van Elswijk - Hoofd Bouwbureau, Coop Supermarkten</cite>
</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
	<!-- wp:paragraph -->
	<p>„Van vergunningen tot complete lay-outs: als u iemand zoekt voor tekenwerk, bent u bij Ron aan het goede adres."</p>
	<!-- /wp:paragraph -->
	<cite>Jeroen Vonck - JEZZ Projectmanagement</cite>
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

$privacy_content = <<<'HTML'
<!-- wp:paragraph {"textColor":"muted","fontSize":"small"} -->
<p class="has-muted-color has-text-color has-small-font-size">Laatst bijgewerkt: 23 oktober 2026</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau gaat zorgvuldig om met uw persoonsgegevens. In deze privacyverklaring leest u welke gegevens worden verwerkt, waarom, hoe lang ze worden bewaard en welke rechten u heeft.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Wie is verantwoordelijk?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau, Hofstedering 65, 3763 XB Soest, is verantwoordelijk voor de verwerking van persoonsgegevens zoals beschreven in deze verklaring. Voor vragen over privacy kunt u terecht bij Ron Werkhoven: <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a> of <a href="tel:0613645507">06-13645507</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Welke persoonsgegevens worden verwerkt?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Persoonsgegevens worden verwerkt doordat u contact opneemt, een offerte aanvraagt of een opdracht geeft. Het gaat om gegevens die u zelf verstrekt:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>naam;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>e-mailadres en telefoonnummer;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>uw adres en het adres van het project;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>gegevens over uw woning of pand die nodig zijn voor ontwerp, tekenwerk of vergunningaanvraag, zoals bestaande tekeningen, maten en foto's;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>bij zakelijke opdrachtgevers: bedrijfsnaam, contactpersoon en KvK-nummer;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>factuur- en betalingsgegevens;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>de inhoud van uw berichten, per e-mail, telefoon of het contactformulier.</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Er worden geen bijzondere persoonsgegevens verwerkt, zoals gegevens over gezondheid, en ook geen burgerservicenummer (BSN). De dienstverlening is niet gericht op personen jonger dan 16 jaar. Denkt u dat er zonder toestemming van een ouder of voogd gegevens van een minderjarige zijn verwerkt? Neem dan contact op; de gegevens worden dan verwijderd.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Waarvoor en op welke grondslag?</h2>
<!-- /wp:heading -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li><strong>Uw vraag of offerteaanvraag beantwoorden.</strong> Grondslag: uw verzoek, voorafgaand aan een mogelijke overeenkomst.</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>De opdracht uitvoeren:</strong> ontwerp, tekenwerk, inmeten en het voorbereiden en indienen van vergunningaanvragen en meldingen. Grondslag: uitvoering van de overeenkomst.</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Facturen versturen en de administratie bijhouden.</strong> Grondslag: wettelijke verplichting, waaronder de fiscale bewaarplicht.</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:paragraph -->
<p>Er worden geen besluiten genomen die uitsluitend gebaseerd zijn op geautomatiseerde verwerking, zoals profilering.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Hoe lang worden gegevens bewaard?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Gegevens worden niet langer bewaard dan nodig is voor het doel waarvoor ze zijn verzameld:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li><strong>Vragen en offerteaanvragen</strong> die niet tot een opdracht leiden: tot één jaar na het laatste contact.</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Projectdossiers</strong> (tekeningen en correspondentie): tot zeven jaar na afronding van de opdracht, zodat vervolgvragen over het ontwerp of de vergunning beantwoord kunnen worden.</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li><strong>Facturen en administratie:</strong> zeven jaar, op grond van de wettelijke bewaarplicht.</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Met wie worden gegevens gedeeld?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Uw gegevens worden niet verkocht. Ze worden alleen gedeeld als dat nodig is voor de uitvoering van de opdracht of om aan een wettelijke verplichting te voldoen:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>met de gemeente of een ander bevoegd gezag, bij het indienen van een aanvraag of melding via het Omgevingsloket;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>met externe adviseurs, zoals een constructeur of energieadviseur, als hun inbreng voor uw project nodig is en na overleg met u;</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>met dienstverleners die het bureau ondersteunen, zoals de hosting van de website en e-mail en de boekhouding. Zij verwerken de gegevens alleen in opdracht en volgens afspraken in een verwerkersovereenkomst.</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Website, cookies en contactformulier</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Deze website gebruikt geen cookies, geen analyse- of advertentiediensten en geen andere trackingtechnieken. Lettertypen en afbeeldingen worden vanaf de eigen server geladen, zodat bij een bezoek geen gegevens naar andere partijen gaan.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Wat u via het contactformulier verstuurt, gaat per e-mail naar <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a> en wordt niet op de website opgeslagen. Zoals bij elke website kan de hostingpartij technische gegevens, zoals uw IP-adres, korte tijd in serverlogboeken bewaren om storingen en misbruik te kunnen onderzoeken.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>De website bevat links naar LinkedIn en Google. Als u die volgt, gelden de privacyregels van die partijen.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Uw rechten</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>U heeft het recht om uw persoonsgegevens in te zien, te laten corrigeren of te laten verwijderen. Ook kunt u vragen de verwerking te beperken, bezwaar maken tegen de verwerking, een gegeven toestemming intrekken en vragen om uw gegevens in een computerbestand aan u of een andere organisatie over te dragen.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Stuur uw verzoek naar <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a>. U ontvangt binnen een maand een reactie. Als het nodig is om vast te stellen dat het verzoek echt van u komt, wordt u om aanvullende informatie gevraagd; stuur niet uit uzelf een kopie van uw identiteitsbewijs mee.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Bent u niet tevreden over de manier waarop met uw gegevens wordt omgegaan? Laat het weten, dan wordt er samen naar een oplossing gezocht. U heeft daarnaast altijd het recht een klacht in te dienen bij de <a href="https://www.autoriteitpersoonsgegevens.nl/een-tip-of-klacht-indienen-bij-de-ap" rel="noopener" target="_blank">Autoriteit Persoonsgegevens</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Beveiliging</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Er worden passende technische en organisatorische maatregelen genomen om misbruik, verlies, onbevoegde toegang, ongewenste openbaarmaking en ongeoorloofde wijziging van uw gegevens te voorkomen. De website gebruikt een beveiligde verbinding (https). Heeft u de indruk dat uw gegevens niet goed beveiligd zijn of dat er sprake is van misbruik? Neem dan direct contact op via <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Wijzigingen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Deze privacyverklaring kan worden aangepast, bijvoorbeeld als de dienstverlening of de wet verandert. De datum bovenaan laat zien wanneer de verklaring voor het laatst is bijgewerkt.</p>
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
		'cta'      => false,
		'seo_t'    => 'Diensten: ontwerp, bouwtekeningen & vergunning | Ron Werkhoven',
		'seo_d'    => 'Bouwkundig ontwerp, bouwtekeningen en omgevingsvergunningen - voor particulieren en bedrijven in Soest en regio Utrecht. Eén aanspreekpunt, van schets tot realisatie.',
	),
	'het-bureau'                     => array(
		'title'    => 'Het bureau',
		'content'  => $bureau_content,
		'cta'      => true,
		'seo_t'    => 'Het bureau: één aanspreekpunt voor uw bouwplan | Ron Werkhoven',
		'seo_d'    => 'Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau, sinds 2010 in Soest. Creatief, snel en betrouwbaar - met één aanspreekpunt van schets tot oplevering.',
	),
	'privacy'                        => array(
		'title'    => 'Privacyverklaring',
		'content'  => $privacy_content,
		'cta'      => false,
		'seo_t'    => 'Privacyverklaring | Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau',
		'seo_d'    => 'Hoe Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau omgaat met uw persoonsgegevens: welke gegevens, waarvoor, hoe lang en wat uw rechten zijn.',
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

// The Werkwijze page was removed from the site (2026-10-23).
$werkwijze_page = get_page_by_path( 'werkwijze', OBJECT, 'page' );
if ( $werkwijze_page ) {
	wp_delete_post( $werkwijze_page->ID, true );
	echo "verwijderd: /werkwijze/ (post {$werkwijze_page->ID})\n";
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
