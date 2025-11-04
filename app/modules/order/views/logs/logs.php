<?php if (get_option('orders_text','') != '') { ?>
<div class="col-sm-12">
  <div class="row">
    <div class="card">
      <div class="card-body">
        <?=get_option('orders_text','')?>
      </div>
    </div>
  </div>
</div>
<?php }?>
<div class="container-fluid">
  <div class="search-box m-r-30 d-none d-lg-block">
      <?php
      if ( allowed_search_bar(segment(1)) || allowed_search_bar(segment(2)) ) {
        echo Modules::run("blocks/search_box");
      }
      ?>
  </div>
  <br>
  <div class="page-options d-flex">
      <ul class="list-inline mb-0 order_btn_group">
          <li class="list-inline-item">
              <a class="nav-link btn <?=($order_status == 'all') ? 'btn-info active1' : ''?>" href="<?=cn($module."/log/all")?>"><?=lang('All')?></a>
          </li>
          <?php 
          $status_array = order_status_array();
          if (!empty($status_array)) {
              foreach ($status_array as $row_status) {
                  if ((get_role('user')) && in_array($row_status, ['error'])) {
                      continue;
                  }
          ?>
          <li class="list-inline-item">
              <a class="nav-link btn <?=($order_status == $row_status) ? 'btn-info active1' : ''?>" href="<?=cn($module."/log/".$row_status)?>">
                  <?=order_status_title($row_status)?>
                  <?php
                      if (in_array($row_status, ['error']) && isset($number_error_orders)) {
                          echo '<span class="badge badge-danger badge-error-orders">'.$number_error_orders.'</span>';
                      }
                  ?>
              </a>
          </li>
          <?php }}?>
      </ul>
  </div>

<?php if ($order_status == 'all' && get_role("admin")): ?>
  <!-- Total Profit Card -->
  <div id="total-profit-card" class="card summary-card">
    <h4><?=lang("Total Profit")?> <small style="font-size:1rem; color:#bfc9d1;">(100 orders)</small></h4>
    <p id="total-profit-value" class="summary-value" style="color: #27ae60; font-size:2rem; font-weight:bold; margin:0;"></p>
  </div>

  <!-- Total Sell Card -->
  <div id="total-sell-card" class="card summary-card">
    <h4><?=lang("Total Sell")?> <small style="font-size:1rem; color:#bfc9d1;">(100 orders)</small></h4>
    <p id="total-sell-value" class="summary-value" style="color: #f39c12; font-size:2rem; font-weight:bold; margin:0;"></p>
  </div>

  <!-- Profit Today Card -->
  <div id="profit-today-card" class="card summary-card">
    <h4><?=lang("Profit Today")?> <small style="font-size:1rem; color:#bfc9d1;">(100 orders)</small></h4>
    <p id="profit-today-value" class="summary-value" style="color: #2980b9; font-size:2rem; font-weight:bold; margin:0;"></p>
  </div>
