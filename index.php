<?php
/**
 * Plugin Name: User Control
 * Plugin URI: https://makiomar.com
 * Description: Adds a user control interface for login, register and forget password 
 * Version: 1.0.0
 * Author: Mohammad Omar
 * Author URI: https://makiomar.com
 * Text Domain: user-control
 * License: GPL2
 */

/**
 * Display a notification if one of required plugins is not activated/installed
 */
add_action( 'admin_notices', function() {
	if (!defined('ANOENGINE')) {
	    ?>
	    <div class="notice notice-error is-dismissible">
	        <p><?php esc_html_e( 'Please activate/install AnonyEngine plugin, for User control plugin can work properly' ); ?></p>
	    </div>
	<?php }
});

register_activation_hook( __FILE__, 'anonyUcRegHook' );

if (!defined("ANOENGINE")) return;

require_once('functions/help.php');

require_once('config.php');

require_once('functions/options.php');



$user_control_login_pages_plugin = new ANONY__User_Control();

register_deactivation_hook( __FILE__, array( 'ANONY__User_Control', 'deactivated' ) );

function anonyUcDie()
{
	if (!defined("ANOENGINE")) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'User Control plugin requires AnonyEngine plugin to be installed/activated.  Sorry about that.' );
	}
}

function anonyUcRegHook()
{
	anonyUcDie();
	call_user_func(array( 'ANONY__User_Control', 'insert_pages' ));
}

/**
 * User controle menu
 * @param  string $location_slug 
 * @return string
 */
function anony_user_main($location_slug){
	if ( has_nav_menu( $location_slug ) ) {
		$location_array = explode('-',$location_slug);
			$args = array (
					'theme_location'=> $location_slug,
					'depth'         => 0,
					'menu_id'       => $location_array[0].'_menu_con',
					'container'     => 'nav',
					'container_id'  => $location_array[0].'_nav_con',
					'echo'          => false,
					);
			
				return wp_nav_menu($args);
	}
}



$cntrl_nav = new ANONY__UC_Meta_Box();

add_action('admin_init', array($cntrl_nav, 'add_nav_menu_meta_boxes'));

/**
 * Load user control plugin textdomain.
 *
 * @since 1.0.0
 */
add_action( 'plugins_loaded', function() {
  load_plugin_textdomain( ANONY_UC_TEXTDOM, false, basename( dirname( __FILE__ ) ) . '/languages' ); 
} );

/**
 * Redirect user after successful login.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
add_filter( 'login_redirect', function ( $redirect_to, $request, $user ) {
    //is there a user to check?
    if (isset($user->roles) && is_array($user->roles)) {
        //check for subscribers
        if (in_array('subscriber', $user->roles)) {
            // redirect them to another URL, in this case, the homepage 
            $redirect_to =  home_url('/anony-account');
        }
    }

    return $redirect_to;
}, 10, 3 );