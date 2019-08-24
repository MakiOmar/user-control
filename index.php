<?php

require_once('config.php');

$user_control_login_pages_plugin = new ANONY__User_Control();
register_activation_hook( __FILE__, array( 'ANONY__User_Control', 'control_pages' ) );

function smpg_user_main($location_slug){
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