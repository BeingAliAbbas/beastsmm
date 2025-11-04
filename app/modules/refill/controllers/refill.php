<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class refill extends MX_Controller {
	public $tb_users;
	public $tb_order;
	public $tb_categories;
	public $tb_services;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');

		//Config Module
		$this->tb_users               = USERS;
		$this->tb_order               = ORDER;
		$this->tb_categories          = CATEGORIES;
		$this->tb_services            = SERVICES;
		$this->module_name            = 'Order';
		$this->module_icon            = "fa ft-users";

		if (get_role("admin") || get_role("supporter")) {
			$this->columns = array(
				"order_id"                  => lang("order_id"),
				"api_order_id"              => lang("api_orderid"),
				"uid"                       => lang("User"),
				"order_basic_details"       => lang("order_basic_details"),
				"created"                   => lang("Created"),
				"status"                    => lang("Status"),
				"refill"					=> lang("Refill"),
				"refill_disable"			=> lang("Refill_disable"),
			);
		}

	}

	// ADD
	public function index(){
		redirect(cn("refill/log"));
	}
	

	/**
	 *
	 * Logs
	 *
	 */
	public function log($order_status = ""){
		if (get_role("user")) {
			redirect(cn("order/log"));
		}
		if ($order_status == "") {
			$order_status = "all";
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


	/**
	 *
	 * Order details for Dripfeed and Subscription
	 *
	 */
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
		$order    = $this->model->get("*", $this->tb_order, "ids = '{$ids}'");
		$data = array(
			"module"   		=> get_class($this),
			"order" 	    => $order,
		);
		$this->load->view('logs/update', $data);
	}


	public function refill_order($ids = ""){
		$refill_status	= "no";

		$data = array(
			"refill_status" => $refill_status,
			"changed" 		=> NOW,
		);

		$this->db->update($this->tb_order, $data, array("ids" => $ids));

		$check_item = $this->model->get("ids, status", $this->tb_order, "ids = '{$ids}'");
		if(!empty($check_item)){
			// if ($status == "completed") {			
			// }
			ms(array(
				"status"  => "success",
				"message" => lang("Completed_successfully")
			));
		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
			));
		}
	}
	
	public function disable_refill_order($ids = ""){
		$refill_status	= "never";

		$data = array(
			"refill_status" => $refill_status,
			"changed" 		=> NOW,
		);

		$this->db->update($this->tb_order, $data, array("ids" => $ids));

		$check_item = $this->model->get("ids, status", $this->tb_order, "ids = '{$ids}'");
		if(!empty($check_item)){
			// if ($status == "completed") {			
			// }
			ms(array(
				"status"  => "success",
				"message" => lang("Disabled_successfully")
			));
		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("There_was_an_error_processing_your_request_Please_try_again_later")
			));
		}
	}

	public function ajax_search(){
		$k = post("k");
		$order_logs = $this->model->get_orders_logs_by_search($k);
		$data = array(
			"module"     => get_class($this),
			"columns"    => $this->columns,
			"order_logs" => $order_logs,
		);
		$this->load->view("logs/ajax_search", $data);
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
	/**
	 *
	 * Delete item
	 *
	 */
	public function ajax_log_delete_item($ids = ""){
		$this->model->delete($this->tb_order, $ids, false);
	}

}