<?php

class ANONY__User_Control {

	/**
	 * @var array User control pagses IDs
	 */
	public static $pages_ids = array();

	/**
	 * @var array User control pagses data
	 */
	public static $page_definitions;

	public function __construct() {
		//Set shortcodes
		$this->shortcodes = 
    	[
    		'anony_login', 
    		'anony_register', 
    		'anony_password_lost', 
    		'anony_password_reset',
    		'account_info',
    	];


    	self::$page_definitions = array(
			'member-login' => array(
				'title' => esc_html__( 'Sign In', 'usercontrol' ),
				'content' => '[anony_login]'
			),

			'member-account' => array(
			   'title' => esc_html__( 'Your Account', 'usercontrol' ),
			   'content' => '[account_info]'
			),

			ANONY_REG => array(
				'title' => esc_html__( 'Register', 'usercontrol' ),
				'content' => '[anony_register]'
			),

			'member-password-lost' => array(
				'title' => esc_html__( 'Forgot Your Password?', 'usercontrol' ),
				'content' => '[anony_password_lost]'
			),

			'member-password-reset' => array(
				'title' => esc_html__( 'Pick a New Password', 'usercontrol' ),
				'content' => '[anony_password_reset]'
			)
		);


		//Add user control shortcodes
		$this->shortcodes();

		//User control redirects
		$this->redirects();

		//User control form actions
		$this->form_actions();
		
		
		add_filter( 'retrieve_password_message', array( $this, 'replace_retrieve_password_message' ), 10, 4 );

		
		
		add_action('wp_enqueue_scripts',array( $this, 'enqueue_scripts'));

		add_action( 'after_setup_theme', array($this, 'user_nav_menu' ) );

		add_filter("wp_nav_menu_user-control_items",array( $this, "add_user_control_menu_pages"),10 , 2);
    }

    /**
     * Add user control shortcodes
     */
    public function shortcodes(){
    	foreach ($this->shortcodes as $shcode) {
    		add_shortcode( $shcode, array( $this, $shcode . '_form' ) );
    	}
    }

    /**
     * Manage redirects
     */
    public function redirects(){

    	/*------------Login redirect---------------------------------------------------------------*/
    	$login_redirect = function(){
    		$this->redirect_to(ANONY_LOGIN);
    	};
    	add_action( 'login_form_login', $login_redirect );

    	/*------------Registration redirect--------------------------------------------------------*/
    	$reg_redirect = function(){
    		$this->redirect_to(ANONY_REG);
    	};
		add_action( 'login_form_register', $reg_redirect );

		/*------------Lost password redirect-------------------------------------------------------*/
    	$lost_redirect = function(){
    		$this->redirect_to(ANONY_LOST);
    	};
    	add_action( 'login_form_lostpassword', $lost_redirect );
		
		/*-------------------Logout redirect--------------------------------------------------------*/
		$logout_redirect = function(){
			wp_redirect( home_url( ANONY_LOGIN.'?logged_out=true' ) );
			exit;
		};	
    	add_action( 'wp_logout', $logout_redirect );

    	/*------------Reset password redirect-------------------------------------------------------*/
    	add_action( 'login_form_rp', array( $this, 'redirect_password_reset' ) );

    	add_action( 'login_form_resetpass', array( $this, 'redirect_password_reset' ) );

    	/*------------authenticate redirect----------------------------------------------------------*/
    	add_filter( 'authenticate', array( $this, 'authenticate_redirect_handling' ), 101, 3 );

    	/*------------Prevent non-admins to access dashboard----------------------------------------------------------*/
    	add_action( 'init', array( $this, 'restrict_none_admins'));
    }

    /**
     * Redirect to password reset
     */
    public function redirect_password_reset() {
	    if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
	        // Verify key / login combo
	        $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );

	        if ( ! $user || is_wp_error( $user ) ) {
	            if ( $user && $user->get_error_code() === 'expired_key' ) {
	                wp_redirect( home_url( ANONY_LOGIN.'?login=expiredkey' ) );
	            } else {
	                wp_redirect( home_url( ANONY_LOGIN.'?login=invalidkey' ) );
	            }
	            exit;
	        }
	 
