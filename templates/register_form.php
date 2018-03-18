<div id="register-form">
    <?php if ( $attributes['show_title'] ) : ?>
        <h3><?php _e( 'Register', 'user-control' ); ?></h3>
    <?php endif; ?>
    <form id="signupform" action="<?php echo wp_registration_url(); ?>" method="post">
		   <p class="form-row">
			   <label for="email"><?php _e( 'Email', 'user-control' ); ?> <strong>*</strong></label>
			   <input type="text" name="email" id="email">
           </p>
           <p class="form-row">
			   <label for="user_name"><?php _e( 'Username', 'user-control' ); ?></label>
			   <input type="text" name="user_name" id="user_name">
           </p>
           <p class="form-row">
           	<?php _e( 'Note: Your password will be generated automatically and sent to your email address.', 'user-control' ); ?>
           </p>
           <p class="signup-submit">
                <input type="submit" name="submit" class="register-button" value="<?php _e( 'Register', 'user-control' ); ?>"/>
		  </p>
	  </form>
</div>
