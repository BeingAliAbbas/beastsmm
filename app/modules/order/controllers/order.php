<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendErrorNotification($order_id, $note) {
    require 'vendor/autoload.php'; // Ensure PHPMailer is autoloaded

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'mail.beastsmm';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = 'Aliabbas321@';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('transactions@beastsmm.pk', 'Beast SMM Notification');
        $mail->addAddress('beastsmm98@gmail.com');

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Order Error Notification - Order ID: ' . $order_id;
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; padding: 20px;'>
                <h2 style='color: #f44336;'>Error Notification</h2>
                <p>An error has occurred with the following order:</p>
                <ul>
                    <li><strong>Order ID:</strong> {$order_id}</li>
                    <li><strong>Note:</strong> {$note}</li>
                </ul>
                <p>Please take the necessary actions.</p>
                <br>
                <p>Regards,<br>Beast SMM System</p>
            </div>
        ";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        error_log("Error in sending email: {$mail->ErrorInfo}");
    }
}

defined('BASEPATH') OR exit('No direct script access allowed');
 
class order extends MX_Controller {
	public $tb_users;
	public $tb_users_price;
	public $tb_order;
	public $tb_categories;
	public $tb_services;
	public $module;
	public $module_name;
	public $module_icon;
	
	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');

		//Config Module
		$this->tb_users               = USERS;
		$this->tb_users_price         = USERS_PRICE;
		$this->tb_order               = ORDER;
		$this->tb_categories          = CATEGORIES;
		$this->tb_services            = SERVICES;
		$this->module                 = get_class($this);
		$this->module_name            = 'Order';
		$this->module_icon            = "fa ft-users";

		$this->columns = array(
			"order_id"                  => lang("order_id"),
			"order_basic_details"       => lang("order_basic_details"),
			"created"                   => lang("Created"),
			"status"                    => lang("Status"),
		);
		