	        $redirect_url = home_url( ANONY_LOST );
	        $redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );
	        $redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );
	 
	        wp_redirect( $redirect_url );
	        exit;
	    }
	}

    /**
     * Manage form actions
     * @return type
     */
    public function form_actions(){

    	add_action( 'login_form_register', array( $this, 'do_register_user' ) );

    	add_action( 'login_form_rp', array( $this, 'do_password_reset' ) );
		
		add_action( 'login_form_resetpass', array( $this, 'do_password_reset' ) );
		
		add_action( 'login_form_lostpassword', array( $this, 'do_password_lost' ) );
    }

    /**
     * Delete pages by IDs.
     * 
     * This method will be called upon plugin deactivation
     */
    public static function delete_pages(){
    	//Pages IDs are stored as an option on install
    	$pages_ids = get_option( 'anony_pages_ids');

    	if(is_array($pages_ids)){

    		foreach ($pages_ids as $id) {

				wp_delete_post( $id, true );
			}
    	}

    	delete_option( 'anony_pages_ids' );
	}

    /**
     * Inserts user control pages with the required shortcodes
     */
 	public static function insert_pages() {
		
		foreach ( self::$page_definitions as $slug => $page ) {
			 // Check that the page doesn't exist already
			$query = new WP_Query( 
				[
					'pagename' => $slug,
				] 
			);
			if ( ! $query->have_posts() ) {
			   // Add the page using the data from the array above
			   $user_id = wp_insert_post(
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
				//Store pages IDs for further use
			   if($user_id !== 0 || !is_wp_error( $user_id )){
			   		self::$pages_ids[]= $user_id;
			   }
			}else{

				foreach (self::$page_definitions as $slug => $info) {

					$page = get_page_by_path($page_slug);

					if ($page) self::$pages_ids[] = $page->ID;
				}
			}
		}

		//Add pages IDs to options for further use
		if(!empty(self::$pages_ids)) update_option( 'anony_pages_ids',self::$pages_ids );
			
	}

	/**
	 * Render actions errors
	 * @param  string $action action name
	 * @return string        Login errors HTML
	 */
	public function action_errors($action){
		// Error messages
		$login_errors = array();

		if ( isset( $_REQUEST[$action] ) ) {
			$error_codes = explode( ',', $_REQUEST[$action] );

			foreach ( $error_codes as $code ) {
				$login_errors []= $this->show_error_message( $code );
			}
		}
		if(count ($login_errors) > 0) { 
			$html = '<ul class="user-errors">';
			foreach($login_errors as $error){
				$html .= '<li>'.$error.'</li>';
			} 
			$html .= '</ul>';
			return $html;
		}
	}

	/**
	 * Render logout message
	 * @return string
	 */
	public function logout_message(){
		$html = '';
		// Check if user just logged out
		if ( isset( $_REQUEST['logged_out'] ) && $_REQUEST['logged_out'] == true ){
			$html .= '<p class="login-info">';
			$html .= esc_html__( 'You have signed out. Would you like to sign in again?', 'usercontrol' );
			$html .= '</p>';
		}

		return $html;
	}

	/**------------------------------------------------------------------
	 * Rendering
	 *-----------------------------------------------------------------*/

	/**
	 * Login form shortcode function
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content 
	 * @return string             Rendered login form
	 */
	public function anony_login_form( $attributes, $content = null ) {

		$default_attributes = array( 'show_title' => 'hide', 'redirect' => home_url() );

		$attributes = shortcode_atts( $default_attributes, $attributes );

		$attributes['password_updated']   = isset( $_REQUEST['password'] ) && $_REQUEST['password'] == 'changed';

		$attributes['lost_password_sent'] = isset( $_REQUEST['checkemail'] ) && $_REQUEST['checkemail'] == 'confirm';

		if ($attributes['redirect'] == $default_attributes['redirect']){

			$redir = $attributes['redirect'];
		}else{

			$redir = home_url($attributes['redirect']);
		}

		$html = '';

		if ( $attributes['show_title'] == 'show') $html .= '<h1>'.esc_html__( 'Sign In', 'usercontrol' ).'</h1>';
		
		
		if (is_user_logged_in()){

		 $html .= "<p>You are already logged in</p>";
		}else{

			//Add login errors if there are any
			$html .= $this->action_errors('login');

			//Show logged out message if user is logged out
			$html .= $this->logout_message();

			
			$html .= $this->get_template_html( 'login_form', $attributes ); 
		}

		//Note that the function called by the shortcode should never produce output of any kind.
		return $html;
	}

	/**
	 * Register form shortcode function
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content 
	 * @return string             Rendered registeration form
	 */
	public function anony_register_form( $attributes, $content = null ) {
		// Parse shortcode attributes
		$default_attributes = array( 'show_title' => false );
		$attributes = shortcode_atts( $default_attributes, $attributes );

		if ( is_user_logged_in() ) {
			return esc_html__( 'You are already signed in.', 'usercontrol' );
		} elseif ( ! get_option( 'users_can_register' ) ) {
			return esc_html__( 'Registering new users is currently not allowed.', 'usercontrol' );
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
					<?php printf(esc_html__( 'You have successfully registered to <strong>%1s</strong>. We have emailed your password to the email address you entered.And you can login from <a href="%2s">Here</a>', 'usercontrol' ), get_bloginfo( 'name'), wp_login_url());  ?>
    			</p>
			<?php }
			return $this->get_template_html( 'register_form', $attributes );
		}
	}

	/**
	 * Lost password form shortcode function
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content 
	 * @return string             Rendered lost password form
	 */
	public function anony_password_lost_form( $attributes, $content = null ) {
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
			return esc_html__( 'You are already signed in.', 'usercontrol' );
		} else {
			if ( count( $attributes['errors'] ) > 0 ) : 
			foreach ( $attributes['errors'] as $error ) : ?>
				<p><?php echo $error; ?></p>
			<?php endforeach; 
			endif; 
			return $this->get_template_html( 'password_lost_form', $attributes );
		}
	}

	/**
	 * Reset password form shortcode function
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content 
	 * @return string             Rendered reset password form
	 */
	public function anony_password_reset_form( $attributes, $content = null ) {
		$default_attributes = array( 'show_title' => false );
		$attributes = shortcode_atts( $default_attributes, $attributes );
		if ( is_user_logged_in() ) {
			return esc_html__( 'You are already signed in.', 'usercontrol' );
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
				return esc_html__( 'Invalid password reset link.', 'usercontrol' );
			}
		}
	}
	/**------------------------------------------------------------------
	 * actions
	 *-----------------------------------------------------------------*/

	/**
	 * Description
	 * @return type
	 */
	public function do_register_user() {

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {

				$redirect_url = home_url( ANONY_REG );

				if(is_user_logged_in()){
					wp_redirect( home_url( ANONY_LOGIN ) );
					exit;
				}

				if ( ! get_option( 'users_can_register' ) ) {
					// Registration closed, display error
					$redirect_url = add_query_arg( 'register-errors', 'closed', $redirect_url );

				} else {

					/*$email = $_POST['email'];
					
					$user_name = sanitize_text_field( $_POST['user_name'] );
*/
					$user = new ANONY__User(
						[
							'user_login' => sanitize_text_field( $_POST['user_name'] ), 
							'user_email' => sanitize_email( $_POST['email'] ),
						]
					);

					var_dump($user);
					die();
					/*$result = $this->register_user( $email, $user_name );

					$errors = new WP_Error();

					if ( is_wp_error( $result ) ) {
						// Parse errors into a string and append as parameter to redirect
						$errors = join( ',', $result->get_error_codes() );
						$redirect_url = add_query_arg( 'register-errors', $errors, $redirect_url );
					} else {
						// Success, redirect to login page.
						$redirect_url = home_url( ANONY_REG );
						$redirect_url = add_query_arg( 'registered', $email, $redirect_url );
					}
					*/
				}

				/*wp_redirect( $redirect_url );
				exit;
				*/
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

	/**------------------------------------------------------------------
	 * helpers
	 *-----------------------------------------------------------------*/
	/**
	 * Get action template
	 * @param  string $template_name Template name
	 * @param  array  $attributes Array of data comming from shortcode 
	 * @return string Template html;
	 */
	private function get_template_html( $template_name, $attributes = null ) {

		if ( ! $attributes ) $attributes = array();
		
		ob_start();

			require( 'templates/' . $template_name . '.php');

			$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Redirects user to a page
	 * @param string $p path to redirct
	 */
	private function redirect_to($p){

		if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {

			if ( is_user_logged_in() ) {

				$user = wp_get_current_user();

				if ( user_can( $user, 'manage_options' ) ) {
					wp_redirect( admin_url() );
				} else {
					wp_redirect( home_url( ANONY_ACCOUNT ) );
				}
				exit;
			}

			// The rest are redirected to the login page
			$login_url = home_url( $p );

			wp_redirect( $login_url );
			exit;
		}	
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
	 
	            $login_url = home_url( ANONY_LOGIN );
	            $login_url = add_query_arg( 'login', $error_codes, $login_url );
	 
	            wp_redirect( $login_url );
	            exit;
	        }
	    }
	 
	    return $user;
	}
	public function show_error_message( $error_code ) {
		switch ( $error_code ) {
			case 'empty_username':
				return esc_html__( 'You do have an email address, right?', 'usercontrol' );
			case 'empty_password':
				return esc_html__( 'You need to enter a password to login.', 'usercontrol' );
			case 'invalid_username':
				return esc_html__("We don't have any users with that username. Maybe you used a different one when signing up?",'usercontrol');
			case 'incorrect_password':
				$err = wp_kses(esc_html__("The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",'usercontrol'), array('a' => array('href')));
				return sprintf( $err, wp_lostpassword_url() );
				// Registration errors
			case 'email':
				return esc_html__( 'The email address you entered is not valid.', 'usercontrol' );

			case 'email_exists':
				return esc_html__( 'An account exists with this email address.', 'usercontrol' );

			case 'closed':
				return esc_html__( 'Registering new users is currently not allowed.', 'usercontrol' );
			// Lost password
 
			case 'empty_username':
				return esc_html__( 'You need to enter your email address to continue.', 'usercontrol' );

			case 'invalid_email':
			case 'invalidcombo':
				return esc_html__( 'There are no users registered with this email address.', 'usercontrol' );
			case 'expiredkey':
			case 'invalidkey':
				return esc_html__( 'The password reset link you used is not valid anymore.', 'usercontrol' );

			case 'password_reset_mismatch':
				return esc_html__( "The two passwords you entered don't match.", 'usercontrol' );

			case 'password_reset_empty':
				return esc_html__( "Sorry, we don't accept empty passwords.", 'usercontrol' );
			default:
			break;
			}
		return esc_html__( 'An unknown error occurred. Please try again later.', 'usercontrol' );
	}

	/*private function register_user( $email, $user_name ) {
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
		$subject = sprintf(esc_html__('Your login credintals for %s','usercontrol'), $blogname);
		$message= sprintf(esc_html__('Thank you %1s for registering to our blog %2s','usercontrol'), $user->nickname, $blogname). "\r\n\r\n";
		$message.=esc_html__('You login information is:','usercontrol') . "\r\n\r\n";
		$message.= sprintf(esc_html__('Username: %s','usercontrol'), $user->user_login) . "\r\n\r\n";
		$message.=sprintf(esc_html__('Password: %s','usercontrol'), $password) . "\r\n";
		$message.= esc_html__('To log into the admin area please us the following address ','usercontrol') .home_url('member-login') . "\r\n";
		$headers ='';
		
		wp_mail($email,$subject,$message,$headers);
		return $user_id;
	}*/
	public function replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {
		$msg  = esc_html__( 'Hello!', 'usercontrol' ) . "\r\n\r\n";
		$msg .= sprintf( esc_html__( 'You asked us to reset your password for your account using the email address %s.', 'usercontrol' ), $user_login ) . "\r\n\r\n";
		$msg .= esc_html__( "If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", 'usercontrol' ) . "\r\n\r\n";
		$msg .= esc_html__( 'To reset your password, visit the following address:', 'usercontrol' ) . "\r\n\r\n";
		$msg .= site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n\r\n";
		$msg .= esc_html__( 'Thanks!', 'usercontrol' ) . "\r\n";

		return $msg;
	}
	
	public function enqueue_scripts() {
		$script = 'user-control';

		wp_enqueue_style(
			$script, 
			ANONY_CNTRL_URI.('assets/css/'.$script.'.css'),
			'',
			filemtime(wp_normalize_path(ANONY_CNTRL_PATH.'assets/css/'.$script.'.css'))
		);

		if(is_rtl()){
			wp_enqueue_style( 
				$script.'-rtl', 
				ANONY_CNTRL_URI.'assets/css/'.$script.'-rtl'.'.css',
				'',
				filemtime(wp_normalize_path(ANONY_CNTRL_PATH.'assets/css/'.$script.'-rtl'.'.css'))
			);
		}

		wp_enqueue_script( 
			$script, 
			ANONY_CNTRL_URI.'assets/js/'.$script.'.js',
			array('jquery'),
			filemtime(wp_normalize_path(ANONY_CNTRL_PATH.'assets/js/'.$script.'.js')),
			true
		);
	}

	public function user_nav_menu(){
		$menus= array(
			'user-control'=>esc_html__('User control menu','user-control'),
		); 
		foreach($menus as $name => $description){
			if(!has_nav_menu($name)){
				register_nav_menu($name, $description);
			}
		}
		$menu_name = 'User control';
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
		$menu = get_term_by('slug','user-control','nav_menu');
		if($menu->count === 0){
			$item .='<li><span><i class="fa fa-user-circle"></i></span>';
			$item .= '<span>';
			if ( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				if(!empty($current_user->user_firstname)){
					$item .= $current_user->user_firstname ;
				}else{
					$item .= esc_html__('Welcome','user-control').'&nbsp;'.$current_user->user_nicename;
				}
				
			} else {
				$item .= esc_html__('Login','user-control');
			}
			$item .= '</span>';
			$item .= '<span><i class="fa fa-angle-down"></i></span></li>';
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
							$item .= '</a></li>';
					}
				$item .='<li>';
							$item .= '<a class="users-menu" href="'.wp_logout_url( home_url() ).'">';
							$item .='Logout';
							$item .= '</a></li>';
				}else{
					foreach($none_logged_menu_pages as $page){

							$post_obj = get_page_by_title($page);
							$page_url = get_permalink($post_obj->ID);
							$item .='<li>';
							$item .= '<a class="users-menu" href="'.$page_url.'">';
							$item .=$page;
							$item .= '</a></li>';
					}
			}
			$item .= '</ul></li>';
			return $item;
		}
		return  $item;	
	}
}