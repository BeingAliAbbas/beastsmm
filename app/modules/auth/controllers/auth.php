<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require APPPATH.'./modules/auth/libraries/google/autoload.php';

class auth extends MX_Controller {
	public $tb_users;
	public $tb_user_logs;
	public $tb_user_block_ip;
	public $google_capcha;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		$this->tb_users 			= USERS;
		$this->tb_user_logs   		= USER_LOGS;
		$this->tb_user_block_ip   	= USER_BLOCK_IP;

		if (get_option("enable_goolge_recapcha", '')  &&  get_option('google_capcha_site_key') != "" && get_option('google_capcha_secret_key') != "") {
			$this->recaptcha = new \ReCaptcha\ReCaptcha(get_option('google_capcha_secret_key'));
		}

		if(session("uid") && segment(2) != 'logout'){
			redirect(cn("statistics"));
		}
	}

	public function index(){
		redirect(cn("auth/login"));
	}


	public function login(){
		$this->lang->load('../../../../themes/'.get_theme().'/language/english/'.get_theme());
		$data = array();
		$this->template->set_layout('blank_page');
		$this->template->build('../../../themes/'.get_theme().'/views/sign_in', $data);
	}

	public function logout(){
		/*----------  Insert User logs  ----------*/
		$this->insert_user_activity_logs('logout');
		unset_session("uid");
		unset_session("auto_confirm");
		unset_session("user_current_info");
		if (get_option("is_maintenance_mode")) {
			delete_cookie("verify_maintenance_mode");
		}
		redirect(cn(''));
	}

	public function signup(){
		if(get_option('disable_signup_page')){
			redirect(cn('auth/login'));
		}
		$this->lang->load('../../../../themes/'.get_theme().'/language/english/'.get_theme());
		$data = array();
		$this->template->set_layout('blank_page');
		$this->template->build('../../../themes/'.get_theme().'/views/sign_up', $data);
	}

	public function forgot_password(){
		$this->lang->load('../../../../themes/'.get_theme().'/language/english/'.get_theme());
		$data = array();
		$this->template->set_layout('blank_page');
		$this->template->build('../../../themes/'.get_theme().'/views/forgot_password', $data);
	}

	public function reset_password(){
		/*----------  check users exists  ----------*/
		$reset_key = segment(3);
		$user = $this->model->get("id, ids, email", $this->tb_users, "reset_key = '{$reset_key}'");
		if (!empty($user)) {
			// redirect to change password page
			$data = array(
				"reset_key" => $reset_key,
			);
			$this->lang->load('../../../../themes/'.get_theme().'/language/english/'.get_theme());
			$this->template->set_layout('blank_page');
			$this->template->build('../../../themes/'.get_theme().'/views/change_password', $data);
		}else{
			redirect(cn("auth/login"));
		}
	}

	public function ajax_sign_up($ids = ""){
    $terms              = post('terms');
    $first_name         = post('first_name');
    $last_name          = post('last_name');
    $email              = post('email');
    $whatsapp_number    = post('whatsapp_number');
    $password           = post('password');
    $re_password        = post('re_password');
    $timezone           = post('timezone');
    if(get_option("enable_affiliate") == "1"){
        $referral       = post('referral');
    }else{
        $referral       = "";
    }

    if($first_name == '' || $last_name == '' || $password == ''|| $email == ''){
        ms(array(
            'status'  => 'error',
            'message' => lang("please_fill_in_the_required_fields"),
        ));
    }

    if (!preg_match("/^[a-zA-Z ]*$/", $first_name)) {
        ms(array(
            'status'  => 'error',
            'message' => lang("only_letters_and_white_space_allowed"),
        ));
    }

    if (!preg_match("/^[a-zA-Z ]*$/", $last_name)) {
        ms(array(
            'status'  => 'error',
            'message' => lang("only_letters_and_white_space_allowed"),
        ));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        ms(array(
            'status'  => 'error',
            'message' => lang("invalid_email_format"),
        ));
    }

    // WhatsApp number validation for +92 and no leading 0 after +92
    if (!preg_match("/^\+92[3-9][0-9]{9}$/", $whatsapp_number)) {
        ms(array(
            'status'  => 'error',
            'message' => lang("invalid_whatsapp_number_format"),
        ));
    }

    if($password != ''){
        if(strlen($password) < 6){
            ms(array(
                'status'  => 'error',
                'message' => lang("Password_must_be_at_least_6_characters_long"),
            ));
        }

        if($re_password!= $password){
            ms(array(
                'status'  => 'error',
                'message' => lang("Password_must_be_at_least_6_characters_long"),
            ));
        }
    }

    if (!$terms) {
        ms(array(
            'status'  => 'error',
            'message' => lang("oops_you_must_agree_with_the_terms_of_services_or_privacy_policy"),
        ));
    }

    if ($this->is_banned_ip_address()) {
        ms(array(
            "status"  => "error",
            "message" => "Access from your IP address has been blocked for security reasons. Please contact the administrator!"
        ));
    }

    if (isset($_POST['g-recaptcha-response']) && get_option("enable_goolge_recapcha", '')  &&  get_option('google_capcha_site_key') != "" && get_option('google_capcha_secret_key') != "") {
        $resp = $this->recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                    ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if (!$resp->isSuccess()) {
            ms(array(
                'status'  => 'error',
                'message' => lang("please_verify_recaptcha"),
            ));
        }
    }

    if(get_option("enable_affiliate") == "1"){
        if($referral != ""){
            $affiliate_user   = $this->model->get('*', $this->tb_users, ["affiliate_id" => $referral]);
            if(!empty($affiliate_user)){
                $afl_bal_update = $affiliate_user->affiliate_bal_available+get_option('affiliate_bonus');
                $user_update_data = [
                    "affiliate_bal_available" => $afl_bal_update,
                ];
                $this->db->update($this->tb_users, $user_update_data, ["id" => $affiliate_user->id]);
            }else{
                $referral = "";
            }
        }
    }

    // Get Settings (Limit payments) for new user
    $this->load->model('payments/payments_model', 'payments_model');
    $limit_payments = $this->payments_model->get_payments_list_for_new_user();
    $settings = [
        'limit_payments' => $limit_payments
    ];

    $data = array(
        "ids"                     => ids(),
        "first_name"             => $first_name,
        "last_name"              => $last_name,
        "whatsapp_number"        => $whatsapp_number, // Add WhatsApp number here
        "password"               => $this->model->app_password_hash($password),
        "timezone"               => $timezone,
        "status"                 => get_option('is_verification_new_account', 0) ? 0 : 1,
        "api_key"                => create_random_string_key(32),
        "referral_id"            => $referral,
        "affiliate_id"           => rand(10000,99999999),
        "settings"               => json_encode($settings),
        'history_ip'             => get_client_ip(),
        "reset_key"              => create_random_string_key(32),
        "activation_key"         => create_random_string_key(32),
        "changed"                => NOW,
    );

    if(get_option("enable_signup_skype_field", '')){
        $skype_id = post("skype_id");
        if($skype_id == ''){
            ms(array(
                'status'  => 'error',
                'message' => lang("please_fill_in_the_required_fields"),
            ));
        }
        $more_information = array(
            "skype_id" => $skype_id,
        );
        $data['more_information'] = json_encode($more_information);
    }

    if($email != ''){
        // check email
        $checkUserEmail = $this->model->get('email, ids', $this->tb_users,"email='{$email}'");
        if(!empty($checkUserEmail)){
            ms(array(
                'status'  => 'error',
                'message' => lang("An_account_for_the_specified_email_address_already_exists_Try_another_email_address"),
            ));
        }

        $data['created'] = NOW;
        $data['email']   = $email;

        if($this->db->insert($this->tb_users, $data)){
            $uid = $this->db->insert_id();
            if (get_option('is_verification_new_account', 0)) {
                $check_send_email_issue = $this->model->send_email(get_option('verification_email_subject', ''), get_option('verification_email_content', 0), $uid);
                if($check_send_email_issue){
                    ms(array(
                        "status" => "error",
                        "message" => $check_send_email_issue,
                    ));
                }

                ms(array(
                    "status"  => "success",
                    "message" => lang('thank_you_for_signing_up_please_check_your_email_to_complete_the_account_verification_process')
                ));
            }else{
                set_session('uid', $uid);
                $data_session = array(
                    'role'       => 'user',
                    'email'      => $email,
                    'first_name' => $first_name,
                    'last_name'  => $last_name,
                    'timezone'   => $timezone,
                );
                set_session('user_current_info', $data_session);
                /*----------  Insert User logs  ----------*/
                $this->insert_user_activity_logs('logout');

                /*----------  Check is send welcome email or not  ----------*/
                if (get_option("is_welcome_email", '')) {
                    $check_send_email_issue = $this->model->send_email(get_option('email_welcome_email_subject', ''), get_option('email_welcome_email_content', 0), $uid);
                    if($check_send_email_issue){
                        ms(array(
                            "status" => "error",
                            "message" => $check_send_email_issue,
                        ));
                    }
                }

                /*----------  Send email notificaltion for Admin  ----------*/
                if (get_option("is_new_user_email", '')) {
                    $subject = get_option('email_new_registration_subject', '');
                    $subject = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $subject);

                    $email_content = get_option('email_new_registration_content', '');
                    $email_content = str_replace("{{user_firstname}}", $first_name, $email_content);
                    $email_content = str_replace("{{user_lastname}}", $last_name, $email_content);
                    $email_content = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $email_content);
                    $email_content = str_replace("{{user_timezone}}", $timezone, $email_content);
                    $email_content = str_replace("{{user_email}}", $email, $email_content);

                    $admin_id = $this->model->get("id", $this->tb_users, "role = 'admin'","id","ASC")->id;
                    if ($admin_id == "") {
                        $admin_id = 1;
                    }

                    $check_send_email_issue = $this->model->send_email( $subject, $email_content, $admin_id, false);
                    if($check_send_email_issue){
                        ms(array(
                            "status" => "error",
                            "message" => $check_send_email_issue,
                        ));
                    }
                }
            }
			

            unset_session("referral");
    // Send Signup Alert to Admin via WhatsApp
	$this->send_signup_alert($uid); // Call the send_signup_alert method

            ms(array(
                'status'  => 'success',
                'message' => lang("welcome_you_have_signed_up_successfully"),
            ));
        }else{
            ms(array(
                "status"  => "Failed",
                "message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
            ));
        }
    }
}

