<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

defined('BASEPATH') or exit('No direct script access allowed');

class sadapay extends MX_Controller
{
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

    public $sadapay_mid;
    public $merchant_key;
    public $currency_rate_to_usd;
    public $payment_fee;

    public function __construct($payment = "")
    {
        parent::__construct();
        $this->load->model('add_funds_model', 'model');

        $this->tb_users            = USERS;
        $this->tb_transaction_logs = TRANSACTION_LOGS;
        $this->tb_payments         = PAYMENTS_METHOD;
        $this->tb_payments_bonuses = PAYMENTS_BONUSES;
        $this->payment_type        = get_class($this);
        $this->currency_code       = get_option("currency_code", "USD");
        if ($this->currency_code == "") {
            $this->currency_code = 'USD';
        }
        if (!$payment) {
            $payment = $this->model->get('id, type, name, params', $this->tb_payments, ['type' => $this->payment_type]);
        }
        $this->payment_id              = $payment->id;
        $params                        = $payment->params;
        $option                        = get_value($params, 'option');
        $this->mode                    = get_value($option, 'environment');
        $this->sadapay_mid             = get_value($option, 'sadapay_mid');
        $this->currency_rate_to_usd    = get_value($option, 'rate_to_usd');
        $this->payment_fee             = get_value($option, 'tnx_fee');
        $this->load->helper("paytm");
        // $this->payment_lib = new paytmapi($this->merchant_key, $this->sadapay_mid, $this->mode, get_option('website_name'));
    }

    public function index()
    {
        redirect(cn('add_funds'));
    }

    /**
     * Create payment
     */
    public function create_payment($data_payment = "")
    {
        _is_ajax($data_payment['module']);
        $amount = $data_payment['amount'];
        if (!$amount) {
            _validation('error', lang('There_was_an_error_processing_your_request_Please_try_again_later'));
        }

        $ORDER_ID = session('qrtransaction_id');
        $TXN_AMOUNT = $amount;

        $data = array(
            "uid" => session('uid'),
        );

        $check_transactionsqr = get_field(TRANSACTION_LOGS, ["transaction_id" => $ORDER_ID], 'id');

        if (empty($check_transactionsqr)) {
            $converted_amount = $amount / $this->currency_rate_to_usd;
            $data_tnx_log = array(
                "ids"               => ids(),
                "uid"               => session("uid"),
                "type"              => $this->payment_type,
                "transaction_id"    => $ORDER_ID,
                "amount"            => round($converted_amount, 4),
                'txn_fee'           => round($converted_amount * ($this->payment_fee / 100), 4),
                "note"              => $TXN_AMOUNT,
                "status"            => 0,
                "created"           => NOW,
            );
            $transaction_log_id = $this->db->insert($this->tb_transaction_logs, $data_tnx_log);

            // Send Email notification for new transaction
            $this->sendTransactionEmail($TXN_AMOUNT, $ORDER_ID, 'Sadapay');

            // Send WhatsApp notification for new transaction
            $this->sendWhatsAppNotification($TXN_AMOUNT, $ORDER_ID, 'new');

            $this->load->view("sadapay/redirect", $data);
        } else {
            ms(array(
                "status"  => "error",
                "message" => lang("transaction_id_already_used"),
            ));
        }
    }

