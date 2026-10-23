<?php
/**
 * Homepage hero carousel.
 *
 * The photos are chosen in the admin under Weergave → Homepage-carousel and
 * stored as attachment IDs in the `rwerkhoven_hero_slides` option. The
 * front-page template renders them through the dynamic block
 * rwerkhoven/hero-carousel, so swapping a photo never means editing a
 * template. Without a selection the block falls back to the photo that ships
 * with the theme, so a fresh install never shows an empty hero.
 */

defined( 'ABSPATH' ) || exit;

const RWERKHOVEN_HERO_OPTION     = 'rwerkhoven_hero_slides';
const RWERKHOVEN_HERO_MAX_SLIDES = 6;

/**
 * The selected slides as attachment IDs, dropping any that no longer exist.
 *
 * @return int[]
 */
function rwerkhoven_hero_slide_ids() {
	$ids = array_map( 'absint', (array) get_option( RWERKHOVEN_HERO_OPTION, array() ) );

	return array_values(
		array_filter(
			$ids,
			static function ( $id ) {
				return $id && wp_attachment_is_image( $id );
			}
		)
	);
}

add_action(
	'init',
	function () {
		register_block_type(
			'rwerkhoven/hero-carousel',
			array(
				'render_callback' => 'rwerkhoven_render_hero_carousel',
			)
		);
	}
);

/**
 * Renders the hero: one <img> per slide, stacked; the script cross-fades
 * between them. Without JavaScript only the first slide is visible.
 */
function rwerkhoven_render_hero_carousel() {
	$slides = array();

	foreach ( rwerkhoven_hero_slide_ids() as $index => $id ) {
		$slides[] = wp_get_attachment_image(
			$id,
			'full',
			false,
			array(
				'class'         => 'rwerkhoven-hero-slide-img',
				'sizes'         => '100vw',
				'loading'       => 0 === $index ? 'eager' : 'lazy',
				'fetchpriority' => 0 === $index ? 'high' : 'low',
				'decoding'      => 'async',
			)
		);
	}

	if ( ! $slides ) {
		$slides[] = sprintf(
			'<img class="rwerkhoven-hero-slide-img" src="%1$s" srcset="%2$s 960w, %1$s 1440w" sizes="100vw" width="1440" height="960" alt="%3$s" fetchpriority="high" decoding="async">',
			esc_url( get_theme_file_uri( 'assets/img/hero-uitbouw-vrijstaande-woning.jpg' ) ),
			esc_url( get_theme_file_uri( 'assets/img/hero-uitbouw-vrijstaande-woning-960.jpg' ) ),
			esc_attr__( 'Uitbouw met houten gevelbekleding en grote glaspartijen aan een vrijstaande woning in Abcoude', 'rwerkhoven' )
		);
	}

	$html = '';
	foreach ( $slides as $index => $img ) {
		$html .= sprintf(
			'<div class="rwerkhoven-hero-slide%s" role="group" aria-roledescription="dia" aria-label="%s">%s</div>',
			0 === $index ? ' is-active' : '',
			esc_attr( sprintf( '%d van %d', $index + 1, count( $slides ) ) ),
			$img
		);
	}

	return sprintf(
		'<section class="rwerkhoven-hero-carousel" aria-roledescription="carrousel" aria-label="%s"><div class="rwerkhoven-hero-slides">%s</div></section>',
		esc_attr__( 'Foto\'s van projecten', 'rwerkhoven' ),
		$html
	);
}

/* ------------------------------------------------------------ admin page */

add_action(
	'admin_menu',
	function () {
		add_theme_page(
			__( 'Homepage-carousel', 'rwerkhoven' ),
			__( 'Homepage-carousel', 'rwerkhoven' ),
			'edit_theme_options',
			'rwerkhoven-hero',
			'rwerkhoven_render_hero_admin'
		);
	}
);

