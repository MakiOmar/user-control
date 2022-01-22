<?php
/**
 * UC helpers
 *
 * @package Anonymous theme
 * @author Makiomar
 * @link http://makiomar.com
 */
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
	
	if(!ANONY_WPML_HELP::isActive()) return $slug;
	
	$translated_page_id = icl_object_id(intval($post_obj->ID), 'page', false, ANONY_WPML_HELP::gatActiveLang());
	
	if(!is_null($translated_page_id)){
		$post_obj = get_post( $translated_page_id );
		
		return $post_obj->post_name;
	}
	
	
}