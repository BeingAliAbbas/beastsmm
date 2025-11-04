<?php
/**
 * IMAP Auto-Verification Library (Enhanced)
 * Features:
 *  - Exact match: transaction_id + amount (note / amount+fee / amount)
 *  - Atomic status update (status=0 -> 1) prevents double-credit
 *  - Automatic balance credit (config toggle)
 *  - WhatsApp notification (config toggle)
 *  - Structured logging
 *
 * Requirements:
 *  - Table general_transaction_logs has columns: id, uid, transaction_id, amount, txn_fee, note, status, verified_at, verify_source
 *  - users table has: id, balance, whatsapp_number, first_name, last_name (adjust if different)
 *  - whatsapp_config table: columns (url, api_key)
 */
class ImapAutoVerifier
{
    protected $imap_host = '{imap.gmail.com:993/imap/ssl}INBOX';
    protected $imap_user = 'beastsmm98@gmail.com';
    protected $imap_pass = 'miii orwi ibaq roqc'; // TODO: move to ENV or config overrides

    protected $db;
    protected $log_file;
    protected $processed_folder      = 'Processed';
    protected $time_window_minutes   = 180;
    protected $max_emails            = 15;
    protected $transaction_table     = 'general_transaction_logs';
    protected $users_table           = 'users';
    protected $whatsapp_config_table = 'whatsapp_config';

    // Behavior toggles (loaded from config)
    protected $enable_credit         = true;
    protected $enable_whatsapp       = true;
    protected $whatsapp_template;

    // Internal flags
    protected $strictPending         = true; // use WHERE status=0 in update for safety

    public function __construct($config = [])
    {
        if (function_exists('get_instance')) {
            $CI = get_instance();
            if ($CI && isset($CI->db)) {
                $this->db = $CI->db;
            }
            // Load imap_cron config for toggles if available
            if (isset($CI->config)) {
                $CI->config->load('imap_cron');
                $this->enable_credit        = (bool)$CI->config->item('imap_auto_credit_enable');
                $this->enable_whatsapp      = (bool)$CI->config->item('imap_auto_whatsapp_enable');
                $this->whatsapp_template    = $CI->config->item('imap_auto_whatsapp_template');
            }
        }

        if (defined('TRANSACTION_LOGS')) {
            $this->transaction_table = TRANSACTION_LOGS;
        }
        if (defined('USERS')) {
            $this->users_table = USERS;
        }

        // Allow overrides from passed $config
        $this->log_file            = $config['log_file']            ?? APPPATH . 'logs/imap_auto_verify.log';
        $this->processed_folder    = $config['processed_folder']    ?? $this->processed_folder;
        $this->time_window_minutes = $config['time_window_minutes'] ?? $this->time_window_minutes;
        $this->max_emails          = $config['max_emails']          ?? $this->max_emails;
        if (isset($config['strict_pending'])) {
            $this->strictPending = (bool)$config['strict_pending'];
        }
        if (isset($config['enable_credit'])) {
            $this->enable_credit = (bool)$config['enable_credit'];
        }
        if (isset($config['enable_whatsapp'])) {
            $this->enable_whatsapp = (bool)$config['enable_whatsapp'];
        }
        if (isset($config['whatsapp_template'])) {
            $this->whatsapp_template = $config['whatsapp_template'];
        }
        if (!$this->whatsapp_template) {
            $this->whatsapp_template = "*Dear {full_name},*\n\nYour transaction of *{amount} PKR* has been successfully processed.\n\n*Transaction ID:* {transaction_id}\n*Payment Method:* {payment_method}\n\nThank you for your payment! 🙏";
        }
    }

