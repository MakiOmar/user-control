<?php
/**
 * User manipulation
 *
 * @package Anonymous theme
 * @author Makiomar
 * @link http://makiomar.com
 */

if(!class_exists('ANONY__User')){

	class ANONY__User{

		/**
		 * @var object An object of wpdb class
		 */
		public $_wpdb;

		/**
		 * @var bool Weaather an email exists
		 */
		public $email_exists = false;

		/**
		 * @var bool Weaather a username exists
		 */
		public $username_exists  = false;

		/**
		 * @var bool Weaather user credintals are updated
		 */
		public $crids_updated  = false;

		/**
		 * @var bool Weaather a user inserted
		 */
		public $user_inserted  = false;

		/**
		 * @var object Stores an object of user
		 */
		public $_user;

		/**
		 * @var bool Weather to force user data to be exactly the same as the supplied data to constructor
		 */
		public $_force;

		/**
		 * Constructor
		 * @param array $user_data An array of user's data. should be the same struction of $userdata for wp_insert_user
		 * @param bool  $force     Flage to check wether to force data change
		 */
		public function __construct( $user_data, $force = false ){


			if (!is_array($user_data) || empty($user_data)) return;

			global $wpdb;

			$this->_wpdb     = $wpdb;

			$this->user_data = $user_data;

			$this->_force    = $force;


			extract($user_data);

			//Main data like username and email should be supplied
			if((!isset($user_login) || empty($user_login)  ) || (!isset($user_email) || empty($user_email))) return;


			if( $this->email_exists( $user_email )){

				$this->email_exists    = true;

				$this->_user           = get_user_by('email' , $user_email);

			}elseif( username_exists( $user_login ) ){

				$this->username_exists = true;

				$this->_user           = get_user_by('login' , $user_login);
			}

			/**----------------------------------------------------------------------------
			 * In case of username and email doesn't exist, insert the user
			 *---------------------------------------------------------------------------*/

			if( ( !$this->username_exists &&  !$this->email_exists )){
				
				if(!isset($this->user_data['user_pass']) || empty($this->user_data['user_pass'])){
					
					$this->register_user();
				}else{
					
					$this->insert_user();
				}
			}
				


			/**---------------------------------------------------------------------------
			 * In case of username or email exist, and needed to forcely change user_data
			 *--------------------------------------------------------------------------*/
			if($this->_force) $this->user_force_manipulate();
				
		}
		
		/**
		 * Inserts new user but won't allow him to choose a password. but will send a password reset link that contains a key that its hash equals the user_activation_key key in the users column. After password reset it sends out a notification of reset.
		 */
		public function register_user(){
			register_new_user( $this->user_data['user_login'], $this->user_data['user_email'] );
		}

		/**
		 * Inserts new user
		 * 
		 * will insert the user using the supplied data, but only notifies him with username and email.
		 * You may for some reason want to notify him with the password, so we used a custom notify method.
		 * This method expects the user_pass to be added to $user_data
		 * @return void
		 */
		public function insert_user(){
			
			extract($this->user_data);
			
			if(!isset($this->user_data['user_pass']) || empty($this->user_data['user_pass'])) 
				return new WP_Error('password_required', esc_html__('You didn\'t choose your password', TEXTDOM));
									
			$user = wp_insert_user( $this->user_data );

			if(!is_wp_error( $user )){
				$this->user_inserted = true;
			
				$this->user_crids_notify($user_login, $user_pass, $user_email);
			}
			
		}
		
		/**
		 * Deletes a user
		 * @param int $id 
		 * @param int $reassign the ID of a user to assign posts of deleted user
		 */
		public function delete_user($id, $reassign = null){
			wp_delete_user( $id, $reassign );
		}

		public function user_force_manipulate(){

			/**--------------------------------------------------------------------------
			 * If email exists we make sure the username equals to the supplied username
			 *-------------------------------------------------------------------------*/

			$this->force_user_login();

			/**--------------------------------------------------------------------------
			 * If username exists we make sure the email equals to the supplied email
			 *-------------------------------------------------------------------------*/

			$this->force_user_email();

			/**--------------------------------------------------------------------------
			 * Force change user password
			 *-------------------------------------------------------------------------*/
			if(isset($this->user_data['user_pass'])) $this->set_user_pass($this->user_data['user_pass']);
		}

		/**
		 * Checks if a user name (user_login) exists and make sure the user's email (user_email) equals to the supplied email within $this->user_data.
		 */
		public function force_user_email(){

			if($this->username_exists){

				extract($this->user_data);

				if($this->_user->user_email !== $user_email){

					//User Id should be supplied to $user_data, or WP_Error will be returned
					$user_data['ID'] =$this->_user->ID;

					$user_data['user_email'] = $user_email;

					$user_id = wp_update_user( $user_data );
					
					if( !is_wp_error($user_id) ) $this->crids_updated = true;

				}
			}
		}

		/**
		 * Force set username (user_login) equals to the supplied username within $this->user_data
		 */
		public function force_user_login(){

			if($this->email_exists){

				extract($this->user_data);

				$this->_user = get_user_by('email' , $user_email);

				if($this->_user->user_login !== $user_login){
					//Add the field to be updated
					$user_data['user_login'] = $user_login;

					$user_id =$this->set_user_login($user_data, ['ID' => $this->_user->ID]);

					if($user_id) $this->crids_updated = true;
				}
					
			}
		}

		/**
		 * Changes the username (user_login) in the DB
		 * @param  array    $user_data Array of user data to change
		 * @param  int|null $id        User's ID 
		 * @return mixed               Return number of rows affected on success, or false if not
		 */
		public function set_user_login($user_data, $id = null){
			//Make sure to supply an arrau contains the ID of user
			if(!isset($id['ID']) || !is_integer($id['ID'])) return;

			//always update user_nicename when you change the user_login
			if(!isset($user_data['user_nicename'])  || empty($user_data['user_nicename'])) 

				$user_data['user_nicename'] = $user_data['user_login'];

			//Make sure display name to be not the same as user_login
			if(!isset($user_data['first_name'])   && !isset($user_data['last_name']))

				$user_data['display_name'] = sanitize_title(esc_html__( 'User' , TEXTDOM));

			//Use $this->_wpdb->update because wp_update_user won't update user_login
			//$this->_wpdb->update returns false on failure or number of rows affected on success
			return $this->_wpdb->update($this->_wpdb->users, $user_data, $id);
		}

		/**
		 * change user's password 
		 * @param string $get_user_by The field to get user by
		 * @param string $value       The value of the field
		 * @param string $password    New password
		 * @return bool               Returns true if password is set, otherwise false
		 */
		public function set_user_pass($password){
			//can't set empty password
			if(empty($password)) return;

			//If hashed password in DB not the same as the hash of $old_pass, set it to $old_pass
			if($this->_user->user_pass !== md5($password)){

				$user_data['user_pass'] = md5($password);
			
				if($this->_wpdb->update($this->_wpdb->users, $user_data, ['ID' => $this->_user->ID])) $this->crids_updated = true;

			}
		}

		/**
		 * Checks weather an email exists
		 * @param string $user_email 
		 * @return bool True if exists, otherwise false
		 */
		public function email_exists($user_email){
			if(is_email($user_email) && email_exists( $user_email )) 
				return true;

			return false;
		}

		/**
		 * Notify user with new cridentals on insertion
		 * @param string $username 
		 * @param string $password 
		 * @param string $email 
		 * @return void
		 */
		public function user_crids_notify($username, $password, $email){

			$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
			
			$subject = sprintf(
						esc_html__('Your login credintals for %s',TEXTDOM), 
						$blogname
					);
			
			$message= sprintf(
						esc_html__('Thank you %1$s for registering to our blog %2$s',TEXTDOM), 
						$username, 
						$blogname
					). "\n\n";
			
			$message.=esc_html__('You login information is:',TEXTDOM) . "\n\n";
			
			$message.= sprintf(
						esc_html__('Username: %s',TEXTDOM), 
						$username
					) . "\n\n";
			
			$message.=sprintf(
						esc_html__('Password: %s',TEXTDOM), 
						$password
					) . "\n";
			
			$message.= esc_html__('To log into the admin area please us the following address ',TEXTDOM) .home_url('/') . "\n";

			$headers  = "From: " . sanitize_email(get_bloginfo('admin_email')) . "\n";
			$headers .= "MIME-Version: 1.0\r\n";
			$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
			
			wp_mail($email,$subject,$message,$headers);
		}
	}
}
