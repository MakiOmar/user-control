<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'What are you trying to do?' );
}
?>

<div id="register-form">
	<?php if ( $attributes['show_title'] ) : ?>
		<h3><?php esc_html_e( 'Register', 'user-control' ); ?></h3>
	<?php endif; ?>
	
	<div class="user-control">
		
		<form id="signupform" class="user-control-form" action="<?php echo esc_url( wp_registration_url() ); ?>" method="post" autocomplete="on">
		 
			<?php

			foreach ( $fields as $field_id => $field ) {

				$render_field = new ANONY_Input_Field( $field, null, 'form' );

				echo $render_field->field_init();

			}

			?>
		   <p class="form-row">
			<?php esc_html_e( 'Note: Your password will be generated automatically and sent to your email address.', 'user-control' ); ?>
		   </p>
		   <p class="signup-submit">
				<button type="submit" name="submit" class="anony-uc-button" form="signupform"><?php esc_html_e( 'Register', 'user-control' ); ?></button>
		  </p>
		  </form>
	  </div>
</div>