    public function run()
    {
        $this->log("---- Cron start ".date('c')." ----");

        if (!$this->db) {
            $this->log("ERROR: DB instance not available.");
            return false;
        }

        $inbox = @imap_open($this->imap_host, $this->imap_user, $this->imap_pass);
        if (!$inbox) {
            $this->log("ERROR: IMAP connect failed: ".imap_last_error());
            return false;
        }

        $sinceDate = date('d-M-Y', strtotime("-{$this->time_window_minutes} minutes"));
        $criteria  = 'SINCE "'.$sinceDate.'"';
        $uids      = imap_search($inbox, $criteria, SE_UID);

        if (!$uids) {
            $this->log("No emails in last {$this->time_window_minutes} minutes.");
            imap_close($inbox);
            $this->log("---- Cron end ".date('c')." ----");
            return true;
        }

        rsort($uids);
        $uids = array_slice($uids, 0, $this->max_emails);

        $processed = 0;
        $matched   = 0;
        foreach ($uids as $uid) {
            $processed++;
            $msgno = imap_msgno($inbox, $uid);
            if (!$msgno) {
                $this->log("WARN: msgno unresolved for UID {$uid}");
                continue;
            }

            $header     = @imap_headerinfo($inbox, $msgno);
            $subjectRaw = $header->subject ?? '';
            $subject    = imap_utf8($subjectRaw);
            $body       = $this->fetchMessageBody($inbox, $uid);
            $normalized = $this->normalizeText($subject."\n".$body);
            $this->logDebugSample($uid, $subject, $normalized);

            $parsed = $this->extractPaymentDetails($subject, $body);
            if (!$parsed) {
                $this->log("PARSE FAIL uid={$uid} subj=".substr($subject,0,80));
                $this->markAsProcessed($inbox, $uid, false);
                continue;
            }
            $this->log("Parsed => ".json_encode($parsed));

            $transaction = $this->findMatchingTransaction($parsed);
            if ($transaction) {
                $statusResult = $this->updateTransactionStatusAndCredit($transaction, $parsed);
                $this->notifyUserIfNeeded($transaction, $parsed, $statusResult['updated']);

                $this->markAsProcessed($inbox, $uid, true);
                $matched++;
                $this->log("SUCCESS: txid={$parsed['transaction_id']} row_id={$transaction->id} updated=".($statusResult['updated']?'yes':'no')." credited=".($statusResult['credited']?'yes':'no')." whatsapp=".($statusResult['whatsapp']?'yes':'no'));
            } else {
                $this->log("NO MATCH txid={$parsed['transaction_id']} amount={$parsed['amount']}");
                $this->markAsProcessed($inbox, $uid, false);
            }
        }

        imap_close($inbox);
        $this->log("SUMMARY processed={$processed} matched={$matched}");
        $this->log("---- Cron end ".date('c')." ----");
        return true;
    }

    /* ---------- Fetch & Decode ---------- */

    protected function fetchMessageBody($inbox, $uid)
    {
        $structure = @imap_fetchstructure($inbox, $uid, FT_UID);
        if (!$structure) {
            return $this->decodeBody(@imap_body($inbox, $uid, FT_UID), 0);
        }
        if (empty($structure->parts)) {
            $raw = @imap_body($inbox, $uid, FT_UID);
            return $this->decodeBody($raw, $structure->encoding ?? 0);
        }
        $partsCollected = [];
        $this->collectParts($inbox, $uid, $structure, '', $partsCollected);
        if (!empty($partsCollected['text/plain'])) return implode("\n",$partsCollected['text/plain']);
        if (!empty($partsCollected['text/html']))  return strip_tags(implode("\n",$partsCollected['text/html']));
        return '';
    }

    protected function collectParts($inbox, $uid, $structure, $prefix, &$store)
    {
        if (!empty($structure->parts)) {
            $i=1;
            foreach ($structure->parts as $part) {
                $partNum = $prefix==='' ? (string)$i : $prefix.'.'.$i;
                $this->collectParts($inbox, $uid, $part, $partNum, $store);
                $i++;
            }
        } else {
            $type = $this->mimeType($structure);
            if (in_array($type, ['text/plain','text/html'])) {
                $raw     = @imap_fetchbody($inbox, $uid, $prefix ?: '1', FT_UID);
                $decoded = $this->decodeBody($raw, $structure->encoding ?? 0);
                $store[$type][] = $decoded;
            }
        }
    }

    protected function mimeType($structure)
    {
        $primary = [0=>'text',1=>'multipart',2=>'message',3=>'application',4=>'audio',5=>'image',6=>'video',7=>'other'];
        $p = $primary[$structure->type] ?? 'other';
        $s = isset($structure->subtype)? strtolower($structure->subtype):'plain';
        return $p.'/'.$s;
    }

    protected function decodeBody($raw, $encoding)
    {
        if ($raw===false || $raw===null) return '';
        switch ($encoding) {
            case 3: return base64_decode($raw);
            case 4: return quoted_printable_decode($raw);
            default: return $raw;
        }
    }

    protected function normalizeText($text)
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES|ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /* ---------- Parse Payment Details ---------- */

