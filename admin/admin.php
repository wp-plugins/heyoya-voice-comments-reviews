<?php
class AdminOptionsPage{

	public function __construct(){
		add_action('admin_menu', array($this, 'heyoya_menu') );		
		add_action('admin_enqueue_scripts', array($this, 'load_admin_scripts') );
		add_action('admin_init', array($this, 'heyoya_admin_init') );
		add_action('admin_head', array($this, 'heyoya_admin_head') );
		add_action( 'wp_ajax_logout', array($this, 'heyoya_logout') );
		add_action( 'wp_ajax_purchased', array($this, 'heyoya_purchased') );
		add_action( 'wp_ajax_is_store', array($this, 'heyoya_is_store') );
		add_action( 'wp_ajax_design_mode', array($this, 'heyoya_design_mode') );
		add_action( 'wp_ajax_placement_path', array($this, 'heyoya_placement_path') );				
	}
	

	function heyoya_menu() {
		add_comments_page( 'Heyoya Options', 'Heyoya', 'manage_options', 'heyoya-options', array($this, 'set_heyoya_options') );
	}
	
	function load_admin_scripts($hook){
		if ( 'comments_page_heyoya-options' != $hook ) {
			return;
		}	
		
		if (!is_heyoya_installed()){
			wp_register_script( 'loggedout_script', plugins_url( '/js/loggedout.js', __FILE__ ) );
			wp_enqueue_script( 'loggedout_script', plugins_url( '/js/loggedout.js', __FILE__ ), array( 'jQuery') );			
		} else {
			wp_register_script( 'loggedin_script', plugins_url( '/js/loggedin.js', __FILE__ ) );
			wp_enqueue_script( 'loggedin_script', plugins_url( '/js/loggedin.js', __FILE__ ), array( 'jQuery') );

			wp_register_script( 'messaging_script', plugins_url( '/js/messaging.js', __FILE__ ) );
			wp_enqueue_script( 'messaging_script', plugins_url( '/js/messaging.js', __FILE__ ) );
		}
	}		
	
	function heyoya_admin_init(){
		$options = get_option('heyoya_options', null);
		if ($options == null){
			$options = array();
			add_option("heyoya_options", $options);
		}
		
		register_setting( 'heyoya-options', 'heyoya-options', array($this, 'heyoya_options_validate' ));
		add_settings_section('heyoya_login', '', array($this, 'heyoya_section_text'), 'admin-login');
		add_settings_section('heyoya_signup', '', array($this, 'heyoya_section_text'), 'admin-signup');
		
		add_settings_field('login_email', 'Email', array($this, 'admin_email_string'), 'admin-login', 'heyoya_login');
		add_settings_field('login_password', 'Password', array($this, 'admin_password_string'), 'admin-login', 'heyoya_login');

		add_settings_field('signup_fullname', 'Full Name', array($this, 'admin_fullname_string'), 'admin-signup', 'heyoya_signup');
		add_settings_field('signup_email', 'Email', array($this, 'admin_email_string'), 'admin-signup', 'heyoya_signup');
		add_settings_field('signup_password', 'Password', array($this, 'admin_password_string'), 'admin-signup', 'heyoya_signup');		
	}	
	
	function heyoya_section_text() {
		echo '';
	} 
	
	function admin_email_string() {
		$options = get_option('heyoya_options');
		echo "<input class='login_email' name='heyoya_options[login_email]' size='30' type='text' value='" . (isset($options["login_email"])?$options["login_email"]:"") . "' />";
	}
	
	function admin_fullname_string() {
		$options = get_option('heyoya_options');
		echo "<input class='signup_fullname' name='heyoya_options[signup_fullname]' size='30' type='text' value='" . (isset($options["signup_fullname"])?$options["signup_fullname"]:"") . "' />";
	}

	
	function admin_password_string() {
		$options = get_option('heyoya_options');
		echo "<input class='login_password' name='heyoya_options[login_password]' size='30' type='password' value='" . (isset($options["login_password"])?$options["login_password"]:"") . "' />";
	}
	