		if (get_role("admin") || get_role("supporter")) {
			$this->columns = array(
				"order_id"                  => lang("order_id"),
				"api_order_id"              => lang("api_orderid"),
				"uid"                       => lang("User"),
				"order_basic_details"       => lang("order_basic_details"),
				"profit"                   => lang("Profit"),
				"created"                   => lang("Created"),
				"status"                    => lang("Status"),
				"response"                  => lang("API_Response"),
				"action"                    => lang("Action"),
			);
		}

	}

	// ADD
	public function index(){
		redirect(cn("order/add"));
	}

	public function add(){
		$this->load->model("services/services_model", 'services_model');
		$categories = $this->services_model->get_active_categories();
		$data = array(
			"module"       => get_class($this),
			'categories'   => $categories,
			'services'     => "",
		);
		$this->template->build('add/add', $data);
	}

	// Get Services by cate ID
	public function get_services($id = ""){
		$check_category = $this->model->check_record("id", $this->tb_categories, $id, false, false);
		if ($check_category) {
			$services    = $this->model->get_services_by_cate($id);
			$this->load->model('users/users_model');
			$data = array(
				"module"   		=> get_class($this),
				"services" 		=> $services,
				"custom_rates"  => $this->users_model->get_custom_rates(),
			);
			$this->load->view('add/get_services', $data);
		}		
	}
	
	// Get Service Detail by ID
	public function get_service($id = ""){
		$check_service    = $this->model->get_service_item($id);
		$data = array(
			"module"   		=> get_class($this),
			"service" 		=> $check_service,
			
		);
		$this->load->view('add/get_service', $data);
	}
	
	public function ajax_add_order(){
		$api_provider_id    = post("api_provider_id");
		$api_service_id 	= post("api_service_id");
		$service_id 		= post("service_id");
		$cate_id 		    = post("category_id");
		$quantity 		    = post("quantity");
		$link 		        = post("link");
		$runs               = post("runs");
		$interval           = post("interval");
		$is_drip_feed       = (post("is_drip_feed") == "on") ? 1 : 0;
		$agree 		        = (post("agree") == "on") ? 1 : 0;
		$service_type 	    = post("service_type");
		
		if ($cate_id == "") {
			ms(array(
				"status"  => "error",
				"message" => lang("please_choose_a_category")
			));
		}	

		if ($service_id == "") {
			ms(array(
				"status"  => "error",
				"message" => lang("please_choose_a_service")
			));
		}

		$check_category = $this->model->check_record("*", $this->tb_categories, $cate_id, false, true);
		$check_service  = $this->model->check_record("*", $this->tb_services, $service_id, false, true);
		if (empty($check_category)) {
			ms(array(
				"status"  => "error",
				"message" => lang("category_does_not_exists")
			));
		}

		if (empty($check_service)) {
			ms(array(
				"status"  => "error",
				"message" => lang("service_does_not_exists")
			));
		}
		
		$check_category_name = $this->model->get("ids, name", $this->tb_categories, "id = '{$cate_id}'");
		if(!empty($check_category_name)){
			$category_name = $check_category_name->name;
			if (strpos($category_name, 'Refill') == true) {
				if (strpos($category_name, 'No Refill') == false) {
					// echo 'true';
					// die;
					$is_refill = "yes";
				}else{
					$is_refill = "no";
				}
	
			}else{
				$is_refill = "yes";
			}
		}else{
			$is_refill = "no";
		}
		
		/*----------  Add all order without quantity  ----------*/
		if ($service_type == "subscriptions") {
			$this->add_order_subscriptions($_POST);
			exit();
		}

		if ($link == "") {
			ms(array(
				"status"  => "error",
				"message" => lang("invalid_link")
			));
		}
		$link = strip_tags($link);
		
		switch ($service_type) {

			case 'custom_comments':
				$comments = strip_tags($_POST['comments']);
				if ($comments == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("comments_field_is_required")
					));
				}
				$quantity = count(explode("\n", $comments));
				break;

			case 'mentions_custom_list':
				$usernames_custom = post("usernames_custom");
				if ($usernames_custom == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("username_field_is_required")
					));
				}
				$quantity = count(explode("\n", $usernames_custom));
				break;

			case 'package':
				$quantity = 1;
				break;

			case 'custom_comments_package':
				$comments = strip_tags($_POST['comments_custom_package']);
				if ($comments == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("comments_field_is_required")
					));
				}
				$quantity = 1;
				break;
		}

		if ($quantity == "") {
			ms(array(
				"status"  => "error",
				"message" => lang("quantity_is_required")
			));
		}
		
		/*----------  Check dripfeed  ----------*/
		if ($is_drip_feed) {

			if ($runs == "") {
				ms(array(
					"status"  => "error",
					"message" => lang("runs_is_required")
				));
			}
			
			if ($interval == "") {
				ms(array(
					"status"  => "error",
					"message" => lang("interval_time_is_required")
				));
			}

			if ($interval > 60) {
				ms(array(
					"status"  => "error",
					"message" => lang("interval_time_must_to_be_less_than_or_equal_to_60_minutes")
				));
			}
			$total_quantity = $runs * $quantity;
		}else{
			$total_quantity = $quantity;
		}
		
		/*----------  Check quantity  ----------*/
		$min          = $check_service->min;
		$max          = $check_service->max;
		$price        = get_user_price(session('uid'), $check_service);

		if ($service_type == "package" || $service_type == "custom_comments_package") {
			$total_charge = $price;
		}else{
			$total_charge = ($price*$total_quantity)/1000;
		}
		
		if ($total_quantity <= 0 || ($total_quantity < $min) || $quantity < $min) {
			ms(array(
				"status"  => "error",
				"message" => lang("quantity_must_to_be_greater_than_or_equal_to_minimum_amount")
			));
		}	

		if ($total_quantity > $max) {
			ms(array(
				"status"  => "error",
				"message" => lang("quantity_must_to_be_less_than_or_equal_to_maximum_amount")
			));
		}
		/*----------  Get balance ----------*/
		$user = $this->model->get("balance", $this->tb_users, ['id' => session('uid')]);

		/*----------  Get Formal Charge and profit  ----------*/
		$formal_charge = ($check_service->original_price * $total_charge) / $check_service->price;
		$profit        = $total_charge - $formal_charge;
		/*----------  Collect data import to database  ----------*/
		$data = array(
			"ids" 	        	=> ids(),
			"uid" 	        	=> session("uid"),
			"cate_id" 	    	=> $cate_id,
			"service_id" 		=> $service_id,
			"service_type" 		=> $service_type,
			"link" 	        	=> $link,
			"quantity" 	    	=> $total_quantity,
			"charge" 	    	=> $total_charge,
			"formal_charge" 	=> $formal_charge,
			"profit" 	    	=> $profit,
			"api_provider_id"  	=> $api_provider_id,
			"api_service_id"  	=> $api_service_id,
			"is_drip_feed"  	=> $is_drip_feed,
			"is_refill"         => $is_refill,
			"status"			=> 'pending',
			"changed" 	    	=> NOW,
			"created" 	    	=> NOW,
		);
		/*----------  get the different required paramenter for each service type  ----------*/
		switch ($service_type) {

			case 'mentions_with_hashtags':
				$hashtags  = post("hashtags");
				$usernames = post("usernames");
				$usernames = strip_tags($usernames);

				if ($usernames == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("username_field_is_required")
					));
				}

				if ($hashtags == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("hashtag_field_is_required")
					));
				}
				$data["usernames"] = $usernames;
				$data["hashtags"]  = $hashtags;
				break;

			case 'mentions_hashtag':
				$hashtag = post("hashtag");
				if ($hashtag == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("hashtag_field_is_required")
					));
				}
				$data["hashtag"] = $hashtag;
				break;	

			case 'comment_likes':
				$username = post("username");
				$username = strip_tags($username);
				if ($username == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("username_field_is_required")
					));
				}

				$data["username"] = $username;
				break;	
							
			case 'mentions_user_followers':
				$username = post("username");
				$username = strip_tags($username);

				if ($username == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("username_field_is_required")
					));
				}

				$data["username"] = $username;
				break;		

			case 'mentions_media_likers':
				$media_url = post("media_url");

				if ($media_url == "" || !filter_var($media_url, FILTER_VALIDATE_URL)) {
				    ms(array(
						"status"  => "error",
						"message" => lang("invalid_link")
					));
				}
				$data["media"] = $media_url;
				break;

			case 'custom_comments':
				$data["comments"] = json_encode($comments);
				break;

			case 'custom_comments_package':
				$data["comments"] = json_encode($comments);
				break;

			case 'mentions_custom_list':
				$data["usernames"] = json_encode($usernames_custom);
				break;

		}
		// Check agree
		if (!$agree) {
			ms(array(
				"status"  => "error",
				"message" => lang("you_must_confirm_to_the_conditions_before_place_order")
			));
		}
		// check balance
		if ($user->balance != 0 && $user->balance < $total_charge || $user->balance == 0) {
			ms(array(
				"status"  => "error",
				"message" => lang("not_enough_funds_on_balance")
			));
		}

		if ($is_drip_feed) {
			$data['runs'] = $runs;
			$data['interval'] = $interval;
			$data['dripfeed_quantity'] = $quantity;
			$data['status'] = 'inprogress';
		}

		if (!empty($api_provider_id) && !empty($api_service_id)) {
			$data['api_order_id'] = -1;
		}
		$this->save_order($this->tb_order, $data, $user->balance, $total_charge);
	}

	private function add_order_subscriptions($post){
		$api_provider_id    = $post["api_provider_id"];
		$api_service_id 	= $post["api_service_id"];
		$service_id 		= $post["service_id"];
		$cate_id 		    = $post["category_id"];
		$agree 		        = (isset($post['agree']) && $post["agree"] == "on") ? 1 : 0;
		$service_type 	    = $post["service_type"];
		$link 		        = $post["link"];
		$link               = strip_tags($link);

		/*----------  check service   ----------*/
		$check_service  = $this->model->check_record("*", $this->tb_services, $service_id, false, true);

		/*----------  Collect data import to database  ----------*/
		$data = array(
			"ids" 	        	=> ids(),
			"uid" 	        	=> session("uid"),
			"cate_id" 	    	=> $cate_id,
			"service_id" 		=> $service_id,
			"service_type" 		=> $service_type,
			"api_provider_id"  	=> $api_provider_id,
			"api_service_id"  	=> $api_service_id,
			"sub_status"  	    => 'Active',
			"status"  	        => 'pending',
			"changed" 	    	=> NOW,
			"created" 	    	=> NOW,
		);

		switch ($service_type) {
			case 'subscriptions':
				$username = $post["sub_username"];
				$posts    = (int)$post["sub_posts"];
				$min      = (int)$post["sub_min"];
				$max      = (int)$post["sub_max"];
				$delay    = (int)$post["sub_delay"];
				$expiry   = $post["sub_expiry"];

				if ($username == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("username_field_is_required")
					));
				}

				if ($min == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("quantity_must_to_be_greater_than_or_equal_to_minimum_amount")
					));
				}

				if ($min < $check_service->min) {
					ms(array(
						"status"  => "error",
						"message" => lang("quantity_must_to_be_greater_than_or_equal_to_minimum_amount")
					));
				}

				if ($max < $min) {
					ms(array(
						"status"  => "error",
						"message" => lang("min_cannot_be_higher_than_max")
					));
				}

				if ($max > $check_service->max) {
					ms(array(
						"status"  => "error",
						"message" => lang("quantity_must_to_be_less_than_or_equal_to_maximum_amount")
					));
				}
				
				if (!in_array($delay, array(0, 5, 10, 15, 30, 60, 90))) {
					ms(array(
						"status"  => "error",
						"message" => lang("incorrect_delay")
					));
				}

				if ($posts <=  0 || $posts == "") {
					ms(array(
						"status"  => "error",
						"message" => lang("new_posts_future_posts_must_to_be_greater_than_or__equal_to_1")
					));
				}

				// Check agree
				if (!$agree) {
					ms(array(
						"status"  => "error",
						"message" => lang("you_must_confirm_to_the_conditions_before_place_order")
					));
				}
				// calculate total charge
				$charge = ($max * $posts * $check_service->price) / 1000;
				
				// check balance
				$current_balance = $this->model->check_record("balance", $this->tb_users, session('uid'), false, true);
				if (($current_balance->balance != 0 && $current_balance->balance < $charge) || $current_balance->balance == 0) {
					ms(array(
						"status"  => "error",
						"message" => lang("not_enough_funds_on_balance")
					));
				}
				if ($expiry != "") {
					$expiry = str_replace('/', '-', $expiry);
					$expiry = date("Y-m-d", strtotime($expiry));
				}else{
					$expiry = "";
				}	
				
				$data["username"]     = $username;
				$data["sub_posts"]    = ($posts == "")? -1: $posts;
				$data["sub_min"]      = $min;
				$data["sub_max"]      = $max;
				$data["sub_delay"]    = $delay;
				$data["sub_expiry"]   = $expiry;

				if (!empty($api_provider_id) && !empty($api_service_id)) {
					$data['api_order_id'] = -1;
				}
				
				$this->save_order($this->tb_order, $data);
				break;
		}

	}
			
