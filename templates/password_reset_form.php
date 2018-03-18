<div id="password-reset-form" class="widecolumn">
    <?php if ( $attributes['show_title'] ) : ?>
        <h3><?php _e( 'Pick a New Password', 'usercontrol' ); ?></h3>
    <?php endif; ?>
	 <div class="user-control">
		<form name="resetpassform" id="resetpassform" action="<?php echo site_url( 'wp-login.php?action=resetpass' ); ?>" method="post" autocomplete="off">
			<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $attributes['login'] ); ?>" autocomplete="off" />
			<input type="hidden" name="rp_key" value="<?php echo esc_attr( $attributes['key'] ); ?>" />

			<p>
				<label for="pass1"><?php _e( 'New password', 'usercontrol' ) ?></label>
				<input type="password" name="pass1" id="pass1" class="password" size="20" value="" autocomplete="off" />
			</p>
			<p>
				<label for="pass2"><?php _e( 'Repeat new password', 'usercontrol' ) ?></label>
				<input type="password" name="pass2" id="pass2" class="password"  size="20" value="" autocomplete="off" />
			</p>

			<p class="description"><?php echo wp_get_password_hint(); ?></p>

			<p class="resetpass-submit">
				<button id="resetpass-button" type="submit" name="submit" class="smpg-button" form="resetpassform"><?php _e( 'Reset Password', 'usercontrol' ); ?></button>
			</p>
		</form>
	</div>
</div>