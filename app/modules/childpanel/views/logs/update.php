<div id="main-modal-content">
  <div class="modal-right">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <?php
        $ids = (!empty($order->ids)) ? $order->ids : '';
        ?>
        <form class="form actionForm" action="<?= cn($module . "/ajax_logs_update/$ids") ?>" data-redirect="<?= cn($module . "/log") ?>" method="POST">
          <div class="modal-header bg-pantone">
            <h4 class="modal-title"><i class="fa fa-edit"></i> <?= lang("Edit_Order") ?></h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </button>
          </div>
          <div class="modal-body">
            <div class="form-body">
              <div class="row justify-content-md-center">

              <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?= lang("User") ?></label>
                    <input type="text" class="form-control square" disabled value="<?= (!empty($order->uid)) ? get_field(USERS, ["id" => $order->uid], 'email') : '' ?>">
                  </div>
                </div>

                <div class="col-md-6 col-sm-6 col-xs-6">
                  <div class="form-group">
                    <label><?= lang("order_id") ?></label>
                    <input type="text" class="form-control square" disabled value="<?= (!empty($order->id)) ? $order->id : '' ?>">
                  </div>
                </div>

                <div class="col-md-6 col-sm-6 col-xs-6">
                  <div class="form-group">
                    <label><?= lang("Amount") ?></label>
                    <input type="text" class="form-control square" name="charge" disabled value="<?= get_option('currency_symbol') ?><?= (!empty($order->charge)) ? currency_format($order->charge, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0 ?>">
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?= lang("Domain") ?></label>
                    <input type="text" class="form-control square" disabled name="domain" value="<?= $order->domain ?>">
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?= lang("Admin_email") ?></label>
                    <input type="text" class="form-control square" disabled name="email" value="<?= $order->email ?>">
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?= lang("Admin_password") ?></label>
                    <input type="text" class="form-control square" disabled name="password" value="<?= $order->password ?>">
                  </div>
                </div>

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label><?= lang("Status") ?></label>
                    <select name="status" class="form-control square">
                      <?php
                      $order_status_array = childpanel_status_array();
                      if (!empty($order_status_array)) {
                        foreach ($order_status_array as $status) {
                      ?>
                          <option value="<?= $status ?>" <?= (!empty($order->status) && $status == $order->status) ? 'selected' : '' ?>><?= childpanel_status_title($status) ?></option>
                      <?php }
                      } ?>
                    </select>
                  </div>
                </div>

              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1"><?= lang("Submit") ?></button>
            <button type="button" class="btn round btn-default btn-min-width mr-1 mb-1" data-dismiss="modal"><?= lang("Cancel") ?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>