<?php
/**
 * Creates (or updates) the site's pages: Contact, Bedankt, Diensten, Over Ron Werkhoven
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

		<!-- wp:social-links {"openInNewTab":true} -->
		<ul class="wp-block-social-links">
			<!-- wp:social-link {"url":"https://www.linkedin.com/in/ron-werkhoven-2594881a","service":"linkedin","label":"LinkedIn"} /-->
			<!-- wp:social-link {"url":"https://g.page/r/Cf3-IzqPxu_8EAc","service":"google","label":"Reviews op Google"} /-->
		</ul>
		<!-- /wp:social-links -->

		<!-- wp:paragraph {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
		<p style="margin-top:var(--wp--preset--spacing--30)">Benieuwd naar ervaringen van eerdere opdrachtgevers? <a href="https://g.page/r/Cf3-IzqPxu_8EAc" rel="noopener" target="_blank">Lees de reviews op Google</a> of <a href="https://g.page/r/Cf3-IzqPxu_8EAc/review" rel="noopener" target="_blank">laat er zelf een achter</a>.</p>
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
<p class="has-large-font-size">Van eerste idee tot vergunning en uitvoering. Ron Werkhoven Bouwkundig Ontwerp verzorgt ontwerp, tekenwerk en vergunningaanvragen voor particulieren en zakelijke opdrachtgevers. Altijd met één vast aanspreekpunt.</p>
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
 * Text as supplied by the owner in "Ik zou.docx" (2026-11-06).
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
			rwerkhoven_paragraph_block( 'Duidelijke en nauwkeurige tekeningen vormen de basis van ieder bouwproject. Geschikt voor vergunningaanvragen, uitvoering en technische uitwerking.' ),
			rwerkhoven_paragraph_block( 'Mogelijkheden:', true ),
			rwerkhoven_list_block( array( 'Vergunningstekeningen', 'Werk- en bestektekeningen', 'Splitsingstekeningen', 'Inmeten en digitaliseren van bestaande gebouwen' ) ),
		),
	),
	'verbouwing-renovatie'   => array(
		'Verbouwing &amp; renovatie',
		array(
			rwerkhoven_paragraph_block( 'Van een nieuwe indeling tot een complete renovatie. Het gehele traject wordt verzorgd, van opname van de bestaande situatie tot de technische uitwerking.' ),
			rwerkhoven_paragraph_block( 'Werkwijze:', true ),
			rwerkhoven_list_block( array( 'Opnemen bestaande situatie', 'Ontwerp uitwerken', 'Technische tekeningen opstellen', 'Vergunning aanvragen indien nodig' ) ),
		),
	),
	'aanbouw-uitbouw'        => array(
		'Aanbouw &amp; uitbouw',
		array(
			rwerkhoven_paragraph_block( 'Meer ruimte zonder te verhuizen. Van dakkapel en erker tot een complete aanbouw of uitbouw.' ),
			rwerkhoven_paragraph_block( 'Voorbeelden:', true ),
			rwerkhoven_list_block( array( 'Aanbouw', 'Uitbouw', 'Dakkapel', 'Garage', 'Carport', 'Erker' ) ),
			rwerkhoven_paragraph_block( 'Vooraf wordt beoordeeld of een vergunning nodig is en welke mogelijkheden er zijn.' ),
		),
	),
	'omgevingsvergunning'    => array(
		'Omgevingsvergunning',
		array(
			rwerkhoven_paragraph_block( 'Geen zorgen over procedures en regelgeving. De complete vergunningaanvraag wordt verzorgd, inclusief tekenwerk, benodigde documenten en indiening via het Omgevingsloket.' ),
			rwerkhoven_paragraph_block( 'Begeleiding bij:', true ),
			rwerkhoven_list_block( array( 'Vergunningaanvragen', 'Vooroverleg met gemeenten', 'Brandveiligheidsaanvragen', 'Gebruiksmeldingen', 'Afstemming met adviseurs' ) ),
		),
	),
	'bedrijfspanden'         => array(
		'Bedrijfspanden',
		array(
			rwerkhoven_paragraph_block( 'Voor winkels, kantoren, horeca en bedrijfsruimten. Praktische oplossingen waarbij snelheid, continuïteit en regelgeving centraal staan.' ),
			rwerkhoven_paragraph_block( 'Diensten voor bedrijven:', true ),
			rwerkhoven_list_block( array( 'Verbouwingen en herindelingen', 'Herbestemmingen', 'Vergunningaanvragen', 'Gevelreclame', 'Brandveiligheidsdocumentatie', 'Inmeten en digitaliseren' ) ),
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
	array( 'Eén vast aanspreekpunt', 'Ruim 30 jaar ervaring', 'Ontwerp, tekenwerk en vergunningen onder één dak', 'Korte lijnen en heldere communicatie', 'Voor particulieren én zakelijke opdrachtgevers' ),
	false,
	'rwerkhoven-checklist'
);
$diensten_why = preg_replace( '/^(?=.)/m', "\t", $diensten_why );

$diensten_content .= <<<HTML


<!-- wp:group {"className":"rwerkhoven-service-section","layout":{"type":"constrained"}} -->
<div class="wp-block-group rwerkhoven-service-section">
	<!-- wp:heading {"level":2,"anchor":"waarom"} -->
	<h2 class="wp-block-heading" id="waarom">Waarom kiezen voor Ron Werkhoven Bouwkundig Ontwerp?</h2>
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
<p class="has-large-font-size">Achter het bureau staat Ron Werkhoven. Met ruim 30 jaar ervaring help ik sinds 2010 particulieren en zakelijke opdrachtgevers met bouwkundig ontwerp, tekenwerk en vergunningaanvragen. Van een eenvoudige aanbouw tot een complete verbouwing of herontwikkeling van een bedrijfspand: ieder project begint met goed luisteren, meedenken en een plan dat uitvoerbaar is.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Eén aanspreekpunt van begin tot eind</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Een bouwproject is vaak ingewikkeld genoeg. Daarom vindt u bij mij geen verschillende contactpersonen voor ontwerp, tekenwerk en vergunningen. U heeft tijdens het hele traject met één persoon te maken.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Dat zorgt voor korte lijnen, heldere communicatie en een efficiënt proces. Ik ken het project van het eerste gesprek tot de laatste tekening en kan daardoor snel schakelen wanneer er vragen of wijzigingen zijn.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Praktisch, duidelijk en betrouwbaar</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ik geloof dat een goed ontwerp niet alleen mooi moet zijn, maar ook technisch haalbaar, betaalbaar en passend binnen de regels. Daarom zoek ik altijd naar oplossingen die werken in de praktijk.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph {"className":"rwerkhoven-list-label"} -->
<p class="rwerkhoven-list-label">Opdrachtgevers waarderen vooral:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>Persoonlijke begeleiding</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Korte communicatielijnen</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Creatieve en praktische oplossingen</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Duidelijke afspraken</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Snelle en betrouwbare levering van tekenwerk</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Kennis van vergunningen en regelgeving</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Voor wie ik werk</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"rwerkhoven-list-label"} -->
<p class="rwerkhoven-list-label">Particulieren</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>Aanbouwen en uitbouwen</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Dakkapellen</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Woningverbouwingen</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Nieuwbouwplannen</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:paragraph {"className":"rwerkhoven-list-label"} -->
<p class="rwerkhoven-list-label">Zakelijke opdrachtgevers</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>Aannemers</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Vastgoedbeheerders</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Winkelketens</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Architecten- en adviesbureaus</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Horeca en bedrijfspanden</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Wat opdrachtgevers zeggen</h2>
<!-- /wp:heading -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
	<!-- wp:paragraph -->
	<p>„Kan zeer zelfstandig en accuraat werken. Komt zijn afspraken na. Coop Supermarkten maakt al vele jaren gebruik van zijn diensten.”</p>
	<!-- /wp:paragraph -->
	<cite>Gerard van Elswijk - Hoofd Bouwbureau, Coop Supermarkten</cite>
</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
	<!-- wp:paragraph -->
	<p>„Van vergunningen tot complete lay-outs: als u iemand zoekt voor tekenwerk, bent u bij Ron aan het goede adres.”</p>
	<!-- /wp:paragraph -->
	<cite>Jeroen Vonck - JEZZ Projectmanagement</cite>
</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
	<!-- wp:paragraph -->
	<p>„Voor de tweede keer in relatief korte tijd heb ik Ron betrokken bij een vastgoedproject, en opnieuw naar volle tevredenheid.</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph -->
	<p>Ron is duidelijk, denkt goed mee en kijkt altijd naar een praktische en passende oplossing. Daarnaast is hij flexibel en komt hij zijn afspraken snel en netjes na.</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph -->
	<p>Wat ik vooral prettig vind, is dat de communicatie persoonlijk en laagdrempelig is en je merkt dat hij echt betrokken is bij het project.</p>
	<!-- /wp:paragraph -->

	<!-- wp:paragraph -->
	<p>Mocht ik in de toekomst weer een vastgoedproject hebben, dan betrek ik Ron er zeker opnieuw bij. Absoluut een aanrader!”</p>
	<!-- /wp:paragraph -->
	<cite>mattijs van't blik - Google-review, 5 sterren</cite>
</blockquote>
<!-- /wp:quote -->

<!-- wp:quote -->
<blockquote class="wp-block-quote">
	<!-- wp:paragraph -->
	<p>„Ron heeft ons goed geholpen bij het realiseren van een technische tekening van onze uitbouw. Hij is punctueel, vriendelijk, denkt goed mee en is pro-actief. En Ron heeft ons goed bijgestaan bij het vergunningstraject.”</p>
	<!-- /wp:paragraph -->
	<cite>woutereniris Linger - Google-review, 5 sterren</cite>
</blockquote>
<!-- /wp:quote -->

<!-- wp:paragraph -->
<p>Meer ervaringen leest u op <a href="https://www.linkedin.com/in/ron-werkhoven-2594881a" rel="noopener" target="_blank">LinkedIn</a> en in de <a href="https://g.page/r/Cf3-IzqPxu_8EAc" rel="noopener" target="_blank">Google Reviews</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:social-links {"openInNewTab":true,"style":{"spacing":{"margin":{"top":"var:preset|spacing|30"}}}} -->
<ul class="wp-block-social-links" style="margin-top:var(--wp--preset--spacing--30)">
	<!-- wp:social-link {"url":"https://www.linkedin.com/in/ron-werkhoven-2594881a","service":"linkedin","label":"LinkedIn"} /-->
	<!-- wp:social-link {"url":"https://g.page/r/Cf3-IzqPxu_8EAc","service":"google","label":"Reviews op Google"} /-->
</ul>
<!-- /wp:social-links -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Kennismaken?</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Heeft u plannen voor een verbouwing, uitbreiding of nieuw project? Ik denk graag met u mee. <a href="/contact/">Neem gerust contact op</a> voor een vrijblijvende kennismaking of offerte.</p>
<!-- /wp:paragraph -->
HTML;

$privacy_content = <<<'HTML'
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau, gevestigd aan Hofstedering 65, 3763 XB Soest, is verantwoordelijk voor de verwerking van persoonsgegevens zoals weergegeven in deze privacyverklaring.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Contactgegevens</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><a href="https://rwerkhoven.nl">https://rwerkhoven.nl</a><br>Hofstedering 65, 3763 XB Soest<br><a href="tel:0613645507">06-13645507</a></p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Ron Werkhoven is het aanspreekpunt voor privacyvragen bij Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau. Hij is te bereiken via <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Persoonsgegevens die wij verwerken</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau verwerkt uw persoonsgegevens doordat u gebruikmaakt van onze diensten en/of omdat u deze zelf aan ons verstrekt. Hieronder vindt u een overzicht van de persoonsgegevens die wij verwerken:</p>
<!-- /wp:paragraph -->

<!-- wp:list -->
<ul class="wp-block-list">
	<!-- wp:list-item -->
	<li>Voor- en achternaam</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>Telefoonnummer</li>
	<!-- /wp:list-item -->

	<!-- wp:list-item -->
	<li>E-mailadres</li>
	<!-- /wp:list-item -->
</ul>
<!-- /wp:list -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Bijzondere en/of gevoelige persoonsgegevens die wij verwerken</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Onze website en/of dienst heeft niet de intentie gegevens te verzamelen over websitebezoekers die jonger zijn dan 16 jaar, tenzij ze toestemming hebben van ouders of voogd. We kunnen echter niet controleren of een bezoeker ouder dan 16 is. Wij raden ouders dan ook aan betrokken te zijn bij de online activiteiten van hun kinderen, om zo te voorkomen dat er gegevens over kinderen verzameld worden zonder ouderlijke toestemming. Als u ervan overtuigd bent dat wij zonder die toestemming persoonlijke gegevens hebben verzameld over een minderjarige, neem dan contact met ons op via <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a>, dan verwijderen wij deze informatie.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Met welk doel en op basis van welke grondslag wij persoonsgegevens verwerken</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau verwerkt uw persoonsgegevens om u te kunnen bellen of e-mailen indien dit nodig is om onze dienstverlening uit te kunnen voeren. De grondslag hiervoor is de uitvoering van de overeenkomst met u, of uw verzoek om contact voorafgaand aan een overeenkomst.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Geautomatiseerde besluitvorming</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau neemt geen besluiten op basis van geautomatiseerde verwerkingen over zaken die (aanzienlijke) gevolgen kunnen hebben voor personen. Het gaat hier om besluiten die worden genomen door computerprogramma's of -systemen, zonder dat daar een mens tussen zit.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Hoe lang we persoonsgegevens bewaren</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau bewaart uw persoonsgegevens niet langer dan strikt nodig is om de doelen te realiseren waarvoor uw gegevens worden verzameld.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Delen van persoonsgegevens met derden</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau verstrekt uw gegevens uitsluitend aan derden als dit nodig is voor de uitvoering van onze overeenkomst met u of om te voldoen aan een wettelijke verplichting.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Cookies, of vergelijkbare technieken, die wij gebruiken</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau gebruikt geen cookies of vergelijkbare technieken.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Gegevens inzien, aanpassen of verwijderen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>U heeft het recht om uw persoonsgegevens in te zien, te corrigeren of te verwijderen. Daarnaast heeft u het recht om uw eventuele toestemming voor de gegevensverwerking in te trekken of bezwaar te maken tegen de verwerking van uw persoonsgegevens door Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau en heeft u het recht op gegevensoverdraagbaarheid. Dat betekent dat u bij ons een verzoek kunt indienen om de persoonsgegevens die wij van u hebben in een computerbestand naar u of een andere, door u genoemde organisatie te sturen.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>U kunt een verzoek tot inzage, correctie, verwijdering of gegevensoverdracht van uw persoonsgegevens, of een verzoek tot intrekking van uw toestemming of bezwaar tegen de verwerking van uw persoonsgegevens, sturen naar <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a>. Om er zeker van te zijn dat het verzoek tot inzage door u is gedaan, vragen wij u een kopie van uw identiteitsbewijs met het verzoek mee te sturen. Maak in deze kopie uw pasfoto, MRZ (machine readable zone, de strook met nummers onderaan het paspoort), paspoortnummer en burgerservicenummer (BSN) zwart. Dit ter bescherming van uw privacy. We reageren zo snel mogelijk, maar binnen vier weken, op uw verzoek.</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau wil u er tevens op wijzen dat u de mogelijkheid heeft om een klacht in te dienen bij de nationale toezichthouder, de <a href="https://www.autoriteitpersoonsgegevens.nl/een-tip-of-klacht-indienen-bij-de-ap" rel="noopener" target="_blank">Autoriteit Persoonsgegevens</a>.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="wp-block-heading has-large-font-size">Hoe wij persoonsgegevens beveiligen</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Ron Werkhoven Bouwkundig Ontwerp- en Tekenbureau neemt de bescherming van uw gegevens serieus en neemt passende maatregelen om misbruik, verlies, onbevoegde toegang, ongewenste openbaarmaking en ongeoorloofde wijziging tegen te gaan. Als u de indruk heeft dat uw gegevens niet goed beveiligd zijn of er aanwijzingen zijn van misbruik, neem dan contact op via <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a>.</p>
<!-- /wp:paragraph -->
HTML;

// Order matters: parents before children.
$pages = array(
	'contact'                        => array(
		'title'    => 'Contact',
		'content'  => $contact_content,
		'cta'      => false,
		'seo_t'    => 'Contact & vrijblijvende offerte | Ron Werkhoven Soest',
		'seo_d'    => 'Neem contact op met Ron Werkhoven, bouwkundig ontwerp- en tekenbureau in Soest. Vraag een vrijblijvende offerte aan voor ontwerp, tekenwerk of vergunning.',
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
		'seo_d'    => 'Bouwkundig ontwerp, bouwtekeningen en omgevingsvergunningen voor particulieren en bedrijven in Soest en regio Utrecht. Eén vast aanspreekpunt.',
	),
	'het-bureau'                     => array(
		'title'    => 'Over Ron Werkhoven',
		'content'  => $bureau_content,
		'cta'      => false,
		'seo_t'    => 'Over Ron Werkhoven: één aanspreekpunt voor uw bouwplan',
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
