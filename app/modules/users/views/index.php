
<div class="page-header">
  <h1 class="page-title">
    <a href="<?=cn("$module/update")?>" class=""><span class="add-new" data-toggle="tooltip" data-placement="bottom" title="" data-original-title="<?=lang("add_new")?>"><i class="fa fa-plus-square text-primary" aria-hidden="true"></i></span></a> 
    <?=lang("users")?>
  </h1>
</div>

<div class="row" id="result_ajaxSearch">
  <?php if(!empty($users)){
  ?>
  <div class="col-md-12 col-xl-12">
    <div class="">
      <div class="card-header">
        <h3 class="card-title"><?=lang("Lists")?></h3>
        <div class="card-options">
        <div class="dropdown1" style="position: relative; display: inline-block;">
  <button type="button" class="btn btn-outline-info dropdown-toggle" onclick="toggleDropdown()" style="color: #04a9f4; border-color: #04a9f4; background-color: transparent;">
    <i class="fe fe-upload mr-2"></i>Export
  </button>

  <div id="customDropdown" class="dropdown-menu1" style="display: none; position: absolute; top: 100%; right: 2px; background-color: #051d2f; border: 1px solid #04a9f4; border-radius: 6px; padding: 8px 0; min-width: 150px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); z-index: 1000;">
    <a class="dropdown-item1" href="<?=cn($module.'/export/excel')?>" style="color: #ffffff; padding: 10px 15px; font-size: 14px; display: block; transition: background-color 0.3s ease, color 0.3s ease;">Excel</a>
    
    <a class="dropdown-item1" href="<?=cn($module.'/export/csv')?>" style="color: #ffffff; padding: 10px 15px; font-size: 14px; display: block; transition: background-color 0.3s ease, color 0.3s ease;">CSV</a>
    
    <a class="dropdown-item1" href="<?= cn('users/export_whatsapp_numbers') ?>" style="color: #ffffff; padding: 10px 15px; font-size: 14px; display: block; transition: background-color 0.3s ease, color 0.3s ease;">WhatsApp Numbers</a>
  </div>
</div>

<script>
  // Function to toggle dropdown visibility
  function toggleDropdown() {
    var dropdown = document.getElementById('customDropdown');
    dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
  }

  // Close the dropdown when clicking outside
  window.onclick = function(event) {
    if (!event.target.matches('.btn')) {
      var dropdown = document.getElementById('customDropdown');
      if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
      }
    }
  }
