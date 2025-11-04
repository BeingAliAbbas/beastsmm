<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class add_funds_model extends MY_Model {
	public function __construct(){
		parent::__construct();
	}
}
class Transaction_model extends CI_Model {
    
    public function get_last_transactions($user_id, $limit = 5) {
        $this->db->select('*');
        $this->db->from('transactions'); // replace 'transactions' with your actual transactions table name
        $this->db->where('user_id', $user_id);
        $this->db->order_by('created_at', 'DESC'); // Assuming you have a 'created_at' timestamp
        $this->db->limit($limit);
        $query = $this->db->get();
        
        return $query->result(); // return the result as an array of objects
    }
}
