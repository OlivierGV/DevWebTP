<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/delicious-recipes/account/form-lostpassword.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Print Errors / Notices.
delicious_recipes_print_notices();

$global_settings          = delicious_recipes_get_global_settings();
$google_recaptcha_enabled = false;
if ( isset( $global_settings['recaptchaEnabled'] ) && is_array( $global_settings['recaptchaEnabled'] ) && isset( $global_settings['recaptchaEnabled'][0] ) && 'yes' === $global_settings['recaptchaEnabled'][0] ) {
	$google_recaptcha_enabled = true;
}
$google_recaptcha_version  = isset( $global_settings['recpatchaVersion'] ) && ! empty( $global_settings['recpatchaVersion'] ) ? $global_settings['recpatchaVersion'] : 'v3'; 
$google_recaptcha_site_key = isset( $global_settings['recaptchaSiteKey'] ) && ! empty( $global_settings['recaptchaSiteKey'] ) ? $global_settings['recaptchaSiteKey'] : false;

?>
<div class="dr-container">

	<div class="dr-form-wrapper dr-form__forgot-password">
		<div class="dr-form__inner-wrapper">
			<form class="dr-form__fields-wrapper" method="post" name="dr-form__lost-pass">
				<div class="dr-form__heading">
					<h2 class="dr-form__title"><?php esc_html_e( 'Lost Your Password?', 'delicious-recipes' ); ?></h2>
					<div class="dr-form__desc">
						<?php esc_html_e( 'Please enter you username or email address. You will receive a link to create a new password via email.', 'delicious-recipes' ); ?>
					</div>
				</div>

				<?php do_action( 'delicious_recipes_lostpassword_fields_before' ); ?>

				<div class="dr-form__field">
					<label for="user-login"><?php esc_html_e( 'Email Or Username', 'delicious-recipes' ); ?></label>
					<input type="text" id="user-login" name="user_login" class="dr-form__field-input" placeholder="<?php esc_attr_e( 'Eg: deliciousrecipes', 'delicious-recipes' ); ?>">
				</div>

				<?php wp_nonce_field( 'delicious_recipes_lost_password' ); ?>

				<?php
				if ( $google_recaptcha_enabled && $google_recaptcha_site_key ) {
					if ( 'v3' === $google_recaptcha_version ) {
						?>
						<div class="dr-form__field-submit">
							<input type="hidden" name="delicious_recipes_reset_password" value="true" />
							<input type="hidden" name="delicious_recipes_reset_password_submit" value="<?php esc_attr_e( 'Reset Password', 'delicious-recipes' ); ?>">
							<input type="submit" class="g-recaptcha dr-form__submit w-100" data-sitekey="<?php echo esc_attr( $google_recaptcha_site_key ); ?>" data-callback='drUserPasswordLost' data-action='submit' value="<?php esc_attr_e( 'Reset Password', 'delicious-recipes' ); ?>">
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="dr-other-link"><?php esc_html_e( 'Back to Sign in?', 'delicious-recipes' ); ?></a>
						</div>
						<?php
					} else {
						?>
						<div class="dr-form__field">
							<div class="g-recaptcha" data-sitekey="<?php echo esc_attr( $google_recaptcha_site_key ); ?>"></div>
						</div>

						<div class="dr-form__field-submit">
							<input type="hidden" name="delicious_recipes_reset_password" value="true" />
							<input type="submit" name="delicious_recipes_reset_password_submit" value="<?php esc_attr_e( 'Reset Password', 'delicious-recipes' ); ?>" class="dr-form__submit w-100">
							<a href="<?php echo esc_url( get_permalink() ); ?>" class="dr-other-link"><?php esc_html_e( 'Back to Sign in?', 'delicious-recipes' ); ?></a>
						</div>
						<?php
					}
					?>
				<?php } else { ?>
					<div class="dr-form__field-submit">
						<input type="hidden" name="delicious_recipes_reset_password" value="true" />
						<input type="submit" name="delicious_recipes_reset_password_submit" value="<?php esc_attr_e( 'Reset Password', 'delicious-recipes' ); ?>" class="dr-form__submit w-100">
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="dr-other-link"><?php esc_html_e( 'Back to Sign in?', 'delicious-recipes' ); ?></a>
					</div>
				<?php } ?>

				<?php do_action( 'delicious_recipes_lostpassword_fields_after' ); ?>

			</form>
		</div>
	</div>

</div>
<?php

add_action( 'wp_footer', function() use ( $google_recaptcha_enabled ) {
	if ( $google_recaptcha_enabled ) {
		wp_enqueue_script( 'dr-google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), array(), true );
	}
});
