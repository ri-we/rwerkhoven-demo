<?php
/**
 * Title: Hero-foto homepage
 * Slug: rwerkhoven/hero-photo
 * Categories: rwerkhoven
 *
 * The homepage hero photo ships with the theme instead of coming from the
 * media library. front-page.html is a static block template and cannot
 * resolve a URL at runtime, so an inline image block there has to hardcode
 * both an absolute URL and an attachment ID — which is what broke the hero
 * when the uploads volume was rebuilt (the ID pointed at an unrelated post
 * and the file 404'd). A pattern is PHP, so it can ask the theme where its
 * own files live and keep working on any host and any install.
 *
 * Photo: the uitbouw of a detached house in Abcoude (project
 * "Uitbouw vrijstaande woning", /project/verbouw-woning-abcoude/).
 */

defined( 'ABSPATH' ) || exit;

$rwerkhoven_hero_full   = get_theme_file_uri( 'assets/img/hero-uitbouw-vrijstaande-woning.jpg' );
$rwerkhoven_hero_mobile = get_theme_file_uri( 'assets/img/hero-uitbouw-vrijstaande-woning-960.jpg' );
$rwerkhoven_hero_alt    = __( 'Uitbouw met houten gevelbekleding en grote glaspartijen aan een vrijstaande woning in Abcoude', 'rwerkhoven' );
?>
<!-- wp:image {"sizeSlug":"full","className":"rwerkhoven-hero-photo","style":{"spacing":{"margin":{"bottom":"0"}}}} -->
<figure class="wp-block-image size-full rwerkhoven-hero-photo" style="margin-bottom:0"><img src="<?php echo esc_url( $rwerkhoven_hero_full ); ?>" srcset="<?php echo esc_url( $rwerkhoven_hero_mobile ); ?> 960w, <?php echo esc_url( $rwerkhoven_hero_full ); ?> 1440w" sizes="100vw" width="1440" height="960" alt="<?php echo esc_attr( $rwerkhoven_hero_alt ); ?>" fetchpriority="high" decoding="async"/></figure>
<!-- /wp:image -->
