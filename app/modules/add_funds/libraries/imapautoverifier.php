<?php
/**
 * IMAP Auto-Verification Library
 * - Exact match on transaction_id + amount (original amount in 'note')
 * - Reads recent IMAP emails, parses payment SMS forwards.
 * - Lowercase filename; class name kept StudlyCase for CI reliability.
 */
class Imapautoverifier
{
    protected $imap_host = '{imap.gmail.com:993/imap/ssl}INBOX';
    protected $imap_user = 'beastsmm98@gmail.com';
    protected $imap_pass = 'miii orwi ibaq roqc'; // APP PASSWORD (move to secure config in production)

    protected $db;
    protected $log_file;
    protected $processed_folder = 'Processed';
    protected $time_window_minutes = 180;
    protected $max_emails = 15;
    protected $transaction_table = 'general_transaction_logs';

    public function __construct($config = [])
    {
        if (function_exists('get_instance')) {
            $CI = get_instance();
            if ($CI && isset($CI->db)) {
                $this->db = $CI->db;
            }
        }
        if (defined('TRANSACTION_LOGS')) {
            $this->transaction_table = TRANSACTION_LOGS;
        }

        $this->log_file            = $config['log_file']            ?? APPPATH . 'logs/imap_auto_verify.log';
        $this->processed_folder    = $config['processed_folder']    ?? $this->processed_folder;
        $this->time_window_minutes = $config['time_window_minutes'] ?? $this->time_window_minutes;
        $this->max_emails          = $config['max_emails']          ?? $this->max_emails;
    }

    public function run()
    {
        $this->log("---- cron start " . date('c') . " ----");

        if (!$this->db) {
            $this->log("ERROR: db instance missing");
            return false;
        }

        $inbox = @imap_open($this->imap_host, $this->imap_user, $this->imap_pass);
        if (!$inbox) {
            $this->log("ERROR: IMAP connect failed: " . imap_last_error());
            return false;
        }

        $sinceDate = date('d-M-Y', strtotime("-{$this->time_window_minutes} minutes"));
        $criteria  = 'SINCE "' . $sinceDate . '"';
        $uids      = imap_search($inbox, $criteria, SE_UID);

        if (!$uids) {
            $this->log("No emails in window {$this->time_window_minutes}m");
            imap_close($inbox);
            $this->log("---- cron end " . date('c') . " ----");
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
                $this->log("WARN: could not resolve msgno for uid {$uid}");
                continue;
            }

            $header     = @imap_headerinfo($inbox, $msgno);
            $subjectRaw = $header->subject ?? '';
            $subject    = imap_utf8($subjectRaw);
            $body       = $this->fetchMessageBody($inbox, $uid);
            $normalized = $this->normalizeText($subject . "\n" . $body);
            $this->logDebugSample($uid, $subject, $normalized);

            $parsed = $this->extractPaymentDetails($subject, $body);
            if (!$parsed) {
                $this->log("Parse fail uid={$uid}");
                $this->markAsProcessed($inbox, $uid, false);
                continue;
            }

            $this->log("Parsed => " . json_encode($parsed));

            $transaction = $this->findPendingTransactionExact($parsed);
            if ($transaction) {
                $this->updateTransactionStatus($transaction, $parsed);
                $this->notifyUser($transaction, $parsed);
                $this->markAsProcessed($inbox, $uid, true);
                $this->log("SUCCESS: txid={$parsed['transaction_id']} marked paid");
                $matched++;
            } else {
                $this->log("NO MATCH txid={$parsed['transaction_id']} amt={$parsed['amount']}");
                $this->markAsProcessed($inbox, $uid, false);
            }
        }

