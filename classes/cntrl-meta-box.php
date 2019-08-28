<?php
if( !defined( 'ABSPATH' ) )
	die( 'What are you trying to do?' );

if( ! class_exists( 'ANONY__Cntrl_Meta_Box' )){
	
	class ANONY__Cntrl_Meta_Box{

		public function add_nav_menu_meta_boxes() {

        	add_meta_box(
        		'anony_cntrl_link',
        		__('User control'),
        		array( $this, 'nav_menu_link'),
        		'nav-menus',
        		'side',
        		'low'
        	);
        }
        
        public function nav_menu_link() {

        	global $nav_menu_selected_id;

        	$links = 
				[
					'#ucntrllogin#'     => esc_html__( 'Log in' ),
					'#ucntrllogout#'    => esc_html__( 'Log out' ),
					'#ucntrlregister#'  => esc_html__( 'Register' ),
					'#ucntrlloginout#'  => esc_html__( 'Log in' ) . '|' . esc_html__( 'Log out' ),

				];

				$links_obj = array();

				foreach ( $links as $value => $title ) {
					$links_obj[ $title ] 				= new ANONY__ucntrlogItems();
					$links_obj[ $title ]->object_id		= esc_attr( $value );
					$links_obj[ $title ]->title			= esc_attr( $title );
					$links_obj[ $title ]->url			= esc_attr( $value );
				}

				$walker = new Walker_Nav_Menu_Checklist( array() );

        	?>
        	<div id="posttype-cntrl-user" class="posttypediv">
        		<div id="tabs-panel-cntrl-user" class="tabs-panel tabs-panel-active">
        			<ul id ="cntrl-user-checklist" class="categorychecklist form-no-clear">
        				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', $links_obj ), 0, (object) array( 'walker' => $walker ) ); ?>
        			</ul>
        		</div>
        		<p class="button-controls">
        			<span class="list-controls">
        				<a href="/wordpress/wp-admin/nav-menus.php?page-tab=all&amp;selectall=1#posttype-cntrl-user" class="select-all"><?php esc_html_e( 'Select all' ) ?></a>
        			</span>
        			<span class="add-to-menu">
        				<input type="submit" class="button-secondary submit-add-to-menu right" value="Add to Menu" name="add-post-type-menu-item" id="submit-posttype-cntrl-user">
        				<span class="spinner"></span>
        			</span>
        		</p>
        	</div>
        <?php }
	}
}
