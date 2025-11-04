<style>
 .mmm {
    margin: 3px;
 }
</style>

<div class="row m-t-5">
  <div class="col-sm-12 col-sm-12">
    <div class="row">
	  <div class="col-sm-7 col-lg-7 item">
       <div class="card">
          <div class="card-header d-flex align-items-center" style="border: 0.1px solid #1B78FC; border-radius: 3.5px 3.5px 0px 0px; background: #1B78FC;">
            <div class="tabs-list">
              <ul class="nav nav-tabs">
                <li class="mmm">
                  <a class="active show" data-toggle="tab" href="#dashboard"><i class="fa fa-book"></i> Dashboard Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#new_order"><i class="fa fa-book"></i> New Order Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#orders"><i class="fa fa-book"></i> Order Logs Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#services"><i class="fa fa-book"></i> Services Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#add_funds"><i class="fa fa-book"></i> Add Funds Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#api"><i class="fa fa-book"></i> API Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#tickets"><i class="fa fa-book"></i> Tickets Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#child_panel"><i class="fa fa-book"></i> Child Panel Page Text</a>
                </li>
                <li class="mmm">
                  <a data-toggle="tab" href="#transactions"><i class="fa fa-book"></i> Transactions Page Text</a>
                </li>
              </ul>
            </div>
          </div>
          <div class="card-body">
            <div class="tab-content">
              
              <div id="dashboard" class="tab-pane fade in active show">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fe fe-bar-chart-2"></i> <?=lang("dashboard")?></h5>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label class="form-label"><?=lang("Content")?></label>
                            <input class="form-control plugin_editor" name="dashboard_text" value="<?=get_option('dashboard_text')?>">
                          </div>
                        </div>
                      </div>
                    </div> 
                    <div class="col-md-12">
                      <div class="form-footer">
                        <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
              
              <div id="new_order" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fe fe fe-shopping-cart"></i> <?=lang("New_Order")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="new_order_text" value="<?=get_option('new_order_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              
              <div id="orders" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fa fa-shopping-cart"></i> <?=lang("Orders")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="orders_text" value="<?=get_option('orders_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              
              <div id="services" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fe fe-list"></i> <?=lang("Services")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="services_text" value="<?=get_option('services_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              
              <div id="add_funds" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fa fa-money"></i> <?=lang("Add_Funds")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="add_funds_text" value="<?=get_option('add_funds_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              
              <div id="api" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fe fe-share-2"></i> <?=lang("API")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="api_text" value="<?=get_option('api_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              
              <div id="tickets" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fa fa-comments-o"></i> <?=lang("Tickets")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="tickets_text" value="<?=get_option('tickets_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              
              <div id="child_panel" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fa fa-child"></i> <?=lang("Child_Panel")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="child_panel_text" value="<?=get_option('child_panel_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
                        </div>
                      </div>
                    </div>
                  </form>
                </div>
              
              <div id="transactions" class="tab-pane fade">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fe fe-calendar"></i> <?=lang("Transsactions")?></h5>
                        <div class="row">
                          <div class="col-md-12">
                            <div class="form-group">
                              <label class="form-label"><?=lang("Content")?></label>
                              <input class="form-control plugin_editor" name="transactions_text" value="<?=get_option('transactions_text')?>">
                            </div>
                          </div>
                        </div>
                      </div> 
                      <div class="col-md-12">
                        <div class="form-footer">
                          <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?=lang("Save")?></button>
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
</div>


    <script>
      $(document).ready(function() {
        plugin_editor('.plugin_editor', {height: 200});
      });
    </script>