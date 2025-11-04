<?php if (get_option('child_panel_text','') != '') { ?>
<div class="col-sm-12 col-sm-12">
  <div class="row">
    <div class="card">
      <div class="card-body">
        <?=get_option('child_panel_text','')?>
      </div>
    </div>
  </div>
</div>
<?php }?>

<div class="row justify-content-md-center justify-content-xl-center m-t-30" id="result_ajaxSearch">
  <div class="col-md-10 col-xl-10 ">
    <div class="card">
        <div class="card-header d-flex align-items-center" style="border: 0.1px solid #05d0a0; border-radius: 3.5px 3.5px 0px 0px; background: #05d0a0;">
        <div class="tabs-list">
          <ul class="nav nav-tabs">
            <li class="">
              <a class="active show" data-toggle="tab" href="#new_order"><i class="fa fa-shopping-cart"></i>&nbsp;<?=lang('add_new')?></a>
            </li>
            <li>
              <a data-toggle="" href="<?=cn('childpanel/log')?>"><i class="fe fe-inbox"></i> <?=lang("History")?></a>
            </li>
   
            </li>
          </ul>
        </div>
      </div>
      <div class="card-header d-flex align-items-center">
            <form class="form actionForm" action="<?=cn($module."/ajax_add_order")?>" data-redirect="<?=cn($module)?>" method="POST">
              <div class="row">
                <div class="col-md-12">
                  
                  
      <h4><i class="fa fa-info-circle"></i> <?=lang('Description')?></h4>
                  <br>
                   

<h1><?=get_option('childpanel_desc')?></h1>
</ul>                  
                  

                  <div class="form-group">
                    <label><?=lang("Domain_name")?></label>
                    <input class="form-control square ajaxQuantity" name="domain" type="text">
                  </div><br>
                  
                  <div class="alert alert-info" role="alert">
                            Point your domain to these name servers - <br>
                            <?=get_option('ns1')?><br>
                            <?=get_option('ns2')?>
                  </div>

                  <div class="form-group">
                    <label><?=lang("Admin_email")?></label>
                    <input class="form-control square" type="email" name="email" placeholder="admin@example.com" id="">
                  </div>

                  <div class="form-group">
                    <label><?=lang("Admin_password")?></label>
                    <input class="form-control square ajaxQuantity" name="pass" type="password">
                  </div>

                  <div class="form-group">
                    <label><?=lang("Confirm_password")?></label>
                    <input class="form-control square ajaxQuantity" name="conf_pass" type="password">
                  </div>
                  
                  <div class="form-group">

                    <p class="btn btn-info total_charge"><?=lang("total_charge")?> <span class="charge_number"><?=get_option("currency_symbol", "")?><?php echo get_option("childpanel_price") ?></span></p>                  
                  
                    <div class="alert alert-icon alert-danger d-none" role="alert">
                      <i class="fe fe-alert-triangle mr-2" aria-hidden="true"></i><?=lang("order_amount_exceeds_available_funds")?>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="agree">
                      <span class="custom-control-label text-uppercase"><?=lang("yes_i_have_confirmed_the_order")?></span>
                    </label>
                  </div>

                  <div class="form-actions left">
                    <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1">
                      <?=lang("place_order")?>
                    </button>

                  </div>
                </div>  
              </div>
            </form>
          </div>
          </div>
          
        </div>
      </div>
    </div>
    
  </div>
</div>

<style>
  .page-title h1{
    margin-bottom: 5px; }
    .page-title .border-line {
      height: 5px;
      width: 250px;
      background: #eca28d;
      background: -webkit-linear-gradient(45deg, #eca28d, #f98c6b) !important;
      background: -moz- oldlinear-gradient(45deg, #eca28d, #f98c6b) !important;
      background: -o-linear-gradient(45deg, #eca28d, #f98c6b) !important;
      background: linear-gradient(45deg, #eca28d, #f98c6b) !important;
      position: relative;
      border-radius: 30px; }
    .page-title .border-line::before {
      content: '';
      position: absolute;
      left: 0;
      top: -2.7px;
      height: 10px;
      width: 10px;
      border-radius: 50%;
      background: #fa6d7e;
      -webkit-animation-duration: 6s;
      animation-duration: 6s;
      -webkit-animation-timing-function: linear;
      animation-timing-function: linear;
      -webkit-animation-iteration-count: infinite;
      animation-iteration-count: infinite;
      -webkit-animation-name: moveIcon;
      animation-name: moveIcon; }

  @-webkit-keyframes moveIcon {
    from {
      -webkit-transform: translateX(0);
    }
    to { 
      -webkit-transform: translateX(250px);
    }
  }
</style>
