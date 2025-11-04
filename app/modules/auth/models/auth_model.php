<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth_model extends MY_Model {
    public function __construct(){
        parent::__construct();
    }

    // Find user by google_id or email in general_users table
    public function get_user_by_google_id_or_email($google_id, $email) {
        $this->db->where('google_id', $google_id);
        $this->db->or_where('email', $email);
        return $this->db->get('general_users')->row();
    }

    // Create user in general_users table
    public function create_user_from_google($name, $email, $google_id) {
        // Split name into first and last (optional)
        $first_name = $name;
        $last_name = '';
        if (strpos($name, ' ') !== false) {
            $parts = explode(' ', $name, 2);
            $first_name = $parts[0];
            $last_name = $parts[1];
        }

        $data = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'google_id' => $google_id,
            'login_type' => 'google',
            'role' => 'user', // default role
            'created' => date('Y-m-d H:i:s'),
            'status' => 1
        ];
        $this->db->insert('general_users', $data);
        return $this->db->insert_id();
    }
}