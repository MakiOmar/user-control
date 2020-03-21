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

/**
 * Holds class prefix
 * @const
 */
define('ANONY_UC_PREFIX', 'ANONY_' );

//Text domain
define('ANONY_UC_TEXTDOM', 'user-control'); 

//Plugin path
define('ANONY_UC_PATH', plugin_dir_path( __FILE__ )); 

//Plugin URI
define('ANONY_UC_URI', plugin_dir_url( __FILE__ ));

//Menu slug
define('ANONY_MENU', 'anony-user-control');

//Login page slug
define('ANONY_LOGIN', 'anony-login');

//Registration page slug
define('ANONY_REG', 'anony-register');
 
//Lost password page slug
define('ANONY_LOST', 'anony-password-lost');

//Lost password page slug
define('ANONY_UC', wp_normalize_path( ANONY_UC_PATH.'classes/' ));

//

/**
 * Holds a serialized array of all pathes to classes folders
 * @const
 */
define('ANONY_UC_AUTOLOADS' ,serialize(array(ANONY_UC)));

/*
*Classes Auto loader
*/
spl_autoload_register( 'anony_UC_autoloader' );

/**
 * User control classes autoloading.
 * **Description: ** Any class should be writtn in the structure of ANONY_{class_name}<br/>
 * **Note: ** ANONY is optional prefixes, but any prefix should be followed by double underscores, so can get class file name. For example: a class name of XYZ__Class_name is located in file class-name.php. file name should use only dashes(no underscores)
 * @param  string $class_name
 * @return void
 */
function anony_UC_autoloader( $class_name ) {

	if ( false !== strpos( $class_name, '__' )) {

		$class_name = preg_replace('/\w+__/', '', strtolower($class_name));

		$class_name  = str_replace('_', '-', $class_name);

		if(file_exists($class_name)){

			require_once($class_name);
		}else{
			foreach(unserialize( ANONY_UC_AUTOLOADS ) as $path){

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