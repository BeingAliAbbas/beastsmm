<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Whatsapp_model extends CI_Model {

    // Fetch WhatsApp API settings
    public function get_whatsapp_settings() {
        $query = $this->db->get('whatsapp_config');
        return $query->row_array();
    }

    // Update or insert WhatsApp API settings
    public function update_whatsapp_settings($data) {
        $query = $this->db->get('whatsapp_config');
        if ($query->num_rows() > 0) {
            // Update the first record (assuming only one row exists)
            $this->db->where('id', 1);
            $this->db->update('whatsapp_config', $data);
        } else {
            // Insert new record
            $this->db->insert('whatsapp_config', $data);
        }
        return $this->db->affected_rows() > 0;
    }
}