public function send_signup_alert($user_id) {
    // Fetch user details
    $user_info = $this->model->get("*", $this->tb_users, ['id' => $user_id]);

    if (empty($user_info)) {
        ms(array(
            "status" => "error",
            "message" => "User not found"
        ));
        return;
    }

    // Get the WhatsApp configuration from the database
    $whatsapp_config = $this->model->get("url, api_key, admin_phone", "whatsapp_config", []);
    if (empty($whatsapp_config) || empty($whatsapp_config->url) || empty($whatsapp_config->api_key)) {
        ms(array(
            "status" => "error",
            "message" => "WhatsApp API URL or API key not configured"
        ));
        return;
    }

    // Get API URL, API key, and admin phone from config
    $apiUrl = $whatsapp_config->url;
    $apiKey = $whatsapp_config->api_key;
    $adminPhone = $whatsapp_config->admin_phone;

    // Get the user's WhatsApp number
    $user_phone_number = isset($user_info->whatsapp_number) ? $user_info->whatsapp_number : 'N/A';

    // Sanitize the phone number: Remove the leading '+' if it exists
    $user_phone_number = ltrim($user_phone_number, '+');

    // Prepare the message
    $message = "*Welcome to BeastSMM! 🎉* \n\n";
    $message .= "Hello *{$user_info->first_name} {$user_info->last_name}* 👋,\n\n";
    $message .= "Thank you for signing up with us! 🙏 Your registration has been successfully completed. ✅ \n\n";
    $message .= "If you need help, just let us know! 😊💬 We're here to assist you! 🛠️";

    // Send the WhatsApp message using cURL to the user's number
    $data = array(
        'apiKey' => $apiKey, // Include API key for validation
        'phoneNumber' => $user_phone_number,
        'message' => $message
    );

    // Initialize cURL
    $ch = curl_init($apiUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Handle the response (optional)
    if ($response === false) {
        ms(array(
            "status" => "error",
            "message" => "Failed to send WhatsApp message to user"
        ));
    } else {
        ms(array(
            "status" => "success",
            "message" => "Welcome, you have signed up successfully"
        ));
    }
}



	public function activation($activation_key = ""){
		$user = $this->model->get("id, first_name, last_name, timezone, email, activation_key", $this->tb_users, "activation_key = '".$activation_key."'");
		if(!empty($user)){
			$this->db->update($this->tb_users, ['status' => 1, 'activation_key' => 1], ['id' => $user->id]);
			/*----------  Check is send welcome email or not  ----------*/
			if (get_option("is_welcome_email", '')) {
				$check_send_email_issue = $this->model->send_email(get_option('email_welcome_email_subject', ''), get_option('email_welcome_email_content', 0), $user->id);
				if($check_send_email_issue){
					ms(array(
						"status" => "error",
						"message" => $check_send_email_issue,
					));
				}
			}

			/*----------  Send email notificaltion for Admin  ----------*/
			if (get_option("is_new_user_email", '')) {
				$subject = get_option('email_new_registration_subject', '');
				$subject = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $subject);

				$email_content = get_option('email_new_registration_content', '');
				$email_content = str_replace("{{user_firstname}}", $user->first_name, $email_content);
				$email_content = str_replace("{{user_lastname}}", $user->last_name, $email_content);
				$email_content = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $email_content);
				$email_content = str_replace("{{user_timezone}}", $user->timezone, $email_content);
				$email_content = str_replace("{{user_email}}", $user->email, $email_content);

				$admin_id = $this->model->get("id", $this->tb_users, "role = 'admin'","id","ASC")->id;
				if ($admin_id == "") {
					$admin_id = 1;
				}
				
				$check_send_email_issue = $this->model->send_email( $subject, $email_content, $admin_id, false);
				if($check_send_email_issue){
					ms(array(
						"status" => "error",
						"message" => $check_send_email_issue,
					));
				}
			}

			$this->lang->load('../../../../themes/'.get_theme().'/language/english/'.get_theme());
			$data = array();
			$this->template->set_layout('blank_page');
			$this->template->build('../../../themes/'.get_theme().'/views/activation_successfully', $data);
		}else{
			redirect(cn("auth/login"));
		}
	}
	


