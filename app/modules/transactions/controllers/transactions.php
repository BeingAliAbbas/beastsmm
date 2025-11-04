<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Transactions Controller
 *
 * Features:
 *  - List / paginate transactions
 *  - Update transaction (admin)
 *  - Add manual funds directly from Transactions module (admin/supporter)
 *  - WhatsApp notification on successful status change (pending -> paid)
 *  - Search
 *
 * NOTE:
 *  - Make sure ms() helper (JSON response) and ids(), NOW constants exist (already in your codebase).
 *  - Ensure you add a trigger button in the transactions index view to open the add_funds_manual modal:
 *      <a href="<?=cn($module.'/add_funds_manual')?>" class="btn btn-outline-info btn-sm ajaxModal">
 *          <i class="fe fe-dollar-sign mr-1"></i><?=lang('Add_Funds')?>
 *      </a>
 */
class transactions extends MX_Controller {

	public $module;
	public $tb_users;
	public $tb_categories;
	public $tb_services;
	public $tb_transaction_logs;
	public $columns;

	public function __construct(){
		parent::__construct();
		$this->load->model(get_class($this).'_model', 'model');

		$this->module                = get_class($this);
		$this->tb_users              = USERS;
		$this->tb_categories         = CATEGORIES;
		$this->tb_services           = SERVICES;
		$this->tb_transaction_logs   = TRANSACTION_LOGS;

		// Columns (admin vs normal user)
		$this->columns = array(
			"uid"            => lang('User'),
			"transaction_id" => lang('Transaction_ID'),
			"type"           => lang('Payment_method'),
			"amount"         => lang('Amount_includes_fee'),
			"txn_fee"        => 'Transaction fee',
			"note"           => 'Note',
			"created"        => lang('Created'),
			"status"         => lang('Status'),
		);

		if (!get_role("admin")) {
			$this->columns = array(
				"type"     => lang('Payment_method'),
				"amount"   => lang('Amount_includes_fee'),
				"txn_fee"  => 'Transaction fee',
				"created"  => lang('Created'),
				"status"   => lang('Status'),
			);
		}
	}

