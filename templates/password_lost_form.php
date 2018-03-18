<?php if ( $attributes['show_title'] ) : ?>
	<h3><?php _e( 'Forgot Your Password?', 'usercontrol' ); ?></h3>
<?php endif; ?>

<p><?php  _e( "Enter your email address and we'll send you a link you can use to pick a new password.",'usercontrol'); ?></p>
 <div id="password-lost-form" class="widecolumn user-control">
    <form id="lostpassword" class="user-control-form" action="<?php echo wp_lostpassword_url(); ?>" method="post">
        <p class="form-row">
            <label for="user_login"><?php _e( 'Email', 'usercontrol' ); ?>
            <input type="text" class="username" name="user_login" id="user_login" placeholder="<?php _e('&#xf090; Email Adress','user-control');?>">
        </p>
 
        <p class="lostpassword-submit">
             <button type="submit" class="smpg-button" name="submit" form="lostpassword"><?php _e( 'Reset Password', 'usercontrol' ); ?></button>
        </p>
    </form>
</div>