// New method for sending sign-in alert via WhatsApp with login details
public function send_signin_alert($user_id) {
    // Fetch user details
    $user_info = $this->model->get("*", $this->tb_users, ['id' => $user_id]);

    if (empty($user_info)) {
        ms(array(
            "status" => "error",
            "message" => "User not found"
        ));
        return;
    }

    // Get the WhatsApp configuration from the database
    $whatsapp_config = $this->model->get("url, api_key, admin_phone", "whatsapp_config", []);
    if (empty($whatsapp_config) || empty($whatsapp_config->url) || empty($whatsapp_config->api_key) || empty($whatsapp_config->admin_phone)) {
        ms(array(
            "status" => "error",
            "message" => "WhatsApp API URL, API key, or Admin phone not configured"
        ));
        return;
    }

    // Get API URL, API key, and admin phone from config
    $apiUrl = $whatsapp_config->url;
    $apiKey = $whatsapp_config->api_key;
    $adminPhone = ltrim($whatsapp_config->admin_phone, '+'); // Remove leading "+" if exists

    // Capture the user's IP address and browser information
    $user_ip = $_SERVER['REMOTE_ADDR'];  // Get the IP address
    $user_browser = $_SERVER['HTTP_USER_AGENT'];  // Get the browser info

    // Prepare the message with login details
    $message = "*🔔 New Login Alert - BeastSMM 🚀*\n\n";
    $message .= "👤 *User:* {$user_info->first_name} {$user_info->last_name}\n";
    $message .= "📧 *Email:* {$user_info->email}\n";
    $message .= "📌 *User ID:* {$user_info->id}\n\n";
    $message .= "🔑 *Login Details:*\n";
    $message .= "📍 *IP Address:* {$user_ip}\n";
    $message .= "🌐 *Browser:* {$user_browser}\n\n";

    // Send the WhatsApp message using cURL to the admin's phone number
    $data = array(
        'apiKey' => $apiKey, // Include API key for validation
        'phoneNumber' => $adminPhone,
        'message' => $message
    );

    // Initialize cURL
    $ch = curl_init($apiUrl);

    // Set cURL options
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute the cURL request
    $response = curl_exec($ch);

    // Close cURL session
    curl_close($ch);

    // Handle the response
    if ($response === false) {
        ms(array(
            "status" => "error",
            "message" => "Failed to login"
        ));
    } else {
        ms(array(
            "status" => "success",
            "message" => "You have successfully logged in!"
        ));
    }
}


