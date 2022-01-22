<?php
if( !defined( 'ABSPATH' ) )
	die( 'What are you trying to do?' );
?>

 <div class="grid-col widgeted">
    <?php if ( $attributes['lost_password_sent'] ) : ?>
        <p class="login-info">
            <?php esc_html_e( 'A reset password link has been sent to your email.', ANONY_UC_TEXTDOM ); ?>
        </p>
    <?php endif; ?>
    
    <?php if ( $attributes['password_updated'] ) : ?>
        <p class="login-info">
            <?php esc_html_e( 'Your password has been changed. You can sign in now.', ANONY_UC_TEXTDOM ); ?>
        </p>
    <?php endif; ?>
    
    <?php if ( isset($_GET['registered']) && $_GET['registered'] == '1' ) : ?>
        <p class="login-info">
            <?php esc_html_e( 'Thank you!. Your account credentials has been emailed to the registered e-mail', ANONY_UC_TEXTDOM ); ?>
        </p>
    <?php endif; ?>
    
	 <div id="usercontrol" class="user-control">
			<p class="hint"><?php esc_html_e('Enter your credintals', ANONY_UC_TEXTDOM) ?></p>

			<form id="login" class="user-control-form" method="post" action="<?php echo wp_login_url(); ?>" autocomplete="on">

			  <input type="text" name="log" id="user_login" class="username"  placeholder="<?php esc_html_e('Email Adress','user-control');?>"/>

			  <input type="password" name="pwd" id="user_pass"  class="password"  placeholder="<?php esc_html_e('Password','user-control');?>"/>

			  <p class="forgetmenot"><label for="rememberme"><input name="rememberme" type="checkbox" id="rememberme" value="forever"/> <?php esc_html_e( 'Remember Me' ); ?></label></p>

			  <button type="submit" class="anony-uc-button" form="login"><?php esc_html_e('Login','user-control');?></button>
			  
			  <a class="forgot-password" href="<?php echo wp_registration_url(); ?>"><?php esc_html_e( 'Register', 'user-control' ); ?></a>
			  <a class="forgot-password" href="<?php echo wp_lostpassword_url(); ?>"><?php esc_html_e( 'Forgot your password?', 'user-control' ); ?></a>
			  
			</form>	
	</div>
</div>