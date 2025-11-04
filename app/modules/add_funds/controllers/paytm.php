<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
class paytm extends MX_Controller {
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
	
	public $paytm_mid;
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
		$this->paytm_mid          		= get_value($option, 'paytm_mid');
		$this->merchant_key       		= get_value($option, 'merchant_key');
		$this->currency_rate_to_usd     = get_value($option, 'rate_to_usd');
		$this->load->library("paytmapi");
		$this->load->helper("paytm");
		$this->payment_lib = new paytmapi($this->merchant_key, $this->paytm_mid, $this->mode, get_option('website_name'));
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

		if (!$this->paytm_mid || !$this->merchant_key) {
			_validation('error', lang('this_payment_is_not_active_please_choose_another_payment_or_contact_us_for_more_detail'));
		}

		$checkSum = "";
		$paramList = array();
		$ORDER_ID         = "ORDS" . strtotime(NOW);
		$CUST_ID          = 'CUST0090';
		$INDUSTRY_TYPE_ID = "Retail";
		$CHANNEL_ID       = 'WEB';

		$TXN_AMOUNT       = $amount; 
		// Create an array having all required parameters for creating checksum.
		$paramList["MID"]              = PAYTM_MERCHANT_MID;
		$paramList["ORDER_ID"]         = $ORDER_ID;
		$paramList["CUST_ID"]          = $CUST_ID;
		$paramList["INDUSTRY_TYPE_ID"] = $INDUSTRY_TYPE_ID;
		$paramList["CHANNEL_ID"]       = $CHANNEL_ID;
		$paramList["TXN_AMOUNT"]       = $TXN_AMOUNT;
		$paramList["WEBSITE"]          = 'Website';
		// callback url
		$paramList["CALLBACK_URL"] = cn("add_funds/paytm/complete");
		$checkSum = getChecksumFromArray($paramList, PAYTM_MERCHANT_KEY);

		$data = array(
			'paramList' => $paramList,
			'checkSum'  => $checkSum,
		);
		$tnx_id = PAYTM_MERCHANT_MID.":".PAYTM_MERCHANT_KEY.":".$ORDER_ID;
		$tnx_id = sha1($tnx_id);

		$converted_amount = $amount / $this->currency_rate_to_usd;
		$data_tnx_log = array(
			"ids" 				=> ids(),
			"uid" 				=> session("uid"),
			"type" 				=> $this->payment_type,
			"transaction_id" 	=> $tnx_id,
			"amount" 	        => round($converted_amount, 4) ,
			'txn_fee'           => round($converted_amount * ($this->payment_fee / 100), 4),
			"note" 	            => $TXN_AMOUNT,
			"status" 	        => 0,
			"created" 			=> NOW,
		);
		$transaction_log_id = $this->db->insert($this->tb_transaction_logs, $data_tnx_log);
		$this->load->view("paytm/redirect", $data);
		
	}

	public function complete(){
		if (!isset($_POST['ORDERID'])) {
			redirect(cn("add_funds"));
		}
		$paytmChecksum   = "";
		$paramList       = array();
		$isValidChecksum = "FALSE";
		/*----------  Verify checksum ----------*/
		foreach($_POST as $key => $value){
			if($key == "CHECKSUMHASH"){
				$paytmChecksum = $value;
			} else {
				$paramList[$key] = $value;
			}
		}

		$isValidChecksum = verifychecksum_e($paramList, PAYTM_MERCHANT_KEY, $paytmChecksum);
		if(PAYTM_MERCHANT_MID != $_POST["MID"] || !$isValidChecksum) {
			redirect(cn("add_funds/unsuccess"));
		}

		$tnx_id = $_POST["MID"].":".PAYTM_MERCHANT_KEY.":".$_POST["ORDERID"];
		$tnx_id = sha1($tnx_id);
		$transaction = $this->model->get('*', $this->tb_transaction_logs, ['transaction_id' => $tnx_id, 'status' => 0, 'type' => $this->payment_type]);
		if (empty($transaction)) {
			redirect(cn("add_funds/unsuccess"));
		}
		// verifying the transaction status
		$requestParamList  = array("MID" => PAYTM_MERCHANT_MID , "ORDERID" => $_POST["ORDERID"]);  
		$StatusCheckSum    = getChecksumFromArray($requestParamList, PAYTM_MERCHANT_KEY);
		$requestParamList['CHECKSUMHASH'] = $StatusCheckSum;
		$responseParamList = getTxnStatus($requestParamList);

		set_session("uid",$transaction->uid);
		
		if ($_POST["STATUS"] == "TXN_SUCCESS"  && $transaction && $responseParamList['ORDERID'] == $_POST["ORDERID"] && $responseParamList['STATUS'] == "TXN_SUCCESS") {

			$this->db->update($this->tb_transaction_logs, ['status' => 1, 'transaction_id' => $_POST["TXNID"]],  ['id' => $transaction->id]);

			// Update Balance 
    		require_once 'add_funds.php';
    		$add_funds = new add_funds();
    		$add_funds->add_funds_bonus_email($transaction, $this->payment_id);
            
			set_session("transaction_id", $transaction->id);
			redirect(cn("add_funds/success"));

		} else {
			$this->db->update($this->tb_transaction_logs, ['status' => -1, 'transaction_id' => $_POST["TXNID"]],  ['id' => $transaction->id]);
			redirect(cn("add_funds/unsuccess"));
		}
	}

}