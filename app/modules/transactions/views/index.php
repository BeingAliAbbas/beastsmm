<?php if (get_option('transactions_text','') != '') { ?>
<div class="col-sm-12 col-sm-12">
  <div class="row">
    <div class="card">
      <div class="card-body">
        <?=get_option('transactions_text','')?>
      </div>
    </div>
  </div>
</div>
<?php }?>

<style>
/* ---------- Custom Styling Add Funds Button + Card Header (matches dark theme) ---------- */
.transaction-card {
  background: #06141b;
  border: 1px solid #0d3242;
  border-radius: 14px;
  box-shadow: 0 8px 18px -8px rgba(0,0,0,.6), 0 2px 6px -2px rgba(0,0,0,.5);
  overflow: hidden;
}
.transaction-card-header{
  display:flex;
  justify-content:space-between;
  align-items:center;
  padding:18px 22px 14px;
  background:linear-gradient(135deg,#042636,#052d40 55%,#041d28);
  border-bottom:1px solid #0e3b4e;
}
.transaction-card-title{
  margin:0;
  font-size:20px;
  font-weight:600;
  letter-spacing:.5px;
  color:#e9f6ff;
  text-shadow:0 1px 2px rgba(0,0,0,.65);
}
.btn-add-funds{
  --c1:#00b4ff;
  --c2:#16d2ff;
  background:linear-gradient(90deg,var(--c1),var(--c2));
  color:#fff !important;
  font-weight:600;
  font-size:14px;
  padding:9px 20px;
  border-radius:9px;
  border:1px solid #22c3ff;
  display:inline-flex;
  align-items:center;
  gap:8px;
  text-decoration:none;
  position:relative;
  overflow:hidden;
  line-height:1.1;
  box-shadow:0 4px 12px -3px rgba(0,180,255,.45),0 2px 4px -2px rgba(0,0,0,.55);
  transition:background .35s,transform .25s,box-shadow .35s;
}
.btn-add-funds:before{
  content:"";
  position:absolute;
  top:0;left:-40%;
  width:40%;height:100%;
  background:linear-gradient(100deg,rgba(255,255,255,.28),rgba(255,255,255,0));
  transform:skewX(-20deg);
  transition: left .6s;
}
.btn-add-funds:hover{
  transform:translateY(-3px);
  box-shadow:0 10px 26px -10px rgba(0,180,255,.6),0 6px 12px -6px rgba(0,0,0,.6);
  background:linear-gradient(90deg,#19c3ff,#47ddff);
  text-decoration:none;
}
.btn-add-funds:hover:before{
  left:110%;
}
.btn-add-funds i{
  font-size:16px;
  display:inline-block;
}
@media (max-width:680px){
  .transaction-card-header{
    flex-direction:column;
    align-items:flex-start;
    gap:10px;
  }
  .btn-add-funds{
    width:100%;
    justify-content:center;
  }
}
</style>

<div class="page-header d-md-none">
  <h1 class="page-title">
    <i class="fe fe-calendar" aria-hidden="true"> </i> 
    <?=lang("Transaction_logs")?>
  </h1>
</div>
<div class="row" id="result_ajaxSearch">
  <?php if (!empty($transactions)) { ?>

<div class="col-md-12 col-xl-12">
  <div class="transaction-card">
    <div class="transaction-card-header">
      <h3 class="transaction-card-title"><?= lang('Lists') ?></h3>

      <?php if (get_role('admin') || get_role('supporter')): ?>
        <a href="<?=cn($module.'/add_funds_manual')?>" class="ajaxModal btn-add-funds">
          <i class="fe fe-plus"></i>
          <span><?=lang('Add_Funds')?></span>
        </a>
      <?php endif; ?>
    </div>

    <div class="table-responsive">
      <table class="table table-hover table-bordered table-outline table-vcenter card-table">
        <thead>
          <tr>
            <th class="text-center w-1"><?=lang('No_')?></th>
            <?php if (!empty($columns)) {
              foreach ($columns as $key => $row) { ?>
                <th><?=$row?></th>
            <?php }} ?>
            <?php if (get_role("admin")) { ?>
              <th class="text-center"><?=lang('Action')?></th>
            <?php } ?>
          </tr>
        </thead>
        <tbody>
          <?php
            if (!empty($transactions)) {
              $i = 0;
              $currency_symbol = get_option("currency_symbol", '$');
              foreach ($transactions as $key => $row) {
                $i++;
          ?>
          <tr class="tr_<?=$row->ids?>">
            <td><?=$i?></td>
            <?php if (get_role("admin")) { ?>
            <td>
              <div class="title"><?=get_field('general_users', ["id" => $row->uid], "email")?></div>
              <?php if ($row->payer_email) { ?>
                <small class="text-muted">Payer Email: <?=$row->payer_email?></small>
              <?php } ?>
            </td>
            <td>
              <?php
                switch ($row->transaction_id) {
                  case 'empty':
                    if ($row->type == 'manual') {
                      echo lang($row->transaction_id);
                    } else {
                      echo lang($row->transaction_id) . " " . lang("transaction_id_was_sent_to_your_email");
                    }
                    break;
                  default:
                    echo $row->transaction_id;
                    break;
                }
              ?>
            </td>
            <?php } ?>
            <td class="">
              <?php if (in_array(strtolower($row->type), ["bonus","manual","other"])) {
                echo ucfirst($row->type);
              } else { ?>
                <img class="payment" src="<?=BASE?>/assets/images/payments/<?=strtolower($row->type); ?>.png" alt="<?=$row->type?> icon">
              <?php } ?>
            </td>
            <td><?=$currency_symbol.$row->amount?></td>
            <td><?=$row->txn_fee?></td>
            <?php if (get_role("admin")) { ?>
              <td><?=$row->note;?></td>
            <?php } ?>
            <td><?=convert_timezone($row->created, 'user')?></td>
            <td>
              <?php
                switch ($row->status) {
                  case 1:
                    echo '<span class="badge badge-default">'.lang('Paid').'</span>';
                    break;
                  case 0:
                    echo '<span class="badge badge-warning">'.lang("waiting_for_buyer_funds").'</span>';
                    break;
                  case -1:
                    echo '<span class="badge badge-danger">'.lang('cancelled_timed_out').'</span>';
                    break;
                }
              ?>
            </td>
            <?php if (get_role("admin")) { ?>
            <td class="text-center">
              <a href="<?=cn("$module/update/".$row->ids)?>" class="ajaxModal">
                <i class="btn btn-info fe fe-edit"> <?=lang('Edit')?></i>
              </a>
            </td>
            <?php } ?>
          </tr>
          <?php }} ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="col-md-12">
  <div class="float-right">
    <?=$links?>
  </div>
</div>

<?php } else {
  echo Modules::run("blocks/empty_data");
} ?>
</div>