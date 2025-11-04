<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class currencies extends MX_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model(get_class($this).'_model', 'model');
    }

    /**
     * Set currency selection (called via AJAX)
     */
    public function set_currency() {
        if ($this->input->method() !== 'post') {
            ms([
                'status'  => 'error',
                'message' => 'Invalid request method'
            ]);
        }

        $currency_code = $this->input->post('currency_code', true);
        
        if (empty($currency_code)) {
            ms([
                'status'  => 'error',
                'message' => 'Currency code is required'
            ]);
        }

        // Verify currency exists and is enabled
        $currency = $this->model->get_by_code($currency_code);
        
        if (!$currency || !$currency->enabled) {
            ms([
                'status'  => 'error',
                'message' => 'Invalid or disabled currency'
            ]);
        }

        // Set in session
        $this->session->set_userdata('selected_currency', $currency_code);
        
        // Set cookie for 30 days
        setcookie('selected_currency', $currency_code, time() + (30 * 24 * 60 * 60), '/');

        ms([
            'status'  => 'success',
            'message' => 'Currency changed successfully'
        ]);
    }

    /**
     * Admin management - list currencies
     */
    public function manage() {
        if (!get_role('admin')) {
            redirect(cn('order/add'));
        }

        $data = [
            'module' => get_class($this),
            'currencies' => $this->model->get_all_currencies()
        ];

        $this->template->build('manage', $data);
    }

    /**
     * Add new currency
     */
    public function add() {
        if (!get_role('admin')) {
            ms(['status' => 'error', 'message' => 'Access denied']);
        }

        if ($this->input->method() !== 'post') {
            ms(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $data = [
            'code'       => strtoupper($this->input->post('code', true)),
            'symbol'     => $this->input->post('symbol', true),
            'name'       => $this->input->post('name', true),
            'rate'       => (float)$this->input->post('rate', true),
            'enabled'    => (int)$this->input->post('enabled', true),
            'is_default' => 0
        ];

        // Validate
        if (empty($data['code']) || empty($data['symbol']) || empty($data['name']) || $data['rate'] <= 0) {
            ms(['status' => 'error', 'message' => 'All fields are required and rate must be positive']);
        }

        // Check if currency code already exists
        if ($this->model->get_by_code($data['code'])) {
            ms(['status' => 'error', 'message' => 'Currency code already exists']);
        }

        if ($this->model->add_currency($data)) {
            ms(['status' => 'success', 'message' => 'Currency added successfully']);
        } else {
            ms(['status' => 'error', 'message' => 'Failed to add currency']);
        }
    }

    /**
     * Update currency
     */
    public function update() {
        if (!get_role('admin')) {
            ms(['status' => 'error', 'message' => 'Access denied']);
        }

        if ($this->input->method() !== 'post') {
            ms(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $id = (int)$this->input->post('id', true);
        $data = [
            'symbol'  => $this->input->post('symbol', true),
            'name'    => $this->input->post('name', true),
            'rate'    => (float)$this->input->post('rate', true),
            'enabled' => (int)$this->input->post('enabled', true)
        ];

        // Validate
        if ($id <= 0 || empty($data['symbol']) || empty($data['name']) || $data['rate'] <= 0) {
            ms(['status' => 'error', 'message' => 'All fields are required and rate must be positive']);
        }

        // Don't allow disabling PKR
        $currency = $this->db->where('id', $id)->get('currencies')->row();
        if ($currency && $currency->code === 'PKR' && $data['enabled'] == 0) {
            ms(['status' => 'error', 'message' => 'Cannot disable base currency (PKR)']);
        }

        if ($this->model->update_currency($id, $data)) {
            ms(['status' => 'success', 'message' => 'Currency updated successfully']);
        } else {
            ms(['status' => 'error', 'message' => 'Failed to update currency']);
        }
    }

    /**
     * Set default currency
     */
    public function set_default() {
        if (!get_role('admin')) {
            ms(['status' => 'error', 'message' => 'Access denied']);
        }

        if ($this->input->method() !== 'post') {
            ms(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $id = (int)$this->input->post('id', true);

        if ($id <= 0) {
            ms(['status' => 'error', 'message' => 'Invalid currency ID']);
        }

        if ($this->model->set_default($id)) {
            ms(['status' => 'success', 'message' => 'Default currency set successfully']);
        } else {
            ms(['status' => 'error', 'message' => 'Failed to set default currency']);
        }
    }

    /**
     * Delete currency
     */
    public function delete() {
        if (!get_role('admin')) {
            ms(['status' => 'error', 'message' => 'Access denied']);
        }

        if ($this->input->method() !== 'post') {
            ms(['status' => 'error', 'message' => 'Invalid request method']);
        }

        $id = (int)$this->input->post('id', true);

        if ($id <= 0) {
            ms(['status' => 'error', 'message' => 'Invalid currency ID']);
        }

        if ($this->model->delete_currency($id)) {
            ms(['status' => 'success', 'message' => 'Currency deleted successfully']);
        } else {
            ms(['status' => 'error', 'message' => 'Failed to delete currency (cannot delete base currency PKR)']);
        }
    }
}