// Updated ajax_sign_in method
public function ajax_sign_in() {
    $email    = post("email");
    $password = post("password");
    $remember = post("remember");

    // Basic validation for input fields
    if ($email == "") {
        ms(array(
            "status"  => "error",
            "message" => lang("email_is_required")
        ));
    }

    if ($password == "") {
        ms(array(
            "status"  => "error",
            "message" => lang("Password_is_required")
        ));
    }

    // Check if the IP is banned
    if ($this->is_banned_ip_address()) {
        ms(array(
            "status"  => "error",
            "message" => "Access from your IP address has been blocked for security reasons. Please contact the administrator!"
        ));
    }

    // Google reCAPTCHA validation (if enabled)
    if (isset($_POST['g-recaptcha-response']) && get_option("enable_google_recaptcha", '') && get_option('google_recaptcha_site_key') != "" && get_option('google_recaptcha_secret_key') != "") {
        $resp = $this->recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                    ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
        if (!$resp->isSuccess()) {
            ms(array(
                'status'  => 'error',
                'message' => lang("please_verify_recaptcha"),
            ));
        }
    }

    // Fetch user details from the database
    $user = $this->model->get("id, status, ids, email, password, role, first_name, last_name, timezone, whatsapp_number", $this->tb_users, ['email' => $email]);

    // Check if the user exists
    if (!$user) {
        ms(array(
            "status"  => "error",
            "message" => lang("email_address_and_password_that_you_entered_doesnt_match_any_account_please_check_your_account_again")
        ));
        return;
    }

    // Verify password securely using password_verify()
    if (!password_verify($password, $user->password)) {
        ms(array(
            "status"  => "error",
            "message" => lang("email_address_and_password_that_you_entered_doesnt_match_any_account_please_check_your_account_again")
        ));
        return;
    }

    // Check if the user is activated
    if ($user->status != 1) {
        ms(array(
            "status"  => "error",
            "message" => lang("your_account_has_not_been_activated")
        ));
        return;
    }

    // Set session data after successful login
    set_session("uid", $user->id);
    $data_session = array(
        'role'       => $user->role,
        'email'      => $user->email,
        'first_name' => $user->first_name,
        'last_name'  => $user->last_name,
        'timezone'   => $user->timezone,
    );
    set_session('user_current_info', $data_session);

    // Log the user activity (IP address)
    $this->model->history_ip($user->id);

    // Insert User activity logs
    $this->insert_user_activity_logs();

    // Send WhatsApp alert on successful sign-in
    $this->send_signin_alert($user->id);

    // Set cookies if "remember me" is checked
    if ($remember) {
        set_cookie("cookie_email", encrypt_encode(post("email")), 1209600); // 14 days
        set_cookie("cookie_pass", encrypt_encode(post("password")), 1209600); // 14 days
    } else {
        delete_cookie("cookie_email");
        delete_cookie("cookie_pass");
    }

    // Update the reset key
    $this->db->update($this->tb_users, ['reset_key' => ids() ], ['id' => $user->id]);

    // Return success response
    ms(array(
        "status"  => "success",
        "message" => lang("Login_successfully")
    ));
}

	

	public function ajax_forgot_password(){
		$email = post("email");

		if($email == ""){
			ms(array(
				"status"  => "error",
				"message" => lang("email_is_required")
			));
		}

		if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
		  	ms(array(
				"status"  => "error",
				"message" => lang("invalid_email_format")
			));
		}

		if (isset($_POST['g-recaptcha-response']) && get_option("enable_goolge_recapcha", '')  &&  get_option('google_capcha_site_key') != "" && get_option('google_capcha_secret_key') != "") {
			$resp = $this->recaptcha->setExpectedHostname($_SERVER['SERVER_NAME'])
                      ->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
            if (!$resp->isSuccess()) {
            	ms(array(
					'status'  => 'error',
					'message' => lang("please_verify_recaptcha"),
				));
            }
		}

		$user = $this->model->get("*", USERS, "email = '{$email}'");
		if(!empty($user)){
			$email_error = $this->model->send_email(get_option("email_password_recovery_subject", ""), get_option("email_password_recovery_content", ""), $user->id);

			if($email_error){
				ms(array(
					"status"  => "error",
					"message" => $email_error
				));
			}

			ms(array(
				"status"  => "success",
				"message" => lang("we_have_send_you_a_link_to_reset_password_and_get_back_into_your_account_please_check_your_email"),
			));
		}else{
			ms(array(
				"status" => "error",
				"message" => lang("the_account_does_not_exists")
			));
		}
	}

	public function ajax_reset_password($reset_key = ""){
		$user = $this->model->get("id, ids, email", $this->tb_users, "reset_key = '{$reset_key}'");
		$password           = post('password');
		$re_password        = post('re_password');

		if($password == '' || $re_password == ''){
			ms(array(
				'status'  => 'error',
				'message' => lang("please_fill_in_the_required_fields"),
			));
		}

		if($password != ''){
			if(strlen($password) < 6){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_must_be_at_least_6_characters_long"),
				));
			}

			if($re_password != $password){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_must_be_at_least_6_characters_long"),
				));
			}
		}

		if (!empty($user)) {
			$data = array(
				"password"  => $this->model->app_password_hash($password),
				"reset_key" => ids(),
				"changed"	=> NOW,
			);

			$this->db->update($this->tb_users, $data, "id = '".$user->id."'");
			if ($this->db->affected_rows() > 0) {
				ms(array(
					"status"   => "success",
					"message"  => lang("your_password_has_been_successfully_changed"),
				));
			}else{
				ms(array(
					"status"  => "Failed",
					"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
				));
			}
		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
			));
		}
	}

	private function insert_user_activity_logs($type = ''){
		if (!$this->db->table_exists($this->tb_user_logs)) {
			return false;
		}
		if (session('uid')) {
			$ip_address = get_client_ip();
			$data_user_logs = array(
				"ids"		=> ids(),
				"uid"		=> session('uid'),
				"ip"		=> $ip_address,
				"type"		=> ($type == 'logout') ? 0 : 1,
				"created"   => NOW,
			);
			$location = get_location_info_by_ip($ip_address);
			if ($location->country != 'Unknown' && $location->country != '') {
				$data_user_logs['country'] = $location->country;
			}else{
				$data_user_logs['country'] = 'Unknown';
			}
			$this->db->insert($this->tb_user_logs, $data_user_logs);
		}
	}

	private function is_banned_ip_address(){
		if (!$this->db->table_exists($this->tb_user_block_ip)) {
			return false;
		}
		$ip_address = get_client_ip();
		$check_item = $this->model->get('ip', $this->tb_user_block_ip, ["ip" => $ip_address]);
		if (!empty($check_item)) {
			return true;
		}
		return false;
	}
}