/*---------- Insert data to order ----------*/
private function save_order($table, $data_orders, $user_balance = "", $total_charge = "") {
    $this->db->insert($table, $data_orders);
    $order_id = $this->db->insert_id();
    if ($this->db->affected_rows() > 0) {

        if ($data_orders["service_type"] != "subscriptions") {
            $new_balance = $user_balance - $total_charge;
            $new_balance = ($new_balance > 0) ? $new_balance : 0;
            $this->db->update($this->tb_users, ["balance" => $new_balance], ["id" => session("uid")]);
        }

        /*---------- Helper Function to Send WhatsApp Notification ----------*/
        function sendWhatsAppNotification($apiUrl, $apiKey, $phoneNumber, $message) {
            // Prepare data for the POST request
            $data = [
                "apiKey" => $apiKey, // Include API key for validation
                "phoneNumber" => $phoneNumber,
                "message" => $message
            ];

            // Initialize cURL
            $ch = curl_init($apiUrl);

            // Set the headers
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json"
            ]);

            // Set the POST method and attach the data
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

            // Set options to return the response
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            // Execute the cURL request
            $response = curl_exec($ch);

            // Check for errors
            if (curl_errno($ch)) {
                error_log("WhatsApp API Error: " . curl_error($ch));
            }

            // Close cURL session
            curl_close($ch);

            return $response;
        }

        /*---------- Send Admin WhatsApp Notification ----------*/
        // Fetch WhatsApp configuration from the database
        $whatsapp_config = $this->model->get("url, api_key, admin_phone", "whatsapp_config", []);
        if (empty($whatsapp_config) || empty($whatsapp_config->url) || empty($whatsapp_config->api_key) || empty($whatsapp_config->admin_phone)) {
            error_log("WhatsApp API URL, API key, or admin phone not configured.");
            return false;
        }

        // Get the API URL, API key, and admin phone from config
        $apiUrl = $whatsapp_config->url;
        $apiKey = $whatsapp_config->api_key;
        $adminPhone = $whatsapp_config->admin_phone;

        // Fetch user details
        $user = $this->model->get("email", $this->tb_users, "id = '" . session('uid') . "'");
        $user_email = $user->email;

        // Format link
        $formatted_link = '';
        if (!empty($data_orders['link'])) {
            $formatted_link = filter_var($data_orders['link'], FILTER_VALIDATE_URL) ? 
                            preg_replace('#^https?://#', '', $data_orders['link']) : 
                            truncate_string($data_orders['link'], 60);
        }

        // Define admin message with formatted link and quantity
        $admin_message = "*🔔 New Order Received*\n\n" .
            "🔢 *Order ID:* #{$order_id}\n" .
            "💰 *Total Charge:* " . get_option("currency_symbol", "") . $total_charge . "\n" .
            "📦 *Quantity:* {$data_orders['quantity']}\n" .
            "🔗 *Link:* {$formatted_link}\n" .
            "📧 *User Email:* {$user_email}\n" .
            "\nPlease review the order details.";

        // Send admin notification
        // Remove + from phone number if present
        $adminPhone = ltrim($adminPhone, '+');
        $response = sendWhatsAppNotification($apiUrl, $apiKey, $adminPhone, $admin_message);

        // Log the response if needed
        if ($response) {
            error_log("Admin WhatsApp notification sent successfully: " . $response);
        } else {
            error_log("Failed to send admin WhatsApp notification.");
        }
        /*---------- Send Order notification email to Admin ----------*/
        if (get_option("is_order_notice_email", '')) {
            $user_email = $this->model->get("email", $this->tb_users, "id = '".session('uid')."'")->email;

            $subject = getEmailTemplate("order_success")->subject;
            $subject = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $subject);
            $email_content = getEmailTemplate("order_success")->content;
            $email_content = str_replace("{{user_email}}", $user_email, $email_content);
            $email_content = str_replace("{{order_id}}", $order_id, $email_content);
            $email_content = str_replace("{{currency_symbol}}", get_option("currency_symbol",""), $email_content);
            $email_content = str_replace("{{total_charge}}", $total_charge, $email_content);
            $email_content = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $email_content);

            $admin_id = $this->model->get("id", $this->tb_users, "role = 'admin'","id","ASC")->id;
            if ($admin_id == "") {
                $admin_id = 1;
            }
            $check_send_email_issue = $this->model->send_email($subject, $email_content, $admin_id, false);
            if ($check_send_email_issue) {
                ms(array(
                    "status" => "error",
                    "message" => $check_send_email_issue,
                ));
            }
        }

        ms(array(
            "status"  => "success",
            "message" => lang("place_order_successfully")
        ));
    } else {
        ms(array(
            "status"  => "error",
            "message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
        ));
    }
}


	// MASS ORDER
	public function ajax_mass_order(){
		$mass_order 		= post("mass_order");
		$agree 		        = (post("agree") == "on") ? 1 : 0;
		
		if (!$agree) {
			ms(array(
				"status"  => "error",
				"message" => lang("you_must_confirm_to_the_conditions_before_place_order")
			));
		}

		if ($mass_order == "") {
			ms(array(
				"status"  => "error",
				"message" => lang("field_cannot_be_blank")
			));
		}

		/*----------  get balance   ----------*/
		$user = $this->model->get("balance", $this->tb_users, ['id' => session('uid')]);
		
		if ($user->balance == 0) {
			ms(array(
				"status"  => "error",
				"message" => lang("you_do_not_have_enough_funds_to_place_order")
			));
		}
		$total_order  = 0;
		$total_errors = 0;
		$sum_charge = 0;
		$error_details = array();
		$orders = array();
		if (is_array($mass_order)) {
			foreach ($mass_order as $key => $row) {
				$order = explode("|", $row);

				// check format
				$order_count = count($order);
				if ($order_count > 3  || $order_count <= 2) {
					$error_details[$row] = lang("invalid_format_place_order");
					continue;
				}
				$service_id = $order[0];
				$quantity   = $order[1];
				$link       = $order[2];

				// check service id
				$check_service = $this->model->check_record("*", $this->tb_services, $service_id, false, true);
				if (empty($check_service)) {
					$error_details[$row] = lang("service_id_does_not_exists");
					continue;
				}

				// check quantity and balance
				$min          = $check_service->min;
				$max          = $check_service->max;
				$price        = get_user_price(session('uid'), $check_service);
				$charge       = (double)$price*($quantity/1000);

				if ($quantity <= 0 || $quantity < $min) {
					$error_details[$row] = lang("quantity_must_to_be_greater_than_or_equal_to_minimum_amount");
					continue;
				}	
						
				if ($quantity > $max) {
					$error_details[$row] = lang("quantity_must_to_be_less_than_or_equal_to_maximum_amount");
					continue;
				}

				// Order charge to .00 decimal points
                $charge = number_format($charge, 2, '.', '');

                /*----------  Get Formal Charge and profit  ----------*/
				$formal_charge = ($check_service->original_price * $charge) / $check_service->price;
				$profit        = $charge - $formal_charge;

				// every thing is ok
				$orders[] = array(
					"ids" 	            => ids(),
					"uid" 	            => session("uid"),
					"cate_id"           => $check_service->cate_id,
					"service_id"        => $service_id,
					"link" 	            => $link,
					"quantity" 	        => $quantity,
					"charge" 	        => $charge,
					"formal_charge" 	=> $formal_charge,
					"profit" 	        => $profit,
					"api_provider_id"  	=> $check_service->api_provider_id,
					"api_service_id"  	=> $check_service->api_service_id,
					"api_order_id"  	=> (!empty($check_service->api_provider_id) && !empty($check_service->api_service_id)) ? -1 : 0,
					"status"			=> 'pending',
					"changed" 	        => NOW,
					"created" 	        => NOW,
				);
				$sum_charge += $charge;
			}

			// check sum_charge and balance
			if ($sum_charge > $user->balance) {
				ms(array(
					"status"  => "error",
					"message" => lang("not_enough_funds_on_balance")
				));
			}
			if (!empty($orders)) {
				$this->db->insert_batch($this->tb_order, $orders);
				$new_balance = $user->balance - $sum_charge;
				$this->db->update($this->tb_users, ["balance" => $new_balance], ["id" => session("uid")]);
			}
		}
		if (!empty($error_details)) {
			$this->load->view("add/mass_order_notification", ["error_details" => $error_details]);
		}else{
			ms(array(
				"status"  => "success",
				"message" => lang("place_order_successfully")
			));
		}

	}

	/*----------  Logs  ----------*/
	public function log($order_status = ""){
		if ($order_status == "") {
			$order_status = "all";
		}

		$number_error_orders = 0;
		if (get_role('user') && in_array($order_status, ['fail', 'error'])) {
          redirect(cn('order/log/all'));
        }

        if (get_role('admin')) {
        	$number_error_orders = $this->model->get_count_orders('error');
        }

		$page        = (int)get("p");
		$page        = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = array();
		$query_string = "";
		if(!empty($query)){
			$query_string = "?".http_build_query($query);
		}

		$config = array(
			'base_url'           => cn(get_class($this)."/log/".$order_status.$query_string),
			'total_rows'         => $this->model->get_order_logs_list(true, $order_status),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();

		$order_logs = $this->model->get_order_logs_list(false, $order_status, $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"                        => get_class($this),
			"columns"                       => $this->columns,
			"order_logs"                    => $order_logs,
			"order_status"                  => $order_status,
			"links"                         => $links,
			"number_error_orders"           => $number_error_orders,
		);
		$this->template->build('logs/logs', $data);
	}
	

	/*----------  Order details for Dripfeed and Subscription  ----------*/
	public function log_details($id = ""){
		$orders = $this->model->get_log_details($id);
		if (!empty($orders)) {
			$data = array(
				"module"     => get_class($this),
				"columns"    => $this->columns,
				"order_logs" => $orders,
			);
			$this->template->build("logs/ajax_search", $data);
		}else{
			redirect(cn('dripfeed'));
		}
	}

	public function log_update($ids = ""){
		$order = $this->model->get("*", $this->tb_order, ['ids' => $ids]);
		if (!$order) {
			redirect(cn($this->module.'/log'));
		}
	
		if (in_array($order->status, ['pending', 'processing', 'inprogress'])) {
			$order_status_array = ['pending', 'processing', 'inprogress', 'completed', 'partial', 'canceled', 'error'];
		}
		
		if (in_array($order->status, ['canceled'])) {
			$order_status_array = ['canceled'];
		}
	
		if (in_array($order->status, ['completed'])) {
			$order_status_array = ['completed', 'canceled', 'partial'];
		}
	
		if (in_array($order->status, ['partial'])) {
			$order_status_array = ['canceled', 'partial'];
		}
	
		if (in_array($order->status, ['error'])) {
			$order_status_array = ['canceled', 'error', 'partial', 'completed'];
		}
	
		$data = array(
			"module"                => get_class($this),
			"order"                 => $order,
			"order_status_array"    => $order_status_array,
		);
		$this->load->view('logs/update', $data);
	}
	

	public function ajax_logs_update($ids = ""){
		if (!get_role('admin')) _validation('error', "Permission Denied!");
		
		$link 			= post("link");
		$start_counter  = post("start_counter");
		$remains 		= post("remains");
		$status 		= post("status");

		if($link == ""){
			ms(array(
				"status"  => "error",
				"message" => lang("link_is_required")
			));
		}

		if(!is_numeric($start_counter) && $start_counter != ""){
			ms(array(
				"status"  => "error",
				"message" => lang("start_counter_is_a_number_format")
			));
		}

		if(!is_numeric($remains) && $remains != ""){
			ms(array(
				"status"  => "error",
				"message" => lang("start_counter_is_a_number_format")
			));
		}

		$data = array(
			"link" 	    	=> $link,
			"status"    	=> $status,
			"start_counter" => $start_counter,
			"remains"    	=> $remains,
			"changed" 		=> NOW,
		);

		$check_item = $this->model->get("ids, cate_id, service_id, service_type, api_provider_id, api_service_id, charge, uid, quantity, status, formal_charge, profit", $this->tb_order, "ids = '{$ids}'");
		if(!empty($check_item)){
			/*----------  If status = refund  ----------*/
			if ($status == "refunded" || $status == "partial" || $status == "canceled") {
				$charge = $check_item->charge;
				$charge_back = 0;
				$real_charge = 0;
				$formal_charge = 0;
				$profit        = 0;

				if ($status == "partial") {
					$charge_back = ($charge * $remains) / $check_item->quantity;
					$real_charge = $charge - $charge_back;

					$formal_charge = $check_item->formal_charge * (1 - ($remains / $check_item->quantity ));
					$profit        = $check_item->profit * (1 - ($remains / $check_item->quantity ));
				}

				$user = $this->model->get("id, balance", $this->tb_users, ["id"=> $check_item->uid]);
				if (!empty($user) && !in_array($check_item->status, array('partial', 'cancelled', 'refunded'))) {
					$balance = $user->balance;
					$balance += $charge - $real_charge;
					$this->db->update($this->tb_users, ["balance" => $balance], ["id"=> $check_item->uid]);
				}
				$data['charge'] = $real_charge;
				$data['formal_charge'] = $formal_charge;
				$data['profit']        = $profit;
			}

			$this->db->update($this->tb_order, $data, array("ids" => $check_item->ids));
			
			ms(array(
				"status"  => "success",
				"message" => lang("Update_successfully")
			));
		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
			));
		}
	}
	public function send_whatsapp_notification() {
		// Check for required parameters
		$whatsapp_number = $this->input->get('whatsapp_number');
		$order_id = $this->input->get('order_id');
		$service_name = urldecode($this->input->get('service_name'));
	
		if (!$whatsapp_number || !$order_id || !$service_name) {
			$this->session->set_flashdata('error', 'Invalid request parameters.');
			redirect('order/log');
		}
	
		try {
			// Fetch additional order details
			$order = $this->model->get("*", $this->tb_order, ['id' => $order_id]);
	
			if (!$order) {
				$this->session->set_flashdata('error', 'Order details not found.');
				redirect('order/log');
			}
	
			// Get WhatsApp API configuration
			$whatsapp_config = $this->model->get("url, api_key, admin_phone", "whatsapp_config", []);
			if (empty($whatsapp_config) || empty($whatsapp_config->url) || empty($whatsapp_config->api_key)) {
				$this->session->set_flashdata('error', 'WhatsApp API URL or API key not configured.');
				redirect('order/log');
			}
	
			// Get API URL, API key, and admin phone from config
			$api_url = $whatsapp_config->url;
			$api_key = $whatsapp_config->api_key;
	
			// Format charge to 2 decimal places
			$formatted_charge = number_format($order->charge, 2);
	
			$formatted_link = filter_var($order->link, FILTER_VALIDATE_URL) ? 
				preg_replace('#^https?://#', '', $order->link) : 
				truncate_string($order->link, 60);
	
			// Prepare message with additional details
			$message = "*✅ Order Completed Successfully!*\n\n"
				. "🔢 *Order ID*: {$order_id}\n"
				. "🛍️ *Service*: {$service_name}\n"
				. "💰 *Total Charge*: ${formatted_charge}\n"
				. "🔗 *Order Link*: {$formatted_link}\n\n"
				. "Thank you for using our services! Your order has been completed successfully.";
	
			// Prepare the data
			$data = [
				"apiKey" => $api_key,  // Include API key for validation
				"phoneNumber" => $whatsapp_number,
				"message" => $message
			];
	
			// Initialize cURL
			$ch = curl_init($api_url);
	
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
				CURLOPT_POSTFIELDS => json_encode($data),
				CURLOPT_TIMEOUT => 30
			]);
	
			$response = curl_exec($ch);
	
			if (curl_errno($ch)) {
				$this->session->set_flashdata('error', 'Failed to send WhatsApp notification: ' . curl_error($ch));
				curl_close($ch);
				redirect('order/log');
			}
	
			curl_close($ch);
	
			$responseData = json_decode($response, true);
	
			// Update notification status
			$this->db->where('order_id', $order_id);
			$update_result = $this->db->update('order_notifications', [
				'is_notified' => 1,
				'created_at' => date('Y-m-d H:i:s')
			]);
	
			if ($this->db->affected_rows() == 0) {
				$insert_result = $this->db->insert('order_notifications', [
					'order_id' => $order_id,
					'is_notified' => 1,
					'created_at' => date('Y-m-d H:i:s')
				]);
	
				if (!$insert_result) {
					log_message('error', 'Failed to insert notification status: ' . $this->db->error()['message']);
					$this->session->set_flashdata('error', 'Failed to update notification status.');
					redirect('order/log');
				}
			}
	
			if (isset($responseData['status']) && $responseData['status'] == 'success') {
				$this->session->set_flashdata('success', 'WhatsApp notification sent successfully!');
			} else {
				$this->session->set_flashdata('error', 'Failed to send WhatsApp notification.');
			}
	
		} catch (Exception $e) {
			log_message('error', 'Exception in send_whatsapp_notification: ' . $e->getMessage());
			$this->session->set_flashdata('error', 'Error: ' . $e->getMessage());
		}
	
		redirect('order/log/completed');
	}
	

    public function refill_order($ids = ""){
		$refill_status	= "yes";
		// $status 		= "processing";

		$data = array(
			"refill_status" => $refill_status,
			"changed" 		=> NOW,
		);

		$this->db->update($this->tb_order, $data, array("ids" => $ids));

		$check_item_refill = $this->model->get("id, api_order_id, uid, status", $this->tb_order, "ids = '{$ids}'");
		if(!empty($check_item_refill)){
			// if ($status == "completed") {			
			// }
			$check_item_user = $this->model->get("id, first_name, last_name, email", $this->tb_users, "id = '{$check_item_refill->uid}'");
			if(!empty($check_item_refill)){


						$subject = get_option('email_new_refill_subject', '');
						$subject = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $subject);

						$email_content = get_option('email_new_refill_content', '');
						$email_content = str_replace("{{user_firstname}}", $check_item_user->first_name, $email_content);
						$email_content = str_replace("{{user_lastname}}", $check_item_user->last_name, $email_content);
						$email_content = str_replace("{{user_email}}", $check_item_user->email, $email_content);
						$email_content = str_replace("{{order_id}}", $check_item_refill->id, $email_content);
						$email_content = str_replace("{{api_order_id}}", $check_item_refill->api_order_id, $email_content);

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
			ms(array(
				"status"  => "success",
				"message" => lang("Refill_successfully")
			));
		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
			));
		}
	}
