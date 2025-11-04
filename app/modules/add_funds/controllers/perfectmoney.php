<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class perfectmoney extends MX_Controller {
	public $tb_users;
	public $tb_transaction_logs;
	public $tb_payments;
	public $tb_payments_bonuses;
	public $paypal;
	public $payment_type;
	public $payment_id;
	public $currency_code;
	public $payment_lib;
	public $mode;
	
	public $pm_usd_wallet;
	public $pm_alternate_pass;

	public function __construct($payment = ""){
		parent::__construct();
		$this->load->model('add_funds_model', 'model');

		$this->tb_users            = USERS;
		$this->tb_transaction_logs = TRANSACTION_LOGS;
		$this->tb_payments         = PAYMENTS_METHOD;
		$this->tb_payments_bonuses = PAYMENTS_BONUSES;
		$this->payment_type		   = get_class($this);
		$this->currency_code       = get_option("currency_code", "USD");
		if ($this->currency_code == "") {
			$this->currency_code = 'USD';
		}
		if (!$payment) {
			$payment = $this->model->get('id, type, name, params', $this->tb_payments, ['type' => $this->payment_type]);
		}
		$this->payment_id 	      = $payment->id;
		$params  			      = $payment->params;
		$option                   = get_value($params, 'option');
		$this->mode               = get_value($option, 'environment');
		$this->payment_fee        = get_value($option, 'tnx_fee');

		// Payment Option
		$this->pm_usd_wallet      = get_value($option, 'usd_wallet');
		$this->pm_alternate_pass  = get_value($option, 'alternate_pass');

	}

	public function index(){
		redirect(cn('add_funds'));
	}

	/**
	 *
	 * Create payment
	 *
	 */
	public function create_payment($data_payment = ""){
		_is_ajax($data_payment['module']);
		$amount = $data_payment['amount'];
		if (!$amount) {
			_validation('error', lang('There_was_an_error_processing_your_request_Please_try_again_later'));
		}

		if (!$this->pm_alternate_pass || !$this->pm_usd_wallet) {
			_validation('error', lang('this_payment_is_not_active_please_choose_another_payment_or_contact_us_for_more_detail'));
		}

		$amount = number_format($amount, 2, '.', ',');
        $users = session('user_current_info');
        $order_id = strtotime(NOW);
        $perfectmoney = array(
        	'PAYEE_ACCOUNT' 	=> $this->pm_usd_wallet,
        	'PAYEE_NAME' 		=> get_option('website_name'),
        	'PAYMENT_UNITS' 	=> $this->currency_code,
        	'STATUS_URL' 		=> cn("add_funds/perfectmoney/complete"),
        	'PAYMENT_URL' 		=> cn("add_funds/perfectmoney/complete"),
        	'NOPAYMENT_URL' 	=> cn("add_funds/perfectmoney/"),
        	'BAGGAGE_FIELDS' 	=> 'IDENT',
        	'ORDER_NUM' 		=> $order_id,
        	'PAYMENT_ID' 		=> strtotime(NOW),
        	'CUST_NUM' 		    => "USERID" . rand(10000,99999999),
        	'memo' 		        => "Balance recharge - ".  $users['email'],

        );
		$tnx_id = $perfectmoney['PAYMENT_ID'].':'.$perfectmoney['PAYEE_ACCOUNT'].':'. $amount.':'.$perfectmoney['PAYMENT_UNITS'];
		$tnx_id = sha1($tnx_id);
		$data_tnx_log = array(
			"ids" 				=> ids(),
			"uid" 				=> session("uid"),
			"type" 				=> $this->payment_type,
			"transaction_id" 	=> $tnx_id,
			"amount" 	        => $amount,
			'txn_fee'           => $amount * ($this->payment_fee / 100),
			"status" 	        => 0,
			"created" 			=> NOW,
		);
		$transaction_log_id = $this->db->insert($this->tb_transaction_logs, $data_tnx_log);
		
		$data = array(
			"module"        => 'add_funds',
			"amount"        => $amount,
			"perfectmoney"  => (object)$perfectmoney,
		);
		$this->load->view("perfectmoney/redirect", $data);
		
	}

	/**
	 *
	 * Call Execute payment after creating payment
	 *
	 */
	public function complete(){
		
		// file_put_contents($_SERVER['DOCUMENT_ROOT'].'/perfectmoney_result.txt', json_encode($_REQUEST).PHP_EOL, FILE_APPEND);
		/*----------  Insert to Transaction table  ----------*/
		if (!isset($_REQUEST['PAYMENT_BATCH_NUM'])) {
			redirect(cn("add_funds"));
		}
		$tnx_id = $_REQUEST['PAYMENT_ID'].':'.$_REQUEST['PAYEE_ACCOUNT'].':'. $_REQUEST['PAYMENT_AMOUNT'].':'.$_REQUEST['PAYMENT_UNITS'];
		$tnx_id = sha1($tnx_id);
		$transaction = $this->model->get('*', $this->tb_transaction_logs, ['transaction_id' => $tnx_id, 'status' => 0, 'type' => $this->payment_type]);

		if (!$transaction) {
			redirect(cn("add_funds"));
		}
		
		// check V2_hash
		$v2_hash = false;
		$post = $_REQUEST;
		$v2_hash  = $this->check_v2_hash($this->pm_alternate_pass, $post);
		
		if ($_POST['PAYEE_ACCOUNT'] == $this->pm_usd_wallet && $transaction && $transaction->amount == $_REQUEST['PAYMENT_AMOUNT'] && $v2_hash) {

            $this->db->update($this->tb_transaction_logs, ['status' => 1, 'transaction_id' => $_REQUEST['PAYMENT_BATCH_NUM']],  ['id' => $transaction->id]);
            // Update Balance 
			require_once 'add_funds.php';
			$add_funds = new add_funds();
			$add_funds->add_funds_bonus_email($transaction, $this->payment_id);
			
			if (!session('uid')) {
				set_session('uid', $transaction->uid);
			}
			redirect(cn("transactions"));
		}else{
			$this->db->update($this->tb_transaction_logs, ['status' => -1],  ['id' => $transaction->id]);
			redirect(cn("add_funds"));
		}
	}

	private function check_v2_hash($perfectmoney_alternate_passphrase = "", $post){
		$alternate_passphrase = strtoupper(md5($perfectmoney_alternate_passphrase));;
		$string= $post['PAYMENT_ID'].':'.$post['PAYEE_ACCOUNT'].':'. $post['PAYMENT_AMOUNT'].':'.$post['PAYMENT_UNITS'].':'. $post['PAYMENT_BATCH_NUM'].':'. $post['PAYER_ACCOUNT'].':'.$alternate_passphrase.':'. $post['TIMESTAMPGMT'];
		$hash = strtoupper(md5($string));
		
		if ($hash == $post['V2_HASH']) {
			return true;
		}else{
			return false;
		}
	}
}