	function login_user($options, $email, $password){
		if ( $email == null || trim($email) == "" || !is_email($email) || ( ( $password == null || trim($password) == "" ) && ( $options == null || $options["apikey"] == null ) ) )
			return;							

		$time = time();		
		$email = trim($email);
		$url = 'https://admin.heyoya.com/client-admin/lwak.heyoya';
		
		if ($password != null){
			$password = trim($password);
			
			
			$args = array ("body" => array ("e" => $email,"p" => $password,"t" => $time), "sslverify" => false);
			
		} else {			
			$args = array('body' => array('e' =>  $email,'ak' => $options["apikey"],'t' =>  $time), "sslverify" => false);
		}
		
		$response = wp_remote_post( $url, $args );	
		
		$options["last_method"] = "login";
		update_option("heyoya_options", $options);
		
		$this->login_signup_handle_response($options, $response, $email, $time);
	}
	
	function signup_user($options, $fullname, $email, $password){
		if ( $email == null || trim($email) == "" || !is_email($email) || $fullname == null || trim($fullname) == "" || $password == null || trim($password) == "" )
			return;							

		$email = trim($email);
		$password = trim($password);			
		$fullname = trim($fullname);
		$time = time(); 

		$url = 'https://admin.heyoya.com/client-admin/rwak.heyoya';
		$args = array('body' => array('e' => $email,'p' => $password,'n' => $fullname,'t' => $time), "sslverify" => false);
		
		$response = wp_remote_post( $url, $args );

		$options["last_method"] = "signup";
		
		$this->login_signup_handle_response($options, $response, $email, $time);
	}

	
	function login_signup_handle_response($options, $response, $email, $last_login_time){
		if ($response == null)
			return;		
		
		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code == "" || $response_code != 200){
			$options["error_raised"] = true;
			$options["error_code"] = -1;
			update_option("heyoya_options", $options);
			return;
		}
		
		$body = wp_remote_retrieve_body( $response );		
		if ($body == null || trim($body) == "" || preg_match('/^-[0-9]{1}$/i', trim($body))){			
			$options["error_raised"] = true;			
			
			if ($body == null || trim($body) == "")
				$options["error_code"] = -1;
			else { 	
				$options["error_code"] = intval(trim($body));
				if ($options["error_code"] == -4)
					$options["error_code"] = -1;
			}
			
// 			$options["error_code"] = $body;
			
			update_option("heyoya_options", $options);
			return;
		}
		
// 		$cookie_value =  wp_remote_retrieve_header( $response, 'set-cookie' );
// 		if ($cookie_value == null || trim($cookie_value) == ""){
// 			$options["error_raised"] = true;
// 			return;
// 		}
// 		$cookie_value = trim($cookie_value);
		
// 		$split1 = explode(";", $cookie_value);
// 		$split2 = explode("=", $split1[0]);
		
// 		$key_value = $split2[1];

		$body_response = json_decode($body, true);
		
		if (isset($body_response["ai"])){
			if (isset($options["last_method"]) &&  $options["last_method"] == "login"){
				$this->updateLoginInfo($body_response["ak"]);
				$options = get_option('heyoya_options', array());
			}
				
			$options["affiliate_id"] = $body_response["ai"];
		}
		
		if (isset($body_response["ak"]))
			$options["apikey"] = $body_response["ak"];
		
		$options["login_email"] = $email;
		$options["last_login_time"] = $last_login_time;				
		