    protected function extractPaymentDetails($subject, $body)
    {
        $text = $this->normalizeText($subject."\n".$body);
        $details = [];

        if (preg_match('/(?:Rs\.?|PKR|Amount)\s*([0-9]{1,9}(?:\.[0-9]{1,2})?)/i', $text, $m)) {
            $details['amount'] = (float)$m[1];
        } elseif (preg_match('/\bRs\s*([0-9]{1,9}\.[0-9]{1,2})\b/i', $text, $m)) {
            $details['amount'] = (float)$m[1];
        }

        if (preg_match('/(?:TID|Transaction ID|Txn ID|TRX ID|TRX|Ref#|Tran#|Trans#)\s*[:#\-]?\s*([A-Za-z0-9]{5,})/i', $text, $m)) {
            $details['transaction_id'] = strtoupper($m[1]);
        }

        if (!isset($details['transaction_id'], $details['amount'])) {
            return false;
        }
        return $details;
    }

    /* ---------- Match Transaction ---------- */

    protected function findMatchingTransaction($parsed)
    {
        if (!$this->db) return false;

        $txid      = $parsed['transaction_id'];
        $amountPKR = (float)$parsed['amount'];

        // Get row(s) by transaction_id
        if ($this->strictPending) {
            $this->db->where('status', 0);
        }
        $q = $this->db->get_where($this->transaction_table, ['transaction_id' => $txid]);

        if ($q->num_rows() === 0) {
            $this->log("DEBUG: no row txid={$txid}".($this->strictPending?' (status=0 enforced)':''));
            return false;
        }

        $row         = $q->row();
        $noteAmt     = isset($row->note) ? (float)$row->note : null;
        $baseAmt     = isset($row->amount) ? (float)$row->amount : null;
        $feeAmt      = isset($row->txn_fee) ? (float)$row->txn_fee : 0.0;
        $basePlusFee = ($baseAmt !== null) ? ($baseAmt + $feeAmt) : null;

        $match = false;
        if ($noteAmt !== null && $this->amountEqual($noteAmt, $amountPKR)) {
            $match = true; $this->log("DEBUG: amount match (note) txid={$txid}");
        } elseif ($basePlusFee !== null && $this->amountEqual($basePlusFee, $amountPKR)) {
            $match = true; $this->log("DEBUG: amount match (amount+fee) txid={$txid}");
        } elseif ($baseAmt !== null && $this->amountEqual($baseAmt, $amountPKR)) {
            $match = true; $this->log("DEBUG: amount match (amount) txid={$txid}");
        } else {
            $this->log("DEBUG: amount mismatch txid={$txid} parsed={$amountPKR} note={$noteAmt} amount={$baseAmt} amount+fee={$basePlusFee}");
        }

        return $match ? $row : false;
    }

    protected function amountEqual($a, $b) { return abs($a - $b) < 0.00001; }

    /* ---------- Update, Credit & Notification ---------- */

    /**
     * Performs atomic status update (0->1), credits balance, sends WhatsApp.
     * Returns array: ['updated'=>bool,'credited'=>bool,'whatsapp'=>bool]
     */
    protected function updateTransactionStatusAndCredit($transaction, $parsed)
    {
        $updated   = false;
        $credited  = false;
        $whatsSent = false;

        // Atomic update
        $this->db->where('id', $transaction->id);
        if ($this->strictPending) {
            $this->db->where('status', 0);
        }
        $this->db->update($this->transaction_table, [
            'status'        => 1,
            'verified_at'   => date('Y-m-d H:i:s'),
            'verify_source' => 'IMAP-AUTO'
        ]);

        $aff = $this->db->affected_rows();
        $this->log("DEBUG UPDATE: txRow={$transaction->id} affected_rows={$aff}");
        if ($aff === 1) {
            $updated = true;

            if ($this->enable_credit && isset($transaction->uid)) {
                $credited = $this->creditUserBalance($transaction);
            }

            if ($this->enable_whatsapp && isset($transaction->uid)) {
                $whatsSent = $this->sendWhatsAppNotification($transaction, $parsed);
            }
        } else {
            // Either already paid OR status not 0
            $this->log("INFO: row id={$transaction->id} not updated (maybe already paid). No credit/notification.");
        }

        return [
            'updated'  => $updated,
            'credited' => $credited,
            'whatsapp' => $whatsSent
        ];
    }

