<?php
/**
 * Plugin Name: User Control
 * Plugin URI: https://prosentra.com
 * Description: Adds a user control interface for login, register and forget password 
 * Version: 1.0.0
 * Author: Mohammad Omar
 * Author URI: https://makiomar.com
 * Text Domain: user-control
 * License: GPL2
 */
require_once('config.php');

$user_control_login_pages_plugin = new ANONY__User_Control();

register_activation_hook( __FILE__, array( 'ANONY__User_Control', 'insert_pages' ) );

register_deactivation_hook( __FILE__, array( 'ANONY__User_Control', 'deactivated' ) );


function anony_user_main($location_slug){
	if ( has_nav_menu( $location_slug ) ) {
		$location_array = explode('-',$location_slug);
			$args = array (
					'theme_location'=>$location_slug,
					'depth'=>0,
					'menu_id' =>$location_array[0].'_menu_con',
					'container' =>'nav',
					'container_id' =>$location_array[0].'_nav_con',
					'echo' => false,
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