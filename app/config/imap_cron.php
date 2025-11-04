<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['imap_cron_token']        = 'a7f4c1f0bb2d4f6c9c0e5e62c8f1b347d6e3a9b1f2847ad0c6e1f59b7d3c42aa';
$config['imap_cron_min_interval'] = 30;
$config['imap_cron_lock_file']    = APPPATH . 'cache/imap_cron_last_run.lock';

$config['imap_auto_credit_enable']   = true;
$config['imap_auto_whatsapp_enable'] = true;

$config['imap_auto_whatsapp_template_2'] =
"✅ Payment Confirmed\n\n"
."Amount: *{amount} PKR*\n"
."Txn ID: `{transaction_id}`\n"
."Method: {payment_method}\n\n"
."Order now: beastsmm.pk/order/add";;