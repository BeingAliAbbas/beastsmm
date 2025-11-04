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
                <h4><strong>Updates</strong></h4>
              </ul>
            </div>
          </div>
          <div class="card-body">
            <div class="tab-content">
              
              <div id="dashboard" class="tab-pane fade in active show">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fa fa-life-ring"></i> <?=lang("Updates")?></h5>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label class="form-label"><?=lang("Content")?></label>
                            <input class="form-control plugin_editor" name="updates_text" value="<?=get_option('updates_text')?>">
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

<div class="row m-t-5">
  <div class="col-sm-12 col-sm-12">
    <div class="row">
	  <div class="col-sm-7 col-lg-7 item">
       <div class="card">
          <div class="card-header d-flex align-items-center" style="border: 0.1px solid #1B78FC; border-radius: 3.5px 3.5px 0px 0px; background: #1B78FC;">
            <div class="tabs-list">
              <ul class="nav nav-tabs">
                <h4><strong>Rules</strong></h4>
              </ul>
            </div>
          </div>
          <div class="card-body">
            <div class="tab-content">
              
              <div id="dashboard" class="tab-pane fade in active show">
                <form class="actionForm" action="<?=cn("$module/ajax_general_settings")?>" method="POST" data-redirect="<?php echo get_current_url(); ?>">
                  <div class="row">
                    <div class="col-md-12 col-lg-12">
                      <h5 class="text-info"><i class="fa fa-info-circle"></i> <?=lang("Rules")?></h5>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label class="form-label"><?=lang("Content")?></label>
                            <input class="form-control plugin_editor" name="order_rules" value="<?=get_option('order_rules')?>">
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