</script>

        </div>
      </div>


      <div class="table-responsive dimmer">
        <table class="table table-hover table-bordered table-vcenter card-table">
          <thead>
            <tr>
              <th class="text-center w-1"><?=lang("No_")?></th>
              <?php if (!empty($columns)) {
                foreach ($columns as $key => $row) {
              ?>
              <th><?=$row?></th>
              <?php }}?>
              
              <?php
                if (!get_role("user")) {
              ?>
              <th class="text-center"><?=lang('Add_funds')?></th>
              <th class="text-center"><?=lang('Action')?></th>
              <?php }?>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($users)) {
              $i = 0;
              $currency_symbol = get_option('currency_symbol', '$');

              switch (get_option('currency_decimal_separator', 'dot')) {
                case 'dot':
                  $decimalpoint = '.';
                  break;
                case 'comma':
                  $decimalpoint = ',';
                  break;
                default:
                  $decimalpoint = '';
                  break;
              } 

              switch (get_option('currency_thousand_separator', 'comma')) {
                case 'dot':
                  $separator = '.';
                  break;
                case 'comma':
                  $separator = ',';
                  break;
                case 'space':
                  $separator = ' ';
                  break;
                default:
                  $separator = '';
                  break;
              }

              foreach ($users as $key => $row) {
              $i++;
            ?>
            <tr class="tr_<?=$row->ids?>">
              <td><?=$i?></td>
              <td>
    <div class="title"><h6><?php _echo($row->first_name) . " " . _echo($row->last_name); ?></h6></div>
    <div class="sub" style="margin-right: 15px; margin-bottom: 10px;">
        <a href="<?=cn("$module/mail/".$row->ids)?>" class="ajaxModal" style="display: inline-flex; align-items: center; padding: 4px 8px; border: 1px solid #04a9f4; border-radius: 4px; color: #333; font-size: 12px; text-decoration: none;">
            <i class="fa fa-envelope" style="margin-right: 6px; font-size: 14px; color: #04a9f4;"></i>
            <small style="color:#fff;"><?php _echo($row->email); ?></small>
        </a>
    </div>

    <div class="sub" style="margin-bottom: 10px;">
        <small>
            <a href="https://wa.me/<?php echo $row->whatsapp_number; ?>" target="_blank" style="display: inline-flex; align-items: center; padding: 4px 8px; border: 1px solid #04a9f4; border-radius: 4px; color: #04a9f4; font-size: 12px; text-decoration: none;">
                <i class="fa fa-whatsapp" style="margin-right: 6px; font-size: 14px; color: #04a9f4;"></i>
                <?php echo $row->whatsapp_number; ?>
            </a>
        </small>
    </div>

    <div class="sub">
        <small>
            <?php
                switch ($row->role) {
                    case 'admin':
                        echo lang("admin");
                        break;
                    case 'supporter':
                        echo lang("Supporter");
                        break;
                    default:
                        echo lang("regular_user");
                        break;
                }
            ?>
        </small>
    </div>

    <!-- Show total orders -->
    <div class="sub">
        <small>
            Total Orders: <?php echo isset($row->total_orders) ? $row->total_orders : 0; ?>
        </small>
    </div>
</td>
              <td>
                <?=(!empty($row->balance)) ? $currency_symbol." ".currency_format($row->balance, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0?>
              </td>
              <td>
    <a href="javascript:void(0)" onclick="viewDeposit(<?= $row->id ?>)">Click to view</a>
</td>

<!-- Modal to show total deposit -->
<div id="depositModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Total Deposit</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          
        </button>
      </div>
      <div class="modal-body" id="depositModalBody">
        Loading...
      </div>
    </div>
  </div>
</div>

<script>
  function viewDeposit(uid) {
    $.ajax({
      url: '<?= cn("$module/deposit_viewer") ?>',
      type: 'GET',
      data: { uid: uid },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          $('#depositModalBody').html('Total Deposit: ' + response.total_deposit);
        } else {
          $('#depositModalBody').html('Error: ' + response.message);
        }
        $('#depositModal').modal('show');
      },
      error: function() {
        $('#depositModalBody').html('An error occurred while fetching data.');
        $('#depositModal').modal('show');
      }
    });
  }
</script>


              <td>
                <button type="button" class="btn btn-square btn-outline-info btn-sm btnEditCustomRate" data-action="<?php echo  cn($module.'/ajax_modal_custom_rates/'.$row->id); ?>"><i class="fe fe-plus mr-2"></i>Custom Rate</button>
              </td>
              <td><?=$row->desc?></td>
              <td><?=convert_timezone($row->created, 'user')?></td>
              
              <td class="w-1">
                <label class="custom-switch">
                  <input type="checkbox" name="item_status" data-id="<?php echo $row->id; ?>" data-action="<?php echo cn($module.'/ajax_toggle_item_status/'); ?>" class="custom-switch-input ajaxToggleItemStatus" <?php if(!empty($row->status) && $row->status == 1) echo 'checked'; ?>>
                  <span class="custom-switch-indicator"></span>
                </label>
              </td>

              <?php
                if (get_role("admin") || get_role('supporter')) {
              ?>
              <td>
                    <a class="ajaxModal" href="<?=cn("$module/add_funds_manual/".$row->ids)?>">
                      <i class="btn btn-info"><?=lang("Add_Funds")?></i>
                    </a>
              </td>
              <td class="text-center">
                <div class="item-action dropdown">
                  <a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>
                  <div class="dropdown-menu">
                    <?php
                      if (get_role("admin")) {
                    ?>
                    <a class="dropdown-item1" href="<?=cn("$module/update/".$row->ids)?>"><i class="dropdown-icon fe fe-edit"></i> <?=lang('edit')?>
                    </a>
                    <a class="dropdown-item1 ajaxViewUser" href="<?=cn("$module/view_user/".$row->ids)?>"><i class="dropdown-icon fe fe-eye"></i> <?=lang('view_user')?>
                    </a>
                    <a class="dropdown-item1 ajaxDeleteItem" href="<?=cn("$module/ajax_delete_item/".$row->ids)?>"><i class="dropdown-icon fe fe-trash"></i> <?=lang('Delete')?>
                    </a>
                    <?php }?>

                    <a class="dropdown-item1 ajaxModal" href="<?=cn("$module/mail/".$row->ids)?>">
                      <i class="dropdown-icon fe fe-mail"></i> <?=lang("send_mail")?>
                    </a>
                    

                  </div>
                </div>
              </td>
              <?php }?>
            </tr>
            <?php }}?>
            
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
  <?php }else{
    echo Modules::run("blocks/empty_data");
  }?>
