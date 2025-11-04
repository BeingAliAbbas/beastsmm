<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * JazzCash payment controller
 * - Single IMAP auto-verification attempt (checks ONLY last 5 emails now)
 * - Immediately logs success (status=1) or pending (status=0)
 * - Sends WhatsApp notification
 * - Applies bonus only on successful auto verification
 *
 * NOTE: Ensure the payments_method.type value in DB matches get_class($this)
 *       (here it's 'jazzcash' because the class name is lowercase).
 *       If your DB uses 'Jazzcash' or 'JazzCash', adjust either the class name
 *       or normalize via strtolower(get_class($this)).
 */
class jazzcash extends MX_Controller {
    protected $tb_transaction_logs = TRANSACTION_LOGS;
    protected $tb_payments         = PAYMENTS_METHOD;
    protected $payment_type;
    protected $payment_id;
    protected $payment_fee = 0;
    protected $currency_rate_to_usd = 1;

    private $imap_host;
    private $imap_user;
    private $imap_pass;

    public function __construct($payment = "") {
        parent::__construct();
        $this->load->model('add_funds_model', 'model');

        // If you want normalized lowercase type across DB, you can enforce:
        // $this->payment_type = strtolower(get_class($this));
        $this->payment_type = get_class($this); // 'jazzcash'

        if (!$payment) {
            $payment = $this->model->get('*', $this->tb_payments, ['type' => $this->payment_type]);
        }

        if (!$payment) {
            show_error("Payment method '{$this->payment_type}' not configured.", 500);
        }

        $this->payment_id = $payment->id;
        $params = $payment->params;
        if (is_string($params)) {
            $params = json_decode($params, true);
        }
        $option = $params['option'] ?? [];

        $this->imap_host = $option['imap_host'] ?? '{imap.gmail.com:993/imap/ssl}INBOX';
        $this->imap_user = $option['imap_user'] ?? '';
        $this->imap_pass = $option['imap_pass'] ?? '';
        $this->payment_fee = (float)($option['tnx_fee'] ?? 0);
        $this->currency_rate_to_usd = (float)($option['rate_to_usd'] ?? 1);
    }

    public function index() {
        redirect(cn('add_funds'));
    }

    public function create_payment($data_payment = []) {
        $rawAmount  = $data_payment['amount'] ?? post('amount') ?? 0;
        $amount     = (float)$rawAmount;
        $tx_orderID = post('order_id') ?: session('qrtransaction_id');

        if (!$amount || !$tx_orderID) {
            return $this->validation_fail('Amount and Order ID are required.');
        }
        if (strlen($tx_orderID) < 5 || strlen($tx_orderID) > 100) {
            return $this->validation_fail('Invalid Order ID format.');
        }

        // Duplicate prevention
        $exists = $this->model->get('id', $this->tb_transaction_logs, [
            'transaction_id' => $tx_orderID,
            'type'           => $this->payment_type
        ]);
        if ($exists) {
            return $this->validation_fail('This Transaction ID has already been used.');
        }

        $converted      = $amount / ($this->currency_rate_to_usd ?: 1);
        $auto_verified  = $this->auto_verify_transaction($tx_orderID, $amount);

        $data_log = [
            'ids'            => ids(),
            'uid'            => session('uid'),
            'type'           => $this->payment_type,
            'transaction_id' => $tx_orderID,
            'amount'         => round($converted, 4),
            'txn_fee'        => round($converted * ($this->payment_fee / 100), 4),
            'note'           => $amount,          // Store original PKR amount (or local currency)
            'status'         => $auto_verified ? 1 : 0,
            'created'        => NOW,
        ];

        $this->db->insert($this->tb_transaction_logs, $data_log);

        $user_info  = session('user_current_info');
        $user_email = $user_info['email'] ?? 'N/A';

        $this->sendWhatsAppNotification(
            $amount,
            $tx_orderID,
            $user_email,
            $auto_verified ? 'completed' : 'new'
        );

        if ($auto_verified) {
            // Apply any bonus & send email
            $addFundsController = APPPATH.'modules/add_funds/controllers/add_funds.php';
            if (file_exists($addFundsController)) {
                require_once $addFundsController;
                if (class_exists('add_funds')) {
                    $adder = new add_funds();
                    $adder->add_funds_bonus_email((object)$data_log, $this->payment_id);
                }
            }

            $this->load->view('jazzcash/redirect', [
                'status'    => 'success',
                'auto'      => true,
                'amount'    => $amount,
                'txid'      => $tx_orderID,
                'converted' => $converted,
                'error_msg' => '',
            ]);
        } else {
            $this->load->view('jazzcash/redirect', [
                'status'    => 'pending',
                'auto'      => false,
                'amount'    => $amount,
                'txid'      => $tx_orderID,
                'converted' => $converted,
                'error_msg' => '',
                'uid'       => session('uid'),
            ]);
        }
    }

    private function validation_fail($msg) {
        $this->load->view('jazzcash/redirect', [
            'status'    => 'failed',
            'auto'      => false,
            'amount'    => '',
            'txid'      => '',
            'converted' => '',
            'error_msg' => $msg,
            'uid'       => session('uid'),
        ]);
        exit;
    }

    /**
     * Auto verification:
     *  - Checks ONLY last 5 emails now (previously 50).
     *  - Looks for transaction ID, amount, and >=3 keywords.
     */
    private function auto_verify_transaction($tx_orderID, $amount) {
        if (empty($this->imap_host) || empty($this->imap_user) || empty($this->imap_pass)) {
            return false;
        }

        // Optional timeouts (ignored if not compiled with IMAP)
        if (function_exists('imap_timeout')) {
            imap_timeout(IMAP_OPENTIMEOUT, 5);
            imap_timeout(IMAP_READTIMEOUT, 5);
            imap_timeout(IMAP_WRITETIMEOUT, 5);
        }

        $imap = @imap_open($this->imap_host, $this->imap_user, $this->imap_pass);
        if (!$imap) {
            log_message('error', 'JazzCash IMAP connection failed: '.imap_last_error());
            return false;
        }

        $uids  = @imap_search($imap, 'ALL', SE_UID);
        $found = false;

        if ($uids && count($uids) > 0) {
            // LIMIT to last 5 messages
            $recent       = array_slice(array_reverse($uids), 0, 5);
            $needleTxID   = strtolower($tx_orderID);
            $formattedAmt = number_format($amount, 2, '.', '');

            // Tailored keywords for JazzCash (removed 'easypaisa')
            $keywords = [
                'received', 'rs', 'pkr', 'jazzcash', 'from',
                'account', 'trx id', 'transaction id', 'tid', 'transaction'
            ];

            foreach ($recent as $uid) {
                $struct = @imap_fetchstructure($imap, $uid, FT_UID);
                $raw    = (!empty($struct->parts) && isset($struct->parts[0]))
                    ? @imap_fetchbody($imap, $uid, 1, FT_UID)
                    : @imap_body($imap, $uid, FT_UID);

                if ($raw === false) continue;

                switch ($struct->encoding ?? 0) {
                    case 3:  $body = base64_decode($raw); break;
                    case 4:  $body = quoted_printable_decode($raw); break;
                    default: $body = $raw;
                }

                $bodyLower = strtolower(trim(preg_replace('/\s+/', ' ', $body)));

                $hasAmount = preg_match('/\brs\.?\s*' . preg_quote($formattedAmt, '/') . '\b/i', $bodyLower);
                $hasTxnID  = strpos($bodyLower, $needleTxID) !== false;

                $keywordMatches = 0;
                foreach ($keywords as $kw) {
                    if (strpos($bodyLower, $kw) !== false) $keywordMatches++;
                }

                if ($hasAmount && $hasTxnID && $keywordMatches >= 3) {
                    $found = true;
                    break;
                }
            }
        }

        imap_close($imap);
        return $found;
    }

    private function sendWhatsAppNotification($amount, $transaction_id, $user_email, $type = 'new') {
        try {
            $config = $this->getWhatsAppConfig();
            if (!$config) return false;

            $api_url               = $config->url;
            $admin_whatsapp_number = $config->admin_phone;
            $api_key               = $config->api_key;
            if (!$api_url || !$admin_whatsapp_number || !$api_key) return false;

            $message = ($type === 'new')
                ? "*🆕 New JazzCash Payment Submission!*\n\n💰 *Amount*: PKR {$amount}\n🔢 *Transaction ID*: {$transaction_id}\n📧 *User Email*: {$user_email}\n\n🔍 Awaiting verification."
                : "*✅ JazzCash Payment Completed!*\n\n💰 *Amount*: PKR {$amount}\n🔢 *Transaction ID*: {$transaction_id}\n📧 *User Email*: {$user_email}\n\n✨ Transaction completed successfully!";

            $payload = json_encode([
                "apiKey"      => $api_key,
                "phoneNumber" => $admin_whatsapp_number,
                "message"     => $message
            ]);

            $ch = curl_init($api_url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ["Content-Type: application/json"],
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_TIMEOUT        => 25,
            ]);

            $response = curl_exec($ch);
            if ($response === false) {
                log_message('error', 'JazzCash WhatsApp cURL error: '.curl_error($ch));
            }
            curl_close($ch);

            $decoded = json_decode($response, true);
            return $decoded['success'] ?? false;
        } catch (\Throwable $e) {
            log_message('error', 'JazzCash WhatsApp exception: '.$e->getMessage());
            return false;
        }
    }

    private function getWhatsAppConfig() {
        try {
            $query = $this->db->select('url, admin_phone, api_key')
                              ->from('whatsapp_config')
                              ->limit(1)
                              ->get();
            return $query->num_rows() ? $query->row() : false;
        } catch (\Throwable $e) {
            log_message('error', 'JazzCash getWhatsAppConfig exception: '.$e->getMessage());
            return false;
        }
    }
}