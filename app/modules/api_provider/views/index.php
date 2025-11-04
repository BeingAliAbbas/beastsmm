<div class="page-header">
  <h1 class="page-title">
    <?php if(get_role("admin")) { ?>
      <a href="<?=cn("$module/update")?>" class="ajaxModal">
        <span class="add-new" data-toggle="tooltip" data-placement="bottom" title="<?=lang("add_new")?>">
          <i class="fa fa-plus-square text-primary" aria-hidden="true"></i>
        </span>
      </a>
    <?php } ?>
    <?=lang("api_providers_list")?>
  </h1>
</div>

<div class="row" id="result_ajaxSearch">
  <?php if(!empty($api_lists)){ ?>
  <div class="col-md-12 col-xl-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><?=lang("Lists")?></h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
          <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-hover table-bordered  table-vcenter card-table">
          <thead>
            <tr>
              <th class="text-center w-1"><?=lang("No_")?></th>
              <?php if (!empty($columns)) {
                foreach ($columns as $key => $row) { ?>
                  <th><?=$row?></th>
              <?php }} ?>
              <?php if (get_role("admin")) { ?>
                <th class="text-center"><?=lang("Action")?></th>
              <?php } ?>
            </tr>
          </thead>
          <tbody>
            <?php
              $i = 0;
              foreach ($api_lists as $row) {
                $i++;
                $api_url_base = explode("/api", $row->url);
            ?>
            <tr class="tr_<?=$row->ids?>">
              <td class="text-center"><?=$i?></td>
              <td>
                <div class="title">
                  <a href="<?=$api_url_base[0]?>" target="_blank"><?=$row->name?></a>
                </div>
              </td>
              <td style="width:15%;"><?=$row->balance.$row->currency_code?></td>
              <td style="width:20%;"><?php echo html_entity_decode($row->description, ENT_QUOTES); ?></td>
              <td style="width:10%;">
                <?php if(!empty($row->status) && $row->status == 1){ ?>
                  <span class="badge badge-info"><?=lang("Active")?></span>
                <?php } else { ?>
                  <span class="badge badge-warning"><?=lang("Deactive")?></span>
                <?php } ?>
              </td>

              <?php if (get_role("admin")) { ?>
              <td class="text-center" style="width:22%;">
                <div class="btn-group">
                  <!-- Edit Provider -->
                  <a href="<?=cn("$module/update/".$row->ids)?>"
                     class="btn btn-icon btn-outline-info ajaxModal"
                     data-toggle="tooltip"
                     data-placement="bottom"
                     title="<?=lang("edit_api")?>">
                     <i class="fe fe-edit"></i>
                  </a>

                  <!-- Sync Services (manual one-off) -->
                  <a href="<?=cn("$module/sync_services/".$row->ids)?>"
                     class="btn btn-icon btn-outline-success ajaxModal"
                     data-toggle="tooltip"
                     title="<?=lang('sync_services')?>">
                     <i class="fe fe-refresh-cw"></i>
                  </a>

                  <!-- Bulk Add New Services -->
                  <a href="<?=cn("$module/bulk_services/".$row->ids)?>"
                     class="btn btn-icon btn-outline-primary ajaxModal"
                     data-toggle="tooltip"
                     title="<?=lang('bulk_add_all_services')?>">
                     <i class="fe fe-plus-square"></i>
                  </a>

                  <!-- NEW: Auto Sync Setting (global settings modal) -->
                  <a href="<?=cn("$module/auto_sync_services_setting")?>"
                     class="btn btn-icon btn-outline-warning ajaxModal"
                     data-toggle="tooltip"
                     title="<?=lang('auto_sync_service_setting')?>">
                     <i class="fe fe-clock"></i>
                  </a>

                  <!-- (Optional) Delete Provider - keep existing if already present (example stub)
                  <a href="<?=cn("$module/ajax_delete_item/".$row->ids)?>"
                     class="btn btn-icon btn-outline-danger ajaxDeleteItem"
                     data-toggle="tooltip"
                     title="<?=lang('delete')?>">
                     <i class="fe fe-trash"></i>
                  </a>
                  -->
                </div>
              </td>
              <?php } ?>
            </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?=lang("Update Order Status")?></h3>
            </div>
            <div class="card-body">
                <form class="form actionForm" action="<?=cn($module."/update_order_status")?>" data-redirect="<?=cn($module)?>" method="POST">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><?=lang("Order ID")?></label>
                                    <input type="text" class="form-control square" name="order_id" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary btn-min-width mr-1 mb-1"><?=lang("Submit")?></button>
                        <button type="button" class="btn btn-default btn-min-width mb-1" onclick="history.go(-1)"><?=lang("Cancel")?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $(".actionForm").submit(function(event) {
        event.preventDefault();
        var _that = $(this);
        var _action = _that.attr("action");
        var _data = _that.serialize();
        var _order_id = _that.find('input[name="order_id"]').val();
        
        if (_order_id == "") {
            notify("error", "<?=lang("Please enter Order ID")?>");
            return false;
        }
        
        window.location.href = "<?=cn($module."/update_order_status")?>" + "/" + _order_id;
    });
});
</script>
  <?php } else {
    echo Modules::run("blocks/empty_data");
  } ?>
</div>