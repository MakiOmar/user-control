<?php

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

        	$links = 
				[
					'login' => 
						[
							'title' => esc_html__( 'Log in' ),
							'url'   => '#ucntrllogin#',
						],
					'logout' => 
						[
							'title' => esc_html__( 'Log out' ),
							'url'   => '#ucntrllogout#',
						],
					'register' => 
						[
							'title' => esc_html__( 'Register' ),
							'url'   => '#ucntrlregister#',
						],
					'login|logout' => 
						[
							'title' => esc_html__( 'Log in' ) . '|' . esc_html__( 'Log out' ),
							'url'   => '#ucntrlloginout#',
						],
				];

        	?>
        	<div id="posttype-cntrl-user" class="posttypediv">
        		<div id="tabs-panel-cntrl-user" class="tabs-panel tabs-panel-active">
        			<ul id ="cntrl-user-checklist" class="categorychecklist form-no-clear">
        				<?php

        					echo $this->anony_render_nav_links($links);

        				 ?>
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

        /**
		 * Renders user control links for nav menu meta box
		 * @param array $links 
		 * @return string Rendered list
		 */
		public function anony_render_nav_links($links){

			$html = '';
			$counter = 0;
			foreach ($links as $link => $data) {
				$counter++;

				$html .= '<li>';

					$html .= '<label class="menu-item-title">';

					$html .= '<input type="checkbox" class="menu-item-checkbox" name="menu-item[-'.$counter.'][menu-item-object-id]" value="-'.$counter.'">'.$data['title'].'</label>';

					$html .= '<input type="hidden" class="menu-item-type" name="menu-item[-'.$counter.'][menu-item-type]" value="custom">';

					$html .= '<input type="hidden" class="menu-item-title" name="menu-item[-'.$counter.'][menu-item-title]" value="'.$data['title'].'">';

					$html .= '<input type="hidden" class="menu-item-url" name="menu-item[-'.$counter.'][menu-item-url]" value="'.$data['url'].'">';

					$html .= '<input type="hidden" class="menu-item-classes" name="menu-item[-'.$counter.'][menu-item-classes]" value="cntrl-'.str_replace(' ','-',$data['title']).'-pop">';

				$html .= '</li>';
			}

			return $html;
		}
		
	}
}