add_action(
	'admin_init',
	function () {
		register_setting(
			'rwerkhoven_hero',
			RWERKHOVEN_HERO_OPTION,
			array(
				'type'              => 'array',
				'default'           => array(),
				'sanitize_callback' => static function ( $value ) {
					// The admin form posts a comma-separated string; update_option() callers pass an array.
					$ids = is_array( $value ) ? $value : explode( ',', (string) $value );
					$ids = array_filter( array_map( 'absint', $ids ) );
					$ids = array_filter( $ids, 'wp_attachment_is_image' );
					return array_slice( array_values( array_unique( $ids ) ), 0, RWERKHOVEN_HERO_MAX_SLIDES );
				},
			)
		);
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'appearance_page_rwerkhoven-hero' !== $hook ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script(
			'rwerkhoven-hero-admin',
			RWERKHOVEN_URI . '/assets/js/hero-admin.js',
			array( 'jquery', 'jquery-ui-sortable' ),
			(string) filemtime( RWERKHOVEN_DIR . '/assets/js/hero-admin.js' ),
			true
		);
		wp_localize_script( 'rwerkhoven-hero-admin', 'rwerkhovenHero', array( 'max' => RWERKHOVEN_HERO_MAX_SLIDES ) );
	}
);

/**
 * One thumbnail in the admin list. assets/js/hero-admin.js builds the same
 * markup for photos added from the media library.
 */
function rwerkhoven_hero_admin_item( $id ) {
	$meta     = wp_get_attachment_metadata( $id );
	$portrait = is_array( $meta ) && ! empty( $meta['width'] ) && $meta['height'] >= $meta['width'];

	return sprintf(
		'<li class="rwerkhoven-hero-item" data-id="%1$d">%2$s%3$s<button type="button" class="button-link rwerkhoven-hero-remove">%4$s</button></li>',
		$id,
		wp_get_attachment_image( $id, 'medium' ),
		$portrait ? '<p class="rwerkhoven-hero-warning">' . esc_html__( 'Staande foto: wordt bijgesneden. Kies liever een liggende foto.', 'rwerkhoven' ) . '</p>' : '',
		esc_html__( 'Verwijderen', 'rwerkhoven' )
	);
}

function rwerkhoven_render_hero_admin() {
	$ids = rwerkhoven_hero_slide_ids();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Homepage-carousel', 'rwerkhoven' ); ?></h1>
		<p>
			<?php
			printf(
				/* translators: %d: maximum number of photos */
				esc_html__( 'Kies de foto\'s die bovenaan de homepage wisselen (maximaal %d). Gebruik liggende foto\'s van minimaal 1200 pixels breed. Sleep de foto\'s om de volgorde te veranderen; de eerste foto verschijnt het eerst. Zonder selectie toont de homepage de standaardfoto van het thema.', 'rwerkhoven' ),
				(int) RWERKHOVEN_HERO_MAX_SLIDES
			);
			?>
		</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'rwerkhoven_hero' ); ?>
			<input type="hidden" id="rwerkhoven-hero-ids" name="<?php echo esc_attr( RWERKHOVEN_HERO_OPTION ); ?>" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>">
			<ul id="rwerkhoven-hero-list">
				<?php
				foreach ( $ids as $id ) {
					echo rwerkhoven_hero_admin_item( $id ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in the helper.
				}
				?>
			</ul>
			<p><button type="button" class="button" id="rwerkhoven-hero-add"><?php esc_html_e( 'Foto\'s toevoegen', 'rwerkhoven' ); ?></button></p>
			<?php submit_button( __( 'Opslaan', 'rwerkhoven' ) ); ?>
		</form>
		<style>
			#rwerkhoven-hero-list { display: flex; flex-wrap: wrap; gap: 16px; margin: 16px 0; }
			.rwerkhoven-hero-item { width: 240px; margin: 0; padding: 8px; background: #fff; border: 1px solid #c3c4c7; cursor: move; }
			.rwerkhoven-hero-item img { display: block; width: 100%; height: 150px; object-fit: cover; }
			.rwerkhoven-hero-warning { margin: 6px 0 0; color: #b32d2e; }
			.rwerkhoven-hero-remove { margin-top: 6px; color: #b32d2e; }
		</style>
	</div>
	<?php
}
