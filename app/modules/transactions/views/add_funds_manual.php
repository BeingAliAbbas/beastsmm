<div id="main-modal-content">
  <div class="modal-right">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <?php
          $url = cn($module."/ajax_add_funds_manual");
        ?>
        <form class="form actionForm" action="<?=$url?>" data-redirect="<?=cn($module)?>" method="POST">
          <div class="modal-header bg-pantone">
            <h4 class="modal-title">
              <i class="fe fe-dollar-sign"></i> <?=lang("Add_Funds")?> (Manual)
            </h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
          </div>

          <div class="modal-body">
            <div class="form-body">
              <div class="row justify-content-md-center">
                
                <div class="col-md-12">
                  <div class="form-group">
                    <label><?=lang('email')?> (User Email)</label>
                    <input type="text" class="form-control square" name="email" placeholder="user@example.com" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label><?=lang('Payment_method')?></label>
                    <select name="payment_method" class="form-control square" required>
                      <option value="manual">Bank/Other (Manual)</option>
                      <option value="bonus">Bonus</option>
                      <option value="other">Other</option>
                      <?php if (!empty($payments_defaut)) {
                        foreach ($payments_defaut as $p) { ?>
                        <option value="<?=$p->type?>"><?=$p->name?></option>
                      <?php }} ?>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label><?=lang("Funds")?></label>
                    <input type="text" class="form-control square" name="funds" placeholder="Amount" required>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label><?=lang("Transaction_ID")?> (Optional)</label>
                    <input type="text" class="form-control square" name="transaction_id" placeholder="Leave blank if none">
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label><?=lang("Transaction fee")?> (Optional)</label>
                    <input type="text" class="form-control square" name="txt_fee" placeholder="0">
                  </div>
                </div>

                <div class="col-md-12">
                  <div class="form-group mb-0">
                    <label><?=lang('Note')?> (Optional)</label>
                    <textarea name="txt_note" rows="2" class="form-control square" placeholder="Internal note"></textarea>
                  </div>
                </div>

              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button class="btn btn-primary btn-min-width"><?=lang("Add_Funds")?></button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=lang("Close")?></button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>