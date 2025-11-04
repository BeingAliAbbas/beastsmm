<?php
defined('BASEPATH') or exit('No direct script access allowed');

class childpanel extends MX_Controller
{
	public $tb_users;
	public $tb_childpanel;
	public $module_name;
	public $module_icon;

	public function __construct()
	{
		parent::__construct();
		$this->load->model(get_class($this) . '_model', 'model');

		//Config Module
		$this->tb_users               = USERS;
		$this->tb_childpanel          = CHILDPANEL;
		$this->module_name            = 'Childpanel';
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
				"uid"                       => lang("User"),
				"order_basic_details"       => lang("order_basic_details"),
				"created"                   => lang("Created"),
				"status"                    => lang("Status"),
				"action"                    => lang("Action"),
			);
		}
	}

	// ADD
	public function index()
	{
		if (get_option("is_childpanel_status") == "0") {
			redirect(cn("statistics"));
		}
		redirect(cn("childpanel/add"));
	}

	public function add()
	{
		if (get_option("is_childpanel_status") == "0") {
			redirect(cn("statistics"));
		}
		$data = array(
			"module"       => get_class($this),
		);
		$this->template->build('add/add', $data);
	}

	public function ajax_add_order()
	{
		$agree 		= post("agree");
		$email      = post("email");
		$domain 	= post("domain");
		$pass 		= post("pass");
		$conf_pass  = post("conf_pass");

		if (empty($email)) {
			ms(array(
				"status"  => "error",
				"message" => lang("please_enter_a_valid_email")
			));
		}

		if (empty($domain)) {
			ms(array(
				"status"  => "error",
				"message" => lang("please_enter_a_valid_domain")
			));
		}

		if (empty($pass)) {
			ms(array(
				"status"  => "error",
				"message" => lang("please_enter_a_valid_pass")
			));
		}

		if ($pass != $conf_pass) {
			ms(array(
				"status"  => "error",
				"message" => lang("passwords_do_not_match")
			));
		}

		$total_charge = get_option("childpanel_price");

		$date = new DateTime(NOW);
		$date->modify('+1 month');
		$renewal_date = $date->format('Y-m-d');

		/*----------  Collect data import to database  ----------*/
		$data = array(
			"ids" 	        	=> ids(),
			"uid" 	        	=> session("uid"),
			"child_key"         => ids(),
			"email"  			=> $email,
			"domain"  			=> $domain,
			"password" 		 	=> $pass,
			"charge" 	    	=> $total_charge,
			"status"			=> 'processing',
			"renewal_date"		=> $renewal_date,
			"changed" 	    	=> NOW,
			"created" 	    	=> NOW,
		);

		// Check agree
		if (!$agree) {
			ms(array(
				"status"  => "error",
				"message" => lang("you_must_confirm_to_the_conditions_before_place_order")
			));
		}
		// check balance
		$user = $this->model->get("balance", $this->tb_users, ['id' => session('uid')]);
		if ($user->balance != 0 && $user->balance < $total_charge || $user->balance == 0) {
			ms(array(
				"status"  => "error",
				"message" => lang("not_enough_funds_on_balance")
			));
		}

		$this->save_order($this->tb_childpanel, $data, $user->balance, $total_charge);
	}

	/*----------  insert data to order  ----------*/
	private function save_order($table, $data_orders, $user_balance = "", $total_charge = "")
	{
		$this->db->insert($table, $data_orders);
		$order_id = $this->db->insert_id();
		if ($this->db->affected_rows() > 0) {

			$new_balance = $user_balance - $total_charge;
			$new_balance = ($new_balance > 0) ? $new_balance : 0;
			$this->db->update($this->tb_users, ["balance" => $new_balance], ["id" => session("uid")]);

			/*----------  Send Order notificaltion notice to Admin  ----------*/
			if (get_option("is_order_notice_email", '')) {
				$user_email = $this->model->get("email", $this->tb_users, "id = '" . session('uid') . "'")->email;

				$subject = getEmailTemplate("order_success")->subject;
				$subject = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $subject);
				$email_content = getEmailTemplate("order_success")->content;
				$email_content = str_replace("{{user_email}}", $user_email, $email_content);
				$email_content = str_replace("{{order_id}}", $order_id, $email_content);
				$email_content = str_replace("{{currency_symbol}}", get_option("currency_symbol", ""), $email_content);
				$email_content = str_replace("{{total_charge}}", $total_charge, $email_content);
				$email_content = str_replace("{{website_name}}", get_option("website_name", "SmartPanel"), $email_content);

				$admin_id = $this->model->get("id", $this->tb_users, "role = 'admin'", "id", "ASC")->id;
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

	/**
	 *
	 * Logs
	 *
	 */
	public function log($order_status = "")
	{
		if (get_option("is_childpanel_status") == "0") {
			redirect(cn("statistics"));
		}
		if ($order_status == "") {
			$order_status = "all";
		}
		$page        = (int)get("p");
		$page        = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);
		$query = array();
		$query_string = "";
		if (!empty($query)) {
			$query_string = "?" . http_build_query($query);
		}
		$config = array(
			'base_url'           => cn(get_class($this) . "/log/" . $order_status . $query_string),
			'total_rows'         => $this->model->get_order_logs_list(true, $order_status),
			'per_page'           => $limit_per_page,
			'use_page_numbers'   => true,
			'prev_link'          => '<i class="fe fe-chevron-left"></i>',
			'first_link'         => '<i class="fe fe-chevrons-left"></i>',
			'next_link'          => '<i class="fe fe-chevron-right"></i>',
			'last_link'          => '<i class="fe fe-chevrons-right"></i>',
		);
		update_options_status();
		$this->pagination->initialize($config);
		$links = $this->pagination->create_links();

		$order_logs = $this->model->get_order_logs_list(false, $order_status, $limit_per_page, $page * $limit_per_page);
		$data = array(
			"module"       => get_class($this),
			"columns"      => $this->columns,
			"order_logs"   => $order_logs,
			"order_status" => $order_status,
			"links"        => $links,
		);
		$this->template->build('logs/logs', $data);
	}


	public function log_update($ids = "")
	{
		$order    = $this->model->get("*", $this->tb_childpanel, "ids = '{$ids}'");
		$data = array(
			"module"   		=> get_class($this),
			"order" 	    => $order,
		);
		$this->load->view('logs/update', $data);
	}

	public function ajax_logs_update($ids = "")
	{
		$status 		= post("status");

		$data = array(
			"status"    	=> $status,
			"changed" 		=> NOW,
		);

		$check_item = $this->model->get("ids, charge, uid, status", $this->tb_childpanel, "ids = '{$ids}'");
		if (!empty($check_item)) {
			/*----------  If status = refund  ----------*/
			if ($status == "refunded") {
				$charge = $check_item->charge;
				$charge_back = 0;
				$real_charge = 0;

				if ($status == "partial") {
					$charge_back = $charge / 2;
					$real_charge = $charge - $charge_back;
				}

				$user = $this->model->get("id, balance", $this->tb_users, ["id" => $check_item->uid]);
				if (!empty($user) && !in_array($check_item->status, array('partial', 'cancelled', 'refunded'))) {
					$balance = $user->balance;
					$balance += $charge - $real_charge;
					$this->db->update($this->tb_users, ["balance" => $balance], ["id" => $check_item->uid]);
				}
				$data['charge'] = $real_charge;
			}
			$this->db->update($this->tb_childpanel, $data, array("ids" => $check_item->ids));

			ms(array(
				"status"  => "success",
				"message" => lang("Update_successfully")
			));
		} else {
			ms(array(
				"status"  => "error",
				"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
			));
		}
	}

	public function renew_panel($ids = "")
	{
		$date = new DateTime(NOW);
		$date->modify('+1 month');
		$renewal_date = $date->format('Y-m-d');

		$check_item = $this->model->get("*", $this->tb_childpanel, "ids = '{$ids}'");
		$user = $this->model->get("id, balance", $this->tb_users, ["id" => $check_item->uid]);

		if (!empty($check_item)) {

			if ($user->balance < $check_item->charge) {
				ms(array(
					"status"  => "error",
					"message" => lang("Not_sufficient_funds")
				));
			} else {
				$balance = $user->balance - $check_item->charge;
				$this->db->update($this->tb_users, ["balance" => $balance], ["id" => $check_item->uid]);
				$this->db->update($this->tb_childpanel, ["status" => "active", "renewal_date" => $renewal_date], ["id" => $check_item->id]);
				ms(array(
					"status"  => "success",
					"message" => lang("Renewed_successfully")
				));
			}
		} else {
			ms(array(
				"status"  => "error",
				"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
			));
		}
	}


	public function ajax_search()
	{
		$k = post("k");
		$order_logs = $this->model->get_orders_logs_by_search($k);
		$data = array(
			"module"     => get_class($this),
			"columns"    => $this->columns,
			"order_logs" => $order_logs,
		);
		$this->load->view("logs/ajax_search", $data);
	}

	public function ajax_order_by($status = "")
	{
		if (!empty($status) && $status != "") {
			$order_logs = $this->model->get_order_logs_list(false, $status);
			$data = array(
				"module"     => get_class($this),
				"columns"    => $this->columns,
				"order_logs" => $order_logs,
			);
			$this->load->view("logs/ajax_search", $data);
		}
	}

	/**
	 *
	 * Delete item
	 *
	 */
	public function ajax_log_delete_item($ids = "")
	{
		$this->model->delete($this->tb_childpanel, $ids, false);
	}

	public function cron()
	{

		$panel_logs = $this->model->get_order_logs_list_cron();

		$date = new DateTime(NOW);
		$date->modify('+1 month');
		$renewal_date = $date->format('Y-m-d');

		if (!empty($panel_logs)) {
			$i = 0;
			foreach ($panel_logs as $key => $row) {
				$i++;
				if ($row->user_balance < $row->charge) {

					$this->db->update($this->tb_childpanel, ["status" => "terminated"], ["id" => $row->id]);
				} else {
					$balance = $row->user_balance - $row->charge;
					$this->db->update($this->tb_users, ["balance" => $balance], ["id" => $row->uid]);
					$this->db->update($this->tb_childpanel, ["status" => "active", "renewal_date" => $renewal_date], ["id" => $row->id]);
				}
			}
			echo "Success";
		}else{
		    echo "No Panels available to be renewed";
		}
	}
	
	public function check_panel_status()
	{

        $params = [];
		$child_key      = (isset($_REQUEST["key"])) ? strip_tags(urldecode($_REQUEST["key"])) : '';
		// Build parameters and call appropriate sub function
		$params = $_REQUEST;
        
        $child_exists = get_field($this->tb_childpanel, ["child_key" => $child_key], "status");
		if ($child_key == "" || empty($child_exists)) {
			echo_json_string(array(
			    'error' => "1",
				'code' => "not_found",
			));
		}

        switch ($child_exists) {

			case 'active':
				echo_json_string(array(
			    'error' => "0",
				'code' => "active",
			    ));
				break;
				
			case 'processing':
				echo_json_string(array(
			    'error' => "1",
				'code' => "processing",
			    ));
				break;	
			
			case 'refunded':
				echo_json_string(array(
			    'error' => "1",
				'code' => "refunded",
			    ));
				break;
			
			case 'disabled':
				echo_json_string(array(
			    'error' => "1",
				'code' => "disabled",
			    ));
				break;
			
			case 'terminated':
				echo_json_string(array(
			    'error' => "1",
				'code' => "terminated",
			    ));
				break;	
			
			default:
				echo_json_string(array(
			    'error' => "1",
				'code' => "not_found",
			    ));
				break;
		}
		
	}
}
