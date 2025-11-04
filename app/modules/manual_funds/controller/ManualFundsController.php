<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ManualFundsController extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('ManualFundsModel'); // Load the model
    }

    // This is the index method that loads the view for the manual funds page
    public function index() {
        // You can pass any necessary data here if needed, like available payment methods
        $data['payments_defaut'] = $this->ManualFundsModel->get_payment_methods();
        $this->load->view('manual_funds/index', $data); // Load the index view from the views folder
    }

    // This is the add_funds method that handles the form submission for adding funds
    public function add_funds() {
        // Get data from the form submission
        $email = $this->input->post('email_to');
        $funds = $this->input->post('funds');
        $payment_method = $this->input->post('payment_method');
        $transaction_id = $this->input->post('transaction_id');

        // Here you would process the data and update the database, call the model method
        $result = $this->ManualFundsModel->add_funds_manual($email, $funds, $payment_method, $transaction_id);
        
        if ($result) {
            echo json_encode(['status' => 'success', 'message' => 'Funds added successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error adding funds.']);
        }
    }
}
?>
