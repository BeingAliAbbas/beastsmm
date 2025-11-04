<style type="text/css">
  .order_btn_group .list-inline-item {
    margin-right: 0px !important;
  }

  .order_btn_group .list-inline-item a.btn {
    font-size: 0.9rem;
    font-weight: 400;
  }

  .btn-show-custom-mention {
    font-weight: 400px !important;
    font-size: 10px !important;
  }
</style>

<div class="row justify-content-md-center justify-content-xl-center m-t-30" id="result_ajaxSearch">
  <div class="col-md-10 col-xl-10 ">
    <div class="card">
    <div class="card-header d-flex align-items-center" style="border: 0.1px solid #05d0a0; border-radius: 3.5px 3.5px 0px 0px; background: #05d0a0;">
        <div class="tabs-list">
          <ul class="nav nav-tabs">
            
            <li>
              <a data-toggle="" href="<?=cn('childpanel/add')?>"><i class="fa fa-shopping-cart"></i> <?=lang("add_new")?></a>
            </li>
            <li class="">
              <a class="active show" data-toggle="tab" href="#new_order"><i class="fe fe-inbox"></i>&nbsp;<?=lang('History')?></a>
            </li>
           
          </ul>
        </div>
      </div>
    

<div class="row" id="result_ajaxSearch">
  <?php if (!empty($order_logs)) {
  ?>

        <div class="col-md-12">
    
      <div class="card-header">
        <h3 class="card-title"><?=lang("Lists")?></h3>
        <div class="card-options">
          <a href="#" class="card-options-collapse" data-toggle="card-collapse"><i class="fe fe-chevron-up"></i></a>
          <a href="#" class="card-options-remove" data-toggle="card-remove"><i class="fe fe-x"></i></a>
        </div>
      </div>
        <div class="table-responsive">
          <table class="table table-hover table-vcenter card-table">
            <thead>
              <tr>
                <?php if (!empty($columns)) {
                  foreach ($columns as $key => $row) {
                ?>
                    <th><?= $row ?></th>
                <?php }
                } ?>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($order_logs)) {
                $i = 0;
                foreach ($order_logs as $key => $row) {
                  $i++;
              ?>
                  
                  <tr class="tr_<?= $row->ids ?>">
                    <td class="text-center"><?= $row->id ?></td>

                    <?php
                    if (get_role("admin") || get_role("supporter")) {
                    ?>
                      <td><?= $row->user_email ?></td>
                    <?php } ?>
                    <td>
                      <div class="title">
                        <h6><?= "Childpanel" . " - " . "1 Month" ?></h6>
                      </div>
                      <div>
                        <small>
                          <ul style="margin:0px">
                            <li><?= lang("Domain") ?>: <a href="https://<?= $row->domain ?>" target="_blank"><?= truncate_string($row->domain, 60) ?></a></li>
                            <li><?= lang("Email") ?>: <?= $row->email ?></li>
                            <li><?= lang("Password") ?>: <?= $row->password ?></li>
                            <li><?= lang("Charge") ?>: <?= get_option('currency_symbol') ?><?= (!empty($row->charge)) ? currency_format($row->charge, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0 ?></li>
                            <li><?= lang("License") ?>: <?= $row->child_key ?></li>
                            <?php
                            $mention_list = get_list_custom_mention($row);
                            if ($mention_list->exists_list) {
                            ?>
                              <li><a href="<?= cn($module . '/ajax_show_list_custom_mention/' . $row->ids) ?>" class="btn btn-gray btn-sm ajaxModal btn-show-custom-mention"><?= $mention_list->title ?></a></li>
                            <?php } ?>
                          </ul>
                        </small>
                      </div>
                    </td>
                    <td><?= convert_timezone($row->created, "user") ?></td>
                    <td>
                      <?php
                      if ($row->status == "processing") {
                        $btn_background = "btn-info";
                      } elseif ($row->status == "refunded") {
                        $btn_background = "btn-orange";
                      } elseif ($row->status == "active") {
                        $btn_background = "btn-blue";
                      } else {
                        $btn_background = "btn-danger";
                      }
                      ?>
                      <center><span class="btn round btn-sm <?= $btn_background ?>"><?= childpanel_status_title($row->status) ?></span></center>
                      <?php if ($row->status == "terminated") { ?>
                        <form class="form actionForm" action="<?= cn($module . "/renew_panel/$row->ids") ?>" data-redirect="<?= cn($module . "/log") ?>" method="POST">
                          <center><button type="submit" class="btn round btn-success">Renew</button></center>
                        </form>
                      <?php } ?>
                    </td>

                    <?php
                    if (get_role("admin") || get_role("supporter")) {
                    ?>
    
              <td class="text-center">
                <div class="item-action dropdown">
                  <a href="javascript:void(0)" data-toggle="dropdown" class="icon"><i class="fe fe-more-vertical"></i></a>
                  <div class="dropdown-menu">
                            <a href="<?= cn("$module/log_update/" . $row->ids) ?>" class="dropdown-item ajaxModal"><i class="dropdown-icon fe fe-edit"></i> <?= lang('Edit') ?> </a>
                            <?php
                            if (get_role('admin')) {
                            ?>
                              <a href="<?= cn("$module/ajax_log_delete_item/" . $row->ids) ?>" class="dropdown-item ajaxDeleteItem"><i class="dropdown-icon fe fe-trash"></i> <?= lang('Delete') ?> </a>
                            <?php } ?>
                          </div>
                        </div>
                      </td>
                    <?php } ?>

                  </tr>
              <?php }
              } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    </div>
    <div class="col-md-12">
      <div class="float-right">
        <?= $links ?>
      </div>
    </div>
  <?php } else {
    echo Modules::run("blocks/empty_data");
  } ?>
</div>