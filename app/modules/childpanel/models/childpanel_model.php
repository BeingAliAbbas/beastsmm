<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class childpanel_model extends MY_Model {
	public $tb_users;
	public $tb_childpanel;

	public function __construct(){
		$this->tb_childpanel        = CHILDPANEL;
		$this->tb_users             = USERS;
		parent::__construct();
	}

	function get_order_logs_list($total_rows = false, $status = "", $limit = "", $start = ""){
		$data  = array();
		if (get_role("user")) {
			$this->db->where("o.uid", session("uid"));
		}
		if ($limit != "" && $start >= 0) {
			$this->db->limit($limit, $start);
		}
		$this->db->select('o.*, u.email as user_email');
		$this->db->from($this->tb_childpanel." o");
		$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
		if($status != "all" && !empty($status)){
			$this->db->where("o.status", $status);
		}
		$this->db->order_by("o.id", 'DESC');

		$query = $this->db->get();
		if ($total_rows) {
			$result = $query->num_rows();
			return $result;
		}else{
			$result = $query->result();
			return $result;
		}
		return false;
	}

	function get_order_logs_list_cron(){
		$data  = array();
		$now = new DateTime(NOW);
		$renewal_date = $now->format('Y-m-d');

		$this->db->select('o.*, u.balance as user_balance');
		$this->db->from($this->tb_childpanel." o");
		$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
		$this->db->where("o.status !=", 'disabled');
		$this->db->where("o.status !=", 'refunded');
		$this->db->where("o.renewal_date <=", $renewal_date);

		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	function get_orders_logs_by_search($k){
		$k = trim(htmlspecialchars($k));
		if (get_role("user")) {
			$this->db->select('o.*, u.email as user_email');
			$this->db->from($this->tb_childpanel." o");
			$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');

			if ($k != "" && strlen($k) >= 2) {
				$this->db->where("(`o`.`id` LIKE '%".$k."%' ESCAPE '!' OR `o`.`link` LIKE '%".$k."%' ESCAPE '!' OR `o`.`status` LIKE '%".$k."%' ESCAPE '!' OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!')");
			}
			$this->db->where("u.id", session("uid"));
			$this->db->order_by("o.id", 'DESC');
			$query = $this->db->get();
			$result = $query->result();

		}else{
			$this->db->select('o.*, u.email as user_email');
			$this->db->from($this->tb_childpanel." o");
			$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');

			if ($k != "" && strlen($k) >= 2) {
				$this->db->where("(`o`.`id` LIKE '%".$k."%' ESCAPE '!' OR `o`.`link` LIKE '%".$k."%' ESCAPE '!' OR `o`.`status` LIKE '%".$k."%' ESCAPE '!' OR  `s`.`name` LIKE '%".$k."%' ESCAPE '!')");
			}
			$this->db->order_by("o.id", 'DESC');

			$query = $this->db->get();
			$result = $query->result();
		}
		return $result;
	}

	function get_log_details($id){
		$this->db->select('o.*, u.email as user_email');
		$this->db->from($this->tb_childpanel." o");
		$this->db->join($this->tb_users." u", "u.id = o.uid", 'left');
		$this->db->where("o.main_order_id", $id);
		$this->db->order_by("o.id", 'DESC');
		$query = $this->db->get();
		$result = $query->result();
		return $result;
	}

	
}

