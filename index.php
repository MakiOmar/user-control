<?php
/*
Plugin Name: User Control
Plugin URI: https://prosentra.com
Description: Adds a user control interface for login, register and forget password 
Version: 1.0.0
Author: Mohammad Omar
Author URI: https://prosentra.com
Text Domain: user-control
License: GPL2
*/
class User_Control_Login_Plugin {
	function __construct() {
		add_shortcode( 'custom-login-form', array( $this, 'render_login_form' ) );
		add_shortcode( 'custom-register-form', array( $this, 'render_register_form' ) );
		add_shortcode( 'custom-password-lost-form', array( $this, 'render_password_lost_form' ) );
		add_shortcode( 'custom-password-reset-form', array( $this, 'render_password_reset_form' ) );
		add_action( 'login_form_login', array( $this, 'redirect_to_custom_login' ) );
		add_action( 'login_form_register', array( $this, 'redirect_to_custom_register' ) );
		add_action( 'login_form_register', array( $this, 'do_register_user' ) );
		add_action( 'init', array( $this, 'restrict_none_admins'));
		add_filter( 'authenticate', array( $this, 'authenticate_redirect_handling' ), 101, 3 );
		add_action( 'wp_logout', array( $this, 'redirect_after_logout' ) );
		add_filter( 'retrieve_password_message', array( $this, 'replace_retrieve_password_message' ), 10, 4 );
		add_action( 'login_form_rp', array( $this, 'do_password_reset' ) );
		add_action( 'login_form_rp', array( $this, 'redirect_to_custom_password_reset' ) );
		add_action( 'login_form_resetpass', array( $this, 'do_password_reset' ) );
		add_action( 'login_form_resetpass', array( $this, 'redirect_to_custom_password_reset' ) );
		add_action( 'login_form_lostpassword', array( $this, 'do_password_lost' ) );
		add_action( 'login_form_lostpassword', array( $this, 'redirect_to_custom_lostpassword' ) );
		add_action('wp_enqueue_scripts',array( $this, 'smartpage_user_control_scripts'));
		add_action( 'after_setup_theme', array($this, 'smartpage_reg_menus' ) );
		add_filter("wp_nav_menu_users-control_items",array( $this, "add_user_control_menu_pages"),10 , 2);
    }
	public static function plugin_activated() {
	  // Information needed for creating the plugin's pages
	  $page_definitions = array(
				  'member-login' => array(
								'title' => __( 'Sign In', 'usercontrol' ),
								'content' => '[custom-login-form]'
				  ),
				  'member-account' => array(
								   'title' => __( 'Your Account', 'usercontrol' ),
								   'content' => '[account-info]'
				  ),
				  'member-register' => array(
									'title' => __( 'Register', 'usercontrol' ),
									'content' => '[custom-register-form]'
				  ),
					'member-password-lost' => array(
										'title' => __( 'Forgot Your Password?', 'usercontrol' ),
										'content' => '[custom-password-lost-form]'
					  ),
					'member-password-reset' => array(
										'title' => __( 'Pick a New Password', 'usercontrol' ),
										'content' => '[custom-password-reset-form]'
									)
					);

	 foreach ( $page_definitions as $slug => $page ) {
			 // Check that the page doesn't exist already
			$query = new WP_Query( 'pagename=' . $slug );
			if ( ! $query->have_posts() ) {
			   // Add the page using the data from the array above
			   wp_insert_post(
						array(
							 'post_content' => $page['content'],
							 'post_name' => $slug,
							 'post_title' => $page['title'],
							 'post_status' => 'publish',
							 'post_type' => 'page',
							 'ping_status' => 'closed',
							 'comment_status' => 'closed',
						  )
				);
			}
	 }

}
	public function render_login_form( $attributes, $content = null ) {
	 $default_attributes = array( 'show_title' => 'hide', 'redirect' => home_url() );
	 $attributes = shortcode_atts( $default_attributes, $attributes );
	 if ( $attributes['show_title'] == 'show'){
		echo'<h1>'.__( 'Sign In', 'usercontrol' ).'</h1>';
	 }
	$attributes['password_updated'] = isset( $_REQUEST['password'] ) && $_REQUEST['password'] == 'changed';
	 $attributes['lost_password_sent'] = isset( $_REQUEST['checkemail'] ) && $_REQUEST['checkemail'] == 'confirm';
		
	 if ($attributes['redirect'] == $default_attributes['redirect']) {
		$redir = $attributes['redirect'];
	 }else{
		 $redir = home_url('/').$attributes['redirect'];
	 }
	 if (is_user_logged_in()){
		 return "<p>You are already logged in</p>";
	 }else{
		 // Error messages
		$errors = array();
		if ( isset( $_REQUEST['login'] ) ) {
			$error_codes = explode( ',', $_REQUEST['login'] );

			foreach ( $error_codes as $code ) {
				$errors []= $this->show_error_message( $code );
			}
		}
		if(count ($errors) > 0) { ?>
			<ul class="login-errors">
			<?php foreach($errors as $error){?>
				<li><?php echo $error ?></li>
			<?php } ?>
			</ul>
			
		<?php }
	 // Check if user just logged out
		 if ( isset( $_REQUEST['logged_out'] ) && $_REQUEST['logged_out'] == true ) : ?>
			<p class="login-info">
				<?php _e( 'You have signed out. Would you like to sign in again?', 'usercontrol' ); ?>
			</p>
		<?php endif; 
		return $this->get_template_html( 'login_form', $attributes ); 
	 }

}
	private function get_template_html( $template_name, $attributes = null ) {
		if ( ! $attributes ) {
			$attributes = array();
		}
		ob_start();
		require( 'templates/' . $template_name . '.php');
		$html = ob_get_contents();
		ob_end_clean();
		return $html;
	}
	private function redirect_users_to($p){
		if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( user_can( $user, 'manage_options' ) ) {
					wp_redirect( admin_url() );
				} else {
					wp_redirect( home_url( 'member-account' ) );
				}
				exit;
			}
			// The rest are redirected to the login page
			$login_url = home_url( $p );
			wp_redirect( $login_url );
			exit;
		}
		
	}
	public function redirect_to_custom_login() {
		$this->redirect_users_to('member-login');
	}
	public function redirect_to_custom_register() {
		$this->redirect_users_to('member-register');
	}
	public function redirect_to_custom_lostpassword() {
		$this->redirect_users_to('member-password-lost');
	}
	public function restrict_none_admins(){
		if ( is_admin() && ! current_user_can( 'administrator' ) &&
			! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			if ( is_user_logged_in() ) {
				wp_redirect( home_url('member-account') );
			}else{
				wp_redirect( home_url('member-login') );
			}
			exit;
		}
	}
	public function authenticate_redirect_handling( $user, $username, $password ) {
    // Check if the earlier authenticate filter (most likely, 
    // the default WordPress authentication) functions have found errors
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
        if ( is_wp_error( $user ) ) {
            $error_codes = join( ',', $user->get_error_codes() );
 
            $login_url = home_url( 'member-login' );
            $login_url = add_query_arg( 'login', $error_codes, $login_url );
 
            wp_redirect( $login_url );
            exit;
        }
    }
 
    return $user;
}
	private function show_error_message( $error_code ) {
		switch ( $error_code ) {
			case 'empty_username':
				return __( 'You do have an email address, right?', 'usercontrol' );
			case 'empty_password':
				return __( 'You need to enter a password to login.', 'usercontrol' );
			case 'invalid_username':
				return __("We don't have any users with that username. Maybe you used a different one when signing up?",'usercontrol');
			case 'incorrect_password':
				$err = __("The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",'usercontrol');
				return sprintf( $err, wp_lostpassword_url() );
				// Registration errors
			case 'email':
				return __( 'The email address you entered is not valid.', 'usercontrol' );

			case 'email_exists':
				return __( 'An account exists with this email address.', 'usercontrol' );

			case 'closed':
				return __( 'Registering new users is currently not allowed.', 'usercontrol' );
			// Lost password
 
			case 'empty_username':
				return __( 'You need to enter your email address to continue.', 'usercontrol' );

			case 'invalid_email':
			case 'invalidcombo':
				return __( 'There are no users registered with this email address.', 'usercontrol' );
case 'expiredkey':
case 'invalidkey':
	return __( 'The password reset link you used is not valid anymore.', 'usercontrol' );

case 'password_reset_mismatch':
	return __( "The two passwords you entered don't match.", 'usercontrol' );

case 'password_reset_empty':
	return __( "Sorry, we don't accept empty passwords.", 'usercontrol' );
				default:
				break;
			}
		return __( 'An unknown error occurred. Please try again later.', 'usercontrol' );
	}
	public function redirect_after_logout() {
		$redirect_url = home_url( 'member-login?logged_out=true' );
		wp_redirect( $redirect_url );
		exit;
	}
	public function render_register_form( $attributes, $content = null ) {
		// Parse shortcode attributes
		$default_attributes = array( 'show_title' => false );
		$attributes = shortcode_atts( $default_attributes, $attributes );

		if ( is_user_logged_in() ) {
			return __( 'You are already signed in.', 'usercontrol' );
		} elseif ( ! get_option( 'users_can_register' ) ) {
			return __( 'Registering new users is currently not allowed.', 'usercontrol' );
		} else {
			$errors = array();
			if ( isset( $_REQUEST['register-errors'] ) ) {
				$error_codes = explode( ',', $_REQUEST['register-errors'] );

				foreach ( $error_codes as $error_code ) {
					$errors []= $this->show_error_message( $error_code );
				}
			}
			if(count ($errors) > 0) { ?>
			<ul class="registration-errors">
			<?php foreach($errors as $error){?>
				<li><?php echo $error ?></li>
			<?php } ?>
			</ul>
			
		<?php }
			if(isset( $_REQUEST['registered'] )) { ?>
				<p class="registeration-info">
					<?php printf(__( 'You have successfully registered to <strong>%1s</strong>. We have emailed your password to the email address you entered.And you can login from <a href="%2s">Here</a>', 'usercontrol' ), get_bloginfo( 'name'), wp_login_url());  ?>
    			</p>
			<?php }
			return $this->get_template_html( 'register_form', $attributes );
		}
	}
	private function register_user( $email, $user_name ) {
		$errors = new WP_Error();
		if ( ! is_email( $email ) ) {
			$errors->add( 'email', $this->show_error_message( 'email' ) );
			return $errors;
		}
		if ( username_exists( $email ) || email_exists( $email ) ) {
			$errors->add( 'email_exists', $this->show_error_message( 'email_exists') );
			return $errors;
		}
		$password = wp_generate_password(8);
		$user_data = array(
			'user_login'    => $user_name,
			'user_email'    => $email,
			'user_pass'     => $password,
			'nickname'      => $user_name,
		);
		$user_id = wp_insert_user( $user_data );
		$user = get_userdata($user_id);
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$subject = sprintf(__('Your login credintals for %s','usercontrol'), $blogname);
		$message= sprintf(__('Thank you %1s for registering to our blog %2s','usercontrol'), $user->nickname, $blogname). "\r\n\r\n";
		$message.=__('You login information is:','usercontrol') . "\r\n\r\n";
		$message.= sprintf(__('Username: %s','usercontrol'), $user->user_login) . "\r\n\r\n";
		$message.=sprintf(__('Password: %s','usercontrol'), $password) . "\r\n";
		$message.= __('To log into the admin area please us the following address ','usercontrol') .home_url('member-login') . "\r\n";
		$headers ='';
		
		wp_mail($email,$subject,$message,$headers);
		return $user_id;
	}
	public function do_register_user() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
				$redirect_url = home_url( 'member-register' );
				if(is_user_logged_in()){
					$redirect_url = home_url( 'member-register' );
					wp_redirect( $redirect_url );
					exit;
				}

				if ( ! get_option( 'users_can_register' ) ) {
					// Registration closed, display error
					$redirect_url = add_query_arg( 'register-errors', 'closed', $redirect_url );
				} else {
					$email = $_POST['email'];
					$user_name = sanitize_text_field( $_POST['user_name'] );

					$result = $this->register_user( $email, $user_name );
					$errors = new WP_Error();
					if ( is_wp_error( $result ) ) {
						// Parse errors into a string and append as parameter to redirect
						$errors = join( ',', $result->get_error_codes() );
						$redirect_url = add_query_arg( 'register-errors', $errors, $redirect_url );
					} else {
						// Success, redirect to login page.
						$redirect_url = home_url( 'member-register' );
						$redirect_url = add_query_arg( 'registered', $email, $redirect_url );
					}
				}

				wp_redirect( $redirect_url );
				exit;
			}
	}
	public function render_password_lost_form( $attributes, $content = null ) {
		$default_attributes = array( 'show_title' => false );
		$attributes = shortcode_atts( $default_attributes, $attributes );
		$attributes['errors'] = array();
		if ( isset( $_REQUEST['errors'] ) ) {
			$error_codes = explode( ',', $_REQUEST['errors'] );

			foreach ( $error_codes as $error_code ) {
				$attributes['errors'] []= $this->show_error_message( $error_code );
			}
		}

		if ( is_user_logged_in() ) {
			return __( 'You are already signed in.', 'usercontrol' );
		} else {
			if ( count( $attributes['errors'] ) > 0 ) : 
			foreach ( $attributes['errors'] as $error ) : ?>
				<p><?php echo $error; ?></p>
			<?php endforeach; 
			endif; 
			return $this->get_template_html( 'password_lost_form', $attributes );
		}
	}
	public function do_password_lost() {
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		$errors = retrieve_password();
		if ( is_wp_error( $errors ) ) {
			// Errors found
			$redirect_url = home_url( 'member-password-lost' );
			$redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
		} else {
			// Email sent
			$redirect_url = home_url( 'member-login' );
			$redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
		}

		wp_redirect( $redirect_url );
		exit;
	}
}
	public function replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {
		$msg  = __( 'Hello!', 'usercontrol' ) . "\r\n\r\n";
		$msg .= sprintf( __( 'You asked us to reset your password for your account using the email address %s.', 'usercontrol' ), $user_login ) . "\r\n\r\n";
		$msg .= __( "If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", 'usercontrol' ) . "\r\n\r\n";
		$msg .= __( 'To reset your password, visit the following address:', 'usercontrol' ) . "\r\n\r\n";
		$msg .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n\r\n";
		$msg .= __( 'Thanks!', 'usercontrol' ) . "\r\n";

		return $msg;
	}
	public function redirect_to_custom_password_reset() {
    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
        // Verify key / login combo
        $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
        if ( ! $user || is_wp_error( $user ) ) {
            if ( $user && $user->get_error_code() === 'expired_key' ) {
                wp_redirect( home_url( 'member-login?login=expiredkey' ) );
            } else {
                wp_redirect( home_url( 'member-login?login=invalidkey' ) );
            }
            exit;
        }
 
        $redirect_url = home_url( 'member-password-reset' );
        $redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );
        $redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );
 
        wp_redirect( $redirect_url );
        exit;
    }
}
	public function render_password_reset_form( $attributes, $content = null ) {
	$default_attributes = array( 'show_title' => false );
	$attributes = shortcode_atts( $default_attributes, $attributes );
	if ( is_user_logged_in() ) {
		return __( 'You are already signed in.', 'usercontrol' );
	} else {
		if ( isset( $_REQUEST['login'] ) && isset( $_REQUEST['key'] ) ) {
			$attributes['login'] = $_REQUEST['login'];
			$attributes['key'] = $_REQUEST['key'];
			$errors = array();
			if ( isset( $_REQUEST['error'] ) ) {
				$error_codes = explode( ',', $_REQUEST['error'] );

				foreach ( $error_codes as $code ) {
					$errors []= $this->show_error_message( $code );
				}
			}
			$attributes['errors'] = $errors;
			if ( count( $attributes['errors'] ) > 0 ) :
				foreach ( $attributes['errors'] as $error ) : ?>
					<p>
						<?php echo $error; ?>
					</p>
				<?php endforeach;
            endif; 
			return $this->get_template_html( 'password_reset_form', $attributes );
		} else {
			return __( 'Invalid password reset link.', 'usercontrol' );
		}
	}
}
	public function do_password_reset() {
	if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
		$rp_key = $_REQUEST['rp_key'];
		$rp_login = $_REQUEST['rp_login'];

		$user = check_password_reset_key( $rp_key, $rp_login );

		if ( ! $user || is_wp_error( $user ) ) {
			if ( $user && $user->get_error_code() === 'expired_key' ) {
				wp_redirect( home_url( 'member-login?login=expiredkey' ) );
			} else {
				wp_redirect( home_url( 'member-login?login=invalidkey' ) );
			}
			exit;
		}

		if ( isset( $_POST['pass1'] ) ) {
			if ( $_POST['pass1'] != $_POST['pass2'] ) {
				// Passwords don't match
				$redirect_url = home_url( 'member-password-reset' );

				$redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
				$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
				$redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );

				wp_redirect( $redirect_url );
				exit;
			}

			if ( empty( $_POST['pass1'] ) ) {
				// Password is empty
				$redirect_url = home_url( 'member-password-reset' );

				$redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
				$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
				$redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );

				wp_redirect( $redirect_url );
				exit;
			}

			// Parameter checks OK, reset password
			reset_password( $user, $_POST['pass1'] );
			wp_redirect( home_url( 'member-login?password=changed' ) );
		} else {
			echo "Invalid request.";
		}

		exit;
	}
}
	public function smartpage_user_control_scripts() {
		$script = 'user-control';
		wp_enqueue_style( $script , plugin_dir_url( __FILE__ ).('assets/css/'.$script.'.css') ,'', filemtime(plugin_dir_path( __FILE__ ).('assets/css/'.$script.'.css')));
		wp_enqueue_script( $script , plugin_dir_url( __FILE__ ).('assets/js/'.$script.'.js') ,array('jquery'),filemtime(plugin_dir_path( __FILE__ ).('assets/js/'.$script.'.js')),true);
}

	public function smartpage_reg_menus(){
		$menus= array(
			'users-control'=>__('User control menu','user-control'),
		); 
		foreach($menus as $name => $description){
			if(!has_nav_menu($name)){
				register_nav_menu($name, $description);
			}
		}
		$menu_name = 'Users control';
		$menu_exists = wp_get_nav_menu_object( $menu_name );
		// If it doesn't exist, let's create it.
		if( !$menu_exists){
			$menu_id = wp_create_nav_menu($menu_name);
			$locations = get_theme_mod('nav_menu_locations');
			$locations[sanitize_title($menu_name)] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
}
	public function add_user_control_menu_pages($item , $args){
		$menu = get_term_by('slug','users-control','nav_menu');
		if($menu->count === 0){
			$item .='<li><span><i class="fa fa-user-circle"></i><strong>';
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				//print_r($current_user);
				if(!empty($current_user->user_firstname)){
					$item .= $current_user->user_firstname ;
				}else{
					$item .= __('Welcome','user-control').'&nbsp;'.$current_user->user_nicename;
				}
				
			} else {
				$item .= __('Login','user-control');
			}
			$item .= '</strong><i class="fa fa-angle-down"></i></span></li>';
			$item .= '<li><ul class = "user-dropdown">';
			$logged_menu_pages = array('Your Account');
			$none_logged_menu_pages = array('Sign in','Register');
			if ( is_user_logged_in()) {
					foreach($logged_menu_pages as $page){

							$post_obj = get_page_by_title($page);
							$page_url = get_permalink($post_obj->ID);
							$item .='<li>';
							$item .= '<a class="users-menu" href="'.$page_url.'">';
							$item .=$page;
							$item .= '</a><li>';
					}
				}else{
					foreach($none_logged_menu_pages as $page){

							$post_obj = get_page_by_title($page);
							$page_url = get_permalink($post_obj->ID);
							$item .='<li>';
							$item .= '<a class="users-menu" href="'.$page_url.'">';
							$item .=$page;
							$item .= '</a><li>';
					}
			}
			$item .= '</ul></li>';
			return $item;
		}
		return  $item;	
}
}

$user_control_login_pages_plugin = new User_Control_Login_Plugin();
register_activation_hook( __FILE__, array( 'User_Control_Login_Plugin', 'plugin_activated' ) );