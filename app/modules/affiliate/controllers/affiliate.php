<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class affiliate extends MX_Controller {
	public $tb_users;
	public $columns;
	public $module_name;
	public $module_icon;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');
		//Config Module
		$this->tb_users                 = USERS;
	}

	public function index(){

		if(get_option("enable_affiliate") != "1"){
			redirect(cn("statistics"));	
		}
		
		if (get_role("admin")) {
			$affiliate_bal_available = $this->model->get_total_values('affiliate_bal_available');
			$affiliate_bal_transferred = $this->model->get_total_values('affiliate_bal_transferred');
			$total_ref = $this->model->get_total_referrals();
			$user_aff_details->affiliate_bal_available = $affiliate_bal_available;
			$user_aff_details->affiliate_bal_transferred = $affiliate_bal_transferred;			
		}else{
			$uid = session('uid');
			$user_aff_details = $this->model->get("*", $this->tb_users, "id = '{$uid}' ");
			$total_ref = $this->model->get_user_referrals($user_aff_details->affiliate_id);
		}

		$data = array(
			"module"       => get_class($this),
			"details"      => $user_aff_details,
			"total_ref"    => $total_ref,
		);

		// print_r($data["details"]["affiliate_bal_available"]); die;
	
		$this->template->build('index', $data);
	}

	public function ajax_transfer_balance(){

		$uid = session("uid");
		$user = $this->model->get("*", $this->tb_users, "id = '{$uid}'");
		$balance = $user->balance+$user->affiliate_bal_available;

		$user_update_data = array(
			"balance" => $balance,
			"affiliate_bal_transferred" => $user->affiliate_bal_transferred+$user->affiliate_bal_available,
			"affiliate_bal_available" => "",
		);

		if($this->db->update($this->tb_users, $user_update_data, ["id" => $uid])){
			ms(array(
				"status"  => "success",
				"message" => lang("balance_transferred_successfully"),
			));

		}else{
			ms(array(
				"status"  => "error",
				"message" => lang("error_occured_while_transferring_balance"),
			));
		}
	}

}