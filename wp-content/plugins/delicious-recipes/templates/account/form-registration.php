<?php
/**
 * Registration Form template.
 *
 * @package Delicious_Recipes
 */

// Print Errors / Notices.
delicious_recipes_print_notices();

$global_toggles          = delicious_recipes_get_global_toggles_and_labels();
$global_settings         = delicious_recipes_get_global_settings();
$registration_image      = isset( $global_settings['registrationImage'] ) && ! empty( $global_settings['registrationImage'] ) ? $global_settings['registrationImage'] : false;
$terms_n_conditions_text = isset( $global_settings['termsNConditionsText'] ) && ! empty( $global_settings['termsNConditionsText'] ) ? $global_settings['termsNConditionsText'] : false;

$google_recaptcha_enabled = false;
if ( isset( $global_settings['recaptchaEnabled'] ) && is_array( $global_settings['recaptchaEnabled'] ) && isset( $global_settings['recaptchaEnabled'][0] ) && 'yes' === $global_settings['recaptchaEnabled'][0] ) {
	$google_recaptcha_enabled = true;
}
$google_recaptcha_version  = isset( $global_settings['recpatchaVersion'] ) && ! empty( $global_settings['recpatchaVersion'] ) ? $global_settings['recpatchaVersion'] : 'v3'; 
$google_recaptcha_site_key = isset( $global_settings['recaptchaSiteKey'] ) && ! empty( $global_settings['recaptchaSiteKey'] ) ? $global_settings['recaptchaSiteKey'] : false;
?>
<div class="dr-container">

	<div class="dr-form-wrapper dr-form__sign-up">
		<div class="dr-form__inner-wrapper">
			<div class="dr-form__grid">
				<form class="dr-form__fields-wrapper" method="post" name="dr-form__sign-up">
					<div class="dr-form__heading">
						<h2 class="dr-form__title"><?php esc_html_e( 'Sign Up', 'delicious-recipes' ); ?></h2>
					</div>

					<?php do_action( 'delicious_recipes_registration_fields_before' ); ?>

					<?php if ( ! $global_toggles['generate_username'] ) : ?>
						<div class="dr-form__field">
							<label for="username"><?php esc_html_e( 'Username', 'delicious-recipes' ); ?></label>
							<input required data-parsley-required-message="<?php esc_attr_e( 'Please enter your desired username', 'delicious-recipes' ); ?>" type="text" id="username" name="username" class="dr-form__field-input" placeholder="<?php esc_attr_e( 'Eg: deliciousrecipes', 'delicious-recipes' ); ?>">
						</div>
					<?php endif; ?>
					<div class="dr-form__field">
						<label for="email"><?php esc_html_e( 'Email', 'delicious-recipes' ); ?></label>
						<input required data-parsley-required-message="<?php esc_attr_e( 'Please enter a valid email address', 'delicious-recipes' ); ?>" type="email" id="email" name="email" class="dr-form__field-input" placeholder="<?php esc_attr_e( 'Eg: deliciousrecipes@example.com', 'delicious-recipes' ); ?>">
					</div>
					<?php if ( ! $global_toggles['generate_password'] ) : ?>
						<div class="dr-form__field">
							<label for="password"><?php esc_html_e( 'Password', 'delicious-recipes' ); ?></label>
							<input required data-parsley-required-message="<?php esc_attr_e( 'Please enter a valid password', 'delicious-recipes' ); ?>" type="password" id="password" name="password" class="dr-form__field-input" placeholder="<?php esc_attr_e( 'Create a password', 'delicious-recipes' ); ?>">
						</div>
						<div class="dr-form__field">
							<label for="c-password"><?php esc_html_e( 'Confirm Password', 'delicious-recipes' ); ?></label>
							<input required data-parsley-required-message="<?php esc_attr_e( 'Please enter a valid password', 'delicious-recipes' ); ?>" type="password" id="c-password" name="c-password" class="dr-form__field-input" placeholder="<?php esc_attr_e( 'Confirm password', 'delicious-recipes' ); ?>">
						</div>
					<?php endif; ?>
					<?php
						// Nonce security.
						wp_nonce_field( 'delicious-recipes-user-register', 'delicious-recipes-user-register-nonce' );
					?>
					<div class="dr-form__field-submit">
						<?php if ( $global_toggles['terms_n_conditions'] && $terms_n_conditions_text ) : ?>
							<div class="dr-form__checkbox">
								<input required data-parsley-required-message="<?php esc_attr_e( 'Please check the terms and conditions', 'delicious-recipes' ); ?>" type="checkbox" id="terms-conditions" name="termsnconditions">
								<label for="terms-conditions">
									<?php echo esc_html( $terms_n_conditions_text ); ?>
								</label>
							</div>
						<?php endif; ?>

						<?php
						if ( $google_recaptcha_enabled && $google_recaptcha_site_key ) {
							if ( 'v3' === $google_recaptcha_version ) {
								?>
								<input type="hidden" name="register" value="<?php esc_attr_e( 'Register Now', 'delicious-recipes' ); ?>">
								<input type="submit" class="g-recaptcha dr-form__submit w-100" data-sitekey="<?php echo esc_attr( $google_recaptcha_site_key ); ?>" data-callback='drUserRegistration' data-action='submit' value="<?php esc_attr_e( 'Register Now', 'delicious-recipes' ); ?>">
								<?php
							} else {
								?>
								<div class="dr-form__field g-recaptcha" data-sitekey="<?php echo esc_attr( $google_recaptcha_site_key ); ?>"></div>
								<input type="submit" name="register" value="<?php esc_attr_e( 'Register Now', 'delicious-recipes' ); ?>" class="dr-form__submit w-100">
								<?php
							}
							?>
						<?php } else { ?>
							<input type="submit" name="register" value="<?php esc_attr_e( 'Register Now', 'delicious-recipes' ); ?>" class="dr-form__submit w-100">
						<?php } ?>
					</div>
					<div class="dr-form__footer">
						<p><?php esc_html_e( 'Already have an account?', 'delicious-recipes' ); ?> <a href="<?php echo esc_url( delicious_recipes_get_page_permalink_by_id( delicious_recipes_get_dashboard_page_id() ) ); ?>"><?php esc_html_e( 'Sign In', 'delicious-recipes' ); ?></a></p>
					</div>

					<?php do_action( 'delicious_recipes_registration_fields_after' ); ?>

				</form>
				<div class="dr-form__img-wrapper">
					<div class="dr-img-holder">
						<?php
						if ( $registration_image ) :
							echo wp_get_attachment_image( $registration_image, 'full' );
							else :
								?>
								<img src="<?php echo esc_url( plugin_dir_url( DELICIOUS_RECIPES_PLUGIN_FILE ) ); ?>/src/dashboard/img/dr-sign-up-img.svg" alt="">
								<?php
							endif;
							?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
<?php

add_action( 'wp_footer', function() use ( $google_recaptcha_enabled ) {
	if ( $google_recaptcha_enabled ) {
		wp_enqueue_script( 'dr-google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), array(), true );
	}
});
