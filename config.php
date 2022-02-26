<?php
/**
 * User control configuration
 *
 * @package User control plugin
 * @author Makiomar
 * @link http://makiomar.com
 */
 
 if( !defined( 'ABSPATH' ) ) die( 'What are you trying to do?' );

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

//Classes path
define('ANONY_UC', wp_normalize_path( ANONY_UC_PATH.'classes/' ));

add_action( 'init', function(){
	//Login page slug
	define('ANONY_LOGIN', anony_uc_page_slug('anony-login', 'login_page'));

	//Account page
	define('ANONY_ACCOUNT', anony_uc_page_slug('account-form', 'account_page'));

	//Registration page slug
	define('ANONY_REG', anony_uc_page_slug('anony-register', 'register_page'));
	 
	//Lost password page slug
	define('ANONY_LOST', anony_uc_page_slug('anony-password-lost', 'forget_password_page'));

	//reset password page slug
	define('ANONY_RESET', anony_uc_page_slug('anony-password-reset', 'reset_password_page'));
} );

//reset password page slug
define('ANONY_UC_ERRORS', serialize(
    [
    
    'email_exists' => esc_html__('E-mail already exists.', ANONY_UC_TEXTDOM),
    'username_exists' => esc_html__('Username already exists.', ANONY_UC_TEXTDOM),
    'no_crids' => esc_html__('Either email or username is not set.', ANONY_UC_TEXTDOM),
    'insertion_faild' => esc_html__('Account insertion failed.', ANONY_UC_TEXTDOM),
    'creation_faild' => esc_html__('Account creation failed.', ANONY_UC_TEXTDOM),
    'email_not_changed' => esc_html__('E-mail hasn\'t been changed.', ANONY_UC_TEXTDOM),
    'username_not_changed' => esc_html__('Username hasn\'t been changed.', ANONY_UC_TEXTDOM),
    'password_not_changed' => esc_html__('Password hasn\'t been changed.', ANONY_UC_TEXTDOM)
    
    ]
    ));

//

/**
 * Holds a JSON encoded array of all pathes to classes folders
 * @const
 */
define('ANONY_UC_AUTOLOADS' ,wp_json_encode(array(ANONY_UC)));

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
			foreach(json_decode( ANONY_UC_AUTOLOADS ) as $path){

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