<?php endif; ?>

  <br><br>
  <?php if ($order_status == 'error') { ?>
    <div class="dropdown d-inline-block">
      <button class="btn btn-secondary dropdown-toggle" type="button" id="bulkActionDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        Bulk Actions
      </button>
      <div class="dropdown-menu" aria-labelledby="bulkActionDropdown">
        <a class="dropdown-item" href="<?=cn("$module/change_status/resend_order/bulk")?>">Resend All</a>
        <a class="dropdown-item" href="<?=cn("$module/change_status/cancel_order/bulk")?>">Cancel All</a>
      </div>
    </div>
  <?php } ?>

  <div class="row" id="result_ajaxSearch">
    <?php if(!empty($order_logs)){ ?>
      <div class="col-md-12">
        <div>
          <div class="card-header bg-gradient text-white">
              <h3 class="card-title"><?=lang("Your Orders")?></h3>
              <div class="card-options">
                  <?php if (get_role("admin")) { ?>
                  <div class="dropdown">
                      <button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" style="background-color: #06324e; color: #fff; border: none;">
                          <i class="fa fa-clone mr-2"></i> Copy Options
                      </button>
                      <div class="dropdown-menu" style="background-color: #051d2f!important; border: 1px solid #04a9f4;">
                          <a class="dropdown-item" href="#" onclick="copyAllApiOrderIds()" style="background-color: #051d2f!important; color: #fff !important;">
                              <i class="fa fa-copy mr-2"></i> Copy All API Order IDs
                          </a>
                      </div>
                  </div>
                  <?php } ?>
              </div>
          </div>
          <div class="table-responsive">
            <table class="table table-hover table-bordered table-vcenter card-table">
              <thead style="color: #fff;">
                <tr>
                  <?php if (!empty($columns)) {
                    foreach ($columns as $key => $row) {
                  ?>
                  <th><?=$row?></th>
                  <?php }}?>
                </tr>
              </thead>
              <tbody>
                <?php
                $currency_symbol = get_option("currency_symbol","");
                $decimal_places = get_option('currency_decimal', 2);
                $decimalpoint = get_option('currency_decimal_separator', 'dot') == 'comma' ? ',' : '.';
                $separator = get_option('currency_thousand_separator', 'space') == 'space' ? ' ' : (get_option('currency_thousand_separator', 'comma') == 'comma' ? ',' : '.');
                $usd_to_pkr_rate = 280; // Example conversion rate

                $total_profit = 0;
                $total_sell = 0;
                $profit_today = 0;
                $today = date('Y-m-d'); // Get today's date

                foreach ($order_logs as $key => $row) {
                  $profit = 0;
                  $provider_charge_in_pkr = isset($row->formal_charge) ? $row->formal_charge * $usd_to_pkr_rate : 0;

                  if (get_role("admin") && is_numeric($row->charge) && is_numeric($row->formal_charge) && $row->charge > 0 && $row->formal_charge > 0) {
                      $profit = $row->charge - $provider_charge_in_pkr;
                      $total_profit += $profit;
                  }
                  if (is_numeric($row->charge) && $row->charge > 0) {
                      $total_sell += $row->charge;
                  }
                  // Today's profit
                  if (get_role("admin") && !empty($row->created) && substr($row->created,0,10) == $today && is_numeric($row->charge) && is_numeric($row->formal_charge) && $row->charge > 0 && $row->formal_charge > 0) {
                      $profit_today += $profit;
                  }
                ?>
                <!-- Table Row -->
                <tr style="color: #fff;" class="tr_<?=$row->ids?>">
                  <!-- Order ID with copy button -->
                  <td style="color: #fff;">
                    <span id="orderId_<?=$row->id?>"><?=$row->id?></span>
                    <button onclick="copyToClipboard('orderId_<?=$row->id?>')" style="background:none; border:none; cursor:pointer;">
                      <i class="fa fa-copy" style="color: #2ecc71; margin-left: 8px;"></i>
                    </button>
                  </td>
                  <?php if (get_role("admin") || get_role("supporter")) { ?>
                  <td class="text-center" style="color: #fff;">
                    <span id="apiOrderId_<?=$row->id?>">
                        <?= ($row->api_order_id == 0 || $row->api_order_id == -1) ? "" : $row->api_order_id ?>
                    </span>
                    <?php if ($row->api_order_id != 0 && $row->api_order_id != -1): ?>
                      <button onclick="copyToClipboard('apiOrderId_<?=$row->id?>')" style="background:none; border:none; cursor:pointer;">
                        <i class="fa fa-copy" style="color: #2ecc71; margin-left: 8px;"></i>
                      </button>
                    <?php endif; ?>
                  </td>
                  <td style="color: #fff;"><?=$row->user_email?></td>
                  <?php } ?>
                  <!-- Service Info -->
                  <td>
                    <div class="title" style="color: #fff; font-size: 13px; font-weight: 400;">
                      <h6><?=$row->service_id." - ".$row->service_name?></h6>
                    </div>
                    <div style="margin-left:5px !important">
                      <small>
                        <ul style="color: #fff; margin:0px">
                          <?php if (get_role("admin")) { ?>
                          <li style="color: #fff;">
                            <?php echo lang("Type")?>: 
                            <?php
                              if (!empty($row->api_service_id)) {
                                if ($row->type == 'api') {
                                  echo $row->api_name." (ID".$row->api_service_id. ")" . ' <span class="badge badge-default">API</span>';
                                } else {
                                  echo $row->api_name." (ID".$row->api_service_id. ")";
                                }
                              } else {
                                echo lang("Manual");
                              }
                            ?>
                          </li>
                          <?php } ?>
                          <li style="color: #fff;"><?=lang("Link")?>:
                            <?php
                              if (filter_var($row->link, FILTER_VALIDATE_URL)) {
                                echo '<a class="text-blue" href="'.$row->link.'" target="_blank">'.truncate_string($row->link, 60).'</a>'; 
                              } else {
                                echo truncate_string($row->link, 60);
                              }
                            ?>
                          </li> 
                          <li><?=lang("Quantity")?>: <?=$row->quantity?></li>
                          <li><?=lang("Charge")?>: <?= $currency_symbol . currency_format($row->charge, $decimal_places, $decimalpoint, $separator) ?></li>
                          <?php if (get_role("admin")): ?>
                          <li><?=lang("Provider Charge")?>: 
                            <small style="color: #3498db; font-size: 12px;">
                              <?= (isset($provider_charge_in_pkr) && $provider_charge_in_pkr > 0) 
                                  ? '$' . $row->formal_charge . ' (' . $currency_symbol . currency_format($provider_charge_in_pkr, $decimal_places, $decimalpoint, $separator) . ')' 
                                  : lang("No charge available"); ?>
                            </small>
                          </li>
                          <?php endif; ?>
                          <li><?=lang("Start_counter")?>: <?=(!empty($row->start_counter)) ? $row->start_counter : lang("N/A")?></li>
                          <li><?=lang("Remains")?>: <?=(!empty($row->remains)) ? $row->remains : lang("N/A")?></li>
                          <?php
                            $mention_list = get_list_custom_mention($row);
                            if ($mention_list && $mention_list->exists_list) {
                          ?>
                          <li>
                            <a href="<?=cn($module.'/ajax_show_list_custom_mention/'.$row->ids)?>" class="btn btn-gray btn-sm ajaxModal btn-show-custom-mention"><?=$mention_list->title?></a>
                          </li>
                          <?php } ?>
                        </ul>
                      </small>
                    </div>
                  </td>
                  <?php if (get_role("admin")): ?>
                  <td><?= $currency_symbol . currency_format($profit, $decimal_places, $decimalpoint, $separator); ?></td>
                  <?php endif; ?>
                  <td style="color: #fff;"><?=convert_timezone($row->created, "user")?></td>
                  <td>
                    <?php
                    $order_status = $row->status;
                    if (!get_role('admin') && in_array($order_status, ['fail', 'error'])) {
                        $order_status = 'processing';
                    }
                    $btn_background = ($order_status == "pending" || $order_status == "processing") ? "btn-info"
                                  : ($order_status == "inprogress" ? "btn-orange"
                                  : ($order_status == "completed" ? "btn-blue" : "btn-danger"));
                    ?>
                    <span class="btn round btn-sm <?=$btn_background?>"><?=order_status_title($order_status)?></span>
                    <?php if ($order_status == "completed"): ?>
                      <?php
                      $user_whatsapp = $this->db->select('whatsapp_number')->from('general_users')->where('id', $row->uid)->get()->row();
                      $notification_status = $this->db->select('is_notified')->from('order_notifications')->where('order_id', $row->id)->get()->row();
                      $is_notified = isset($notification_status->is_notified) ? $notification_status->is_notified : 0;
                      if ($user_whatsapp && $user_whatsapp->whatsapp_number):
                        $whatsapp_number = ltrim($user_whatsapp->whatsapp_number, '+');
                      ?>
                        <?php if (get_role('admin')): ?>
                          <?php if (!$is_notified): ?>
                            <a href="<?=base_url("order/send_whatsapp_notification?whatsapp_number={$whatsapp_number}&order_id={$row->id}&service_name=" . urlencode($row->service_name))?>" 
                            class="btn btn-sm btn-primary mt-2" id="notify-btn-<?=$row->id?>">
                              <i class="fab fa-whatsapp"></i> Notify User
                            </a>
                          <?php else: ?>
                            <span class="btn btn-sm btn-success mt-2">Notification Sent</span>
                          <?php endif; ?>
                        <?php endif; ?>
                      <?php endif; ?>
                    <?php endif; ?>
                  </td>
                  <?php if (get_role("admin") || get_role("supporter")) { ?>
                  <td class="text-red"><?=!empty($row->note)? $row->note : ""?></td>
                  <td class="text-center">
                    <a href="<?=cn("$module/log_update/".$row->ids)?>" class="ajaxModal">
                      <i class="btn btn-info fe fe-edit"> <?=lang('Edit')?></i>
                    </a>
                    <br><br>
                    <?php
                    if (get_role('admin') && $row->status == 'error') {
                    ?>
                      <!-- Single order resend as anchor (no JS, follows original) -->
                      <a href="<?=cn("$module/change_status/resend_order/".$row->id)?>" class="">
                        <i class="btn btn-success fe fe-send"> Resend</i>
                      </a>
                      <br><br>
                    <?php } ?>
                  </td>
                  <?php } ?>
                </tr>
                <?php } ?>
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
    }?>
  </div>
