<div class="page-header">
  <h1 class="page-title">
    <i class="fa fa-handshake-o" aria-hidden="true"></i></span>
    Affiliate
  </h1>
</div>
<?php if(get_option('affiliate_notice') != ""){ ?>
<div class="alert alert-danger" role="alert">
  <h4 class="alert-heading">Notice!</h4>
  <p><?= get_option('affiliate_notice', "") ?></p>
</div>
<?php }?>

<div class="row justify-content-center row-card statistics" id="result_ajaxSearch">

  <div class="col-sm-12">
    <div class="row">
    
      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-success-gradient text-white mr-3">
               <i class="fe fe-users"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?= $total_ref ?></h4>
                <small class="text-muted "><?= lang("total_referrals") ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-info-gradient text-white mr-3">
              <i class="fe fe-dollar-sign"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?= get_option('currency_symbol', "$") ?><?= (!empty($details->affiliate_bal_available+$details->affiliate_bal_transferred)) ? currency_format($details->affiliate_bal_available+$details->affiliate_bal_transferred, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0 ?></h4>
                <small class="text-muted "><?= lang("total_amount_earned") ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-warning-gradient text-white mr-3">
              <i class="fe fe-dollar-sign"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?= get_option('currency_symbol', "$") ?><?= (!empty($details->affiliate_bal_available)) ? currency_format($details->affiliate_bal_available, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0 ?></h4>
                <small class="text-muted "><?= lang("total_amount_available") ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-danger-gradient text-white mr-3">
              <i class="fe fe-dollar-sign"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?= get_option('currency_symbol', "$") ?><?= (!empty($details->affiliate_bal_transferred)) ? currency_format($details->affiliate_bal_transferred, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0 ?></h4>
                <small class="text-muted "><?= lang("total_amount_transferred") ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php
      if (get_role("user")) {
      ?>

  <div class="col-md-12 col-xl-12">
    <div class="card">
      <div class="card-header">
        Affiliate Details
      </div>
      <div class="card-body">
        <center><h3><?= get_option('currency_symbol', "$") ?><?= get_option('affiliate_bonus', "") ?></h3></center>
        <center><h5>Total Bonus on every Sign Up</h5></center>
        <br>
        <center>Balance available to transfer : <?= get_option('currency_symbol', "$") ?><?= (!empty($details->affiliate_bal_available)) ? currency_format($details->affiliate_bal_available, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0 ?></center>
        <center><form class="form actionForm" method="POST" action="<?=cn("affiliate/ajax_transfer_balance")?>" data-redirect="<?= cn($module) ?>">                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>"><button type="submit" class="btn btn-blue mx-auto">Transfer Balance</button></form></center>
        <br>
        <center>Your Referral Link: </center>
        <center><input style="text-align: center; margin: auto; width: 70%;" type="text" class="form-control" readonly value="<?=BASE?>?referral=<?=$details->affiliate_id?>" /></center>
        <br>
        <center>Your Referral Id: </center>
        <center><input style="text-align: center; margin: auto; width: 70%;" type="text" class="form-control" readonly value="<?=$details->affiliate_id?>" /></center>

      </div>
    </div>
  </div>

  <?php }?>

</div>