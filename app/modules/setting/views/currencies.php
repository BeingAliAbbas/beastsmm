
<div class="card content">
  <div class="card-header" style="border: 0.1px solid #05d0a0; border-radius: 3.5px 3.5px 0px 0px; background: #05d0a0;">
    <h3 class="card-title"><i class="fe fe-dollar-sign"></i> <?=lang("Multi_Currency_Management")?></h3>
  </div>
  <div class="card-body">
    
    <div class="row mb-4">
      <div class="col-md-12">
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCurrencyModal">
          <i class="fe fe-plus"></i> Add New Currency
        </button>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover">
        <thead>
          <tr>
            <th>Code</th>
            <th>Symbol</th>
            <th>Name</th>
            <th>Rate (to PKR)</th>
            <th>Default</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($currencies)) { 
            foreach ($currencies as $currency) { ?>
          <tr>
            <td><strong><?=$currency->code?></strong></td>
            <td><?=$currency->symbol?></td>
            <td><?=$currency->name?></td>
            <td><?=number_format($currency->rate, 8)?></td>
            <td>
              <?php if ($currency->is_default) { ?>
                <span class="badge badge-success">Default</span>
              <?php } else { ?>
                <button class="btn btn-sm btn-outline-secondary actionSetDefault" data-id="<?=$currency->id?>">
                  Set Default
                </button>
              <?php } ?>
            </td>
            <td>
              <?php if ($currency->enabled) { ?>
                <span class="badge badge-success">Enabled</span>
              <?php } else { ?>
                <span class="badge badge-secondary">Disabled</span>
              <?php } ?>
            </td>
            <td>
              <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editCurrencyModal" 
                      data-id="<?=$currency->id?>"
                      data-code="<?=$currency->code?>"
                      data-symbol="<?=$currency->symbol?>"
                      data-name="<?=$currency->name?>"
                      data-rate="<?=$currency->rate?>"
                      data-enabled="<?=$currency->enabled?>">
                <i class="fe fe-edit"></i> Edit
              </button>
              <?php if ($currency->code !== 'PKR') { ?>
              <button class="btn btn-sm btn-danger actionDeleteCurrency" data-id="<?=$currency->id?>">
                <i class="fe fe-trash"></i> Delete
              </button>
              <?php } ?>
            </td>
          </tr>
          <?php } 
          } else { ?>
          <tr>
            <td colspan="7" class="text-center">No currencies found. Please run the migration.</td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<!-- Add Currency Modal -->
<div class="modal fade" id="addCurrencyModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form class="actionForm" action="<?=cn('currencies/add')?>" method="POST" data-redirect="<?=cn('setting/currencies')?>">
        <div class="modal-header">
          <h5 class="modal-title">Add New Currency</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Currency Code *</label>
            <input type="text" class="form-control" name="code" required maxlength="10" placeholder="e.g., USD">
            <small class="text-muted">3-letter ISO code (e.g., USD, EUR, GBP)</small>
          </div>
          <div class="form-group">
            <label>Symbol *</label>
            <input type="text" class="form-control" name="symbol" required maxlength="10" placeholder="e.g., $">
          </div>
          <div class="form-group">
            <label>Name *</label>
            <input type="text" class="form-control" name="name" required maxlength="100" placeholder="e.g., US Dollar">
          </div>
          <div class="form-group">
            <label>Exchange Rate (relative to PKR) *</label>
            <input type="number" class="form-control" name="rate" required step="0.00000001" min="0.00000001" placeholder="e.g., 0.00357143">
            <small class="text-muted">How much of this currency equals 1 PKR. Example: if 1 PKR = 0.0036 USD, enter 0.0036</small>
          </div>
          <div class="form-group">
            <label class="custom-switch">
              <input type="hidden" name="enabled" value="0">
              <input type="checkbox" name="enabled" class="custom-switch-input" value="1" checked>
              <span class="custom-switch-indicator"></span>
              <span class="custom-switch-description">Enable Currency</span>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Add Currency</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Currency Modal -->
<div class="modal fade" id="editCurrencyModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form class="actionForm" action="<?=cn('currencies/update')?>" method="POST" data-redirect="<?=cn('setting/currencies')?>">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-header">
          <h5 class="modal-title">Edit Currency</h5>
          <button type="button" class="close" data-dismiss="modal">
            <span>&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Currency Code</label>
            <input type="text" class="form-control" id="edit_code" readonly>
          </div>
          <div class="form-group">
            <label>Symbol *</label>
            <input type="text" class="form-control" name="symbol" id="edit_symbol" required maxlength="10">
          </div>
          <div class="form-group">
            <label>Name *</label>
            <input type="text" class="form-control" name="name" id="edit_name" required maxlength="100">
          </div>
          <div class="form-group">
            <label>Exchange Rate (relative to PKR) *</label>
            <input type="number" class="form-control" name="rate" id="edit_rate" required step="0.00000001" min="0.00000001">
            <small class="text-muted">How much of this currency equals 1 PKR</small>
          </div>
          <div class="form-group">
            <label class="custom-switch">
              <input type="hidden" name="enabled" value="0">
              <input type="checkbox" name="enabled" class="custom-switch-input" id="edit_enabled" value="1">
              <span class="custom-switch-indicator"></span>
              <span class="custom-switch-description">Enable Currency</span>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Currency</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Populate edit modal with currency data
$('#editCurrencyModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var modal = $(this);
  
  modal.find('#edit_id').val(button.data('id'));
  modal.find('#edit_code').val(button.data('code'));
  modal.find('#edit_symbol').val(button.data('symbol'));
  modal.find('#edit_name').val(button.data('name'));
  modal.find('#edit_rate').val(button.data('rate'));
  
  if (button.data('enabled') == 1) {
    modal.find('#edit_enabled').prop('checked', true);
  } else {
    modal.find('#edit_enabled').prop('checked', false);
  }
});

// Set default currency
$(document).on('click', '.actionSetDefault', function(e) {
  e.preventDefault();
  var id = $(this).data('id');
  
  if (confirm('Are you sure you want to set this as the default currency?')) {
    $.post('<?=cn("currencies/set_default")?>', {
      id: id,
      '<?=csrf_token()?>': '<?=csrf_hash()?>'
    }, function(data) {
      if (data.status == 'success') {
        window.location.reload();
      } else {
        alert(data.message || 'Failed to set default currency');
      }
    }, 'json');
  }
});

// Delete currency
$(document).on('click', '.actionDeleteCurrency', function(e) {
  e.preventDefault();
  var id = $(this).data('id');
  
  if (confirm('Are you sure you want to delete this currency? This action cannot be undone.')) {
    $.post('<?=cn("currencies/delete")?>', {
      id: id,
      '<?=csrf_token()?>': '<?=csrf_hash()?>'
    }, function(data) {
      if (data.status == 'success') {
        window.location.reload();
      } else {
        alert(data.message || 'Failed to delete currency');
      }
    }, 'json');
  }
});
</script>
