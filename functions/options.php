<?php
/**
 * UC options fields and navigation
 *
 * @package Anonymous theme
 * @author Makiomar
 * @link http://makiomar.com
 */

if(!class_exists('ANONY_Options_Model')) return;
add_action( 'init', function(){
	if(get_option('Anouc_Options')) $anoucOptions = ANONY_Options_Model::get_instance('Anouc_Options');

// Navigation elements
$options_nav = array(
	// General --------------------------------------------
	'uc-pages' => array(
		'title' => esc_html__('User pages', ANONY_UC_TEXTDOM),
		'sections' => array('uc-pages'),
	),
);

$anoucsections['uc-pages']= array(
		'title' => esc_html__('User pages', ANONY_UC_TEXTDOM),
		'icon' => 'x',
		'fields' => array(
						array(
							'id'      => 'account_page',
							'title'   => esc_html__('Account page', ANONY_UC_TEXTDOM),
							'type'    => 'select',
							'options' => ANONY_POST_HELP::queryPostTypeSimple('page'),
							'default' => get_page_by_path('anony-account')->ID,
							'validate'=> 'multiple_options',
							
						),

						array(
							'id'      => 'login_page',
							'title'   => esc_html__('Login page', ANONY_UC_TEXTDOM),
							'type'    => 'select',
							'options' => ANONY_POST_HELP::queryPostTypeSimple('page'),
							'default' => get_page_by_path('anony-login')->ID,
							'validate'=> 'multiple_options',
							
						),

						array(
							'id'      => 'register_page',
							'title'   => esc_html__('Registeration page', ANONY_UC_TEXTDOM),
							'type'    => 'select',
							'options' => ANONY_POST_HELP::queryPostTypeSimple('page'),
							'default' => get_page_by_path('anony-register')->ID,
							'validate'=> 'multiple_options',
							
						),

						array(
							'id'      => 'forget_password_page',
							'title'   => esc_html__('Forget password page', ANONY_UC_TEXTDOM),
							'type'    => 'select',
							'options' => ANONY_POST_HELP::queryPostTypeSimple('page'),
							'default' => get_page_by_path('anony-password-lost')->ID,
							'validate'=> 'multiple_options',
							
						),

						array(
							'id'      => 'reset_password_page',
							'title'   => esc_html__('Reset password page', ANONY_UC_TEXTDOM),
							'type'    => 'select',
							'options' => ANONY_POST_HELP::queryPostTypeSimple('page'),
							'default' => get_page_by_path('anony-password-reset')->ID,
							'validate'=> 'multiple_options',
							
						),						
					)
);


$anoucOptionsPage['opt_name'] = 'Anouc_Options';		
$anoucOptionsPage['menu_title'] = esc_html__('User control', ANONY_UC_TEXTDOM);
$anoucOptionsPage['page_title'] = esc_html__('User control', ANONY_UC_TEXTDOM);
$anoucOptionsPage['menu_slug'] = 'Anouc_Options';
$anoucOptionsPage['page_cap'] = 'manage_options';
$anoucOptionsPage['icon_url'] = 'dashicons-admin-users';
$anoucOptionsPage['page_position'] = 100;
$anoucOptionsPage['page_type'] = 'menu';



$Anouc_Options_Page = new ANONY_Theme_Settings( $options_nav, $anoucsections, [], $anoucOptionsPage);

} );
/**
 * User control multilingual options
 * @return array of option group names
 */
add_filter( 'anony_wpml_multilingual_options', function($options){
	$options[] = 'Anouc_Options';
	
	return $options;
} );