    protected function creditUserBalance($transaction)
    {
        // Re-fetch current amounts (to be safe)
        $uid = (int)$transaction->uid;
        $user = $this->db->get_where($this->users_table, ['id' => $uid])->row();
        if (!$user) {
            $this->log("WARN: credit failed user missing uid={$uid}");
            return false;
        }

        $net = (float)$transaction->amount - (float)$transaction->txn_fee;
        if ($net <= 0) {
            $this->log("INFO: net amount <=0 skip credit uid={$uid} txid={$transaction->transaction_id}");
            return false;
        }

        // Atomic increment
        $this->db->set('balance', 'balance + '.$this->db->escape($net), false)
                 ->where('id', $uid)
                 ->update($this->users_table);

        if ($this->db->affected_rows() === 1) {
            $this->log("CREDIT: uid={$uid} +{$net} (txid={$transaction->transaction_id})");
            return true;
        }
        $this->log("WARN: credit affected_rows=0 uid={$uid}");
        return false;
    }

    protected function notifyUserIfNeeded($transaction, $parsed, $justUpdated)
    {
        // Placeholder if you want synchronous email or other channels later
        // Called after updateTransactionStatusAndCredit
    }

    /* ---------- WhatsApp Notification ---------- */

    protected function sendWhatsAppNotification($transaction, $parsed)
    {
        $uid = (int)$transaction->uid;
        $user = $this->db->get_where($this->users_table, ['id' => $uid])->row();
        if (!$user) {
            $this->log("WARN: whatsapp user missing uid={$uid}");
            return false;
        }

        $phone      = isset($user->whatsapp_number) ? trim($user->whatsapp_number) : '';
        $first_name = isset($user->first_name) ? $user->first_name : 'Customer';
        $last_name  = isset($user->last_name) ? $user->last_name : '';
        $full_name  = trim($first_name.' '.$last_name);

        if ($phone === '') {
            $this->log("INFO: whatsapp skipped empty phone uid={$uid}");
            return false;
        }

        $payment_method = $transaction->type ?? 'N/A';
        $amountDisplay  = (float)$transaction->amount;

        $template = $this->whatsapp_template;
        $message  = strtr($template, [
            '{full_name}'      => $full_name,
            '{amount}'         => $amountDisplay,
            '{transaction_id}' => $transaction->transaction_id,
            '{payment_method}' => $payment_method,
        ]);

        $phone = preg_replace('/\D/', '', $phone);
        if ($phone === '') {
            $this->log("INFO: whatsapp phone only non-digits uid={$uid}");
            return false;
        }

        // Load config row
        $cfg = $this->db->get($this->whatsapp_config_table)->row();
        if (!$cfg || empty($cfg->url) || empty($cfg->api_key)) {
            $this->log("ERROR: whatsapp config missing");
            return false;
        }

        $payload = [
            'phoneNumber' => $phone,
            'message'     => $message,
            'apiKey'      => $cfg->api_key,
        ];

        $resp = $this->postJson($cfg->url, $payload);
        if ($resp['error']) {
            $this->log("ERROR: whatsapp curl=".$resp['error']);
            return false;
        }

        $decoded = json_decode($resp['body'], true);
        if (!isset($decoded['success']) || !$decoded['success']) {
            $this->log("ERROR: whatsapp API failure response=".$resp['body']);
            return false;
        }

        $this->log("WHATSAPP: sent uid={$uid} txid={$transaction->transaction_id}");
        return true;
    }

    protected function postJson($url, $data)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $body = curl_exec($ch);
        $err  = curl_errno($ch) ? curl_error($ch) : null;
        curl_close($ch);
        return ['body' => $body, 'error' => $err];
    }

    /* ---------- Mark Processed ---------- */

    protected function markAsProcessed($inbox, $uid, $success=true)
    {
        @imap_setflag_full($inbox, $uid, "\\Seen", ST_UID);
        if ($success && $this->processed_folder) {
            @imap_mail_move($inbox, $uid, $this->processed_folder, CP_UID);
            @imap_expunge($inbox);
        }
    }

    /* ---------- Logging Helpers ---------- */

    protected function log($msg)
    {
        // Simple size cap (5MB) rotate (optional)
        if (file_exists($this->log_file) && filesize($this->log_file) > 5 * 1024 * 1024) {
            @rename($this->log_file, $this->log_file.'.'.date('Ymd_His').'.bak');
        }
        @file_put_contents($this->log_file, "[".date('c')."] ".$msg."\n", FILE_APPEND);
    }

    protected function logDebugSample($uid, $subject, $normalized)
    {
        $sample = substr($normalized, 0, 400);
        $sample = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i','[EMAIL]',$sample);
        $sample = preg_replace('/\b[0-9]{8,16}\b/','[NUM]',$sample);
        $this->log("DEBUG uid={$uid} subj={$subject} sample={$sample}");
    }
}