<?php
/**
 * User control configuration
 *
 * @package User control plugin
 * @author Makiomar
 * @link http://makiomar.com
 */
 
 if( !defined( 'ABSPATH' ) )
	die( 'What are you trying to do?' );

//Text domain
define('ANONY_TEXTDOM', 'user-control'); 

//Plugin path
define('ANONY_CNTRL_PATH', plugin_dir_path( __FILE__ )); 

//Plugin URI
define('ANONY_CNTRL_URI', plugin_dir_url( __FILE__ ));

//Login page slug
define('ANONY_LOGIN', 'member-login');

//Registration page slug
define('ANONY_REG', 'member-register');
 
//Lost password page slug
define('ANONY_LOST', 'member-password-lost');

//Lost password page slug
define('ANONY_CNTRL', wp_normalize_path( plugin_dir_path(__FILE__).'classes/' ));

//

/**
 * Holds a serialized array of all pathes to classes folders
 * @const
 */
define('ANONY_CNTRL_AUTOLOADS' ,serialize(array(ANONY_CNTRL)));

/*
*Classes Auto loader
*/
spl_autoload_register( 'anony_cntrl_autoloader' );

/**
 * User control classes autoloading.
 * **Description: ** Any class should be writtn in the structure of ANONY_{class_name}<br/>
 * **Note: ** ANONY is optional prefixes, but any prefix should be followed by double underscores, so can get class file name. For example: a class name of XYZ__Class_name is located in file class-name.php. file name should use only dashes(no underscores)
 * @param  string $class_name
 * @return void
 */
function anony_cntrl_autoloader( $class_name ) {

	if ( false !== strpos( $class_name, '__' )) {

		$class_name = preg_replace('/\w+__/', '', strtolower($class_name));

		$class_name  = str_replace('_', '-', $class_name);

		if(file_exists($class_name)){

			require_once($class_name);
		}else{
			foreach(unserialize( ANONY_CNTRL_AUTOLOADS ) as $path){

				$class_file = wp_normalize_path($path) .$class_name . '.php';

				if(file_exists($class_file)){

					require_once($class_file);
				}else{

					$class_file = wp_normalize_path($path) .$class_name .'/' .$class_name . '.php';

					if(file_exists($class_file)){

						require_once($class_file);
					}
				}
			}
		}
		
	}
}