<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class setting extends MX_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->model(get_class($this).'_model', 'model');
        $this->load->model('language/language_model', 'sub_model');
    }

    public function index($tab = ""){
        $path              = APPPATH.'./modules/setting/views/';
        $path_integrations = APPPATH.'./modules/setting/views/integrations/';
        $tabs = array_merge(
            get_name_of_files_in_dir($path, ['.php']),
            get_name_of_files_in_dir($path_integrations, ['.php'])
        );
        if (($idx = array_search('index', $tabs, true)) !== false) {
            unset($tabs[$idx]);
        }

        if ($tab == "") {
            $tab = "website_setting";
        }
        if (!in_array($tab, $tabs)) {
            redirect(cn('setting'));
        }

        // Load WhatsApp API settings from whatsapp_config (single-row pattern)
        $whatsapp_api = $this->db->get('whatsapp_config')->row();
        $data = [
            "module"       => get_class($this),
            "tab"          => $tab,
            "whatsapp_api" => $whatsapp_api,  // may be null if not created yet
        ];

        $this->template->build('index', $data);
    }

    public function get_content($tab = ""){
        $path              = APPPATH.'./modules/setting/views/';
        $path_integrations = APPPATH.'./modules/setting/views/integrations/';
        $tabs = array_merge(
            get_name_of_files_in_dir($path, ['.php']),
            get_name_of_files_in_dir($path_integrations, ['.php'])
        );
        if (($idx = array_search('index', $tabs, true)) !== false) {
            unset($tabs[$idx]);
        }

        if ($tab == "") {
            $tab = "website_setting";
        }
        if (!in_array($tab, $tabs)) {
            redirect(cn('setting'));
        }

        // Also supply API settings here if partial loads happen via AJAX tab switching
        $whatsapp_api = $this->db->get('whatsapp_config')->row();
        $data = [
            "module"       => get_class($this),
            "tab"          => $tab,
            "whatsapp_api" => $whatsapp_api,
        ];
        $this->template->build('index', $data);
    }

    /**
     * Generic settings saver (existing logic).
     * Saves POST keys as options, including whatsapp_number.
     */
    public function ajax_general_settings() {
        if ($this->input->method() !== 'post') {
            ms([
                'status'  => 'error',
                'message' => 'Invalid method'
            ]);
        }

        $data              = $this->input->post(NULL, true);
        $default_home_page = $this->input->post("default_home_page", true);

        if (is_array($data)) {
            foreach ($data as $key => $value) {

                if (in_array($key, ['csrf_token_name','csrf_test_name'], true)) {
                    continue;
                }

                if (in_array($key, ['embed_javascript', 'embed_head_javascript', 'manual_payment_content'])) {
                    $value = htmlspecialchars(@$_POST[$key], ENT_QUOTES);
                }

                if (in_array($key, ['midtrans_payment_channels', 'coinpayments_acceptance', 'freekassa_acceptance'], true)) {
                    $value = json_encode($value);
                }

                if ($key === 'new_currecry_rate') {
                    $value = (double)$value;
                    if ($value <= 0) $value = 1;
                }

                if ($key === 'whatsapp_number') {
                    $value = trim($value);
                    $normalized = preg_replace('/[\s\-\(\)]+/', '', $value);
                    if ($normalized !== '' && !preg_match('/^\+?[0-9]{6,20}$/', $normalized)) {
                        ms([
                            'status'  => 'error',
                            'message' => 'Invalid WhatsApp number format'
                        ]);
                    }
                    $value = $normalized;
                }

                update_option($key, $value);
            }
        }

        if ($default_home_page != "") {
            $theme_file_path = APPPATH."../themes/config.json";
            if (is_writable(dirname($theme_file_path))) {
                if ($theme_file = @fopen($theme_file_path, "w")) {
                    $txt = '{ "theme" : "'.$default_home_page.'" }';
                    fwrite($theme_file, $txt);
                    fclose($theme_file);
                }
            }
        }

        ms([
            "status"  => "success",
            "message" => lang('Update_successfully')
        ]);
    }

    /**
     * Save WhatsApp API settings (url, api_key, admin_phone) to whatsapp_config table.
     * Table schema expected:
     * id (INT PK, usually 1), url VARCHAR, api_key VARCHAR, admin_phone VARCHAR
     */
    public function ajax_whatsapp_api_settings() {
        if ($this->input->method() !== 'post') {
            ms([
                'status'  => 'error',
                'message' => 'Invalid method'
            ]);
        }

        $url         = trim($this->input->post('url', true));
        $api_key     = trim($this->input->post('api_key', true));
        $admin_phone = trim($this->input->post('admin_phone', true));

        // Basic validation (adjust as needed)
        if ($url === '' || $api_key === '' || $admin_phone === '') {
            ms([
                'status'  => 'error',
                'message' => 'All fields are required'
            ]);
        }

        // Normalize admin phone (optional)
        $normalized_phone = preg_replace('/[\s\-\(\)]+/', '', $admin_phone);
        if (!preg_match('/^\+?[0-9]{6,20}$/', $normalized_phone)) {
            ms([
                'status'  => 'error',
                'message' => 'Invalid admin phone format'
            ]);
        }

        // Ensure single row pattern
        $existing = $this->db->get('whatsapp_config')->row();
        $data = [
            'url'         => $url,
            'api_key'     => $api_key,
            'admin_phone' => $normalized_phone,
        ];

        if ($existing) {
            $this->db->where('id', $existing->id)->update('whatsapp_config', $data);
        } else {
            // Force id=1 (optional) or let auto-increment
            $this->db->insert('whatsapp_config', $data);
        }

        if ($this->db->affected_rows() >= 0) {
            ms([
                'status'  => 'success',
                'message' => lang('Update_successfully'),
                'data'    => $data
            ]);
        } else {
            ms([
                'status'  => 'error',
                'message' => 'No changes detected'
            ]);
        }
    }
}