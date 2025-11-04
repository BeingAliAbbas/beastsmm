<style>
  .action-options{
    margin-left: auto;
  }  
  .dropdown-item.ajaxActionOptions{
    padding-top: 0px!important;
    padding-bottom: 0px!important;
  }
</style>
<br>
<?php if (get_option('services_text','') != '') { ?>
<div class="col-sm-12 col-sm-12">
  <div class="row">
    <div class="card">
      <div class="card-body">
        <?=get_option('services_text','')?>
      </div>
    </div>
  </div>
</div>
<?php }?>

<form class="actionForm"  method="POST">
  <section class="page-title">
    <div class="row justify-content-between">
      <div class="col-md-2">
        <h1 class="page-title">
          <?php 
            if(get_role("admin") || get_role("supporter")) {
          ?>
          <a href="service_updation_for_new.php" class=""><span class="add-new" data-toggle="tooltip" data-placement="bottom" title="<?=lang("loading...")?>" data-original-title="Add new"><i class="btn btn-info fa fa-plus"> <?=lang("add_new")?></i></span></a> 
          <?php }else{?>
            <i class="fe fe-list" aria-hidden="true"> </i> 
          <?php }?>
        </h1>
      </div>
      <div class="col-md-7">
        <?php
          if (get_option("enable_explication_service_symbol")) {
        ?>
        <div class="btn-list">
          <span class="btn round btn-secondary ">⭐ = <?=lang("__good_seller")?></span>
          <span class="btn round btn-secondary ">⚡️ = <?=lang("__speed_level")?></span>
          <span class="btn round btn-secondary ">🔥 = <?=lang("__hot_service")?></span>
          <span class="btn round btn-secondary ">💎 = <?=lang("__best_service")?></span>
          <span class="btn round btn-secondary ">💧 = <?=lang("__drip_feed")?></span>
        </div>
        <?php } ?>
      </div>

      <div class="col-md-3">
        <div class="form-group ">
          <select  name="status" class="btn btn-info form-control order_by ajaxChange" data-url="<?=cn($module."/ajax_service_sort_by_cate/")?>">
            <option value="all"> <?=lang("sort_by")?></option>
            <?php 
              if (!empty($categories)) {
                foreach ($categories as $key => $category) {
            ?>
            <option value="<?=$category[0]->main_cate_id?>"><?=$key?></option>
            <?php }}?>
          </select>
        </div>
      </div>
      <?php
        if (get_role("admin")) {
      ?>
      <div class="col-12">
        <div class="form-group d-flex">
          <div>
            <a href="<?=cn('api_provider/services')?>" class="btn btn-secondary "><?php echo lang("import_services"); ?></a>
          </div>
          <div class="item-action dropdown action-options">
            <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown">
               <i class="fe fe-menu mr-2"></i> <?php echo lang("actions"); ?>
            </button>
            <div class="dropdown-menu dropdown-menu-right">
              <a class="dropdown-item ajaxActionOptions" href="<?=cn($module.'/ajax_actions_option')?>" data-type="delete"><i class="fe fe-trash-2 text-danger mr-2"></i> <?=lang("Delele")?></a>
              <a class="dropdown-item ajaxActionOptions" href="<?=cn($module.'/ajax_actions_option')?>" data-type="all_deactive"><i class="fe fe-trash-2 text-danger mr-2"></i> <?=lang("all_deactivated_services")?></a>
              <a class="dropdown-item ajaxActionOptions" href="<?=cn($module.'/ajax_actions_option')?>" data-type="deactive"><i class="fe fe-x-square text-danger mr-2"></i> <?=lang("Deactive")?></a>   
              <a class="dropdown-item ajaxActionOptions" href="<?=cn($module.'/ajax_actions_option')?>" data-type="active"><i class="fe fe-check-square text-success mr-2"></i> <?=lang("Active")?></a>
            </div>
          </div>
        </div>
      </div>
      <?php }?>
     
    </div>
  </section>

  <div class="row m-t-5" id="result_ajaxSearch">
    <?php if(!empty($all_services)){
      foreach ($all_services as $key => $category) {
    ?>
    <div class="col-md-12 col-xl-12">
      <div class="card">
        <div class="card-header" style="border: 0.1px solid #05d0a0; border-radius: 3.5px 3.5px 0px 0px; background: #05d0a0;">
          <h3 class="card-title"><?php echo $key; ?></h3>
          <div class="card-options">
            <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
          </div>
        </div>
		<div class="table-responsive dimmer">
        <?php
          $data = array(
            "module"     => $module,
            "columns"    => $columns,
            "services"   => $category,
            "cate_id"    => $category[0]->main_cate_id,
          );
          $this->load->view("ajax_load_services_by_cate", $data);
        ?>
        </div>
      </div>
    </div>
    <?php }}else{
      echo Modules::run("blocks/empty_data");
    }?>
    
  </div>
</form>