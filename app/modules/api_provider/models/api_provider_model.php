<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class api_provider_model extends MY_Model {
	public $tb_users;
	public $tb_categories;
	public $tb_services;
	public $tb_api_providers;
	public $tb_orders;

	public function __construct(){
		$this->tb_categories 		= CATEGORIES;
		$this->tb_services   		= SERVICES;
		$this->tb_api_providers   	= API_PROVIDERS;
		$this->tb_orders     		= ORDER;
		parent::__construct();
	}

	function get_api_lists($status = false){
		$data  = array();
		if ($status) {
			$this->db->where("status", 1);
		}
		$this->db->select("*");
		$this->db->from($this->tb_api_providers);
		$this->db->order_by("id", 'ASC');

		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	function get_all_orders(){
		$data  = array();
		$where = "(`status` = 'pending' or `status` = 'inprogress')";
		$this->db->select("*");
		$this->db->from($this->tb_orders);
		$this->db->where($where);
		$this->db->where("api_provider_id !=", 0);
		$this->db->where("api_order_id =", -1);
		$this->db->order_by("id", 'ASC');
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

function get_all_orders_status($limit = "", $start = ""){
    $this->db->select("*");
    $this->db->from($this->tb_orders);

    // Ignore completed, partial, canceled, and error orders
    $this->db->where_not_in('status', ['completed', 'partial', 'canceled', 'error']);

    // Order by latest
    $this->db->order_by("id", 'DESC');

    // Apply limit and start for pagination
    $this->db->limit($limit, $start);

    $query = $this->db->get();
    return $query->result();
}


	function get_all_subscriptions_status(){
		$where = "(`sub_status` = 'Active' or `sub_status` = 'Paused' OR `status` = '') AND `api_provider_id` != 0 AND `api_order_id` > 0 AND `changed` < '".NOW."' AND service_type = 'subscriptions'";
		$this->db->select("*");
		$this->db->from($this->tb_orders);
		$this->db->where($where);
		$this->db->order_by("id", 'ASC');
		$this->db->limit(15,0);
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}
}
