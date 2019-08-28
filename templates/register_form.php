<?php
if( !defined( 'ABSPATH' ) )
	die( 'What are you trying to do?' );
?>

<div id="register-form">
    <?php if ( $attributes['show_title'] ) : ?>
        <h3><?php _e( 'Register', 'user-control' ); ?></h3>
    <?php endif; ?>
    <div class="user-control">
		<form id="signupform" class="user-control-form" action="<?php echo wp_registration_url(); ?>" method="post">
		   <input type="text" class="username" name="email" id="email" placeholder="<?php _e('&#xf090; Email Adress','user-control');?>">
		   <input type="text" class="username" name="user_name" id="user_name" placeholder="<?php _e('&#xf007; Username','user-control');?>">

		   <p class="form-row">
			<?php _e( 'Note: Your password will be generated automatically and sent to your email address.', 'user-control' ); ?>
		   </p>
		   <p class="signup-submit">
				<button type="submit" name="submit" class="smpg-button" form="signupform"><?php _e( 'Register', 'user-control' ); ?></button>
		  </p>
		  </form>
	  </div>
</div>
