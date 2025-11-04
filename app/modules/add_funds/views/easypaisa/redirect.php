<?php
$status    = isset($status) ? $status : session('easypaisa_status');
$auto      = isset($auto) ? $auto : session('easypaisa_auto');
$amount    = isset($amount) ? $amount : session('easypaisa_amount');
$txid      = isset($txid) ? $txid : session('easypaisa_txid');
$converted = isset($converted) ? $converted : session('easypaisa_converted');
$error_msg = isset($error_msg) ? $error_msg : session('easypaisa_err');
$uid       = isset($uid) ? $uid : session('uid');
?>
<style>
.easypaisa-fadein{animation:fadeIn .5s;}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px);}to{opacity:1;transform:translateY(0);}}
</style>

<?php if ($status == 'success'): ?>
<div class="card easypaisa-fadein" style="margin:40px auto;max-width:520px;">
  <div class="card-body text-center">
    <i class="fa fa-check-circle" style="color:green;font-size:60px"></i>
    <h3 style="margin-top:15px;">Deposit Successful!</h3>
    <p style="margin-top:8px;">
      Amount: <strong><?=htmlspecialchars($amount)?></strong> PKR<br>
      Transaction ID: <strong><?=htmlspecialchars($txid)?></strong>
    </p>
    <div style="margin-top:20px;">
      <a href="<?=cn('add_funds')?>" class="btn btn-primary">Go to Add Funds</a>
      <a href="<?=cn('')?>" class="btn btn-secondary">Go to Dashboard</a>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($status == 'pending'): ?>
<div class="card easypaisa-fadein" style="margin:40px auto;max-width:520px;">
  <div class="card-body text-center">
    <i class="fa fa-clock-o" style="color:orange;font-size:60px"></i>
    <h3 style="margin-top:15px;">Deposit Under Review</h3>
    <p style="margin-top:8px;">
      Your payment has been received and is currently being reviewed.<br>
      Amount: <strong><?=htmlspecialchars($amount)?></strong> PKR<br>
      Transaction ID: <strong><?=htmlspecialchars($txid)?></strong>
    </p>
    <small style="color:#888;">Funds will be added to your account shortly once confirmation is complete.</small>
    <div style="margin-top:20px;">
      <a href="<?=cn('add_funds')?>" class="btn btn-primary">Add Another</a>
      <a href="<?=cn('')?>" class="btn btn-secondary">Dashboard</a>
    </div>
  </div>
</div>
<?php endif; ?>


<?php if ($status == 'failed'): ?>
<div class="card easypaisa-fadein" style="margin:40px auto;max-width:520px;">
  <div class="card-body text-center">
    <i class="fa fa-times-circle" style="color:red;font-size:60px"></i>
    <h3 style="margin-top:15px;">Deposit Failed</h3>
    <p style="margin-top:8px;"><?=htmlspecialchars($error_msg)?></p>
    <div style="margin-top:20px;">
      <a href="<?=cn('add_funds')?>" class="btn btn-primary">Try Again</a>
      <a href="<?=cn('')?>" class="btn btn-secondary">Dashboard</a>
    </div>
  </div>
</div>
<?php endif; ?>