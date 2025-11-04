<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class affiliate_model extends MY_Model {
	public $tb_users;

	public function __construct(){
		$this->tb_users      			= USERS;
		parent::__construct();
	}

	function get_user_referrals($affiliate_id){
		$data  = array();
		$this->db->select("*");
		$this->db->from($this->tb_users);
		$this->db->where("(`referral_id` = '$affiliate_id')");
		$query = $this->db->get();
		$result = $query->num_rows();
		return $result;
	}

	function get_total_referrals(){
		$data  = array();
		$this->db->select("*");
		$this->db->from($this->tb_users);
		$this->db->where("(`referral_id` != '')");
		$query = $this->db->get();
		$result = $query->num_rows();
		return $result;
	}

	function get_total_values($field){
		$result = $this->get_sum_value($field);
		return $result;
	}

	private function get_sum_value($field){
		$this->db->select("SUM(".$field.") as total");
		$this->db->from($this->tb_users);
		$query = $this->db->get();
		$result = $query->result();
		if ($result[0]->total > 0) {
			return $result[0]->total;
		}else{
			return 0;
		}
	}

}
