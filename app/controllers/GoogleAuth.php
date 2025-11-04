<?php
ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'libraries/Google_oauth.php';

class GoogleAuth extends CI_Controller {

    public $benchmark; // For PHP 8.2+ dynamic property warning fix

    private $client_id = '226964174730-mj14ek68agqgr5c89sv2dsqkdgt221jj.apps.googleusercontent.com';
    private $client_secret = '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';
    private $redirect_uri = 'googleauth/callback';

    public function login() {
        $google = new Google_oauth($this->client_id, $this->client_secret, $this->redirect_uri);
        $auth_url = $google->create_login_url();
        redirect($auth_url);
    }

    public function callback() {
        $google = new Google_oauth($this->client_id, $this->client_secret, $this->redirect_uri);
        $token = $google->get_access_token();

        if (!$token) {
            echo "Failed to get token.";
            exit;
        }

        $user_info = $google->get_user_info();
        if (!$user_info || empty($user_info->email)) {
            echo "Failed to get user info.";
            var_dump($user_info);
            exit;
        }

        $email = $user_info->email;
        $name = $user_info->name;
        $google_id = $user_info->id;

        $this->load->model('auth/Auth_model', 'auth_model');
        $user = $this->auth_model->get_user_by_google_id_or_email($google_id, $email);

        if (!$user) {
            $user_id = $this->auth_model->create_user_from_google($name, $email, $google_id);
            if (!$user_id) {
                echo "Failed to create user.";
                exit;
            }
        } else {
            $user_id = $user->id;
        }

        $this->session->set_userdata('user_id', $user_id);

        echo "Login successful! User ID: " . $user_id;
        // Uncomment this after testing:
        // redirect('dashboard');
    }
}