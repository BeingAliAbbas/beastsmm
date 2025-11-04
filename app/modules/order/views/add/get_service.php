<div class="col-md-12 col-sm-12 col-xs-12">
  <div class="form-group">
    <input type="hidden" name="service_id" id="service_id" value="<?=(!empty($service->id))? $service->id :''?>">
    <input type="hidden" class="form-control223344 square" name="api_service_id" value="<?=(!empty($service->api_service_id))? $service->api_service_id : ''?>">
    <input type="hidden" class="form-control223344 square" name="api_provider_id" value="<?=(!empty($service->api_provider_id))? $service->api_provider_id : ''?>">
    <input class="form-control223344 square" name="service_type" type="hidden" value="<?=(!empty($service->type))? $service->type :''?>">
    <input class="form-control223344 square" name="service_name" type="text" value="<?=(!empty($service->name))? $service->name :''?>" disabled>
  </div>
</div>   

<div class="col-md-12 col-sm-12 col-xs-12"style" margin-bottom:0px;>
  <div class="form-group">
    <label for="userinput8"><?=lang("Description")?></label>
    <?php
      if (!empty($service->desc)) { ?>
      <div class="card border">
        <div style="padding: 11px; min-height: 200px; max-height: 400px; overflow-y: scroll; border: 1px solid white; border-radius: 5px; background:transparent;">
          <?php
            $desc = html_entity_decode($service->desc, ENT_QUOTES);
            $desc = str_replace("\n", "<br>", $desc);
            echo strip_tags($desc, "<br>");
          ?>
        </div>
      </div>
      <?php
      }else{
      ?>
      <textarea rows="10" class="form-control square" name="service_desc" id="service_desc" class="form-control square" disabled>
      </textarea>
    <?php }?>  
    
  </div>
</div>

<table>
  <thead>
	<tr>
  <th style="background: none !important; border: none !important ;">
		<div class="col-md-12  col-sm-12 col-xs-12">
		  <div class="form-group">
			<label><?=lang("minimum")?></label>
			<input class="form-control2233 square" name="service_min" type="hidden" value="<?=$service->min?>">
			<input class="form-control2233 square" type="text" name="service_min" value="<?=(!empty($service->min))? $service->min :''?>"  readonly>
		  </div>
		</div>
	  </th>
						  
    <th style="background: none !important; border: none !important ;">
		<div class="col-md-12  col-sm-12 col-xs-12">
		  <div class="form-group">
			<label><?=lang("maximum")?></label>
			<input class="form-control2233 square" name="service_max" type="hidden" value="<?=$service->max?>">
			<input class="form-control2233 square"  type="text" name="service_max" value="<?=(!empty($service->max))? $service->max :''?>" readonly>
		  </div>
		</div>
	  </th>
	</tr>
  </thead>
</table>

<div class="col-md-10  col-sm-12 col-xs-12">
  <div class="form-group">
    <?php
      $user_price = get_user_price(session('uid'), $service);
    ?>
    <input class="form-control223344 square" name="service_price" type="hidden" value="<?php echo $user_price; ?>">
    <input class="form-control223344 square" type="text" name="service_price" value="<?=(!empty($service->price))?currency_format($service->price, get_option("currency_decimal")) :''?>" readonly>
  </div>
</div>