</div>

<div id="customRate" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title"></i> Edit custom rates (ID: 1)</h4>
        <button type="button" class="close" data-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="form-group">
              <select name="service-id" class="select-service-item" class="form-control custom-select">
                <option value='{"service_id": "1189", "rate": "0.53", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}' data-rate="1" data-data='{"rate": "0.53", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}'>128 - Instagram Likes [100 - 3K] [Instant] [Exclusive] [$0.18]</option>
                <option value='{"service_id": "123", "rate": "0.78", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}' data-rate="1" data-data='{"rate": "0.53", "name": "Instagram Likes [100 - 3K] [Instant] [Exclusive]"}'>123 - Instagram Likes [100 - 3K] [Instant] [Exclusive] [$0.18]</option>
              </select>
            </div>
          </div>
        </div>
        
        <div class="o-auto" style="height: 20rem">
          <ul class="list-unstyled list-separated services-group-items">

            <div class="s-items">
              <li class="list-separated-item s-item">
                <div class="row align-items-center">
                  <div class="col">
                    111
                  </div>
                  <div class="col-md-7">
                    Facebook [Real Relevant Comments - Custom Comments]
                  </div>
                  <div class="col-md-1">
                    0.53
                  </div>
                  <div class="col-md-2">
                    <input type="hidden" class="form-control" value="customRates[1123][price]">
                    <input type="text" class="form-control" >
                  </div>
                  <div class="col-md-1">
                    <button class="btn btn-secondary btn-remove-item" type="button"><i class="fe fe-trash-2"></i></button>
                  </div>
                </div>
              </li>
            </div>

            <div class="s-item-more d-none">
              <li class="list-separated-item s-item" id="item__serviceID__">
                <div class="row align-items-center">
                  <div class="col">
                    __serviceID__
                  </div>
                  <div class="col-md-7">
                    __serviceName__
                  </div>
                  <div class="col-md-1">
                    __serviceRate__
                  </div>
                  <div class="col-md-2">
                    <input type="hidden" class="form-control" value="customRates[__serviceID__][rate_id]">
                    <input type="number" class="form-control" value="customRates[__serviceID__][price]">
                  </div>
                  <div class="col-md-1">
                    <button class="btn btn-secondary btn-remove-item" type="button"><i class="fe fe-trash-2"></i></button>
                  </div>
                </div>
              </li>
            </div>
            
          </ul>
        </div>
       
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>
<style>
/* Dropdown container style */
.dropdown-menu {
    background-color: #0b304a !important;  /* Dark background */
    border: none !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
    padding: 8px 0 !important;
    min-width: 150px !important;
}

/* Dropdown link style */
.dropdown-item1 {
    display: flex !important;
    align-items: center !important;
    padding: 8px 12px !important;
    color: #ffffff !important;
    font-size: 14px !important;
    font-family: 'Poppins', sans-serif !important;
    text-decoration: none !important;
    transition: background-color 0.3s ease !important;
}

/* Icon inside dropdown */
.dropdown-item1 .dropdown-icon {
    margin-right: 8px !important;
    font-size: 16px !important;
    color: #d1e3f0 !important;
}

/* Hover effect for dropdown item */
.dropdown-item1:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: #00d1b2 !important;  /* Light green hover text */
}

/* Active state (if needed) */
.dropdown-item1.active {
    background-color: #005082 !important;
    color: #ffffff !important;
}

/* Customize the dropdown toggle icon */
.icon {
    color: #ffffff !important;
    font-size: 20px !important;
    cursor: pointer !important;
}
</style>
