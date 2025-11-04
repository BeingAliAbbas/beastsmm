<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class currencies_model extends MY_Model {
    
    public $table = 'currencies';

    public function __construct(){
        parent::__construct();
    }

    /**
     * Get all active currencies
     */
    public function get_active_currencies() {
        return $this->db->where('enabled', 1)
                        ->order_by('is_default', 'DESC')
                        ->order_by('code', 'ASC')
                        ->get($this->table)
                        ->result();
    }

    /**
     * Get currency by code
     */
    public function get_by_code($code) {
        return $this->db->where('code', $code)
                        ->get($this->table)
                        ->row();
    }

    /**
     * Get default currency
     */
    public function get_default_currency() {
        return $this->db->where('is_default', 1)
                        ->get($this->table)
                        ->row();
    }

    /**
     * Update currency
     */
    public function update_currency($id, $data) {
        return $this->db->where('id', $id)
                        ->update($this->table, $data);
    }

    /**
     * Add new currency
     */
    public function add_currency($data) {
        return $this->db->insert($this->table, $data);
    }

    /**
     * Delete currency
     */
    public function delete_currency($id) {
        // Don't allow deleting the base currency (PKR)
        $currency = $this->db->where('id', $id)->get($this->table)->row();
        if ($currency && $currency->code === 'PKR') {
            return false;
        }
        return $this->db->where('id', $id)->delete($this->table);
    }

    /**
     * Set default currency
     */
    public function set_default($id) {
        // First, unset all defaults
        $this->db->update($this->table, ['is_default' => 0]);
        
        // Then set the new default
        return $this->db->where('id', $id)
                        ->update($this->table, ['is_default' => 1]);
    }

    /**
     * Get all currencies (including disabled)
     */
    public function get_all_currencies() {
        return $this->db->order_by('is_default', 'DESC')
                        ->order_by('code', 'ASC')
                        ->get($this->table)
                        ->result();
    }
}
