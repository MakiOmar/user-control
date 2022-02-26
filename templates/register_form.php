<?php
if( !defined( 'ABSPATH' ) )
	die( 'What are you trying to do?' );
?>

<div id="register-form">
    <?php if ( $attributes['show_title'] ) : ?>
        <h3><?php esc_html_e( 'Register', 'user-control' ); ?></h3>
    <?php endif; ?>
    
    <div class="user-control">
        
		<form id="signupform" class="user-control-form" action="<?php echo wp_registration_url(); ?>" method="post" autocomplete="on">
		 
            <?php
            
            foreach($fields as $id => $field_atts){
                extract($field_atts);
            ?>
                <input type="<?= $type ?>" name="<?= $name ?>" id ="<?= $id ?>" class="<?= $class ?>" placeholder="<?= $placeholder ?>"/>
            <?php }
            
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