    /**
     * Send Email Notification
     */
    private function sendTransactionEmail($amount, $transaction_id, $payment_method)
    {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'mail.beastsmm.pk'; // Your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'transactions@beastsmm.pk'; // SMTP username
            $mail->Password = 'Aliabbas321@'; // SMTP password

            // SSL on port 465
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Recipients
            $mail->setFrom('transactions@beastsmm.pk', 'Beast SMM Transaction');
            $mail->addAddress('beastsmm98@gmail.com'); // Admin email

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Sadapay Transaction Form Submitted';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; line-height: 1.8; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px; background-color: #f9f9f9;'>
                    <div style='text-align: center; margin-bottom: 20px;'>
                        <img src='https://beastsmm.pk/assets/images/payments/sadapay.png' alt='Sadapay Logo' style='width: 150px;'>
                    </div>
                    <h3 style='color: #4CAF50; text-align: center; font-size: 24px;'>Transaction Form Details</h3>
                    <table style='width: 100%; border-collapse: collapse; margin-top: 20px;'>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold; width: 30%;'>Amount:</td>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$amount PKR</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Transaction ID:</td>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$transaction_id</td>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd; font-weight: bold;'>Payment Method:</td>
                            <td style='padding: 10px; border-bottom: 1px solid #ddd;'>$payment_method</td>
                        </tr>
                    </table>
                    <p style='text-align: center; margin-top: 30px;'>
                        <a href='https://beastsmm.pk/transactions' style='background-color: #4CAF50; color: white; padding: 15px 25px; text-decoration: none; font-size: 16px; font-weight: bold; border-radius: 5px; display: inline-block;'>View Transaction</a>
                    </p>
                </div>";
            // Send email
            $mail->send();
        } catch (Exception $e) {
            log_message('error', 'Sadapay Email Notification Error: ' . $e->getMessage());
        }
    }

    /**
     * Send WhatsApp Notification
     */
    private function sendWhatsAppNotification($amount, $transaction_id, $type = 'new') {
        try {
            // Get user email from session
            $user_info = session('user_current_info');
            $user_email = $user_info['email'] ?? 'N/A';

            // Get WhatsApp configuration from database
            $config = $this->getWhatsAppConfig();

            // If no config found, log error and return
            if (!$config) {
                log_message('error', 'WhatsApp configuration not found in database');
                return false;
            }

            // Assign config values
            $api_url = $config->url;
            $admin_whatsapp_number = $config->admin_phone;
            $api_key = $config->api_key;

            // Validate API configuration
            if (empty($api_url) || empty($admin_whatsapp_number) || empty($api_key)) {
                log_message('error', 'WhatsApp API configuration is incomplete.');
                return false;
            }

            // Different messages for new and completed transactions
            if ($type === 'new') {
                $message = "*🆕 New Sadapay Payment Submission!*\n\n"
                        . "💰 *Amount*: PKR {$amount}\n"
                        . "🔢 *Transaction ID*: {$transaction_id}\n"
                        . "📧 *User Email*: {$user_email}\n\n"
                        . "🔍 New payment submission received. Awaiting verification.";
            } else {
                $message = "*✅ Sadapay Payment Completed!*\n\n"
                        . "💰 *Amount*: PKR {$amount}\n"
                        . "🔢 *Transaction ID*: {$transaction_id}\n"
                        . "📧 *User Email*: {$user_email}\n\n"
                        . "✨ Transaction has been completed successfully!";
            }

            $data = [
                "apiKey" => $api_key,
                "phoneNumber" => $admin_whatsapp_number,
                "message" => $message
            ];

            $ch = curl_init($api_url);

            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_TIMEOUT => 30
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                log_message('error', 'WhatsApp Notification Error: ' . curl_error($ch));
                return false;
            }

            curl_close($ch);

            $responseData = json_decode($response, true);

            return $responseData['success'] ?? false;

        } catch (Exception $e) {
            log_message('error', 'WhatsApp Notification Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch WhatsApp API configuration from the database
     */
    private function getWhatsAppConfig() {
        try {
            $query = $this->db->select('url, admin_phone, api_key')
                              ->from('whatsapp_config')
                              ->limit(1)
                              ->get();

            if ($query->num_rows() > 0) {
                return $query->row();
            }

            return false;
        } catch (Exception $e) {
            log_message('error', 'Error fetching WhatsApp config: ' . $e->getMessage());
            return false;
        }
    }

    public function complete()
    {
        $requestParamList = array("MID" => $this->sadapay_mid, "ORDERID" => session('qrtransaction_id'));
        $responseParamList = getTxnStatusNew($requestParamList);

        $tnx_id = $responseParamList["ORDERID"];
        $transaction = $this->model->get('*', $this->tb_transaction_logs, ['transaction_id' => $tnx_id, 'status' => 0, 'type' => $this->payment_type]);

        if (empty($transaction)) {
            echo "wrong txn id";
            return;
        }

        set_session("uid", $transaction->uid);

        if ($responseParamList["STATUS"] == "TXN_SUCCESS" && $transaction && $responseParamList["TXNAMOUNT"] == $transaction->note) {
            $this->db->update($this->tb_transaction_logs, ['status' => 1, 'transaction_id' => $responseParamList["ORDERID"]], ['id' => $transaction->id]);

            // Send Email notification for completed transaction
            $this->sendTransactionEmail($transaction->note, $responseParamList["ORDERID"], 'Sadapay');

            // Send WhatsApp notification for completed transaction
            $this->sendWhatsAppNotification($transaction->note, $responseParamList["ORDERID"], 'completed');

            // Update Balance 
            require_once 'add_funds.php';
            $add_funds = new add_funds();
            $add_funds->add_funds_bonus_email($transaction, $this->payment_id);

            set_session("transaction_id", $transaction->id);
            redirect(cn("add_funds/success"));
        } else {
            $this->db->update($this->tb_transaction_logs, ['status' => -1, 'transaction_id' => $responseParamList["ORDERID"]], ['id' => $transaction->id]);
            redirect(cn("add_funds/unsuccess"));
        }
    }
}