public function error_order_notification() {
    // Query to check if there are any orders with 'error' status
    $error_orders = $this->model->fetch("*", $this->tb_order, "status = 'error'");
    
    if (!empty($error_orders)) {
        $admin = $this->model->get("id, first_name, last_name, email", $this->tb_users, "role = 'admin'", "id", "ASC");
        
        if (!empty($admin)) {
            // Prepare subject and email content using placeholders
            $subject = get_option('email_error_order_subject', 'Error Order Notification - {{website_name}}');
            $subject = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $subject);
            
            $email_content = get_option('email_error_order_content', '');
            $email_content = str_replace("{{admin_firstname}}", $admin->first_name, $email_content);
            $email_content = str_replace("{{admin_lastname}}", $admin->last_name, $email_content);
            $email_content = str_replace("{{admin_email}}", $admin->email, $email_content);

            $order_list = "";
            foreach ($error_orders as $order) {
                $order_list .= "<li>Order ID: {$order->id}, Note: {$order->note}</li>";
            }
            
            $email_content = str_replace("{{error_orders}}", "<ul>{$order_list}</ul>", $email_content);

            // Send the email to the admin
            $check_send_email = $this->model->send_email($subject, $email_content, $admin->id, false);
            
            if ($check_send_email) {
                ms(array(
                    "status"  => "error",
                    "message" => $check_send_email,
                ));
            }

            ms(array(
                "status"  => "success",
                "message" => lang("Error notification email sent successfully.")
            ));
        }
    } else {
        ms(array(
            "status"  => "error",
            "message" => lang("No error orders found.")
        ));
    }
}

	// function Search Data
	public function search(){
		$k           = get('query');
		$k           = htmlspecialchars($k);
		$search_type = (int)get('search_type');
		$data_search = ['k' => $k, 'type' => $search_type];
		$page        = (int)get("p");
		$page        = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = ['query' => $k, 'search_type' => $search_type];
		$query_string = "";
		if(!empty($query)){
			$query_string = "?".http_build_query($query);
		}
		$config = array(
			'base_url'           => cn(get_class($this)."/search".$query_string),
			'total_rows'         => $this->model->get_count_orders_by_search($data_search),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();

		$order_logs = $this->model->search_logs_by_get_method($data_search, $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"       => get_class($this),
			"columns"      => $this->columns,
			"order_logs"   => $order_logs,
			"order_status" => '',
			"links"        => $links,
		);
		$this->template->build('logs/logs', $data);
	}

	public function ajax_order_by($status = ""){
		if (!empty($status) && $status !="" ) {
			$order_logs = $this->model->get_order_logs_list(false, $status);
			$data = array(
				"module"     => get_class($this),
				"columns"    => $this->columns,
				"order_logs" => $order_logs,
			);
			$this->load->view("logs/ajax_search", $data);
		}
	}

	public function ajax_show_list_custom_mention($ids = ''){
		if (!get_role('admin')) _validation('error', "Permission Denied!");

		$order    = $this->model->get("*", $this->tb_order, ['ids' => $ids]);
		if (!empty($order)) {
			$mentions = get_list_custom_mention($order);
            if($mentions->exists_list){
				$data = array(
					"module"   		=> get_class($this),
					"title" 	    => $mentions->title,
					"list" 	        => $mentions->list,
				);
				$this->load->view('logs/show_list_custom_mention', $data);
			}
		}
	}

	public function change_status($status = "", $ids = "") {
		if (!get_role('admin')) {
			_validation('error', "Permission Denied!");
		}
	
		// Check if it's a bulk action
		if ($ids == "bulk") {
			// Fetch all orders in 'error' status
			$orders = $this->model->get_bulk_orders('error');
			if (!empty($orders)) {
				foreach ($orders as $order) {
					switch ($status) {
						case 'resend_order':
							$data = [
								'status'       => 'pending',
								'note'         => 'Resent',
								'api_order_id' => -1,
							];
							$this->db->update($this->tb_order, $data, ['ids' => $order->ids]);
							break;
	
						case 'cancel_order':
							$data = ['status' => 'canceled', 'note' => 'Canceled'];
							$this->db->update($this->tb_order, $data, ['ids' => $order->ids]);
							break;
					}
				}
				redirect(cn('order/log/error'));
			} else {
				_validation('error', "No orders found in error status!");
			}
		}
	
		// Handle single order actions
		if (!is_string($ids)) {
			redirect(cn('order/log'));
		}
	
		$check_item = $this->model->get("ids", $this->tb_order, ['ids' => $ids]);
		if ($check_item) {
			switch ($status) {
				case 'resend_order':
					$data = [
						'status'       => 'pending',
						'note'         => 'Resent',
						'api_order_id' => -1,
					];
					$this->db->update($this->tb_order, $data, ['ids' => $check_item->ids]);
					redirect(cn('order/log/error'));
					break;
	
				default:
					# code...
					break;
			}
		}
		redirect(cn('order/log'));
	}
	
	
	// Delete
	public function ajax_log_delete_item($ids = ""){
		$this->model->delete($this->tb_order, $ids, false);
	}
	public function update_whatsapp_number()
	{
		session_start();
	
		// Ensure the user is logged in
		if (!isset($_SESSION['uid'])) {
			$_SESSION['message'] = 'User not logged in.';
			$_SESSION['message_type'] = 'danger';
			header('Location: ' . cn('order/add')); // Redirect to the user dashboard
			exit;
		}
	
		$user_id = $_SESSION['uid'];
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['whatsapp_number'])) {
			$whatsapp_number = trim($_POST['whatsapp_number']);
	
			// Validate the WhatsApp number format
			if (!preg_match('/^\+?[0-9]{10,15}$/', $whatsapp_number)) {
				$_SESSION['message'] = 'Invalid WhatsApp number format.';
				$_SESSION['message_type'] = 'danger';
				header('Location: ' . cn('order/add'));
				exit;
			}
	
			// Database credentials
			$host = 'localhost';
			$dbname = 'beastsmm_ali';
			$username = 'beastsmm_ali';
			$password = 'ra6efcTo[4z#';
	
			try {
				// Create a new PDO connection
				$conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
				$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
				// Update the WhatsApp number and set the updated flag
				$stmt = $conn->prepare("UPDATE general_users SET whatsapp_number = :whatsapp_number, whatsapp_number_updated = 1 WHERE id = :user_id");
				$stmt->bindParam(':whatsapp_number', $whatsapp_number, PDO::PARAM_STR);
				$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
	
				if ($stmt->execute()) {
					$_SESSION['message'] = 'Thanks, WhatsApp number updated successfully.';
					$_SESSION['message_type'] = 'success';
				} else {
					$_SESSION['message'] = 'Failed to update WhatsApp number.';
					$_SESSION['message_type'] = 'danger';
				}
	
			} catch (PDOException $e) {
				$_SESSION['message'] = 'Connection failed: ' . $e->getMessage();
				$_SESSION['message_type'] = 'danger';
			}
	
			// Close the connection
			$conn = null;
		}
	
		// Redirect to the user dashboard
		header('Location: ' . cn('order/add'));
		exit;
	}
}