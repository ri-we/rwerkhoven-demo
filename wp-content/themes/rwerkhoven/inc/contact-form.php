<?php
/**
 * Native contact form: a dynamic block, a submission handler and a
 * local-development mail transport.
 *
 * Privacy by design (brief §27): nothing is stored in the database — a
 * submission is validated, mailed to the studio and forgotten. Spam is kept
 * out with a nonce, a honeypot field and a minimum-fill-time check, so no
 * third-party captcha service sees visitor data.
 */

defined( 'ABSPATH' ) || exit;

const RWERKHOVEN_CONTACT_RECIPIENT = 'info@rwerkhoven.nl';

add_action(
	'init',
	function () {
		register_block_type(
			'rwerkhoven/contact-form',
			array(
				'render_callback' => 'rwerkhoven_render_contact_form',
			)
		);
	}
);

/**
 * Renders the form, including the error state after a rejected submission.
 */
function rwerkhoven_render_contact_form() {
	$status = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	ob_start();
	?>
	<div class="rwerkhoven-contact-form">
		<?php if ( 'invalid' === $status ) : ?>
			<p class="rwerkhoven-form-error" role="alert">Er ging iets mis. Controleer of naam, e-mailadres, bericht en de toestemmings-checkbox zijn ingevuld en probeer het opnieuw.</p>
		<?php elseif ( 'failed' === $status ) : ?>
			<p class="rwerkhoven-form-error" role="alert">Het bericht kon niet worden verzonden. Probeer het later opnieuw of mail direct naar <a href="mailto:info@rwerkhoven.nl">info@rwerkhoven.nl</a>.</p>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="rwerkhoven_contact" />
			<?php wp_nonce_field( 'rwerkhoven_contact', 'rwerkhoven_contact_nonce' ); ?>
			<input type="hidden" name="rwerkhoven_ts" value="<?php echo esc_attr( (string) time() ); ?>" />

			<p class="rwerkhoven-hp" aria-hidden="true">
				<label>Laat dit veld leeg<input type="text" name="rwerkhoven_website" tabindex="-1" autocomplete="off" /></label>
			</p>

			<p>
				<label for="rw-name">Naam *</label>
				<input id="rw-name" type="text" name="rw_name" required autocomplete="name" />
			</p>
			<p>
				<label for="rw-email">E-mailadres *</label>
				<input id="rw-email" type="email" name="rw_email" required autocomplete="email" />
			</p>
			<p>
				<label for="rw-phone">Telefoonnummer</label>
				<input id="rw-phone" type="tel" name="rw_phone" autocomplete="tel" />
			</p>
			<p>
				<label for="rw-message">Bericht *</label>
				<textarea id="rw-message" name="rw_message" rows="6" required></textarea>
			</p>
			<p class="rwerkhoven-consent">
				<label><input type="checkbox" name="rw_consent" value="1" required /> Ik ga ermee akkoord dat deze gegevens worden gebruikt om contact met mij op te nemen. *</label>
			</p>
			<p>
				<button type="submit" class="wp-element-button">Verstuur bericht</button>
			</p>
			<p class="rwerkhoven-form-privacy">Uw gegevens worden alleen gebruikt om uw bericht te beantwoorden en worden niet op deze website opgeslagen.</p>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

add_action( 'admin_post_rwerkhoven_contact', 'rwerkhoven_handle_contact' );
add_action( 'admin_post_nopriv_rwerkhoven_contact', 'rwerkhoven_handle_contact' );

/**
 * Validates a submission and mails it; stores nothing.
 */
function rwerkhoven_handle_contact() {
	$back = home_url( '/contact/' );

	$nonce = isset( $_POST['rwerkhoven_contact_nonce'] ) ? sanitize_key( wp_unslash( $_POST['rwerkhoven_contact_nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'rwerkhoven_contact' ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'invalid', $back ) );
		exit;
	}

	// Honeypot and minimum fill time: pretend success so bots get no feedback.
	$honeypot  = isset( $_POST['rwerkhoven_website'] ) ? trim( (string) wp_unslash( $_POST['rwerkhoven_website'] ) ) : '';
	$timestamp = isset( $_POST['rwerkhoven_ts'] ) ? (int) $_POST['rwerkhoven_ts'] : 0;
	if ( '' !== $honeypot || ( time() - $timestamp ) < 3 ) {
		wp_safe_redirect( home_url( '/bedankt/' ) );
		exit;
	}

	$name    = isset( $_POST['rw_name'] ) ? sanitize_text_field( wp_unslash( $_POST['rw_name'] ) ) : '';
	$email   = isset( $_POST['rw_email'] ) ? sanitize_email( wp_unslash( $_POST['rw_email'] ) ) : '';
	$phone   = isset( $_POST['rw_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['rw_phone'] ) ) : '';
	$message = isset( $_POST['rw_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['rw_message'] ) ) : '';
	$consent = ! empty( $_POST['rw_consent'] );

	if ( '' === $name || ! is_email( $email ) || '' === $message || ! $consent ) {
		wp_safe_redirect( add_query_arg( 'contact', 'invalid', $back ) );
		exit;
	}

	$body  = "Naam: {$name}\n";
	$body .= "E-mail: {$email}\n";
	if ( '' !== $phone ) {
		$body .= "Telefoon: {$phone}\n";
	}
	$body .= "\nBericht:\n{$message}\n";

	// Explicit From: the default (wordpress@<host>) is refused by PHPMailer on
	// hosts without a dot (e.g. localhost) and hurts deliverability elsewhere.
	$sent = wp_mail(
		RWERKHOVEN_CONTACT_RECIPIENT,
		'Contactformulier rwerkhoven.nl — ' . $name,
		$body,
		array(
			'From: Ron Werkhoven — website <info@rwerkhoven.nl>',
			'Reply-To: ' . $name . ' <' . $email . '>',
		)
	);

	if ( ! $sent ) {
		wp_safe_redirect( add_query_arg( 'contact', 'failed', $back ) );
		exit;
	}

	wp_safe_redirect( home_url( '/bedankt/' ) );
	exit;
}

// Local development only: route mail through the Mailpit container so
// delivery is testable end to end. Production SMTP is a deployment concern.
add_action(
	'phpmailer_init',
	function ( $phpmailer ) {
		if ( 'local' !== wp_get_environment_type() ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host     = 'mailpit';
		$phpmailer->Port     = 1025;
		$phpmailer->SMTPAuth = false;
	}
);
