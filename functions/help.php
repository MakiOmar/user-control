<?php
/**
 * UC helpers
 *
 * @package Anonymous theme
 * @author Makiomar
 * @link http://makiomar.com
 */
if(!class_exists('ANONY_Options_Model')) return;
if(get_option('Anouc_Options')){
	$anoucOptions = ANONY_Options_Model::get_instance('Anouc_Options');
}

function anony_uc_page_slug($slug, $option){
	
	$anoucOptions = ANONY_Options_Model::get_instance('Anouc_Options');
	
	$post_obj = get_page_by_path( $slug );
	
	if(is_null($post_obj)){
		if(isset($anoucOptions->$option) && !empty($anoucOptions->$option)){
			$post_obj = get_post(intval($anoucOptions->$option));
		}
	}
	
	
	if(is_null($post_obj)) return $slug;
	
	if(!ANONY_Wpml_Help::is_active()) return $slug;
	
	$translated_page_id = icl_object_id(intval($post_obj->ID), 'page', false, ANONY_Wpml_Help::gat_active_lang());
	
	if(!is_null($translated_page_id)){
		$post_obj = get_post( $translated_page_id );
		
		return $post_obj->post_name;
	}
	
	
}

/**
 * Add custom roles.
 * 
 */ 
add_action( 'init', function () {

	$custom_roles = apply_filters( 'uc_custom_roles', array() );

	if ( empty( $custom_roles ) ) {
		return;
	}

	foreach( $custom_roles as $custom_roles ){

		if( !isset( $custom_roles[ 'role' ] ) || !isset( $custom_roles[ 'display_name' ] ) ){
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Custom role is missing required data: role/display_name' );
			}
			continue;
		}

		if ( !isset( $custom_roles[ 'capabilities' ] ) ) {
			$custom_roles[ 'capabilities' ] = array();
		}

		if(is_null( get_role( $custom_roles[ 'role' ] ) )){
	        add_role( $custom_roles[ 'role' ],  $custom_roles[ 'display_name' ], $custom_roles[ 'capabilities' ]  );
	    }
	}


} );