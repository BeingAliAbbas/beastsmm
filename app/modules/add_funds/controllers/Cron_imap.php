<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Imap_cron extends CI_Controller
{
    private $requiredToken;
    private $minInterval = 0;
    private $lockFile;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('imap_cron', true);
        $this->requiredToken = $this->config->item('imap_cron_token');
        $this->minInterval   = $this->config->item('imap_cron_min_interval') ?? 0;
        $this->lockFile      = $this->config->item('imap_cron_lock_file') ?? APPPATH.'cache/imap_cron_last_run.lock';

        $this->load->library('ImapAutoVerifier');
    }

    public function run()
    {
        // Simple debug banner
        header('X-Debug-Controller: Imap_cron');

        $token = $this->input->get('token', true);

        if (!$token) {
            return $this->debugRespond('missing_token', 'Token not supplied');
        }
        if (!$this->requiredToken) {
            return $this->debugRespond('missing_config', 'Config token not loaded');
        }
        if (!hash_equals($this->requiredToken, $token)) {
            log_message('error', 'IMAP cron bad token');
            return $this->debugRespond('bad_token', 'Token mismatch');
        }

        if ($this->minInterval > 0 && $this->lockFile) {
            $now = time();
            if (file_exists($this->lockFile)) {
                $last = (int)trim(@file_get_contents($this->lockFile));
                if ($last && ($now - $last) < $this->minInterval) {
                    return $this->debugRespond('rate_limited', 'Try later', [
                        'retry_after_sec' => $this->minInterval - ($now - $last)
                    ]);
                }
            }
            @file_put_contents($this->lockFile, (string)$now);
        }

        $ok = $this->imapautoverifier->run();

        return $this->debugRespond($ok ? 'ok' : 'fail', 'Run completed');
    }

    private function debugRespond($status, $msg, $extra = [])
    {
        $payload = array_merge([
            'status' => $status,
            'message'=> $msg,
            'time'   => date('c'),
            'expected_token_prefix' => substr((string)$this->requiredToken,0,8),
        ], $extra);

        $this->output
             ->set_content_type('application/json')
             ->set_output(json_encode($payload));
    }
}