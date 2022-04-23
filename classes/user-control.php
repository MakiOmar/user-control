<?php
if ( ! defined( 'ABSPATH' ) ) {
	die( 'What are you trying to do?' );
}

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

		global $anoucOptions;

		$this->anoucOptions = $anoucOptions;

		// Set shortcodes
		$this->shortcodes =
		array(
			'anony_login',
			'anony_register',
			'anony_password_lost',
			'anony_password_reset',
			'account_info',
		);

		self::$page_definitions = array(
			'anony-login'          => array(
				'title'   => esc_html__( 'Log in' ),
				'content' => '[anony_login]',
			),

			'anony-account'        => array(
				'title'   => esc_html__( 'Profile' ),
				'content' => '[account_info]',
			),

			'anony-register'       => array(
				'title'   => esc_html__( 'Register' ),
				'content' => '[anony_register]',
			),

			'anony-password-lost'  => array(
				'title'   => esc_html__( 'Forgot Your Password?', ANONY_UC_TEXTDOM ),
				'content' => '[anony_password_lost]',
			),

			'anony-password-reset' => array(
				'title'   => esc_html__( 'Pick a New Password', ANONY_UC_TEXTDOM ),
				'content' => '[anony_password_reset]',
			),
		);

		// Add user control shortcodes
		$this->shortcodes();

		// User control redirects
		$this->redirects();

		// User control form actions
		$this->form_actions();

		add_filter( 'retrieve_password_message', array( $this, 'replace_retrieve_password_message' ), 10, 4 );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'after_setup_theme', array( $this, 'user_nav_menu' ) );

		add_filter( 'wp_nav_menu_' . $this->getMenuSlug() . '_items', array( $this, 'add_user_control_menu_pages' ), 10, 2 );

		add_filter( 'wp_setup_nav_menu_item', array( $this, 'nav_menu_type_label' ) );

		// Filter links on frontend
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'setup_nav_menu_item' ) );

		/**
		 * Will filter front end links, so to show registration link, conditionaly, deppending on login condition
		 */
		add_filter( 'wp_nav_menu_objects', array( $this, 'wp_nav_menu_objects' ) );
	}

	public function getMenuSlug() {

		$menu = get_term_by( 'slug', ANONY_MENU, 'nav_menu' );

		if ( !$menu ) {
			return ANONY_MENU;
		}
		if ( ANONY_WPML_HELP::isActive() ) {

			$lang = apply_filters( 'wpml_current_language', null );

			$uc_menu_translation = ANONY_TERM_HELP::getTermBy( $menu->term_id, 'nav_menu', $lang );

			if ( is_null( $uc_menu_translation ) ) {
				return ANONY_MENU;
			}
			return $uc_menu_translation->slug;
		}

		return ANONY_MENU;
		
	}

	/**
	 * Decorates a menu item object with the shared navigation menu item properties.
	 *
	 * @param object $item
	 * @return object The menu item with standard menu item properties.
	 */
	public function setup_nav_menu_item( $item ) {

		global $pagenow;

		if ( $pagenow != 'nav-menus.php' && ! defined( 'DOING_AJAX' ) && isset( $item->url ) && strstr( $item->url, '#ucntrl' ) != '' ) {

			$item_url = substr( $item->url, 0, strpos( $item->url, '#', 1 ) ) . '#';

			$item_redirect = str_replace( $item_url, '', $item->url );

			if ( $item_redirect == '%actualpage%' ) {
				$item_redirect = $_SERVER['REQUEST_URI'];
			}
			switch ( $item_url ) {
				case '#ucntrlloginout#':
										$item_redirect = explode( '|', $item_redirect );

					if ( count( $item_redirect ) != 2 ) {
						$item_redirect[1] = $item_redirect[0];
					}
					for ( $i = 0; $i <= 1; $i++ ) {
						if ( '%actualpage%' == $item_redirect[ $i ] ) {
							$item_redirect[ $i ] = $_SERVER['REQUEST_URI'];
						}
					}
										$item->url = is_user_logged_in() ? wp_logout_url( $item_redirect[1] ) : wp_login_url( $item_redirect[0] );

										$item->title = $this->ucntrl_loginout_title( $item->title );

					break;

				case '#ucntrllogin#':
					$item->url = esc_url( wp_login_url( $item_redirect ) );
					break;

				case '#ucntrllogout#':
					if ( is_user_logged_in() ) {

												$item->url = esc_url( wp_logout_url( $item_redirect ) );
					} else {

						$item->title = '#ucntrllogout#';
					}

											$item = apply_filters( 'ucntrllogout_item', $item );
					break;

				case '#ucntrlregister#':
					if ( is_user_logged_in() ) {

												$item->title = '#ucntrlregister#';

					} else {

						$item->url = esc_url( wp_registration_url() );
					}

											$item = apply_filters( 'ucntrlregister_item', $item );
					break;
			}
			$item->url = esc_url( $item->url );
		}

		return $item;
	}

	/**
	 * Decides link title for double login|logout link
	 *
	 * @param string $title
	 * @return string Link's title
	 */
	public function ucntrl_loginout_title( $title ) {

		$titles = explode( '|', $title );

		if ( ! is_user_logged_in() ) {
			return esc_html( isset( $titles[0] ) ? $titles[0] : $title );
		}

		return esc_html( isset( $titles[1] ) ? $titles[1] : $title );
	}
	/**
	 * Unset menu item if its title&url == #ucntrlregister#
	 *
	 * The #ucntrlregister# will be set to the menu item if the user is looged in, so we then un set it if user is logged in. Check method (ucntrl_setup_nav_menu_item)
	 *
	 * @param array $sorted_menu_items
	 * @return array Filered array of menu items objects
	 */
	function wp_nav_menu_objects( $sorted_menu_items ) {
		foreach ( $sorted_menu_items as $k => $item ) {
			if ( ( $item->title == $item->url && '#ucntrlregister#' == $item->title ) || ( $item->title == $item->url && '#ucntrllogout#' == $item->title ) ) {
				unset( $sorted_menu_items[ $k ] );
			}
		}

		return $sorted_menu_items;
	}

	/**
	 * Add user control shortcodes
	 */
	public function shortcodes() {
		foreach ( $this->shortcodes as $shcode ) {
			add_shortcode( $shcode, array( $this, $shcode . '_form' ) );
		}
	}

	/**
	 * Will get the redirect url for each user page
	 *
	 * @param string $option The option name that stores page id
	 * @param string $path   The path to check if no option found
	 * @return string
	 */
	public function redirectUrl( $option, $path, $default = null ) {

		$default = is_null( $default ) ? esc_url( home_url() ) : $default;

		if ( isset( $this->anoucOptions->$option ) && ! empty( $this->anoucOptions->$option ) ) {

				$redirect = esc_url( get_permalink( intval( $this->anoucOptions->$option ) ) );

		} elseif ( null !== $post = get_page_by_path( $path ) ) {
			$redirect = esc_url( get_permalink( $post ) );
		}

			return isset( $redirect ) ? $redirect : $default;
	}

	/**
	 * Manage redirects
	 */
	public function redirects() {

		/*------------Login redirect----------------------------------*/
		$login_redirect = function() {

			$this->redirect_to( $this->redirectUrl( 'login_page', ANONY_LOGIN, esc_url( wp_login_url() ) ) );
		};
		add_action( 'login_form_login', $login_redirect );

		/*------------Registration redirect---------------------------------*/
		$reg_redirect = function() {

			$this->redirect_to( $this->redirectUrl( 'register_page', ANONY_REG, esc_url( wp_registration_url() ) ) );
		};
		add_action( 'login_form_register', $reg_redirect );

		/*------------Lost password redirect-----------------------------*/
		$lost_redirect = function() {
			$this->redirect_to( $this->redirectUrl( 'forget_password_page', ANONY_LOST, esc_url( wp_lostpassword_url() ) ) );
		};
		add_action( 'login_form_lostpassword', $lost_redirect );

		/*-------------------Logout redirect-----------------------------*/
		$logout_redirect = function() {

			$redirect = $this->redirectUrl( 'login_page', ANONY_LOGIN, esc_url( wp_login_url() ) );

			$redirect = add_query_arg( 'logged_out', 'true', $redirect );

			$this->redirect_to( $redirect );
		};
		add_action( 'wp_logout', $logout_redirect );

		/*------------Reset password redirect-------------------------------*/
		add_action( 'login_form_rp', array( $this, 'redirect_password_reset' ) );

		add_action( 'login_form_resetpass', array( $this, 'redirect_password_reset' ) );

		/*------------authenticate redirect---------------------------------*/
		add_filter( 'authenticate', array( $this, 'authenticate_redirect_handling' ), 101, 3 );

		/*------------Prevent non-admins to access dashboard--------------------*/
		add_action( 'init', array( $this, 'restrict_none_admins' ) );
	}

	/**
	 * Redirect to password reset
	 */
	public function redirect_password_reset() {
		if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
			// Verify key / login combo
			$user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );

			$redirect_url = $this->redirectUrl( 'login_page', ANONY_LOGIN );

			if ( ! $user || is_wp_error( $user ) ) {

				$msg = ( $user && $user->get_error_code() === 'expired_key' ) ? 'expired_key' : 'invalidkey';

				$redirect_url = add_query_arg( 'login', $msg, $redirect_url );

				$this->redirect_to( $redirect_url );
			}

			$redirect_url = $this->redirectUrl( 'reset_password_page', ANONY_RESET );

			$redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );

			$redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );

			$this->redirect_to( $redirect_url );
		}
	}

	/**
	 * Manage form actions
	 *
	 * @return type
	 */
	public function form_actions() {

		add_action( 'login_form_register', array( $this, 'do_register_user' ) );

		add_action( 'login_form_rp', array( $this, 'do_password_reset' ) );

		add_action( 'login_form_resetpass', array( $this, 'do_password_reset' ) );

		add_action( 'login_form_lostpassword', array( $this, 'do_password_lost' ) );
	}

	/**
	 * Inserts user control pages with the required shortcodes
	 */
	public static function insert_pages() {

		foreach ( self::$page_definitions as $slug => $page ) {
			 // Check that the page doesn't exist already
			$query = new WP_Query(
				array(
					'pagename' => $slug,
				)
			);
			if ( ! $query->have_posts() ) {
				// Add the page using the data from the array above
				$user_id = wp_insert_post(
					array(
						'post_content'   => $page['content'],
						'post_name'      => $slug,
						'post_title'     => $page['title'],
						'post_status'    => 'publish',
						'post_type'      => 'page',
						'ping_status'    => 'closed',
						'comment_status' => 'closed',
					)
				);
				// Store pages IDs for further use
				if ( $user_id !== 0 || ! is_wp_error( $user_id ) ) {
					self::$pages_ids[] = $user_id;
				}
			} else {

				foreach ( self::$page_definitions as $slug => $info ) {

					$page = get_page_by_path( $slug );

					if ( $page ) {
						self::$pages_ids[] = $page->ID;
					}
				}
			}
		}

		// Add pages IDs to options for further use
		if ( ! empty( self::$pages_ids ) ) {
			update_option( 'anony_pages_ids', self::$pages_ids );
		}

	}

	/**
	 * Render actions errors
	 *
	 * @param  string $action action name
	 * @return string        Login errors HTML
	 */
	public function action_errors( $action ) {
		// Error messages
		$login_errors = array();

		if ( isset( $_REQUEST[ $action ] ) ) {
			$error_codes = explode( ',', $_REQUEST[ $action ] );

			foreach ( $error_codes as $code ) {
				$login_errors [] = $this->show_error_message( $code );
			}
		}
		if ( count( $login_errors ) > 0 ) {
			$html = '<ul class="user-errors">';
			foreach ( $login_errors as $error ) {
				$html .= '<li>' . $error . '</li>';
			}
			$html .= '</ul>';
			return $html;
		}
	}

	/**
	 * Render logout message
	 *
	 * @return string
	 */
	public function logout_message() {
		$html = '';
		// Check if user just logged out
		if ( isset( $_REQUEST['logged_out'] ) && $_REQUEST['logged_out'] == true ) {
			$html .= '<p class="login-info">';
			$html .= esc_html__( 'You have signed out. Would you like to sign in again?', ANONY_UC_TEXTDOM );
			$html .= '</p>';
		}

		return $html;
	}

	/**------------------------------------------------------------------
	 * Rendering
	 * -----------------------------------------------------------------*/

	/**
	 * Account info form shortcode function
	 *
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content
	 * @return string             Rendered account info
	 */
	public function account_info_form( $attributes, $content = null ) {
		$html = '';

		if ( ! is_user_logged_in() ) {

			return esc_html__( 'You have to log in to control your account', ANONY_UC_TEXTDOM );
		}

		// Note that the function called by the shortcode should never produce output of any kind.
		return apply_filters( 'account_info_form', $html );
	}

	/**
	 * Login form shortcode function
	 *
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content
	 * @return string             Rendered login form
	 */
	public function anony_login_form( $attributes, $content = null ) {

		$default_attributes = array(
			'show_title' => 'hide',
			'redirect'   => home_url(),
		);

		$attributes = shortcode_atts( $default_attributes, $attributes );

		$attributes['password_updated'] = isset( $_REQUEST['password'] ) && $_REQUEST['password'] == 'changed';

		$attributes['lost_password_sent'] = isset( $_REQUEST['checkemail'] ) && $_REQUEST['checkemail'] == 'confirm';

		if ( $attributes['redirect'] == $default_attributes['redirect'] ) {

			$redir = $attributes['redirect'];
		} else {

			$redir = home_url( $attributes['redirect'] );
		}

		$html = '';

		if ( $attributes['show_title'] == 'show' ) {
			$html .= '<h1>' . esc_html__( 'Sign In', ANONY_UC_TEXTDOM ) . '</h1>';
		}

		if ( is_user_logged_in() ) {

			$html .= '<p>' . esc_html__( 'You are already signed in.', ANONY_UC_TEXTDOM ) . '</p>';
		} else {

			// Add login errors if there are any
			$html .= $this->action_errors( 'login' );

			// Show logged out message if user is logged out
			$html .= $this->logout_message();

			$html .= $this->get_template_html( 'login_form', $attributes );
		}

		// Note that the function called by the shortcode should never produce output of any kind.
		return $html;
	}

	/**
	 * Register form shortcode function
	 *
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content
	 * @return string             Rendered registeration form
	 */
	public function anony_register_form( $attributes, $content = null ) {
		// Parse shortcode attributes
		$default_attributes = array( 'show_title' => false );

		$attributes = shortcode_atts( $default_attributes, $attributes );

		$fields = apply_filters(
			'uc-registration-fields',
			array(

				'user_email' =>
					array(
						'id'          => 'user_email',
						'name'        => 'user_email',
						'type'        => 'email',
						'class'       => 'username',
						'placeholder' => esc_html__( 'Email Adress', 'user-control' ),
					),

				'user_login' =>
					array(
						'id'          => 'user_login',
						'name'        => 'user_login',
						'type'        => 'text',
						'class'       => 'username',
						'placeholder' => esc_html__( 'Username', 'user-control' ),
					),

			)
		);

		if ( is_user_logged_in() ) {
			return esc_html__( 'You are already signed in.', ANONY_UC_TEXTDOM );
		} elseif ( ! get_option( 'users_can_register' ) ) {
			return esc_html__( 'Registering new users is currently not allowed.', ANONY_UC_TEXTDOM );
		} else {

			$html = $this->action_errors( 'register' );

			if ( isset( $_REQUEST['registered'] ) ) {
				$html .= '<p class="registeration-info">' . sprintf( esc_html__( 'You have successfully registered to <strong>%1$1s</strong>. We have emailed your password to the email address you entered.And you can login from <a href="%2$2s">Here</a>', ANONY_UC_TEXTDOM ), get_bloginfo( 'name' ), wp_login_url() ) . '</p>';

			}
			$html .= $this->get_template_html( 'register_form', $attributes, $fields );

			return $html;
		}
	}

	/**
	 * Lost password form shortcode function
	 *
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content
	 * @return string             Rendered lost password form
	 */
	public function anony_password_lost_form( $attributes, $content = null ) {
		$default_attributes   = array( 'show_title' => false );
		$attributes           = shortcode_atts( $default_attributes, $attributes );
		$attributes['errors'] = array();
		if ( isset( $_REQUEST['errors'] ) ) {
			$error_codes = explode( ',', $_REQUEST['errors'] );

			foreach ( $error_codes as $error_code ) {
				$attributes['errors'] [] = $this->show_error_message( $error_code );
			}
		}

		if ( is_user_logged_in() ) {
			return esc_html__( 'You are already signed in.', ANONY_UC_TEXTDOM );
		} else {
			if ( count( $attributes['errors'] ) > 0 ) :
				foreach ( $attributes['errors'] as $error ) : ?>
				<p><?php echo $error; ?></p>
					<?php
			endforeach;
			endif;
			return $this->get_template_html( 'password_lost_form', $attributes );
		}
	}

	/**
	 * Reset password form shortcode function
	 *
	 * @param  array  $attributes shortcode attributes
	 * @param  string $content    shortcode content
	 * @return string             Rendered reset password form
	 */
	public function anony_password_reset_form( $attributes, $content = null ) {
		$default_attributes = array( 'show_title' => false );
		$attributes         = shortcode_atts( $default_attributes, $attributes );
		if ( is_user_logged_in() ) {
			return esc_html__( 'You are already signed in.', ANONY_UC_TEXTDOM );
		} else {
			if ( isset( $_REQUEST['login'] ) && isset( $_REQUEST['key'] ) ) {
				$attributes['login'] = $_REQUEST['login'];
				$attributes['key']   = $_REQUEST['key'];
				$errors              = array();
				if ( isset( $_REQUEST['error'] ) ) {
					$error_codes = explode( ',', $_REQUEST['error'] );

					foreach ( $error_codes as $code ) {
						$errors [] = $this->show_error_message( $code );
					}
				}
				$attributes['errors'] = $errors;
				if ( count( $attributes['errors'] ) > 0 ) :
					foreach ( $attributes['errors'] as $error ) :
						?>
						<p>
							<?php echo $error; ?>
						</p>
						<?php
					endforeach;
				endif;
				return $this->get_template_html( 'password_reset_form', $attributes );
			} else {
				return esc_html__( 'Invalid password reset link.', ANONY_UC_TEXTDOM );
			}
		}
	}
	/**------------------------------------------------------------------
	 * actions
	 * -----------------------------------------------------------------*/

	/**
	 * Description
	 *
	 * @return type
	 */
	public function do_register_user() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
			return;
		}

		$redirect_url = $this->redirectUrl( 'register_page', ANONY_REG );

		if ( is_user_logged_in() ) {
			$this->redirect_to( $this->redirectUrl( 'login_page', ANONY_LOGIN ) );
		}

		if ( ! get_option( 'users_can_register' ) ) {
			// Registration closed, display error
			$redirect_url = add_query_arg( 'register-errors', 'closed', $redirect_url );

		} else {

			$user = new ANONY__User(
				array(
					'user_login' => sanitize_text_field( $_POST['user_login'] ),
					'user_email' => sanitize_email( $_POST['user_email'] ),
				)
			);

			if ( ! empty( $user->errors ) ) {

				// Parse errors into a string and append as parameter to redirect
				$errors       = join( ',', $user->errors );
				$redirect_url = add_query_arg( 'register', $errors, $redirect_url );
			} else {
				extract( $user->user_crids );

				$this->user_crids_notify( $username, $password, $email );

				foreach ( $_POST as $meta_key => $meta_value ) {
					if ( in_array( $meta_key, array( 'user_login', 'user_email' ) ) ) {
						continue;
					}
					update_user_meta( $user->_user_id, $meta_key, wp_strip_all_tags( $meta_value ) );
				}

				$redirect_url = $this->redirectUrl( 'login_page', ANONY_LOGIN );
				// Success, redirect to login page.
				$redirect_url = add_query_arg( 'registered', '1', $redirect_url );
			}
		}

		wp_redirect( $redirect_url );
		exit;

	}

	public function do_password_lost() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {

			$loginUrl = get_permalink( intval( $this->anoucOptions->login_page ) );

			$lostPassUrl = get_permalink( intval( $this->anoucOptions->forget_password_page ) );

			$errors = retrieve_password();
			if ( is_wp_error( $errors ) ) {

				// Errors found
				$redirect_url = $lostPassUrl ? $lostPassUrl : home_url();

				$redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
			} else {

				// Email sent
				$redirect_url = $loginUrl ? $loginUrl : home_url();

				$redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
			}

			wp_redirect( $redirect_url );
			exit;
		}
	}
	public function do_password_reset() {
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
			$rp_key   = $_REQUEST['rp_key'];
			$rp_login = $_REQUEST['rp_login'];

			$loginUrl = get_permalink( intval( $this->anoucOptions->login_page ) );

			$resetPassUrl = get_permalink( intval( $this->anoucOptions->reset_password_page ) );

			$user = check_password_reset_key( $rp_key, $rp_login );

			if ( ! $user || is_wp_error( $user ) ) {
				$redirect = $loginUrl ? $loginUrl : home_url();
				if ( $user && $user->get_error_code() === 'expired_key' ) {

					$redirect = add_query_arg( 'login', 'expiredkey' );

				} else {

					$redirect = add_query_arg( 'login', 'invalidkey' );
				}

				wp_redirect( $redirect );
				exit;
			}

			$redirect_url = add_query_arg( 'key', $rp_key, $resetPassUrl );

			$redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );

			if ( ! isset( $_POST['pass1'] ) || ! isset( $_POST['pass2'] ) ) {
				$redirect_url = add_query_arg( 'error', 'password_missing', $this->resetPassUrl );

				wp_redirect( $redirect_url );
				exit();
			}

			if ( empty( $_POST['pass1'] ) || empty( $_POST['pass2'] ) ) {

				$redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );

				wp_redirect( $redirect_url );
				exit();
			}

			if ( $_POST['pass1'] != $_POST['pass2'] ) {
					// Passwords don't match
					$redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );

				wp_redirect( $redirect_url );
				exit();
			}

			// Parameter checks OK, reset password
			reset_password( $user, $_POST['pass1'] );

			$redirect = add_query_arg( 'password', 'changed', $loginUrl );

			wp_redirect( $redirect );

			exit();

		}
	}

	/**------------------------------------------------------------------
	 * helpers
	 * -----------------------------------------------------------------*/
	/**
	 * Get action template
	 *
	 * @param  string $template_name Template name
	 * @param  array  $attributes Array of data comming from shortcode
	 * @return string Template html;
	 */
	private function get_template_html( $template_name, $attributes = null, $fields = array() ) {

		if ( ! $attributes ) {
			$attributes = array();
		}

		ob_start();

			do_action( 'uc_before_template' );

			require ANONY_UC_PATH . 'templates/' . $template_name . '.php';

			do_action( 'after_before_template' );

			$html = ob_get_contents();

		ob_end_clean();

		return $html;
	}

	/**
	 * Redirects user to a page
	 *
	 * @param string $p path to redirct
	 */
	private function redirect_to( $p ) {

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

			preg_match( '/http/', $p, $match );

			// The rest are redirected to the login page
			$login_url = ! empty( $match ) ? $p : home_url( $p );

			wp_redirect( $login_url );
			exit;
		}
	}

	public function restrict_none_admins() {
		if ( is_admin() && ! current_user_can( 'administrator' ) &&
			! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {

			$this->accountUrl = get_permalink( intval( $this->anoucOptions->account_page ) );

			if ( is_user_logged_in() ) {
				// wp_redirect( $this->accountUrl );
			} else {
				// wp_redirect( $loginUrl );
			}
			// exit;
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
				return esc_html__( 'You do have an email address, right?', ANONY_UC_TEXTDOM );

			case 'empty_password':
				return esc_html__( 'You need to enter a password to login.', ANONY_UC_TEXTDOM );

			case 'invalid_username':
				return esc_html__( "We don't have any users with that username. Maybe you used a different one when signing up?", ANONY_UC_TEXTDOM );

			case 'incorrect_password':
				$err = wp_kses(
					__( "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?", ANONY_UC_TEXTDOM ),
					array( 'a' => array( 'href' ) )
				);
				return sprintf( $err, wp_lostpassword_url() );
			// Registration errors
			case 'email':
				return esc_html__( 'The email address you entered is not valid.', ANONY_UC_TEXTDOM );

			case 'email_exists':
				return esc_html__( 'An account exists with this email address.', ANONY_UC_TEXTDOM );

			case 'username_exists':
				return esc_html__( 'An account exists with this username.', ANONY_UC_TEXTDOM );

			case 'closed':
				return esc_html__( 'Registering new users is currently not allowed.', ANONY_UC_TEXTDOM );
			// Lost password

			case 'empty_username':
				return esc_html__( 'You need to enter your email address to continue.', ANONY_UC_TEXTDOM );

			case 'invalid_email':
			case 'invalidcombo':
				return esc_html__( 'There are no users registered with this email address.', ANONY_UC_TEXTDOM );

			case 'expiredkey':
			case 'invalidkey':
				return esc_html__( 'The password reset link you used is not valid anymore.', ANONY_UC_TEXTDOM );

			case 'password_reset_mismatch':
				return esc_html__( "Password and password confirmation don't match.", ANONY_UC_TEXTDOM );

			case 'password_reset_empty':
				return esc_html__( "Sorry, we don't accept empty passwords.", ANONY_UC_TEXTDOM );

			default:
				return esc_html__( 'An unknown error occurred. Please try again later.', ANONY_UC_TEXTDOM );
			break;
		}
	}

	public function replace_retrieve_password_message( $message, $key, $user_login, $user_data ) {
		$msg  = '</p>' . esc_html__( 'Hello!', ANONY_UC_TEXTDOM ) . '</p>';
		$msg .= '</p>' . sprintf( esc_html__( 'You asked us to reset your password for your account using the email address %s.', ANONY_UC_TEXTDOM ), $user_login ) . '</p>';
		$msg .= '</p>' . esc_html__( "If this was a mistake, or you didn't ask for a password reset, just ignore this email and nothing will happen.", ANONY_UC_TEXTDOM ) . '</p>';
		$msg .= '</p>' . esc_html__( 'To reset your password, visit the following address:', ANONY_UC_TEXTDOM ) . '</p>';
		$msg .= '</p>' . site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . '</p>';
		$msg .= '</p>' . esc_html__( 'Thanks!', ANONY_UC_TEXTDOM ) . '</p>';

		return $msg;
	}

	/**
	 * Notify user with new cridentals on insertion
	 *
	 * @param string $username
	 * @param string $password
	 * @param string $email
	 * @return void
	 */
	public function user_crids_notify( $username, $password, $email ) {

		$blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

		$subject    = sprintf(
			esc_html__( 'Your login credintals for %s', ANONY_UC_TEXTDOM ),
			$blogname
		);
		$direction  = is_rtl() ? 'rtl' : 'ltr';
		$text_align = is_rtl() ? 'right' : 'left';

		$style = sprintf( 'direction:%1$s;text-align:%2$s', $direction, $text_align );

		$message = '<div style="' . $style . '">';

		$message .= '<p>' . sprintf(
			esc_html__( 'Thank you %1$s for registering to our website %2$s', ANONY_UC_TEXTDOM ),
			$username,
			$blogname
		) . '</p>';

		$message .= '<p>' . esc_html__( 'You login information is:', ANONY_UC_TEXTDOM ) . '</p>';

		$message .= '<p>' . sprintf(
			esc_html__( 'Username: %s', ANONY_UC_TEXTDOM ),
			$username
		) . '</p>';

		$message .= '</p>' . sprintf(
			esc_html__( 'Password: %s', ANONY_UC_TEXTDOM ),
			$password
		) . '</p>';

		$message .= '<p>' . esc_html__( 'To log into your account please use the following address ', ANONY_UC_TEXTDOM ) . $this->redirectUrl( 'login_page', ANONY_LOGIN ) . '</p>';
		$message .= '</div>';

		$from = sprintf( 'From: %1$s <%2$s>', get_bloginfo('name'), sanitize_email( get_bloginfo( 'admin_email' ) ) );

		$headers[] = $from;
		$headers[] = 'MIME-Version: 1.0';
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=UTF-8';

		wp_mail( $email, $subject, $message, $headers );
	}

	/**------------------------------------------------------------------
	 * Installations
	 * -----------------------------------------------------------------*/

	public function user_nav_menu() {

		$menu_name = ucfirst( str_replace( '-', ' ', ANONY_MENU ) );

		$menu = wp_get_nav_menu_object( ANONY_MENU );

		// If it doesn't exist, let's create it.
		if ( ! $menu ) {

			$menu_id = wp_create_nav_menu( $menu_name );

			$locations = get_theme_mod( 'nav_menu_locations' );

			$locations[ ANONY_MENU ] = $menu_id;

			set_theme_mod( 'nav_menu_locations', $locations );
		}

		$menus = array(
			ANONY_MENU => esc_html__( 'User control plugin menu', ANONY_UC_TEXTDOM ),
		);

		foreach ( $menus as $location => $description ) {

			if ( ! has_nav_menu( $location ) ) {
				register_nav_menu( $location, $description );
			}
		}

	}

	public function userPage( $slug, $option ) {
		$check_page = get_page_by_path( $slug );

		if ( ! is_null( $check_page ) ) {
			return $slug;
		} elseif ( isset( $this->anoucOptions->$option ) && ! empty( $this->anoucOptions->$option ) ) {
			return intval( $this->anoucOptions->$option );
		}

		return false;
	}

	public function add_user_control_menu_pages( $item, $args ) {

		$menu = get_term_by( 'slug', ANONY_MENU, 'nav_menu' );

		if ( $menu->count === 0 ) {

			$item .= '<li class="ucntrl-menu-item"><a id="anony-uc-menu-toggle" href="#"><span id="anony-user-menu-icon"></span>';

			$item .= '<span>';

			if ( is_user_logged_in() ) {

				$current_user = wp_get_current_user();

				if ( ! empty( $current_user->user_firstname ) ) {

					$item .= $current_user->user_firstname;

				} else {

					$item .= esc_html__( 'Welcome', ANONY_UC_TEXTDOM ) . '&nbsp;' . $current_user->user_nicename;
				}
			} else {

				$item .= esc_html__( 'Log in' );
			}

			$item .= '</span>';
			$item .= '<span><i class="fa fa-toggle-down"></i></span></a></li>';
			$item .= '<li class="ucntrl-menu-item"><ul class = "anony-user-dropdown">';

			if ( is_user_logged_in() ) {

				if ( false !== $page = $this->userPage( 'anony-account', 'account_page' ) ) {
					$item .= $this->render_user_page_menu( $page );
				} else {
					$item .= esc_html__( 'No account page', ANONY_UC_TEXTDOM );
				}

				$item .= '<li>';
				$item .= '<a class="users-menu" href="' . esc_url( wp_logout_url() ) . '">';
				$item .= esc_html__( 'Log out' );
				$item .= '</a></li>';

			} else {

				if ( false !== $page = $this->userPage( 'anony-login', 'login_page' ) ) {
					$item .= $this->render_user_page_menu( $page );
				} else {
					$item .= esc_html__( 'No login page', ANONY_UC_TEXTDOM );
				}

				if ( false !== $page = $this->userPage( 'anony-register', 'register_page' ) ) {
					$item .= $this->render_user_page_menu( $page );
				} else {
					$item .= esc_html__( 'No login page', ANONY_UC_TEXTDOM );
				}
			}

			$item .= '</ul></li>';
			return $item;
		}
		return $item;
	}

	/**
	 * Renders user pages menu
	 *
	 * @param  array $data Pages slugs/IDs to render
	 * @return string HTNL list tags
	 */
	public function render_user_page_menu( $page ) {

		$item = '';

		$post_obj = is_integer( $page ) ? get_post( $page ) : get_page_by_path( $page );

		if ( is_object( $post_obj ) ) {

			if ( ANONY_WPML_HELP::isActive() ) {
				$translated_page_id = icl_object_id( intval( $post_obj->ID ), 'page', false, ANONY_WPML_HELP::gatActiveLang() );

				if ( ! is_null( $translated_page_id ) ) {
					$post_obj = get_post( $translated_page_id );
				}
			}

			$item .= '<li class="ucntrl-menu-item">';
			$item .= '<a class="users-menu" href="' . esc_url( get_permalink( $post_obj->ID ) ) . '">';
			$item .= esc_html( $post_obj->post_title );
			$item .= '</a></li>';
		}

		return apply_filters( 'anony_user_page_menu', $item );

	}

	/**
	 * Modify the "type_label"
	 *
	 * @param object $menu_item
	 * @return object
	 */
	public function nav_menu_type_label( $menu_item ) {
		$elems = array( '#ucntrllogin#', '#ucntrllogout#', '#ucntrlloginout#', '#ucntrlregister#' );
		if ( isset( $menu_item->object, $menu_item->url ) && 'custom' == $menu_item->object && in_array( $menu_item->url, $elems ) ) {
			$menu_item->type_label = esc_html__( 'User control', ANONY_UC_TEXTDOM );
		}

		return $menu_item;
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {

		$styles = array( 'user-control' );

		foreach ( $styles as $style ) {
			wp_enqueue_style(
				$style,
				ANONY_UC_URI . ( 'assets/css/' . $style . '.css' ),
				'',
				filemtime( wp_normalize_path( ANONY_UC_PATH . 'assets/css/' . $style . '.css' ) )
			);
		}

		if ( is_rtl() ) {
			wp_enqueue_style(
				'user-control-rtl',
				ANONY_UC_URI . 'assets/css/user-control-rtl.css',
				'',
				filemtime( wp_normalize_path( ANONY_UC_PATH . 'assets/css/user-control-rtl.css' ) )
			);
		}

		$scripts = array( 'user-control' );

		foreach ( $scripts as $script ) {
			wp_enqueue_script(
				$script,
				ANONY_UC_URI . 'assets/js/' . $script . '.js',
				array( 'jquery' ),
				filemtime( wp_normalize_path( ANONY_UC_PATH . 'assets/js/' . $script . '.js' ) ),
				true
			);
		}

	}

	/**
	 * Delete pages by IDs.
	 *
	 * This method will be called upon plugin deactivation
	 */
	public static function delete_pages() {
		// Pages IDs are stored as an option on install
		$pages_ids = get_option( 'anony_pages_ids' );

		if ( is_array( $pages_ids ) ) {

			foreach ( $pages_ids as $id ) {

				wp_delete_post( $id, true );
			}
		}

		delete_option( 'anony_pages_ids' );
	}

	/**
	 * delete menus.
	 *
	 * Will be called upon deactivation.
	 */
	public static function delete_menus() {

		unregister_nav_menu( ANONY_MENU );

		$menu = wp_get_nav_menu_object( ANONY_MENU );

		if ( $menu ) {
			wp_delete_term( $menu->term_id, 'nav_menu' );
		}
	}

	/**
	 * Will be called uppon deactivation
	 */
	public static function deactivated() {
		self::delete_pages();
		self::delete_menus();
	}

}
