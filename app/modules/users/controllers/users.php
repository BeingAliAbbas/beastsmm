<?php
defined('BASEPATH') OR exit('No direct script access allowed');
	// Include the Composer autoloader to load PHPMailer and other dependencies
	require 'vendor/autoload.php';

	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;
	
	

class users extends MX_Controller {
	public $tb_users;
	public $tb_users_price;
	public $tb_user_mail_logs;
	public $tb_payments;
	public $tb_categories;
	public $tb_transaction_logs;
	public $tb_services;
	public $columns;
	public $module_name;
	public $module;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		//Config Module
		$this->tb_users                 = USERS;
		$this->tb_users_price           = USERS_PRICE;
		$this->tb_payments              = PAYMENTS_METHOD;
		$this->tb_categories            = CATEGORIES;
		$this->tb_services              = SERVICES;
		$this->tb_transaction_logs      = TRANSACTION_LOGS;
		$this->tb_user_mail_logs        = USER_MAIL_LOGS;
		$this->module_name              = 'Users';
		$this->module                   = get_class($this);
		$this->module_icon              = "fa ft-users";
		$this->columns = array(
			"name"           => lang("User_profile"),
			"balance"        => lang('Funds'),
			"total_deposit"  => lang('Total Deposit'),
			"custom_rate"    => lang("custom_rate"),
			"desc"           => lang('Description'),
			"created"        => lang("Created"),
			"status"         => lang('Status'),
		);
	}

	public function index() {
		$page = (int)get("p");
		$page = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = array();
		$query_string = "";
		if (!empty($query)) {
			$query_string = "?" . http_build_query($query);
		}
	
		// Fetch users with total orders
		$users = $this->model->get_users_list(false, "all", $limit_per_page, $page * $limit_per_page);
		
		foreach ($users as &$user) {
			$user->total_orders = $this->model->get_total_orders_by_user_id($user->id); // Get total orders for each user
		}
	
		$config = array(
			'base_url' => cn(get_class($this) . $query_string),
			'total_rows' => $this->model->get_users_list(true),
			'per_page' => $limit_per_page,
			'use_page_numbers' => true,
			'prev_link' => '<i class="fe fe-chevron-left"></i>',
			'first_link' => '<i class="fe fe-chevrons-left"></i>',
			'next_link' => '<i class="fe fe-chevron-right"></i>',
			'last_link' => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();
	
		$data = array(
			"module" => get_class($this),
			"columns" => $this->columns,
			"users" => $users,
			"links" => $links,
		);
	
		$this->template->build('index', $data);
	}
	
	public function deposit_viewer() {
		try {
			if (isset($_GET['uid']) && is_numeric($_GET['uid'])) {
				$uid = intval($_GET['uid']);
				$this->load->model('Transaction_model'); // Make sure the model is loaded here
				$total_deposit = $this->Transaction_model->get_total_deposit($uid); // Call model method
	
				if ($total_deposit !== false) {
					echo json_encode(["status" => "success", "total_deposit" => number_format($total_deposit, 2)]);
				} else {
					echo json_encode(["status" => "error", "message" => "No deposits found."]);
				}
			} else {
				echo json_encode(["status" => "error", "message" => "Invalid user ID."]);
			}
		} catch (Exception $e) {
			echo json_encode(["status" => "error", "message" => $e->getMessage()]);
		}
	}
	

	public function update($ids = ""){
		// Fetch the user data based on the unique ID (ids)
		$user = $this->model->get("*", $this->tb_users, "ids = '{$ids}'");
	
		// Check if the user exists
		if (empty($user)) {
			show_404();
			return;
		}
	
		// Assuming the WhatsApp number is stored in a field called 'whatsapp_number'
		$whatsapp_number = isset($user->whatsapp_number) ? $user->whatsapp_number : '';
	
		// Get the payment methods (for example)
		$payments_defaut = $this->model->fetch('id, type, name, status', $this->tb_payments, ['status' => 1]);
	
		// Limit payments based on user settings, or set default limits
		$settings = json_decode($user->settings);
		$limit_payments = isset($settings->limit_payments) ? (array)$settings->limit_payments : [];
		foreach ($payments_defaut as $payment) {
			if (!isset($limit_payments[$payment->type])) {
				$limit_payments[$payment->type] = 1;  // Default limit for each payment method
			}
		}
	
		// Prepare data to pass to the view
		$data = array(
			"module"          => get_class($this),
			"user"            => $user,  // User data including WhatsApp number
			"whatsapp_number" => $whatsapp_number,  // WhatsApp number specifically
			"payments_defaut" => $payments_defaut,
			"limit_payments"  => $limit_payments,
		);
	
		// Load the 'update' view with the user data, including the WhatsApp number
		$this->template->build('update', $data);
	}
	

	public function mail($ids = ""){
		$user    = $this->model->get("ids, first_name, last_name, email", $this->tb_users, "ids = '{$ids}' ");

		$data = array(
			"module"    => get_class($this),
			"user" 		=> $user,
		);
		$this->load->view('mail_to_user', $data);
	}



	public function sendFundAddedEmail($emailTo, $firstName, $lastName, $amount, $transaction_id, $payment_method) {
		$mail = new PHPMailer(true);
	
		try {
			// Server settings
			$mail->isSMTP();
			$mail->Host = 'mail.beastsmm.pk'; // Your SMTP server
			$mail->SMTPAuth = true;
			$mail->Username = 'transactions@beastsmm.pk'; // SMTP username
			$mail->Password = 'Aliabbas321@'; // SMTP password
	
			// SSL on port 465
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // SSL encryption
			$mail->Port = 465;
	
			// Recipients
			$mail->setFrom('transactions@beastsmm.pk', 'Beast SMM Transaction');
			$mail->addAddress($emailTo); // User's email
	
			// Determine the payment method and set the logo
			$paymentLogo = '';
			$logoPath = '';
			$cid = '';
	
			// Fallback Image (Base64)
			$fallbackLogo = 'data:image/png;base64, iVBORw0KGgoAAAANSUhEUgAA...'; // Put your base64 encoded placeholder logo here
	
			if ($payment_method == 'EasyPaisa') {
				// Embedding the EasyPaisa logo
				$logoPath = '/path/to/your/assets/images/payments/easypaisa.png'; // Local path or absolute path
				$cid = 'easypaisa_logo';
				$mail->addEmbeddedImage($logoPath, $cid, 'easypaisa.png', 'base64', 'image/png');
				$paymentLogo = "<img src='cid:$cid' alt='EasyPaisa Logo' style='width: 150px;' onerror=\"this.src='$fallbackLogo'\">";
			} elseif ($payment_method == 'JazzCash') {
				// Embedding the JazzCash logo
				$logoPath = 'assets/images/payments/jazzcash.png'; // Local path or absolute path
				$cid = 'jazzcash_logo';
				$mail->addEmbeddedImage($logoPath, $cid, 'jazzcash.png', 'base64', 'image/png');
				$paymentLogo = "<img src='cid:$cid' alt='JazzCash Logo' style='width: 150px;' onerror=\"this.src='$fallbackLogo'\">";
			}
	
			// Content
			$mail->isHTML(true);
			$mail->Subject = 'Funds Added Successfully to Your Account';
			$mail->Body = "
<div style='font-family: Arial, sans-serif; line-height: 1.8; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 10px; background-color: #ffffff; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;'>
    
    <!-- Header -->
    <div style='background-color: #f1f1f1; padding: 20px; text-align: center;'>
        <img src='https://beastsmm.pk/assets/uploads/userda4b9237bacccdf19c0760cab7aec4a8359010b0/03f55486b7bbbc80dd4482e269a2a1b9.png' alt='Beast SMM Logo' style='max-width: 150px; height: auto;'>
    </div>
    
    <!-- Body -->
    <div style='padding: 20px;'>
        <div style='text-align: center; margin-bottom: 20px;'>
            $paymentLogo
        </div>

        <h3 style='color: #4CAF50; text-align: center; font-size: 24px; margin-top: 0;'>Your Funds Have Been Added Successfully!</h3>

        <table style='width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 16px;'>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold; width: 40%;'>User:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$firstName $lastName</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Amount Added:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$amount PKR</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Transaction ID:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$transaction_id</td>
            </tr>
            <tr>
                <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Payment Method:</td>
                <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$payment_method</td>
            </tr>
        </table>

        <p style='text-align: center; margin-top: 30px; font-size: 14px;'>Thank you for using Beast SMM. We value your business!</p>
    </div>
    
    <!-- Footer -->
    <div style='background-color: #f1f1f1; padding: 10px; text-align: center; font-size: 12px; color: #555;'>
        &copy; 2025 Beast SMM. All rights reserved.
    </div>
</div>
";

	
			// Send email
			$mail->send();
			return true; // Email sent successfully
		} catch (Exception $e) {
			// Log the error for debugging
			error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
			return false; // Email failed to send
		}
	}
	

	

	public function add_funds_manual($ids = ""){
		$user    = $this->model->get("ids, id, first_name, last_name, email", $this->tb_users, "ids = '{$ids}' ");
		$payments_defaut = $this->model->fetch('type, name', $this->tb_payments, ['status' => 1]);
		$data = array(
			"module"                => get_class($this),
			"user" 		            => $user,
			"payments_defaut" 		=> $payments_defaut,
		);
		$this->load->view('add_funds_manual', $data);
	}
	public function ajax_add_funds_manualy($ids = ""){
		// _is_ajax($this->module);
		$funds     			= (double)post('funds');
		$payment_method     = post('payment_method');
		$transaction_id     = post('transaction_id');
		$txt_note	        = post("txt_note");
		$txt_fee	        = post("txt_fee");
	
		$checkUser = $this->model->get('id, ids, email, balance, spent, first_name, last_name', $this->tb_users, "`ids` = '{$ids}'");
		
		if ($ids == "" || empty($checkUser)) {
			ms(array(
				'status'  => 'error',
				'message' => lang("the_account_does_not_exists"),
			));
		}
		
		if ($payment_method == '') {
			ms(array(
				'status'  => 'error',
				'message' => 'Please choose payment method!',
			));
		}
	
		if ($funds == '') {
			ms(array(
				'status'  => 'error',
				'message' => 'Funds is required',
			));
		}	
	
		if(!is_double($funds)){
			ms(array(
				'status'  => 'error',
				'message' => lang('the_input_value_was_not_a_correct_number'),
			));
		}
	
		// Calculate new balance and total spent
		$new_balance = $checkUser->balance + $funds;
		$total_spent_before = $this->model->sum_results('amount', $this->tb_transaction_logs, ['status' => 1, 'uid' => $checkUser->id]);
		$total_spent = (double)round($total_spent_before + $funds, 4);
	
		$data = array(
			"balance" => $new_balance,
			"spent"   => $total_spent,
		);
	
		if ($transaction_id == "") {
			$transaction_id = 'empty';
		}
	
		$data_transaction = array(
			"ids" 				=> ids(),
			"uid" 				=> $checkUser->id,
			"type" 				=> $payment_method,
			"transaction_id" 	=> $transaction_id,
			"txn_fee" 	        => $txt_fee,
			"note" 	            => $txt_note,
			"amount" 	        => $funds,
			"created" 			=> NOW,
		);
	
		// Insert transaction log
		$this->db->insert($this->tb_transaction_logs, $data_transaction);
		
		// Update user data
		if ($this->db->update($this->tb_users, $data, ['ids' => $ids])) {
			// Send email to the user with fund details
			$this->sendFundAddedEmail($checkUser->email, $checkUser->first_name, $checkUser->last_name, $funds, $transaction_id, $payment_method);
	
			ms(array(
				'status'  => 'success',
				'message' => lang("Update_successfully"),
			));
		}
	}
	
	
	

	public function ajax_update($ids = ""){
		$first_name         = post('first_name');
		$last_name          = post('last_name');
		$email              = post('email');
		$password           = post('password');
		$re_password        = post('re_password');
		$status             = (int)post('status');
		$role               = post('role');
		$timezone           = post('timezone');
		$desc               = post('desc');
		$settings           = post('settings');
		$whatsapp_number           = post('whatsapp_number');
		if($first_name == '' || $last_name == ''){
			ms(array(
				'status'  => 'error',
				'message' => lang("please_fill_in_the_required_fields"),
			));
		}

		$data = array(
			"first_name"              => $first_name,
			"last_name"               => $last_name,
			"whatsapp_number"               => $whatsapp_number,
			"role"                    => $role,
			"status"                  => $status,
			"timezone"                => $timezone,
			"desc"        	          => $desc,
			"changed"                 => NOW,
			"settings"                => json_encode($settings),
			"reset_key"               => ids(),
		);
		
		if($password != ''|| $ids == ''){
			if($password == ''){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_is_required"),
				));
			}

			if(strlen($password) < 6){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_must_be_at_least_6_characters_long"),
				));
			}

			if($re_password!= $password){
				ms(array(
					'status'  => 'error',
					'message' => lang("Password_does_not_match_the_confirm_password"),
				));
			}
			$data['password'] = $this->model->app_password_hash($password);
		}
		
		if($ids != ''){
			$checkUser = $this->model->get('id, ids, email', $this->tb_users, "`ids` = '{$ids}'");

			if(empty($checkUser)){
				ms(array(
					'status'  => 'error',
					'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
				));
			}

			// check email
			$checkUserEmail = $this->model->get('email, ids', $this->tb_users,"email='{$email}' AND `ids` != '{$ids}'");
			if(!empty($checkUserEmail)){
				ms(array(
					'status'  => 'error',
					'message' => lang('An_account_for_the_specified_email_address_already_exists_Try_another_email_address'),
				));
			}
			if($this->db->update( $this->tb_users, $data ,"ids = '{$ids}'")){
				ms(array(
					'status'  => 'success',
					'message' => lang("Update_successfully"),
				));
			}
		}else{

			if($email == ''){
				ms(array(
					'status'  => 'error',
					'message' => lang("email_is_required"),
				));
			}

			if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		      	ms(array(
					'status'  => 'error',
					'message' => lang("invalid_email_format"),
				));
		    }

		    // check email
			$checkUserEmail = $this->model->get('email, ids', $this->tb_users,"email='{$email}'");
			if(!empty($checkUserEmail)){
				ms(array(
					'status'  => 'error',
					'message' => lang('An_account_for_the_specified_email_address_already_exists_Try_another_email_address'),
				));
			}
			$data['ids']         = ids();
			$data['created']     = NOW;
			$data['email']       = $email;
			$data['api_key']     = create_random_string_key(32);

			if($this->db->insert( $this->tb_users,$data)){
				ms(array(
					'status'  => 'success',
					'message' => lang("Update_successfully"),
				));
			}
		}
	}
	public function get_user_transactions() {
		$user_id = $this->input->post('user_id');
		
		// Log the user ID to ensure it's being received
		log_message('debug', 'Received user ID: ' . $user_id);
	
		// Query the transactions for the user
		$this->db->where('uid', $user_id);
		$query = $this->db->get('general_transaction_logs');
		
		// Log the number of rows returned
		log_message('debug', 'Transactions found: ' . $query->num_rows());
	
		// Check if transactions are found
		if ($query->num_rows() > 0) {
			$transactions = $query->result();
			
			// Prepare the HTML to display the transactions
			$html = '<table class="table table-striped">';
			$html .= '<thead><tr><th>Transaction ID</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead><tbody>';
			
			foreach ($transactions as $transaction) {
				$html .= '<tr>';
				$html .= '<td>'.$transaction->transaction_id.'</td>';
				$html .= '<td>'.$transaction->amount.'</td>';
				$html .= '<td>'.$transaction->created.'</td>';
				$html .= '<td>'.($transaction->status == 1 ? 'Completed' : 'Pending').'</td>';
				$html .= '</tr>';
			}
			
			$html .= '</tbody></table>';
			
			echo $html;
		} else {
			echo '<p>No transactions found.</p>';
		}
	}
	
	public function ajax_send_email(){
		_is_ajax(get_class($this));
		$user_email       = post("email_to");
		$subject          = post("subject");
		$email_content    = post("email_content");

		if($subject == ''){
			ms(array(
				'status'  => 'error',
				'message' => lang("subject_is_required"),
			));
		}

		if($email_content == ''){
			ms(array(
				'status'  => 'error',
				'message' => lang("message_is_required"),
			));
		}

		$user = $this->model->get("id, email", $this->tb_users, "email = '{$user_email}'");
		if (!empty($user)) {
			$subject = get_option("website_name", "") ." - ".$subject;
			$check_email_issue = $this->model->send_email($subject, $email_content, $user->id, false);
			if ($check_email_issue) {
				ms(array(
					"status"  => "error",
					"message" => $check_email_issue,
				));
			}

			if ($this->db->table_exists($this->tb_user_mail_logs)) {
				$data = array(
					'ids'                 => ids(),
					'uid'                 => session('uid'),
					'received_uid'        => $user->id,
					'subject'             => post("subject"),
					'content'             => htmlspecialchars(@$email_content, ENT_QUOTES),
					'created'             => NOW,
					'changed'             => NOW,
				);
				$this->db->insert($this->tb_user_mail_logs, $data);
			}

			ms(array(
				"status"  => "success",
				"message" => lang("your_email_has_been_successfully_sent_to_user"),
			));
		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("the_account_does_not_exists"),
			));
		}
	}

	public function ajax_update_more_infors($ids = ''){
		_is_ajax(get_class($this));
		$website            = post('website');
		$phone              = post('phone');
		$skype_id           = post('skype_id');
		$what_asap          = post('what_asap');
		$address            = post('address');

		$more_information = array(
			"website"         => $website,
			"phone"        	  => $phone,
			"what_asap"       => $what_asap,
			"skype_id"        => $skype_id,
			"address"         => $address,
		);

		$data = array(
			"more_information"        => json_encode($more_information),
			"changed"                 => NOW,
		);

		if($ids != ''){
			$checkUser = $this->model->get('id,ids,email', $this->tb_users, "`ids` = '{$ids}'");

			if(empty($checkUser)){
				ms(array(
					'status'  => 'error',
					'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
				));
			}

			if($this->db->update($this->tb_users, $data, "ids ='{$ids}'")){
				ms(array(
					'status'  => 'success',
					'message' => lang("Update_successfully"),
				));
			}
		}else{
			ms(array(
				'status'  => 'error',
				'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
			));
		}
	}

	public function ajax_update_fund($ids = ""){
		_is_ajax(get_class($this));
		$funds     = post('funds');
		$checkUser = $this->model->get('id, ids, email, balance', $this->tb_users, "`ids` = '{$ids}'");
		if ($ids == "" || empty($checkUser)) {
			ms(array(
				'status'  => 'error',
				'message' => lang("the_account_does_not_exists"),
			));
		}
		
		if ($funds == '') {
			ms(array(
				'status'  => 'error',
				'message' => 'Incorrect funds',
			));
		}

		if(!is_numeric($funds) && $funds != 0){
			ms(array(
				'status'  => 'error',
				'message' => lang('the_input_value_was_not_a_correct_number'),
			));
		}

		$data = array(
			"balance" => $funds,
		);

		if($this->db->update( $this->tb_users, $data ,"ids = '{$ids}'")){
			ms(array(
				'status'  => 'success',
				'message' => lang("Update_successfully"),
			));
		}
	}

	//Search
	public function search(){
		$k           = get('query');
		$k           = htmlspecialchars($k);
		$search_type = (int)get('search_type');
		$data_search = ['k' => $k, 'type' => $search_type];
		$page        = (int)get("p");
		$page        = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = ['query' => $k];
		$query_string = "";
		if(!empty($query)){
			$query_string = "?".http_build_query($query);
		}
		$config = array(
			'base_url'           => cn(get_class($this)."/search".$query_string),
			'total_rows'         => $this->model->get_count_users_by_search($data_search),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();
		$users = $this->model->search_logs_by_get_method($data_search, $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"       => get_class($this),
			"columns"      => $this->columns,
			"users"        => $users,
			"links"        => $links,
		);
		$this->template->build('index', $data);
	}

	public function ajax_delete_item($ids = ""){
		$this->model->delete($this->tb_users, $ids, false);
	}

	// Change Item Status
	public function ajax_toggle_item_status($id = ""){
		_is_ajax($this->module);
		$status  = post('status');
		$item  = $this->model->get("id", $this->tb_users, ['id' => $id]);
		if ( $item ) {
			$this->db->update($this->tb_users, ['status' => (int)$status], ['id' => $id]);
			_validation('success', lang("Update_successfully"));
		}
	}

	public function view_user($ids = ""){
		$user = $this->model->get("id, ids", $this->tb_users, ['ids' => $ids]);
		if (empty($user)) {
			ms(array(
				'status'  => 'error',
				'message' => lang("There_was_an_error_processing_your_request_Please_try_again_later"),
			));
		}
		set_session('uid_tmp', $user->id);
		unset_session("user_current_info");
		if (session('uid_tmp')) {
			ms(array(
				'status'  => 'success',
				'message' => lang("processing_"),
			));
		}
	}

	public function export($type = ""){

		switch ($type) {

			case 'excel':
				$users = $this->model->fetch('*', $this->tb_users, ['status' => 1]);
				if (!empty($users )) {
					$columns = ['id' ,'first_name', 'last_name', 'email', 'timezone', 'balance', 'status', 'created'];
					$filename = 'List-Users-'.date("d-m-Y", strtotime(NOW)).".xlsx";
					$this->load->library('phpspreadsheet_lib');
					$phpexel = new Phpspreadsheet_lib();
					$phpexel->export_excel($columns, $users, $filename);
				}

				break;

			case 'csv':
				$users = $this->model->fetch('*', $this->tb_users, ['status' => 1]);
				if (!empty($users )) {
					$columns = ['id' ,'first_name', 'last_name', 'email', 'timezone', 'balance', 'status', 'created'];
					$filename = 'List-Users-'.date("d-m-Y", strtotime(NOW)).".csv";
					$this->load->library('phpspreadsheet_lib');
					$phpexel = new Phpspreadsheet_lib();
					$phpexel->export_csv($columns, $users, $filename);
				}

				break;
			
			default:
				$filename = 'List-Users['.date("d-m-Y", strtotime(NOW))."].csv";
				export_csv($filename, $this->tb_users);
				break;
		}
	}

	public function export_whatsapp_numbers() {
		// Load the helper for file and download operations
		$this->load->helper('file');
		$this->load->helper('download');
	
		// Define the CSV file header
		$header = array("User ID", "First Name", "Last Name", "WhatsApp Number", "Email");
	
		// Fetch users from the database who have WhatsApp numbers
		$users = $this->model->fetch('*', $this->tb_users, "whatsapp_number IS NOT NULL AND whatsapp_number != ''");
	
		// Open output in memory to write the CSV
		$output = fopen('php://output', 'w');
	
		// Start the buffer so the headers are included in the CSV
		ob_start();
	
		// Add the header to the CSV
		fputcsv($output, $header);
	
		// Loop through the users and add their data to the CSV
		foreach ($users as $user) {
			// Replace `whatsapp_number` with the correct column from your table
			$row = array(
				$user->id,
				$user->first_name,
				$user->last_name,
				$user->whatsapp_number, // Replace with actual column name
				$user->email
			);
	
			// Write the row data into CSV
			fputcsv($output, $row);
		}
	
		// Get the content of the output
		$csv_data = ob_get_clean();
	
		// Close output
		fclose($output);
	
		// Set filename for download
		$filename = 'whatsapp_numbers_' . date('Y-m-d_H-i-s') . '.csv';
	
		// Force the CSV file to be downloaded
		force_download($filename, $csv_data);
	}
	
	// ajax_modal_custom_rates
	public function ajax_modal_custom_rates($uid = ""){
		_is_ajax($this->module);
		$uid = (int)$uid;
		$user = $this->model->get('id, ids, email', $this->tb_users, ['status' => 1, 'id' => $uid]);
		if ($user) {
			$user_prices = $this->model->get_current_customrate_by($uid);
			$services    = $this->model->fetch('id, price, name, original_price', $this->tb_services, ['status' => 1]);
			$data_modal = [
				'module'      => $this->module,
				'user'        => $user,
				'user_prices' => $user_prices,
				'services'    => $services,
			];
			$this->load->view('modal_custom_rate', $data_modal);
		}else{
			echo 	'<div class="modal-dialog">
					    <div class="modal-content">
						    <div class="alert  alert-dismissible">
							  <button type="button" class="close" data-dismiss="modal"></button>
							  <h4>Warning!</h4>
							  <p>
							   User is inactive mode, please active this user before adding custom rate!
							  </p>
							  <div class="btn-list">
							    <button class="btn btn-warning btn-sm" type="button" data-dismiss="modal">Okay</button>
							  </div>
							</div>
					    </div>
				 	</div>';
		}
	}

	public function ajax_save_custom_rates($uid = ""){
		_is_ajax($this->module);
		$user = $this->model->get('id, ids, email', $this->tb_users, ['status' => 1, 'id' => $uid]);

		if (!$user) {
			_validation('error', 'There was an error processing your request. Please try again later');
		}
		$custom_rates = post('customRates');
		unset($custom_rates['__serviceID__']);
		if (!empty($custom_rates)) {
			$exist_db_custom_rates = [];
			$exist_items = $this->model->fetch('*', $this->tb_users_price, ['uid' => $user->id]);

			// update the current Items
			if ($exist_items) {
				foreach ($exist_items as $key => $row) {
					$exist_db_custom_rates[$row->service_id]['uid']           = $row->uid;
					$exist_db_custom_rates[$row->service_id]['service_id']    = $row->service_id;
					$exist_db_custom_rates[$row->service_id]['service_price'] = $row->service_price;
					foreach ($custom_rates as $key => $custom_rate) {
						if ($custom_rate['service_id'] == $row->service_id && $row->uid == $custom_rate['uid']) {
							$this->db->update($this->tb_users_price, ['service_price' => $custom_rate['service_price']], ['id' => $row->id ]);
						}
					}
				}	
			}
			/*----------  Compare Custom rates New and exists on Database  ----------*/
			if (!empty($exist_db_custom_rates)) {
				// Get new
				$new_custom_rates = array_udiff($custom_rates, $exist_db_custom_rates,
				  	function ($obj_a, $obj_b) {
					    return $obj_a['service_id'] - $obj_b['service_id'];
				  	}
				);
				// Get Disbale Custom rates
				$disable_custom_rates = array_udiff($exist_db_custom_rates, $custom_rates,
				  	function ($obj_a, $obj_b) {
					    return $obj_a['service_id'] - $obj_b['service_id'];
				  	}
				);

			}else{
				$new_custom_rates = $custom_rates;
			}

			/*----------  Insert New  ----------*/
			if (!empty($new_custom_rates)) {
				$this->db->insert_batch($this->tb_users_price, $new_custom_rates);
			}

			/*----------  Delete non custom rate  ----------*/
			if (!empty($disable_custom_rates)) {
				foreach ($disable_custom_rates as $key => $row) {
					$this->db->delete($this->tb_users_price, [ 'uid' => $row['uid'] , 'service_id' => $row['service_id'] ]);
				}
			}
		}else{
			$this->db->delete($this->tb_users_price, ['uid' => $user->id]);
		}
		_validation( 'success', lang("Update_successfully") );
	}
}