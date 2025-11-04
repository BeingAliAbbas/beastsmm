<div id="main-modal-content">
  <div class="modal-right">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <?php
          $id = (!empty($payment->id))? $payment->id: '';
          if ($id != "") {
            $url = cn($module."/ajax_update/$id");
          }else{
            $url = cn($module."/ajax_update");
          }
        ?>
        <form class="form actionForm" action="<?php echo $url?>" data-redirect="<?php echo cn($module); ?>" method="POST">
          <div class="modal-header bg-pantone">
            <h4 class="modal-title"><i class="fa fa-edit"></i> <?php echo $payment->name; ?></h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            </button>
          </div>
          <div class="modal-body">
            <div class="form-body">
              
              <center class="form-control" style="background:blue; margin-bottom: 10px;">
                <a style="color :#fff;" href="https://youtu.be/s-FrFoeec2w" target="_blank"><strong>Help</strong> <i class="fa fa-question-circle-o" aria-hidden="true"></i></a>
              </center>
              <div class="row justify-content-md-center">

                <div class="col-md-12 col-sm-12 col-xs-12">
                  <div class="form-group">
                    <label class="form-label" ><?php echo lang("method_name"); ?></label>
                    <input type="hidden" class="form-control square" name="payment_params[type]" value="<?php echo $payment->type; ?>">
                    <input type="text" class="form-control square" name="payment_params[name]" value="<?php echo (!empty($payment->name))? $payment->name : '' ; ?>">
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" ><?php echo lang("Minimal_payment"); ?></label>
                    <input type="number" class="form-control square" name="payment_params[min]" value="<?php echo (!empty($payment->min))? $payment->min : '' ; ?>">
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" ><?php echo lang("Maximal_payment"); ?></label>
                    <input type="number" class="form-control square" name="payment_params[max]" value="<?php echo (!empty($payment->max))? $payment->max : '' ; ?>">
                  </div>
                </div>
               
                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label" ><?php echo lang("new_users"); ?></label>
                    <select name="payment_params[new_users]" class="form-control square">
                      <option value="1" <?php echo (!empty($payment->new_users) && $payment->new_users == 1)? 'selected' : '' ; ?>><?php echo lang("allowed"); ?></option>
                      <option value="0" <?php echo (isset($payment->new_users) && $payment->new_users != 1)? 'selected' : '' ; ?>><?php echo lang("not_allowed"); ?></option>
                    </select>
                  </div>
                </div>

                <div class="col-md-6">
                  <div class="form-group">
                    <label class="form-label"><?php echo lang("Status"); ?></label>
                    <select name="payment_params[status]" class="form-control square">
                      <option value="1" <?php echo (!empty($payment->status) && $payment->status == 1) ? 'selected' : '' ; ?>><?php echo lang("Active")?></option>
                      <option value="0" <?php echo (isset($payment->status) && $payment->status != 1) ? 'selected' : '' ; ?>><?php echo lang("Deactive")?></option>
                    </select>
                  </div>
                </div>
                <?php
                  $payment_params = json_decode($payment->params);
                  $option = $payment_params->option;
                ?>
                <div class="col-md-12">
                  <div class="form-group">
                    <label class="form-label"><?=lang("transaction_fee")?></label>
                    <select name="payment_params[option][tnx_fee]" class="form-control square">
                      <?php
                        for ($i = 0; $i <= 30; $i++) {
                      ?>
                      <option value="<?=$i?>" <?=(isset($option->tnx_fee) && $option->tnx_fee == $i)? "selected" : ''?>><?php echo $i; ?>%</option>
                      <?php } ?>
                    </select>
                  </div>
                </div> 
                 
                <div class="col-md-12">
                  <hr>

                  <div class="form-group">
                    <label class="form-label">Account Title</label>
                    <input class="form-control" name="payment_params[option][title]" value="<?php echo (isset($option->title)) ? $option->title : ''; ?>">
                  </div>

                  <div class="form-group">
                    <label class="form-label">NayaPay ID</label>
                    <input class="form-control" name="payment_params[option][nayapayid]" value="<?php echo (isset($option->nayapayid)) ? $option->nayapayid : ''; ?>">
                  </div>

                  <div class="form-group">
                    <label class="form-label">Account Number</label>
                    <input class="form-control" name="payment_params[option][number]" value="<?php echo (isset($option->number)) ? $option->number : ''; ?>">
                  </div>

                  <hr>

                  <div class="form-group">
                    <label class="form-label"> Nayapay email (your gmail id registered on Nayapay)</label>
                    <input class="form-control" name="payment_params[option][gmail]" value="<?php echo (isset($option->gmail)) ? $option->gmail : ''; ?>">
                  </div>

                  <div class="form-group">
                    <label class="form-label">nayapay Email app password</label>
                    <input class="form-control" name="payment_params[option][gmail_password]" type="password" value="<?php echo (isset($option->gmail_password)) ? $option->gmail_password : ''; ?>">
                  </div>

                  <div class="form-group">
                    <label class="form-label"><?=lang("currency_rate")?></label>
                    <div class="input-group">
                      <span class="input-group-prepend">
                        <span class="input-group-text">1USD =</span>
                      </span>
                      <input type="text" class="form-control text-right" name="payment_params[option][rate_to_usd]" value="<?php echo (isset($option->rate_to_usd)) ? $option->rate_to_usd : 76; ?>">
                      <span class="input-group-append">
                        <span class="input-group-text">PKR</span>
                      </span>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="form-group">
                      <span class="text-danger"><strong><?=lang('note')?></strong></span>
                      <ul class="small">
                        <li> Enable access to less secure apps and unlock captcha for your Google account using:
                            <ol>
                              <li><a href="https://www.google.com/settings/security/lesssecureapps">https://www.google.com/settings/security/lesssecureapps</a></li>
                              <li><a href="https://accounts.google.com/b/0/DisplayUnlockCaptcha.">https://accounts.google.com/b/0/DisplayUnlockCaptcha.</a></li>
                            </ol>
                        </li>
                      </ul>
                    </div>
                  </div>

                </div>

              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1"><?php echo lang("Submit")?></button>
            <button type="button" class="btn round btn-default btn-min-width mr-1 mb-1" data-dismiss="modal"><?php echo lang("Cancel")?></button>
          </div>
        </form>
          
      <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
       <a href="https://hqsmmprovider.com" target="blank"> <?=get_option('copy_right_content',"Copyright &copy; 2020 - ")?></a>
      </div>
      </div>
    </div>
  </div>
</div>
