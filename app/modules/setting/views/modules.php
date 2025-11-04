<div class="card content">
  <div class="card-header" style="border:0.1px solid #1B78FC;border-radius:3.5px 3.5px 0 0;background:#1B78FC;">
    <h3 class="card-title"><i class="fa fa-question-circle"></i> <?= lang("Modules") ?></h3>
  </div>

  <div class="card-body">

    <!-- WhatsApp Number (stored in options) -->
    <form class="actionForm" action="<?= cn("$module/ajax_general_settings") ?>" method="POST" data-redirect="<?= get_current_url(); ?>">
      <div class="row">
        <div class="col-md-12">
          <h5 class="text-info"><i class="fe fe-link"></i> <?= lang("Whatsapp_Number") ?></h5>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label class="form-label"><?= lang("Whatsapp_Number") ?></label>
                <input class="form-control" name="whatsapp_number" value="<?= get_option('whatsapp_number') ?>" placeholder="+1234567890">
                <small class="text-muted">Will be stored in options table as whatsapp_number.</small>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-8">
          <div class="form-footer">
            <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?= lang("Save") ?></button>
          </div>
        </div>
      </div>
    </form>

    <br><hr><br>

    <!-- WhatsApp API Settings (stored in whatsapp_config table) -->
    <form class="actionForm" action="<?= cn("$module/ajax_whatsapp_api_settings") ?>" method="POST" data-redirect="<?= get_current_url(); ?>">
      <div class="row">
        <div class="col-md-12">
          <h5 class="text-info"><i class="fe fe-link"></i> <?= lang("Whatsapp_API_Settings") ?></h5>
        </div>

        <?php
          $api_url       = isset($whatsapp_api->url) ? $whatsapp_api->url : '';
          $api_key       = isset($whatsapp_api->api_key) ? $whatsapp_api->api_key : '';
          $admin_phone   = isset($whatsapp_api->admin_phone) ? $whatsapp_api->admin_phone : '';
        ?>

        <div class="col-md-4">
          <div class="form-group">
            <label class="form-label"><?= lang("url") ?></label>
            <input class="form-control" name="url" value="<?= html_escape($api_url) ?>" placeholder="https://api.example.com/send" required>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label class="form-label"><?= lang("api_key") ?></label>
            <input class="form-control" name="api_key" value="<?= html_escape($api_key) ?>" placeholder="Your API key" required>
          </div>
        </div>

        <div class="col-md-4">
          <div class="form-group">
            <label class="form-label"><?= lang("admin_phone") ?></label>
            <input class="form-control" name="admin_phone" value="<?= html_escape($admin_phone) ?>" placeholder="+1234567890" required>
          </div>
        </div>

        <div class="col-md-12">
          <small class="text-muted">
            Stored in whatsapp_config table (single row). Fields: url, api_key, admin_phone.
          </small>
        </div>

        <div class="col-md-8 mt-3">
          <div class="form-footer">
            <button class="btn btn-primary btn-min-width btn-lg text-uppercase"><?= lang("Save") ?></button>
          </div>
        </div>
      </div>
    </form>

    <br><hr><br>

    <!-- Refill Expiry Days (options) -->
    <form class="actionForm" action="<?= cn("$module/ajax_general_settings") ?>" method="POST" data-redirect="<?= get_current_url(); ?>">
      <div class="row">
        <div class="col-md-12">
          <h5 class="text-info"><i class="fe fe-link"></i> <?= lang("refill_expiry_days") ?></h5>
          <div class="form-group">
            <label><?= lang("disable_refill_option_from_and_order_after_X_days") ?></label>
            <select name="refill_expiry_days" class="form-control square">
              <?php
              $selected_days = (int) get_option('refill_expiry_days', 30);
              for ($i = 1; $i <= 90; $i++):
              ?>
                <option value="<?= $i ?>" <?= ($selected_days === $i ? 'selected' : '') ?>><?= $i ?></option>
              <?php endfor; ?>
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

<script>
  $(document).ready(function() {
    plugin_editor('.plugin_editor', {height: 200, toolbar: 'code'});
  });
</script>