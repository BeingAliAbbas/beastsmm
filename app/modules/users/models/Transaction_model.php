<?php

class Transaction_model extends CI_Model {
    public function get_total_deposit($uid) {
        // Query to calculate the total deposit, including all types of deposits (easypaisa, jazzcash, manual)
        $this->db->select_sum('amount');  // Sum the 'amount' column
        $this->db->from('general_transaction_logs');
        $this->db->where('uid', $uid);  // Filter by the user ID
        $this->db->where('status', 1);  // Only include successful transactions (status 1)
        
        // Add any other filters if needed, like deposit type
        // You can use $this->db->where_in('type', ['easypaisa', 'jazzcash', 'manual']); if you need to filter by specific types

        $query = $this->db->get();

        if ($query->num_rows() > 0) {
            return $query->row()->amount;  // Return the sum of all amounts
        }

        return false;
    }
}