        imap_close($inbox);
        $this->log("SUMMARY processed={$processed} matched={$matched}");
        $this->log("---- cron end " . date('c') . " ----");
        return true;
    }

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
        if (!empty($partsCollected['text/plain'])) return implode("\n", $partsCollected['text/plain']);
        if (!empty($partsCollected['text/html']))  return strip_tags(implode("\n", $partsCollected['text/html']));
        return '';
    }

    protected function collectParts($inbox, $uid, $structure, $prefix, &$store)
    {
        if (!empty($structure->parts)) {
            $i = 1;
            foreach ($structure->parts as $part) {
                $partNum = $prefix === '' ? (string)$i : $prefix . '.' . $i;
                $this->collectParts($inbox, $uid, $part, $partNum, $store);
                $i++;
            }
        } else {
            $type = $this->mimeType($structure);
            if (in_array($type, ['text/plain', 'text/html'])) {
                $raw     = @imap_fetchbody($inbox, $uid, $prefix ?: '1', FT_UID);
                $decoded = $this->decodeBody($raw, $structure->encoding ?? 0);
                $store[$type][] = $decoded;
            }
        }
    }

    protected function mimeType($structure)
    {
        $primary = [
            0=>'text',1=>'multipart',2=>'message',3=>'application',
            4=>'audio',5=>'image',6=>'video',7=>'other'
        ];
        $p = $primary[$structure->type] ?? 'other';
        $s = isset($structure->subtype) ? strtolower($structure->subtype) : 'plain';
        return $p . '/' . $s;
    }

    protected function decodeBody($raw, $encoding)
    {
        if ($raw === false || $raw === null) return '';
        switch ($encoding) {
            case 3: return base64_decode($raw);
            case 4: return quoted_printable_decode($raw);
            default: return $raw;
        }
    }

    protected function normalizeText($text)
    {
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    protected function extractPaymentDetails($subject, $body)
    {
        $text = $this->normalizeText($subject . "\n" . $body);
        $details = [];

        if (preg_match('/(?:Rs\.?|PKR|Amount)\s*([0-9]{1,7}(?:\.[0-9]{1,2})?)/i', $text, $m)) {
            $details['amount'] = (float)$m[1];
        } elseif (preg_match('/\bRs\s*([0-9]{1,7}\.[0-9]{1,2})\b/i', $text, $m)) {
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

    protected function findPendingTransactionExact($parsed)
    {
        if (!$this->db) return false;

        $txid      = $parsed['transaction_id'];
        $amountPKR = (float)$parsed['amount'];

        $q = $this->db->get_where($this->transaction_table, [
            'transaction_id' => $txid,
            'status'         => 0
        ]);
        if ($q->num_rows() === 0) {
            $this->log("DEBUG: no pending row txid={$txid}");
            return false;
        }

        $row         = $q->row();
        $noteAmt     = isset($row->note) ? (float)$row->note : null;
        $baseAmt     = isset($row->amount) ? (float)$row->amount : null;
        $feeAmt      = isset($row->txn_fee) ? (float)$row->txn_fee : 0.0;
        $basePlusFee = ($baseAmt !== null) ? ($baseAmt + $feeAmt) : null;

        $match=false; $reason='';

        if ($noteAmt !== null && $this->amountEqual($noteAmt, $amountPKR)) {
            $match=true; $reason='note';
        } elseif ($basePlusFee !== null && $this->amountEqual($basePlusFee, $amountPKR)) {
            $match=true; $reason='amount+fee';
        } elseif ($baseAmt !== null && $this->amountEqual($baseAmt, $amountPKR)) {
            $match=true; $reason='amount';
        }

        if (!$match) {
            $this->log("DEBUG: amount mismatch txid={$txid} parsed={$amountPKR} note={$noteAmt} amount={$baseAmt} amount+fee={$basePlusFee}");
            return false;
        }

        $this->log("DEBUG: match reason={$reason} txid={$txid}");
        return $row;
    }

    protected function amountEqual($a, $b)
    {
        return abs($a - $b) < 0.00001;
    }

    protected function updateTransactionStatus($transaction, $parsed)
    {
        $this->db->where('id', $transaction->id)
                 ->where('status', 0)
                 ->update($this->transaction_table, [
                     'status'        => 1,
                     'verified_at'   => date('Y-m-d H:i:s'),
                     'verify_source' => 'imap-auto'
                 ]);

        // Optional balance credit (uncomment & adjust)
        /*
        if ($this->db->affected_rows() > 0 && isset($transaction->uid)) {
            $this->db->set('balance', 'balance + ' . $this->db->escape($transaction->note), false)
                     ->where('id', $transaction->uid)
                     ->update('users');
        }
        */
    }

    protected function notifyUser($transaction, $parsed)
    {
        // Implement notifications if desired.
    }

    protected function markAsProcessed($inbox, $uid, $success=true)
    {
        @imap_setflag_full($inbox, $uid, "\\Seen", ST_UID);
        if ($success && $this->processed_folder) {
            @imap_mail_move($inbox, $uid, $this->processed_folder, CP_UID);
            @imap_expunge($inbox);
        }
    }

    protected function log($msg)
    {
        @file_put_contents($this->log_file, "[".date('c')."] ".$msg."\n", FILE_APPEND);
    }

    protected function logDebugSample($uid, $subject, $normalized)
    {
        $sample = substr($normalized, 0, 400);
        $sample = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i', '[EMAIL]', $sample);
        $sample = preg_replace('/\b[0-9]{8,16}\b/', '[NUM]', $sample);
        $this->log("DEBUG uid={$uid} subj={$subject} sample={$sample}");
    }
}