		update_option("heyoya_options", $options);
	}
	
	function updateLoginInfo($apiKey){
		if ($apiKey == null || trim($apiKey) == "")
			return;
		
		$options = get_option('heyoya_options', array());		
		
		$time = time();
		$url = 'https://admin.heyoya.com/client-admin/gsbak.heyoya';		
		$args = array ("body" => array ("ak" => trim($apiKey),"t" => $time), "sslverify" => false);
			
		$response = wp_remote_post( $url, $args );
				
		$response_code = wp_remote_retrieve_response_code($response);
		if ($response_code == "" || $response_code != 200){
			$options["error_raised"] = true;
			$options["error_code"] = -10;
			update_option("heyoya_options", $options);
			return;
		}
		
		$body = wp_remote_retrieve_body( $response );		
		if ($body == null || trim($body) == "" || preg_match('/^-[0-9]{1}$/i', trim($body))){			
			$options["error_raised"] = true;
			$options["error_code"] = -5;					
			update_option("heyoya_options", $options);
			return;
		}
		
		$body_response = json_decode($body, true);
		
		if (isset($body_response["isStore"]))
			$options["is_store"] = $body_response["isStore"];
		
		if (isset($body_response["title"]))
			$options["title"] = $body_response["title"];
		
		if (isset($body_response["colorTextTitle"]))
			$options["title_color_text"] = $body_response["colorTextTitle"];
		
		if (isset($body_response["colorBackgroundTitle"]))
			$options["title_color_background"] = $body_response["colorBackgroundTitle"];
		
		if (isset($body_response["placementPath"]))
			$options["placement_path"] = $body_response["placementPath"];	

		if (isset($body_response["published"]) && $body_response["published"])
			$options["purchased"] = true;
		else 
			$options["purchased"] = false;

		update_option("heyoya_options", $options);				
	}
	
	
	function heyoya_options_validate($input) {				
		$input = $_POST["heyoya_options"];
		$options = get_option('heyoya_options', null);			
		
		if ($input == null){
			update_option("heyoya_options", $options);			
			return;				
		}		
		
		if ( !isset($input["login_email"]) || !is_email(trim($input["login_email"])) || ( ( !isset($input["signup_fullname"]) || trim($input["signup_fullname"]) == "" ) && ( !isset($input["login_password"]) || trim($input["login_password"]) == "" ) ) ){
			update_option("heyoya_options", $options);
			return;				
		}
		
		if ( !isset($input["signup_fullname"]) || trim($input["signup_fullname"]) == "" ){
			$this->login_user( $options, trim($input["login_email"]), trim($input["login_password"]) );			
			return;
		} 
		
		$this->signup_user($options, trim($input["signup_fullname"]), trim($input["login_email"]), trim($input["login_password"]));		
		return;		
	}
	
	function heyoya_is_store(){
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}		
	
		$options = get_option('heyoya_options', null);
		
		$last_login_time = intval($options["last_login_time"], 10);
		if ( (time() - $last_login_time) > 10800 ){
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}		
		
		if (
				$options == null ||
				!isset($options["login_email"]) ||
				!is_email($options["login_email"]) ||
				!isset($options["apikey"]) ||
				!isset($_POST['state']) ||
				!preg_match('/^[0-1]{1}$/i', trim($_POST['state']))
		){
			echo "0";
			exit();
		}
	
		if (trim($_POST['state']) == "1")
			$options["is_store"] = true;
		else
			$options["is_store"] = false;
	
		update_option("heyoya_options", $options);
		
		echo "1";		
		exit();
	}
	
	function heyoya_design_mode(){
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}		
	
		$options = get_option('heyoya_options', null);
		
		$last_login_time = intval($options["last_login_time"], 10);
		if ( (time() - $last_login_time) > 10800 ){
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		if (
				$options == null ||
				!isset($options["login_email"]) ||
				!is_email($options["login_email"]) ||
				!isset($options["apikey"]) ||
				!isset($_POST['title']) ||
				!isset($_POST['title_color_text']) ||
				!isset($_POST['title_color_background']) ||
				trim($_POST['title']) == "" ||
				trim($_POST['title_color_text']) == "" ||
				trim($_POST['title_color_background']) == ""				
		){
			echo "0";
			exit();
		}
		
		$options["title"] = trim($_POST['title']);
		$options["title_color_text"] = trim($_POST['title_color_text']);
		$options["title_color_background"] = trim($_POST['title_color_background']);		
		update_option("heyoya_options", $options);
		
		echo "1";
		exit();
	}
	
	function heyoya_placement_path(){
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}		
	
		$options = get_option('heyoya_options', null);
		
		$last_login_time = intval($options["last_login_time"], 10);
		if ( (time() - $last_login_time) > 10800 ){
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		if (
				$options == null ||
				!isset($options["login_email"]) ||
				!is_email($options["login_email"]) ||
				!isset($options["apikey"]) ||
				!isset($_POST['path']) ||				
				trim($_POST['path']) == ""
		){
			echo "0";
			exit();
		}
	
		$options["placement_path"] = trim($_POST['path']);
		update_option("heyoya_options", $options);
	
		echo "1";
		exit();
	}
	
	function heyoya_purchased(){
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		$options = get_option('heyoya_options', null);
		
		$last_login_time = intval($options["last_login_time"], 10);
		if ( (time() - $last_login_time) > 10800 ){
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		
		if (
				$options == null || 
				!isset($options["login_email"]) || 
				!is_email($options["login_email"]) || 
				!isset($options["apikey"]) || 
				!isset($_POST['state']) || 
				!preg_match('/^[0-1]{1}$/i', trim($_POST['state'])) 
			){
				echo "0";
				exit();				
		}	
		
		if (trim($_POST['state']) == "1")
			$options["purchased"] = true;
		else
			$options["purchased"] = false;
		
		update_option("heyoya_options", $options);
		
		echo "1";
		exit();
	}
	
	function heyoya_logout(){
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}		
		
		echo "1";
		
		$options = get_option('heyoya_options', null);
		if ($options == null){
			exit();
			return;		
		}
		
		if (isset($options["apikey"]))
			$options["apikey"] = null;
		
		if (isset($options["login_email"]))
			$options["login_email"] = null;
		
		if (isset($options["last_login_time"]))
			$options["last_login_time"] = null;
		
		if (isset($options["last_method"]))
			$options["last_method"] = null;
		
		if (isset($options["affiliate_id"]))
			$options["affiliate_id"] = null;

		if (isset($options["is_store"]))
			$options["is_store"] = null;
		
		if (isset($options["title"]))
			$options["title"] = null;
		
		if (isset($options["title_color_text"]))
			$options["title_color_text"] = null;
		
		if (isset($options["title_color_background"]))
			$options["title_color_background"] = null;
		
		if (isset($options["placement_path"]))
			$options["placement_path"] = null;		
		
		$options["purchased"] = false;
		
		
		update_option("heyoya_options", $options);

		exit();
	}

	
	function set_heyoya_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		
		$session_valid = false;
		$error_raised = false;
		$error_code = 0;
		$last_method = "login";
		$options = get_option('heyoya_options', null);
		
		if ($options != null && isset($options["error_raised"]) && trim($options["error_raised"]) != "" && isset($options["last_method"])){
			$error_raised = true;
			$last_method = $options["last_method"];
			$error_code = $options["error_code"];
			
			$options["error_raised"] = false;
			$options["last_method"] = null;
			$options["error_code"] = null;
			
			update_option("heyoya_options", $options);			
		}
		
		if (!$error_raised && ($options != null && isset($options["last_login_time"]) && is_numeric($options["last_login_time"]))){
			$last_login_time = intval($options["last_login_time"], 10);
			if ( (time() - $last_login_time) < 10800 )
				$session_valid = true;
		}
		
		$is_heyoya_installed = is_heyoya_installed();
		
		if (!$error_raised && $is_heyoya_installed && !$session_valid){
			$this->login_user($options, $options["login_email"], null);
			$is_heyoya_installed = is_heyoya_installed();
				
			if (isset($options["error_raised"])){
				$session_valid = false;
// 				$error_raised = true;
				$last_method = $options["last_method"];
// 				$error_code = $options["error_code"];
					
				$options["error_raised"] = false;
				$options["last_method"] = null;
				$options["error_code"] = null;
					
				update_option("heyoya_options", $options);
			}				
		}
		
		if (!$is_heyoya_installed || $error_raised || !$session_valid){
			//echo '<pre>'; print_r($options); echo '</pre>';
		?>
		<div id="heyoyaAdmin">
			<div id="heyoyaSignUpDiv" class="<?php echo $last_method != "login"?"":"invisible" ?>">
				<h2>Create Heyoya Account</h2>				
				<div class="updated <?php echo $error_raised?"":"invisible" ?>">
					<p>
						<span class="invisible email_invalid">Email address is <strong>invalid</strong></span>
						<span class="invisible email_missing">Email address is <strong>required</strong></span>
						<span class="invisible name_missing">Name is <strong>required</strong></span>
						<span class="invisible password_missing">Password is <strong>required</strong></span>
						<span class="<?php echo ($error_raised && $error_code == -1)?"":"invisible" ?> general_error">An error has occurred, please try again in a few seconds</span>
						<span class="<?php echo ($error_raised && $error_code == -2)?"":"invisible" ?> general_error">Please make sure to fill the fields below</span>
						<span class="<?php echo ($error_raised && $error_code == -3)?"":"invisible" ?> general_error">Email already exists</span>						
					</p>																
				</div>
				<form action="options.php" method="post">
				<?php settings_fields('heyoya-options'); ?>
				<?php do_settings_sections('admin-signup'); ?>
		 
				<input class="button-primary button" name="Submit" type="submit" value="<?php esc_attr_e('Create account'); ?>" /><span class="alternate">Already registered?&nbsp;&nbsp;<a id="login">Log in!</a></span>
				</form>
			</div> 
			<div id="heyoyaLoginDiv" class="<?php echo $last_method == "login"?"":"invisible" ?>">
				<h2>Login with your Heyoya account</h2>			
				<div class="updated <?php echo $error_raised?"":"invisible" ?>">
					<p>
						<span class="invisible email_invalid">Email address is <strong>invalid</strong></span>
						<span class="invisible email_missing">Email address is <strong>required</strong></span>
						<span class="invisible password_missing">password is <strong>required</strong></span>
						<span class="<?php echo ($error_raised && $error_code == -1)?"":"invisible" ?> general_error">Email or password is incorrect</span>
						<span class="<?php echo ($error_raised && $error_code == -2)?"":"invisible" ?> general_error">Please make sure to fill the fields below</span>
						<span class="<?php echo ($error_raised && $error_code == -5)?"":"invisible" ?> general_error">An error has occurred, please try again in a few seconds</span>
					</p>
				</div>
				<form action="options.php" method="post">
				<?php settings_fields('heyoya-options'); ?>
				<?php do_settings_sections('admin-login'); ?>
				
				<input class="button-primary button" name="Submit" type="submit" value="<?php esc_attr_e('Log in'); ?>" /><span class="alternate">No account?&nbsp;&nbsp;<a id="createAccount">Sign up!</a></span>
				</form>
			</div> 
		</div>
		<?php } else { 
			//echo '<pre>'; print_r($options); echo '</pre>';
			?>			
			<div id="heyoyaContainer" aa="<?php echo $options["apikey"] ?>"></div>
		<?php }
	}
	
	
	function heyoya_admin_head(){
		if (isset($_GET['page']) && $_GET['page'] == 'heyoya-options') {?>
		<link rel='stylesheet' href='<?php echo esc_url( plugins_url( 'css/admin.css', __FILE__ ) ); ?>' type='text/css' />
	<?php }
	}

}
?>
