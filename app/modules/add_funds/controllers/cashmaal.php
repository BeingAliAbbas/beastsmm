<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class cashmaal extends MX_Controller {
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
	
	public $merchant_key;
	public $currency_rate_to_usd;

	public function __construct($payment = ""){
		parent::__construct();
		$this->load->model('add_funds_model', 'model');

		$this->tb_users            = USERS;
		$this->tb_transaction_logs = TRANSACTION_LOGS;
		$this->tb_payments         = PAYMENTS_METHOD;
		$this->tb_payments_bonuses = PAYMENTS_BONUSES;
		$this->payment_type		   = get_class($this);
		
		if (!$payment) {
			$payment = $this->model->get('id, type, name, params', $this->tb_payments, ['type' => $this->payment_type]);
		}
		$this->payment_id 	      = $payment->id;
		$params  			      = $payment->params;
		$option                   = get_value($params, 'option');
		$this->mode               = get_value($option, 'environment');
		$this->payment_fee        = get_value($option, 'tnx_fee');
		$this->currency_code      = get_value($option, "currency_code");
		if ($this->currency_code == "") {
			$this->currency_code = 'USD';
		}

		// Payment Option
		$this->merchant_key       		= get_value($option, 'merchant_key');
		$this->ipn_key       			= get_value($option, 'ipn_key');
		$this->currency_rate_to_usd     = get_value($option, 'rate_to_usd');
		if ($this->currency_rate_to_usd == "") {
			$this->currency_rate_to_usd = 1;
		}

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

		if (!$this->merchant_key) {
			_validation('error', lang('this_payment_is_not_active_please_choose_another_payment_or_contact_us_for_more_detail'));
		}

		if(!session('uid')){
			_validation('error', lang('There_was_an_error_processing_your_request_Please_try_again_later'));
		}

		$checkSum = "";
		$paramList = array();
		$ORDER_ID         = "ORDS" . strtotime(NOW);
		$TXN_AMOUNT       = $amount;  // convert to PKR

		$users = session('user_current_info');
		$website_name = get_option('website_name');

		$paramList["pay_method"]              = "";
		$paramList["amount"]                  = $TXN_AMOUNT;
		$paramList["currency"]                = $this->currency_code;
		$paramList["succes_url"]              = cn("add_funds/cashmaal/complete");
		$paramList["cancel_url"]              = cn("add_funds/unsuccess");
		$paramList["client_email"]            = $users['email'];
		$paramList["web_id"]                  = $this->merchant_key;
		$paramList["order_id"]                = $ORDER_ID;
		$paramList["addi_info"]               = lang('Deposit_to_').$website_name. ' ('.$users['email'].')';

		$data = array(
			'paramList' 	=> $paramList,
			'action_url'  	=> 'https://www.cashmaal.com/Pay/',
		);
		// get TXN ID
		$tnx_id = $this->ipn_key . ":" . $TXN_AMOUNT . ":" . $ORDER_ID;
		$converted_amount = $amount / $this->currency_rate_to_usd;
		$data_tnx_log = array(
			"ids" 				=> ids(),
			"uid" 				=> session("uid"),
			"type" 				=> $this->payment_type,
			"transaction_id" 	=> sha1($tnx_id),
			"amount" 	        => round($converted_amount, 4) ,
			'txn_fee'           => round($converted_amount * ($this->payment_fee / 100), 4),
			"note" 	            => $TXN_AMOUNT,
			"status" 	        => 0,
			"created" 			=> NOW,
		);
		$transaction_log_id = $this->db->insert($this->tb_transaction_logs, $data_tnx_log);
		$this->load->view("cashmaal/redirect", $data);
		
	}

	public function complete(){
		redirect(cn("statistics"));
	}

	public function cashmaal_ipn(){
		if (!isset($_REQUEST) && $_REQUEST['order_id'] && $_REQUEST['CM_TID'] && $_REQUEST['web_id'] && $_REQUEST['ipn_key']  && $_REQUEST['status'] ) {
			_validation('error', "Order validation Error!");
		}

		$order_id = strip_tags(urldecode($_REQUEST["order_id"]));
		$CM_TID = strip_tags(urldecode($_REQUEST["CM_TID"]));
		$web_id = strip_tags(urldecode($_REQUEST["web_id"]));
		$ipn_key = strip_tags(urldecode($_REQUEST["ipn_key"]));
		$status = strip_tags(urldecode($_REQUEST["status"]));
		$amount 	= strip_tags(urldecode($_REQUEST["Amount"]));

		// Tnx ID
		$tnx_id = $ipn_key . ":" . $amount . ":" . $order_id;

		$transaction = $this->model->get('*', $this->tb_transaction_logs, ['transaction_id' => sha1($tnx_id), 'status' => 0, 'type' => $this->payment_type]);

		if (empty($transaction)) {
			_validation('error', "Transaction doesn't exists!");
		}
		if ($transaction && $web_id == $this->merchant_key  && $ipn_key == $this->ipn_key) {

			switch ($status) {
				case 1:
					$tnx_data = [
		        		'status' 			=> 1,
		        		'transaction_id'   	=> $CM_TID,
		        		'data'   			=> json_encode($_REQUEST),
		        		'note'   			=> 'Pay Success',
		        	];
					$this->db->update($this->tb_transaction_logs, $tnx_data, ['id' => $transaction->id]);
					require_once 'add_funds.php';
					$add_funds = new add_funds();
					$add_funds->add_funds_bonus_email($transaction, $this->payment_id);
					_validation('success', "Paid");
					break;

				case 3:
					$tnx_data = [
		        		'status' 			=> -1,
		        		'transaction_id'   	=> $CM_TID,
		        		'data'   			=> json_encode($_REQUEST),
		        		'note'   			=> 'Rejected',
		        	];
					$this->db->update($this->tb_transaction_logs, $tnx_data, ['id' => $transaction->id]);
					_validation('error', "Rejected");
					break;
				case 0:
					$tnx_data = [
		        		'status' 			=> -1,
		        		'transaction_id'   	=> $CM_TID,
		        		'data'   			=> json_encode($_REQUEST),
		        		'note'   			=> 'Cancelled',
		        	];
					$this->db->update($this->tb_transaction_logs, $tnx_data, ['id' => $transaction->id]);
					_validation('error', "Cancelled");
					break;
			}
			
		}else{
			_validation('error', "Error logged");
		}
	}
}