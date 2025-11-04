<div class="card content">
      <div class="card-header" style="border: 0.1px solid #1B78FC; border-radius: 3.5px 3.5px 0px 0px; background: #1B78FC;">
        <h3 class="card-title"><i class="fe fe-archive"></i> <?= lang("childpanel_setting") ?></h3>
    </div>
         <div class="card-body">
        <form class="actionForm" action="<?= cn("$module/ajax_general_settings") ?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <div class="form-group">
                        <div class="form-label"><?= lang("Childpanel_status") ?></div>
                        <label class="custom-switch">
                            <input type="hidden" name="is_childpanel_status" value="0">
                            <input type="checkbox" name="is_childpanel_status" class="custom-switch-input" <?= (get_option("is_childpanel_status", 0) == 1) ? "checked" : "" ?> value="1">
                            <span class="custom-switch-indicator"></span>
                            <span class="custom-switch-description"><?= lang("Active") ?></span>
                        </label>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?= lang("childpanel_price") ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-prepend">
                                <span class="input-group-text"><?= get_option('currency_symbol') ?></span>
                            </span>
                            <input type="number" class="form-control" name="childpanel_price" value="<?= get_option('childpanel_price') ?>">
                        </div>
                        <small class="text-muted"><span class="text-danger">*</span> <?= lang("this_is_the_price_of_childpanel") ?></small>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?= lang("Nameserver_1") ?>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="ns1" value="<?= get_option('ns1') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label"><?= lang("Nameserver_2") ?>
                        </label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="ns2" value="<?= get_option('ns2') ?>">
                        </div>
                    </div>
                    
                   <h5 class="text-info"> <?=lang("childpanel_description")?></h5 class="text-info">
                   <div class="form-group">
                     <textarea rows="3" name="childpanel_desc" id="childpanel_desc" class="form-control plugin_editor"><?=get_option('childpanel_desc')?></textarea>
                   </div> 
                    
                </div>
                
                <div class="col-md-12">
                    <div class="">
                        <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?= lang("Save") ?></button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>



<div class="card content">
  <div class="card-header">
    <h3 class="card-title"><i class="fe fe-sliders"></i> <?= lang("affiliate_settings") ?></h3>
  </div>
  <div class="card-body">
    <form class="actionForm" action="<?= cn("$module/ajax_general_settings") ?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
      <div class="row">

        <div class="col-md-12 col-lg-12">

          <h5 class="text-info"><i class="fe fe-link"></i> <?= lang("enable_affiliate") ?></h5>
          <div class="form-group">
            <div class="form-label"><?= lang("Status") ?></div>
            <label class="custom-switch">
              <input type="hidden" name="enable_affiliate" value="0">
              <input type="checkbox" name="enable_affiliate" class="custom-switch-input" <?= (get_option("enable_affiliate", 0) == 1) ? "checked" : "" ?> value="1">
              <span class="custom-switch-indicator"></span>
              <span class="custom-switch-description"><?= lang("Active") ?></span>
            </label>
            <br>
          </div>

          <h5 class="text-info"><i class="fe fe-link"></i> <?= lang("affiliate_page_notice") ?></h5>
          <div class="form-group">
            <textarea rows="3" name="affiliate_notice" class="form-control"><?= get_option('affiliate_notice', "") ?></textarea>
          </div>

          <h5 class="text-info"><i class="fe fe-link"></i> <?= lang("affiliate_bonus") ?></h5>

          <div class="form-group">
            <div class="input-group">
              <span class="input-group-prepend">
                <span class="input-group-text"><?=get_option('currency_symbol',"$")?></span>
              </span>
              <input class="form-control" name="affiliate_bonus" value="<?= get_option('affiliate_bonus', "10") ?>">
            </div>
            <small class="text-muted"><span class="text-danger">*</span> <?= lang("this_is_fund_which_will_be_added_to_the_affiliate_user_whenever_someone_signup_with_their_referral_code.") ?></small>
          </div>

        </div>
        <div class="col-md-8">
          <div class="form-footer">
            <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?= lang("Save") ?></button>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>


<div class="card content">
  <div class="card-header">
    <h3 class="card-title"><i class="fe fe-sliders"></i> <?= lang("refill_settings") ?></h3>
  </div>
  <div class="card-body">
    <form class="actionForm" action="<?= cn("$module/ajax_general_settings") ?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
      <div class="row">

        <div class="col-md-12 col-lg-12">

            <h5 class="text-info"><i class="fe fe-link"></i> <?= lang("refill_expiry_days") ?></h5>

              <div class="row">
                <div class="col-md-12">
                  <div class="form-group">
                    <label><?= lang("disable_refill_option_from_and_order_after_X_days") ?></label>
                    <select name="refill_expiry_days" class="form-control square">
                      <?php
                      for ($i = 1; $i <= 90; $i++) {
                      ?>
                        <option value="<?= $i ?>" <?= (get_option('refill_expiry_days', 30) == $i) ? 'selected' : '' ?>> <?= $i ?></option>
                      <?php } ?>
                    </select>
                      </div>

        </div>
        <div class="col-md-8">
          <div class="form-footer">
            <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?= lang("Save") ?></button>
          </div>
        </div>

      </div>
    </form>
  </div>
</div>
 
<!-- codemirror -->
<link rel="stylesheet" type="text/css" href="<?php echo BASE; ?>assets/plugins/codemirror/lib/codemirror.css">
<link rel="stylesheet" type="text/css" href="<?php echo BASE; ?>assets/plugins/codemirror/theme/monokai.css">
<script src="<?php echo BASE; ?>assets/plugins/codemirror/lib/codemirror.js" type="text/javascript" charset="utf-8"></script>
<script src="<?php echo BASE; ?>assets/plugins/codemirror/mode/css/css.js" type="text/javascript" charset="utf-8"></script>
<script>
  setTimeout(function() {

    var editor = CodeMirror.fromTextArea(document.getElementById("embed_head_javascript"), {
      lineNumbers: true,
      theme: "monokai",
    });

    var editor = CodeMirror.fromTextArea(document.getElementById("embed_javascript"), {
      lineNumbers: true,
      theme: "monokai",
    });

  }, 200);
</script>