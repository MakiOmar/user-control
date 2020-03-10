<?php
if( !defined( 'ABSPATH' ) )
	die( 'What are you trying to do?' );
/**
 * Menu items object
 */
class ANONY__ucntrlItems {
	public $db_id = 0;
	public $object = 'ucntritems';
	public $object_id;
	public $menu_item_parent = 0;
	public $type = 'custom';
	public $title;
	public $url;
	public $target = '';
	public $attr_title = '';
	public $classes = array();
	public $xfn = '';
}