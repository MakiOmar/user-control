<?php
if( !defined( 'ABSPATH' ) )
    die( 'What are you trying to do?' );
?>

<?php if ( $attributes['show_title'] ) : ?>
	<h3><?php _e( 'Forgot Your Password?', ANONY_UC_TEXTDOM ); ?></h3>
<?php endif; ?>

<p><?php  _e( "Enter your email address and we'll send you a link you can use to pick a new password.",ANONY_UC_TEXTDOM); ?></p>
 <div id="password-lost-form" class="widecolumn user-control">
    <form id="lostpassword" class="user-control-form" action="<?php echo wp_lostpassword_url(); ?>" method="post">
        <p class="form-row">
            <label for="user_login"><?php _e( 'Email', ANONY_UC_TEXTDOM ); ?>
            <input type="text" class="username" name="user_login" id="user_login" placeholder="<?php _e('&#xf090; Email Adress','user-control');?>">
        </p>
 
        <p class="lostpassword-submit">
             <button type="submit" class="anony-uc-button" name="submit" form="lostpassword"><?php _e( 'Reset Password', ANONY_UC_TEXTDOM ); ?></button>
        </p>
    </form>
</div>