	/**
	 * Index - transaction list
	 */
	public function index(){
		// Delete unpaid over 2 days
		$this->model->delete_unpaid_payment(2);

		$page            = (int)get("p");
		$page            = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page  = get_option("default_limit_per_page", 10);

		$query           = array(); // Extend with filters if needed
		$query_string    = (!empty($query)) ? "?".http_build_query($query) : "";

		$config = array(
			'base_url'         => cn(get_class($this).$query_string),
			'total_rows'       => $this->model->get_transaction_list(true),
			'per_page'         => $limit_per_page,
			'use_page_numbers' => true,
			'prev_link'        => '<i class="fe fe-chevron-left"></i>',
			'first_link'       => '<i class="fe fe-chevrons-left"></i>',
			'next_link'        => '<i class="fe fe-chevron-right"></i>',
			'last_link'        => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links        = $this->pagination->create_links();
		$transactions = $this->model->get_transaction_list(false, "all", $limit_per_page, $page * $limit_per_page);

		$data = array(
			"module"       => $this->module,
			"columns"      => $this->columns,
			"transactions" => $transactions,
			"links"        => $links,
		);

		$this->template->build('index', $data);
	}

	/**
	 * Edit transaction modal
	 */
	public function update($ids = ""){
		if (!get_role('admin')) {
			redirect(cn());
		}
		$transaction = $this->model->get("*", $this->tb_transaction_logs, ['ids' => $ids]);
		$data = array(
			"module"       => $this->module,
			"transaction"  => $transaction,
		);
		$this->load->view('update', $data);
	}

	/**
	 * Update transaction (status / note / transaction id / method)
	 */
	public function ajax_update($ids = "") {
		if (!get_role('admin')) {
			ms(array('status' => 'error', 'message' => 'Permission denied'));
		}

		$uid            = (int)post("uid");
		$posted_ids     = trim(post("ids"));
		$note           = post("note");
		$transaction_id = trim(post("transaction_id"));
		$payment_method = trim(post("payment_method"));
		$status         = (int)post("status");

		if (!$posted_ids) {
			ms(array("status" => "error", "message" => 'Missing transaction reference'));
		}
		if (!$uid) {
			ms(array("status" => "error", "message" => 'User ID missing'));
		}
		if ($transaction_id == "") {
			ms(array("status" => "error", "message" => 'Transaction ID is required'));
		}
		if ($payment_method == "") {
			ms(array("status" => "error", "message" => 'Payment method is required'));
		}

		// Fetch by unique ids (more reliable) then confirm matching fields
		$check_item = $this->model->get("*", $this->tb_transaction_logs, ['ids' => $posted_ids]);
		if (empty($check_item) || (int)$check_item->uid !== $uid) {
			ms(array("status" => "error", "message" => 'Transaction does not exist'));
		}

		// Previous status
		$prev_status = (int)$check_item->status;

		// Prepare update data
		$update_data = array(
			'transaction_id' => $transaction_id,
			'type'           => $payment_method,
			'note'           => $note,
			'status'         => $status
		);

		$this->db->update($this->tb_transaction_logs, $update_data, ['ids' => $posted_ids]);

		if ($this->db->affected_rows() > 0) {
			// If moved from pending (0) to paid (1) credit the amount (net of fee) to user
			if ($status == 1 && $prev_status == 0) {
				$user_balance_obj = $this->model->get("balance", $this->tb_users, ['id' => $check_item->uid]);
				$current_balance  = $user_balance_obj ? $user_balance_obj->balance : 0;
				$new_balance      = $current_balance + ($check_item->amount - $check_item->txn_fee);
				$this->db->update($this->tb_users, ["balance" => $new_balance], ["id" => $check_item->uid]);

				$this->send_transaction_success_whatsapp($check_item->uid, $transaction_id, $check_item->amount);
			}

			ms(array("status" => "success", "message" => lang("Updated successfully")));
		} else {
			ms(array("status" => "info", "message" => 'No changes detected / already up to date'));
		}
	}

	/**
	 * Modal for adding manual funds (user looked up by email inside the modal)
	 */
	public function add_funds_manual() {
		if (!(get_role('admin') || get_role('supporter'))) {
			redirect(cn());
		}
		$payments_defaut = $this->model->fetch('type, name', PAYMENTS_METHOD, ['status' => 1]);

		$data = [
			'module'          => $this->module,
			'payments_defaut' => $payments_defaut,
		];
		$this->load->view('add_funds_manual', $data);
	}

	/**
	 * AJAX: Add manual funds (admin/supporter)
	 *
	 * POST fields:
	 *  - email
	 *  - funds
	 *  - payment_method
	 *  - transaction_id (optional)
	 *  - txt_note (optional)
	 *  - txt_fee (optional)
	 */
	public function ajax_add_funds_manual() {
		if (!(get_role('admin') || get_role('supporter'))) {
			ms(['status' => 'error', 'message' => 'Permission denied']);
		}

		$email          = trim(post('email'));
		$funds_raw      = post('funds');
		$funds          = (double)$funds_raw;
		$payment_method = trim(post('payment_method'));
		$transaction_id = trim(post('transaction_id'));
		$note           = trim(post('txt_note'));
		$fee_raw        = trim(post('txt_fee'));
		$fee            = ($fee_raw === '' ? 0 : (double)$fee_raw);

		if ($email == '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
			ms(['status' => 'error', 'message' => 'Valid user email is required']);
		}
		if ($funds <= 0) {
			ms(['status' => 'error', 'message' => 'Amount must be greater than zero']);
		}
		if ($payment_method == '') {
			ms(['status' => 'error', 'message' => 'Please choose a payment method']);
		}

		$user = $this->model->get('id, email, balance', $this->tb_users, ['email' => $email]);
		if (!$user) {
			ms(['status' => 'error', 'message' => 'User not found']);
		}

		if ($transaction_id == '') {
			$transaction_id = 'empty';
		}

		// Update user balance
		$new_balance = $user->balance + $funds;
		$this->db->update($this->tb_users, ['balance' => $new_balance], ['id' => $user->id]);

		// Log transaction
		$data_log = [
			'ids'            => ids(),
			'uid'            => $user->id,
			'type'           => $payment_method,
			'transaction_id' => $transaction_id,
			'amount'         => round($funds, 4),
			'txn_fee'        => round($fee, 4),
			'note'           => ($note != '') ? $note : $funds,
			'status'         => 1, // immediate credit
			'created'        => NOW,
		];
		$this->db->insert($this->tb_transaction_logs, $data_log);

		ms([
			'status'  => 'success',
			'message' => 'Funds added successfully',
		]);
	}

	/**
	 * Delete transaction (by ids)
	 */
	public function ajax_delete_item($ids = ""){
		if (!get_role('admin')) {
			ms(['status' => 'error', 'message' => 'Permission denied']);
		}
		$this->model->delete($this->tb_transaction_logs, $ids, false);
		ms(['status' => 'success', 'message' => 'Deleted']);
	}

	/**
	 * Search page
	 */
	public function search(){
		if (!get_role('admin')) {
			redirect(cn($this->module));
		}
		$k              = htmlspecialchars(get('query'));
		$search_type    = (int)get('search_type');
		$data_search    = ['k' => $k, 'type' => $search_type];

		$page           = (int)get("p");
		$page           = ($page > 0) ? ($page - 1) : 0;
		$limit_per_page = get_option("default_limit_per_page", 10);

		$query          = ['query' => $k, 'search_type' => $search_type];
		$query_string   = "?".http_build_query($query);

		$config = array(
			'base_url'         => cn($this->module."/search".$query_string),
			'total_rows'       => $this->model->get_count_items_by_search($data_search),
			'per_page'         => $limit_per_page,
			'use_page_numbers' => true,
			'prev_link'        => '<i class="fe fe-chevron-left"></i>',
			'first_link'       => '<i class="fe fe-chevrons-left"></i>',
			'next_link'        => '<i class="fe fe-chevron-right"></i>',
			'last_link'        => '<i class="fe fe-chevrons-right"></i>',
		);
		$this->pagination->initialize($config);
		$links        = $this->pagination->create_links();
		$transactions = $this->model->search_items_by_get_method($data_search, $limit_per_page, $page * $limit_per_page);

		$data = array(
			"module"       => $this->module,
			"columns"      => $this->columns,
			"transactions" => $transactions,
			"links"        => $links,
		);

		$this->template->build('index', $data);
	}

	/* ----------------------------------------------------------------------
	 * INTERNAL HELPERS
	 * -------------------------------------------------------------------- */

	/**
	 * Send WhatsApp message when a transaction becomes Paid.
	 * @param int    $user_id
	 * @param string $transaction_id
	 * @param mixed  $amount
	 */
	private function send_transaction_success_whatsapp($user_id, $transaction_id, $amount) {
		$user_info    = $this->model->get("*", $this->tb_users, ['id' => $user_id]);
		if (!$user_info) return;

		$phone_number = $user_info->whatsapp_number ?? '';
		$first_name   = $user_info->first_name ?? 'Customer';
		$last_name    = $user_info->last_name ?? '';
		$full_name    = trim($first_name.' '.$last_name);

		$tx = $this->model->get("*", $this->tb_transaction_logs, [
			'uid'            => $user_id,
			'transaction_id' => $transaction_id
		]);
		$payment_method = $tx ? $tx->type : 'N/A';

		$phone_number = ltrim($phone_number, '+'); // basic cleanup

	$message =
"*Payment Received Successfully ✅*\n\n"
."Amount: *{$amount} PKR*\n"
."Transaction ID: `{$transaction_id}`\n"
."Payment Method: {$payment_method}\n\n"
."➡️ Place your order here: beastsmm.pk/order/add";

		$this->send_whatsapp_message($phone_number, $message);
	}

	/**
	 * Low-level WhatsApp message sender
	 */
	private function send_whatsapp_message($phoneNumber, $message) {
		$phoneNumber = preg_replace('/\D/', '', $phoneNumber);
		if ($phoneNumber == '') return;

		$whatsapp_config = $this->model->get("url, api_key", "whatsapp_config", []);
		if (empty($whatsapp_config) || empty($whatsapp_config->url) || empty($whatsapp_config->api_key)) {
			log_message('error', 'WhatsApp config missing');
			return;
		}

		$apiUrl = $whatsapp_config->url;
		$apiKey = $whatsapp_config->api_key;

		$data = [
			'phoneNumber' => $phoneNumber,
			'message'     => $message,
			'apiKey'      => $apiKey,
		];

		$ch = curl_init($apiUrl);
		curl_setopt_array($ch, [
			CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
			CURLOPT_POST           => true,
			CURLOPT_POSTFIELDS     => json_encode($data),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT        => 30,
		]);

		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			log_message('error', 'WhatsApp cURL Error: '.curl_error($ch));
			curl_close($ch);
			return;
		}
		curl_close($ch);

		$respArr = json_decode($response, true);
		if (empty($respArr['success'])) {
			log_message('error', 'WhatsApp API error: '.$response);
		}
	}

}