<?php
$currency_symbol = get_option('currency_symbol', "$");
$decimal_places  = get_option('currency_decimal', 2);
?>

<div class="form-group service-select-wrapper" data-wrapper="service">
  <label for="dropdownservices"><?= lang("order_service") ?></label>
  <select
      id="dropdownservices"
      name="service_id"
      class="form-control square ajaxChangeService service-select"
      data-url="<?= cn($module . "/get_service/") ?>"
      aria-label="<?= lang('order_service') ?>"
      aria-describedby="serviceHelpText">
    <?php if (!empty($services)): ?>
      <?php foreach ($services as $service):
        // Base price or custom override
        $service_price = $service->price;
        if (get_role('user') && isset($custom_rates[$service->id])) {
          $service_price = $custom_rates[$service->id]['service_price'];
        }
        $price_per_1k = currency_format($service_price, $decimal_places);
        $safe_name    = htmlspecialchars($service->name, ENT_QUOTES, 'UTF-8');
        
        // Modified format with ID clearly at the beginning
        $label_full   = "#{$service->id} - {$safe_name} - {$currency_symbol}{$price_per_1k}";
      ?>
        <option
          value="<?= (int)$service->id ?>"
          data-dripfeed="<?= (int)$service->dripfeed ?>"
          data-min="<?= (int)$service->minimum ?>"
          data-max="<?= (int)$service->maximum ?>"
          data-rate="<?= $price_per_1k ?>"
          data-price-raw="<?= htmlspecialchars($service_price, ENT_QUOTES, 'UTF-8') ?>"
          data-name="<?= $safe_name ?>"
          data-fullname="<?= $safe_name ?>"
          title="<?= htmlspecialchars($label_full, ENT_QUOTES, 'UTF-8') ?>"
        ><?= htmlspecialchars($label_full, ENT_QUOTES, 'UTF-8') ?></option>
      <?php endforeach; ?>
    <?php endif; ?>
  </select>
</div>