</div>

<!-- Scripts -->
<script>
$(document).ready(function() {
  $('.sidebar-toggle').click(function() {
    $('.sidebar').toggleClass('active');
  });

  // Display profit cards
  let totalProfit = <?=json_encode(round($total_profit, 1))?>;
  let totalSell = <?=json_encode(round($total_sell, 1))?>;
  let profitToday = <?=json_encode(round($profit_today, 1))?>;

  $('#total-profit-value').text(totalProfit + ' PKR');
  $('#total-sell-value').text(totalSell + ' PKR');
  $('#profit-today-value').text(profitToday + ' PKR');

  // No JS required for single order resend (uses anchor)
});

// Copy single order id to clipboard
function copyToClipboard(elementId) {
  var textToCopy = document.getElementById(elementId).innerText;
  var tempInput = document.createElement("textarea");
  tempInput.value = textToCopy;
  document.body.appendChild(tempInput);
  tempInput.select();
  tempInput.setSelectionRange(0, 99999);
  document.execCommand("copy");
  document.body.removeChild(tempInput);
  alert("Order ID copied: " + textToCopy);
}

// Copy all API order IDs to clipboard
function copyAllApiOrderIds() {
  let apiOrderIds = [];
  document.querySelectorAll('[id^="apiOrderId_"]').forEach(function(element) {
    if (element.textContent.trim() !== "") {
      apiOrderIds.push(element.textContent.trim());
    }
  });
  if (apiOrderIds.length > 0) {
    let formattedText = 'order ids\n' + apiOrderIds.join(',\n');
    copyTextToClipboard(formattedText);
    alert("API Order IDs copied to clipboard!");
  } else {
    alert("No API Order IDs found to copy.");
  }
}
function copyTextToClipboard(text) {
  const textarea = document.createElement('textarea');
  textarea.value = text;
  document.body.appendChild(textarea);
  textarea.select();
  document.execCommand('copy');
  document.body.removeChild(textarea);
}
</script>

<style>
.summary-card {
  margin-top: 20px;
  padding: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  text-align: center;
}
.summary-value {
  font-size: 18px;
  font-weight: bold;
}
</style>