<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ManualFundsModel extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    // Method to fetch available payment methods (this is an example)
    public function get_payment_methods() {
        // Assume there's a 'payment_methods' table for available methods
        $query = $this->db->get('payment_methods');
        return $query->result();
    }

    // Method to add funds manually to the user balance
    public function add_funds_manual($email, $funds, $payment_method, $transaction_id) {
        // Check if the user exists
        $this->db->where('email', $email);
        $user = $this->db->get('users')->row();
        
        if (!$user) {
            return false; // User not found
        }

        // Update the user's balance
        $new_balance = $user->balance + $funds;
        $this->db->set('balance', $new_balance);
        $this->db->where('email', $email);
        $this->db->update('users');

        // Log the transaction (assuming a 'transactions' table exists)
        $transaction_data = [
            'user_id' => $user->id,
            'amount' => $funds,
            'payment_method' => $payment_method,
            'transaction_id' => $transaction_id,
            'status' => 'success',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->insert('transactions', $transaction_data);

        return true;
    }
}
?>
