<!-- jQuery (Required for Select2) -->

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var $jq = jQuery.noConflict();  // Save jQuery in a different variable
</script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['uid'])) {
    die('User not logged in');
}

$user_id = $_SESSION['uid']; // Get the user ID from the session

// Database credentials
$host = 'localhost';
$dbname = 'beastsmm_ali';
$username = 'beastsmm_ali';
$password = 'ra6efcTo[4z#';

try {
    // Create a new PDO connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query to check if the user has a WhatsApp number
    $stmt = $conn->prepare("SELECT whatsapp_number FROM general_users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if WhatsApp number exists and is valid (not empty and not just '+92')
    $whatsapp_number_exists = $user && !empty($user['whatsapp_number']) && $user['whatsapp_number'] !== '+92';

    // Query to fetch balance and spent from the general_users table
    $stmt = $conn->prepare("SELECT balance, spent FROM general_users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $balance = $user ? $user['balance'] : 0.0000;
    $total_spent = $user ? $user['spent'] : 0.0000;

    // Query to count total orders for the user
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_orders FROM orders WHERE uid = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $data_orders_log = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_orders = $data_orders_log ? $data_orders_log['total_orders'] : 0;

    // Query to calculate total spent from general_transaction_logs
    $stmt = $conn->prepare("SELECT SUM(amount) AS total_spent FROM general_transaction_logs WHERE uid = :user_id AND status = 1");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $transaction_log = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If there's a result, update total_spent, otherwise keep it as 0
    $total_spent = $transaction_log && $transaction_log['total_spent'] ? $transaction_log['total_spent'] : 0.0000;

    // Check if the current user is an admin
    $stmt = $conn->prepare("SELECT role FROM general_users WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $user_role = $stmt->fetch(PDO::FETCH_ASSOC)['role'];

    // If user is admin, get total users and total amount received
    if ($user_role === 'admin') {
        // Query to get total users
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_users FROM general_users");
        $stmt->execute();
        $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

        // Query to get total amount received (sum of transactions)
        $stmt = $conn->prepare("SELECT SUM(amount) AS total_received FROM general_transaction_logs WHERE status = 1");
        $stmt->execute();
        $total_received = $stmt->fetch(PDO::FETCH_ASSOC)['total_received'];
        
        // Query to get total orders (all orders, not just the current user's orders)
        $stmt = $conn->prepare("SELECT COUNT(*) AS total_orders_all FROM orders");
        $stmt->execute();
        $total_orders_all = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders_all'];
    }

} catch (PDOException $e) {
    // Handle connection errors
    echo "Connection failed: " . $e->getMessage();
    $balance = 0.0000;
    $total_orders = 0;
    $total_spent = 0.0000;
    $total_users = 0;
    $total_received = 0.0000;
    $total_orders_all = 0;
}

// Close the connection
$conn = null;
?>


<div class="container-cards">
<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-<?= $_SESSION['message_type'] ?> mt-3">
        <?= $_SESSION['message'] ?>
    </div>
    <?php unset($_SESSION['message']); ?> <!-- Clear message after display -->
<?php endif; ?>


  <!-- Card for Updating WhatsApp Number -->
<?php if (!$whatsapp_number_exists): ?>
<!-- Info Card -->
<div class="whatsapp-card mt-3">
    <div class="text-center">
        <h5 class="card-title">Update WhatsApp Number</h5>
        <p class="card-text">We noticed you haven't added a WhatsApp number. Please update it.</p>
        
        <!-- Button to trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#whatsappUpdateModal">
            Update Number
        </button>
    </div>
</div>

<?php endif; ?>
<style>
.cat-icon-filter-bar {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin: 0 0 15px 0;
  justify-content: flex-start;
}

/* Buttons auto-adjust width */
.catf-btn {
  flex: 1 1 180px;   /* grow/shrink but keep min width */
  min-width: 140px;  /* ensures not too small */
  max-width: 250px;  /* prevents oversized buttons */
  box-sizing: border-box;

  position: relative;
  background: #ffffff08;
  border: 1px solid #ffffff22;
  color: #fff;
  padding: 8px 16px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  backdrop-filter: blur(4px);
  transition: background 0.18s, border-color 0.18s, transform 0.15s;
  white-space: nowrap;
}

.catf-btn i { font-size: 16px; line-height: 1; }

.catf-btn:hover {
  background: #005a9f33;   /* light blue hover */
  border-color: #005a9f66;
}

.catf-btn.active {
  background: #005a9f;
  border-color: #0073cc;
}

.catf-btn.active:hover {
  background: #0073cc;
}

.catf-btn span { pointer-events:none; }

.catf-btn:focus {
  outline: 2px solid #005a9f;
  outline-offset: 2px;
}

/* Smaller text & padding on small screens */
@media (max-width:600px){
  .catf-btn {
    flex: 1 1 120px;
    font-size: 12px;
    padding: 6px 10px;
    border-radius: 8px;
  }
  .catf-btn i { font-size: 14px; }
}

  .announcement-container {
    margin: 20px auto !important;
    padding: 20px !important;
    /* background: linear-gradient(135deg, #051d2f, #0a3b5e) !important; */
    color: #ffffff !important;
    border: 0.5px solid #0e4c75 !important;
    border-radius: 15px !important;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.4) !important;
    text-align: center !important;
  }
  .announcement-heading {
    font-size: 1.4rem !important;
    font-weight: 800 !important;
    color: #00c4ff !important;
    text-transform: uppercase !important;
    margin-bottom: 20px !important;
  }
  .announcement-body {
    font-size: 1rem !important; /* Standard text size */
    font-weight: 500 !important; /* Slightly bold for emphasis */
    text-align: justify !important; /* Clean justified alignment */
    word-spacing: -1px !important; /* Reduce space between words */
    line-height: 1.5 !important; /* Maintain good line height for readability */
    padding: 10px !important; /* Add some inner spacing */
    margin: 0 !important; /* Remove extra margin */
}

  .emoji-highlight {
    font-size: 1.5rem !important;
    vertical-align: middle !important;
    margin-right: 10px !important;
  }
</style>

<?php if (get_option('new_order_text', '') != '') { ?>
<div class="col-sm-12">
  <div class="row">
    <div class="card announcement-container">
      <h1 class="announcement-heading">
       Announcement <span class="emoji-highlight">📢</span>
      </h1>
      <div class="announcement-body">
        <?= get_option('new_order_text', '') ?>
      </div>
    </div>
  </div>
</div>
<?php } ?>

    <!-- Welcome Card -->
    <div class="info-card">
    <div class="text-center">
    <h4 class="m-0 number">
    <?php 
        $first_name = get_field(USERS, ["id" => session('uid')], 'first_name'); 
        $last_name = get_field(USERS, ["id" => session('uid')], 'last_name'); 
        echo $first_name . " " . $last_name; 
    ?>
</h4>

        <small class="text-muted">❤️Welcome❤️</small>
    </div>
</div>




<!-- Modal for Updating WhatsApp Number -->
<div id="whatsappUpdateModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update WhatsApp Number</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Form that submits to the update controller -->
                <form id="whatsappUpdateForm" action="<?= cn("$module/update_whatsapp_number") ?>" method="POST">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">

                    <div class="form-group">
                        <label for="whatsapp_number">WhatsApp Number</label>
                        <input type="text" id="whatsapp_number" name="whatsapp_number" class="form-control" placeholder="+923XXXXXXXXX" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Account Balance / Total Amount Received Card -->
<div class="card0">
    <span class="stamp stamp-md bg-success-gradient text-white">
        <i class="fe fe-dollar-sign"></i>
    </span>
    <div class="ml-2 d-lg-block text-right">
        <?php if ($user_role === 'admin'): ?>
            <!-- Admin: Total Amount Received -->
            <h4 class="m-0 text-right number"><?= get_option('currency_symbol', "$") ?><?= number_format($total_received, 4) ?></h4>
            <small class="text-muted"><?= lang("total_received") ?></small>
        <?php else: ?>
            <!-- User: Account Balance -->
            <?php if ($balance == 0): ?>
                <h4 class="m-0 text-right number"><?= lang("Low Balance") ?></h4>
                <small class="text-muted">
                    <?= lang("Your balance is low. Please") ?>
                    <a href="<?= cn('add_funds') ?>" style="text-decoration:underline;" class="text-primary"><?= lang("add funds") ?></a>
                </small>
            <?php else: ?>
                <h4 class="m-0 text-right number"><?= get_option('currency_symbol', "$") ?><?= number_format($balance, 4) ?></h4>
                <small class="text-muted"><?= lang("account_balance") ?></small>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


<!-- Spent Balance / Total Users Card -->
<div class="card0">
    <span class="stamp stamp-md bg-info-gradient text-white">
        <i class="fe fe-dollar-sign"></i>
    </span>
    <div class="ml-2 d-lg-block text-right">
        <?php if ($user_role === 'admin'): ?>
            <!-- Admin: Total Users -->
            <h4 class="m-0 text-right number"><?= number_format($total_users) ?></h4>
            <small class="text-muted"><?= lang("total_users") ?></small>
        <?php else: ?>
            <!-- User: Spent Balance -->
            <h4 class="m-0 text-right number"><?= get_option('currency_symbol', "$") ?><?= number_format($total_spent, 4) ?></h4>
            <small class="text-muted"><?= lang("spent_balance") ?></small>
        <?php endif; ?>
    </div>
</div>

<!-- Total Orders / Current Order Card -->
<div class="card0">
    <span class="stamp stamp-md bg-warning-gradient text-white">
        <i class="fe fe-shopping-cart"></i>
    </span>
    <div class="ml-2 d-lg-block text-right">
        <?php if ($user_role === 'admin'): ?>
            <!-- Admin: Total Orders -->
            <h4 class="m-0 text-right number"><?= number_format($total_orders_all) ?></h4>
            <small class="text-muted"><?= lang("total_orders_all") ?></small>
        <?php else: ?>
            <!-- User: Current Order -->
            <h4 class="m-0 text-right number"><?= number_format($total_orders) ?></h4>
            <small class="text-muted"><?= lang("your_orders") ?></small>
        <?php endif; ?>
    </div>
</div>
</div>



<div class="row m-t-5">
  <div class="col-sm-12 col-sm-12">
    <div class="row">
	<div class="col-sm-7 col-lg-7 item">
     <div class="card">
      <div class="d-flex align-items-center">
        <div class="tabs-list">
          
        </div>
      </div>
      <div class="card-body">
        <div class="tab-content">
          <div id="new_order" class="tab-pane fade in active show">
            <form class="form actionForm" action="<?=cn($module."/ajax_add_order")?>" data-redirect="<?=cn($module."/order/add")?>" method="POST">

              <div class="row">
                <div class="">
                <div class="form-group">
                    <div id="category-icon-filters" class="cat-icon-filter-bar">
  <button type="button" class="catf-btn active" data-platform="all">
    <i class="fa fa-bars"></i><span>All</span>
  </button>
  <button type="button" class="catf-btn" data-platform="tiktok">
    <i class="fa-brands fa-tiktok"></i><span>TikTok</span>
  </button>
  <button type="button" class="catf-btn" data-platform="youtube">
    <i class="fa-brands fa-youtube"></i><span>Youtube</span>
  </button>
  <button type="button" class="catf-btn" data-platform="instagram">
    <i class="fa-brands fa-instagram"></i><span>Instagram</span>
  </button>
  <button type="button" class="catf-btn" data-platform="telegram">
    <i class="fa-brands fa-telegram"></i><span>Telegram</span>
  </button>
  <button type="button" class="catf-btn" data-platform="facebook">
    <i class="fa-brands fa-facebook"></i><span>Facebook</span>
  </button>
  <button type="button" class="catf-btn" data-platform="twitter">
    <i class="fa-brands fa-x-twitter"></i><span>Twitter</span>
  </button>
  <button type="button" class="catf-btn" data-platform="whatsapp">
    <i class="fa-brands fa-whatsapp"></i><span>Whatsapp</span>
  </button>
  <button type="button" class="catf-btn" data-platform="snapchat">
    <i class="fa-brands fa-snapchat"></i><span>Snapchat</span>
  </button>
  <button type="button" class="catf-btn" data-platform="linkedin">
    <i class="fa-brands fa-linkedin"></i><span>Linkedin</span>
  </button>
  <button type="button" class="catf-btn" data-platform="other">
    <i class="fa fa-plus"></i><span>Other</span>
  </button>
</div>
<div class="form-group category-select-wrapper">
  <label for="dropdowncategories"><?=lang("Category")?></label>
  <select id="dropdowncategories"
          name="category_id"
          class="form-control square ajaxChangeCategory"
          data-url="<?=cn($module."/get_services/")?>">
    <option value="" disabled selected hidden><?=lang("choose_a_category")?></option>
    <?php if (!empty($categories)):
      foreach ($categories as $c): ?>
        <option value="<?=$c->id?>"><?=$c->name?></option>
    <?php endforeach; endif; ?>
  </select>
</div>

<div class="form-group" id="result_onChange">
    <label><?=lang("order_service")?></label>
    <select id="dropdownservices" name="service_id" class="form-control square ajaxChangeService" data-url="<?=cn($module."/get_service/")?>">
        <option> <?=lang("choose_a_service")?></option>
        <?php
            if (!empty($services)) {
                $service_item_default = $services[0];
                foreach ($services as $key => $service) {
        ?>
        <option value="<?=$service->id?>"><?=$service->name?></option>
        <?php }}?>
    </select>
</div>




				<div id="order_resume">
                  <div class="row" id="result_onChangeService">
                    
					<div class="col-md-12 col-sm-12 col-xs-12">
                      <div class="form-group">
                        <input type="hidden" name="service_id" id="service_id" value="<?=(!empty($service_item_default->id))? $service_item_default->id :''?>">
                        <input class="form-control223344 square" name="service_name" type="text" readonly>
                      </div>
                    </div>   

                    <div class="col-md-12 col-sm-12 col-xs-12">
    <div class="form-group">
        <label for="userinput8"><?=lang("Description")?></label>
        <textarea 
            style="padding: 10px; 
                   min-height: 150px; 
                   max-height: 400px; 
                   overflow-y: scroll; 
                   border: 1px solid #ddd; 
                   border-radius: 5px; 
                   background: transparent;
                   font-size: 14px; 
                   color: #333; 
                   line-height: 1.5;
                   width: 100%; 
                   resize: none;" 
            name="service_desc" 
            class="form-control square" 
            readonly>
        </textarea>
    </div>
</div>

                    
					<table>
					  <thead>
						  <th style="background: none !important; border: none !important ;">
						    <div class="col-md-12  col-sm-12 col-xs-12">
							  <div class="form-group">
								<label><?=lang("minimum")?></label>
								<input class="form-control2233 square" name="service_min" type="text" readonly>
							  </div>
							</div>
						  </th>
						  
              <th style="background: none !important; border: none !important ;">
						    <div class="col-md-12  col-sm-12 col-xs-12">
							  <div class="form-group">
							    <label><?=lang("maximum")?></label>
								<input class="form-control2233 square" name="service_max" type="text" readonly>
							  </div>
							</div>
						  </th>
					  </thead>
					</table>
					
					<div class="col-md-12  col-sm-12 col-xs-4">
                      <div class="form-group">
                        <input class="form-control223344 square" name="service_price" type="text" readonly>
                      </div>
                    </div>

                  </div>
                </div>
				  
				<!-- Min/max on responsive d-md-none-->
                  <div class="row d-none">
                    <div class="col-md-4  col-sm-12 col-xs-12">
                      <div class="form-group">
                        <label><?=lang("minimum_amount")?></label>
                        <input class="form-control square" name="service_min" type="text" value="" readonly>
                      </div>
                    </div>

                    <div class="col-md-4  col-sm-12 col-xs-12">
                      <div class="form-group">
                        <label><?=lang("maximum_amount")?></label>
                        <input class="form-control square" name="service_max" type="text" value="" readonly>
                      </div>
                    </div>

                    <div class="col-md-4  col-sm-12 col-xs-12">
                      <div class="form-group">
                        <label><?=lang("price_per_1000")?></label>
                        <input class="form-control square" name="service_price" type="text" value="" readonly>
                      </div>
                    </div>
                  </div>
                  
                  <div class="form-group order-default-link">
                    <label><?=lang("Link")?></label>
                    <input class="form-control square" type="text" name="link" placeholder="https://" id="">
                  </div>

                  <div class="form-group order-default-quantity">
                    <label><?=lang("Quantity")?></label>
                    <input class="form-control square ajaxQuantity" name="quantity" type="number">
                  </div>
                  
                  <div class="form-group order-comments d-none">
                    <label for=""><?=lang("Comments")?> <?php lang('1_per_line')?></label>
                    <textarea  rows="10" name="comments" class="form-control square ajax_custom_comments"></textarea>
                  </div> 

                  <div class="form-group order-comments-custom-package d-none">
                    <label for=""><?=lang("Comments")?> <?php lang('1_per_line')?></label>
                    <textarea  rows="10" name="comments_custom_package" class="form-control square"></textarea>
                  </div>

                  <div class="form-group order-usernames d-none">
                    <label for=""><?=lang("Usernames")?></label>
                    <input type="text" class="form-control input-tags" name="usernames" value="usenameA,usenameB,usenameC,usenameD">
                  </div>

                  <div class="form-group order-usernames-custom d-none">
                    <label for=""><?=lang("Usernames")?> <?php lang('1_per_line')?></label>
                    <textarea  rows="10" name="usernames_custom" class="form-control square ajax_custom_lists"></textarea>
                  </div>

                  <div class="form-group order-hashtags d-none">
                    <label for=""><?=lang("hashtags_format_hashtag")?></label>
                    <input type="text" class="form-control input-tags" name="hashtags" value="#goodphoto,#love,#nice,#sunny">
                  </div>

                  <div class="form-group order-hashtag d-none">
                    <label for=""><?=lang("Hashtag")?> </label>
                    <input class="form-control square" type="text" name="hashtag">
                  </div>

                  <div class="form-group order-username d-none">
                    <label for=""><?=lang("Username")?></label>
                    <input class="form-control square" name="username" type="text">
                  </div>   
                  
                  <!-- Mentions Media Likers -->
                  <div class="form-group order-media d-none">
                    <label for=""><?=lang("Media_Url")?></label>
                    <input class="form-control square" name="media_url" type="link">
                  </div>

                  <!-- Subscriptions  -->
                  <div class="row order-subscriptions d-none">

                    <div class="col-md-6">
                      <div class="form-group">
                        <label><?=lang("Username")?></label>
                        <input class="form-control square" type="text" name="sub_username">
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label><?=lang("New_posts")?></label>
                        <input class="form-control square" type="number" placeholder="<?=lang("minimum_1_post")?>" name="sub_posts">
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label><?=lang("Quantity")?></label>
                        <input class="form-control square" type="number" name="sub_min" placeholder="<?=lang("min")?>">
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label>&nbsp;</label>
                        <input class="form-control square" type="number" name="sub_max" placeholder="<?=lang("max")?>">
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label><?=lang("Delay")?> (<?=lang("minutes")?>)</label>
                        <select name="sub_delay" class="form-control square">
                          <option value="0"><?=lang("")?><?=lang("No_delay")?></option>
                          <option value="5">5</option>
                          <option value="10">10</option>
                          <option value="15">15</option>
                          <option value="30">30</option>
                          <option value="60">60</option>
                          <option value="90">90</option>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <div class="form-group">
                        <label><?=lang("Expiry")?></label>
                        <div class="input-group">
                          <input type="text" class="form-control datepicker" name="sub_expiry" onkeydown="return false" name="expiry" placeholder="" id="expiry">
                          <span class="input-group-append">
                            <button class="btn btn-info" type="button" onclick="document.getElementById('expiry').value = ''"><i class="fe fe-trash-2"></i></button>
                          </span>
                        </div>
                      </div>
                    </div>

                  </div>
                  <?php
                    if (get_option("enable_drip_feed","") == 1) {
                  ?>
                  <div class="row drip-feed-option d-none">
                    <div class="col-md-12">
                      <div class="form-group">
                        <div class="form-label"><?=lang("dripfeed")?> 
                          <label class="custom-switch">
                            <span class="custom-switch-description m-r-20"><i class="fa fa-question-circle" data-toggle="popover" data-trigger="hover" data-placement="right" data-content="<?=lang("drip_feed_desc")?>" data-title="<?=lang("what_is_dripfeed")?>"></i></span>

                            <input type="checkbox" name="is_drip_feed" class="is_drip_feed custom-switch-input" data-toggle="collapse" data-target="#drip-feed" aria-expanded="false" aria-controls="drip-feed">
                            <span class="custom-switch-indicator"></span>
                          </label>
                        </div>
                      </div>

                      <div class="row collapse" id="drip-feed">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang("Runs")?></label>
                            <input class="form-control square ajaxDripFeedRuns" type="number" name="runs" value="<?=get_option("default_drip_feed_runs", "")?>">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label><?=lang("interval_in_minutes")?></label>
                            <select name="interval" class="form-control square">
                              <?php
                                for ($i = 1; $i <= 60; $i++) {
                                  if ($i%10 == 0) {
                              ?>
                              <option value="<?=$i?>" <?=(get_option("default_drip_feed_interval", "") == $i)? "selected" : ''?>><?=$i?></option>
                              <?php }} ?>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <label><?=lang("total_quantity")?></label>
                            <input class="form-control square" name="total_quantity" type="number" disabled>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php }?>
                  <div class="form-group" id="result_total_charge">
                    <input type="hidden" name="total_charge" value="0.00">
                    <input type="hidden" name="currency_symbol" value="<?=get_option("currency_symbol", "")?>">
                    <br>
                    <center><p class="btn btn-info2 total_charge"><?=lang("total_charge")?> <span class="charge_number">PKR 0</span></p></center>
                    
                    <?php
                      $user = $this->model->get("balance, custom_rate", 'general_users', ['id' => session('uid')]);
                      if ($user->custom_rate > 0 ) {
                    ?>
                    <p class="small text-muted"><?=lang("custom_rate")?>: <span class="charge_number"><?=$user->custom_rate?>%</span></p>
                    <?php }?>
                    <div class="alert alert-icon alert-danger d-none" role="alert">
                      <i class="fe fe-alert-triangle mr-2" aria-hidden="true"></i><?=lang("order_amount_exceeds_available_funds")?>
                    </div>
                  </div>
                  <br>
                  <div class="form-group">
                    <label class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" name="agree">
                      <span class="custom-control-label text-uppercase"><?=lang("yes_i_have_confirmed_the_order")?></span>
                    </label>
                  </div>

                  <div class="form-actions left">
                  <button type="submit" class="btn round btn-primary btn-min-width mr-1 mb-1" style="border-radius: 5px !important; background-color: #04a9f4; color: #fff; min-width: 120px; margin-right: 5px; margin-top: 15px; margin-bottom: 5px;">
                  <?=lang("place_order")?>
                  </button>
                  </div>


                  </div>
                </div>
              </div>
            </form>
          </div>
          <div id="rules" class="tab-pane fade">
            <form class="form actionForm" action="<?=cn($module."/ajax_mass_order")?>" data-redirect="<?=cn($module."/log")?>" method="POST">
              <div class="x_content row">
                <?=get_option('order_rules','')?>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>


 

  
  <div class="col-sm-5 col-lg-5 item">
    <div class="card">
      <div class=" d-flex align-items-center">
        <div class="tabs-list">
          <ul class="nav nav-tabs">
            <li class="">
              <h4><strong>Updates</strong></h4>
              <!-- Embed YouTube Shorts after the button -->
<div class="mt-3" style="text-align: center;">
  <div style="
    display: inline-block;
    padding: 4px; /* Thickness of border */
    border-radius: 24px; /* Rounded corners */
    background: linear-gradient(to right, #05cbfd 0%, #203d9d 100%);
  ">
    <iframe
      width="315"
      height="570"
      src="https://www.youtube.com/embed/Y_nv63RWNXQ"
      title="YouTube Shorts"
      frameborder="0"
      allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
      allowfullscreen
      style="
        border-radius: 20px; /* Inner radius, slightly less than wrapper */
        background: #fff; /* Optional: makes inner background white */
        border: none; /* Remove default border */
        display: block;
      "
    ></iframe>
  </div>
</div>
            </li>
          </ul>
        </div>
      </div>
      <div class="card-body" style="max-height: calc(80vh - 180px); overflow-y: scroll;">
        <div class="tab-content">
          <div id="Manual_Payments" class="tab-pane fade in active show">
            <div class="content">
              <?=get_option('updates_text')?>
			</div> 
		   </div>
        </div>
      </div>
    </div>
  </div>
  
    </div>
  </div>
</div>




<style>
  .page-title h1{
    margin-bottom: 5px; }
    .page-title .border-line {
      height: 5px;
      width: 250px;
      background: #eca28d;
      background: -webkit-linear-gradient(45deg, #eca28d, #f98c6b) !important;
      background: -moz- oldlinear-gradient(45deg, #eca28d, #f98c6b) !important;
      background: -o-linear-gradient(45deg, #eca28d, #f98c6b) !important;
      background: linear-gradient(45deg, #eca28d, #f98c6b) !important;
      position: relative;
      border-radius: 30px; }
    .page-title .border-line::before {
      content: '';
      position: absolute;
      left: 0;
      top: -2.7px;
      height: 10px;
      width: 10px;
      border-radius: 50%;
      background: #fa6d7e;
      -webkit-animation-duration: 6s;
      animation-duration: 6s;
      -webkit-animation-timing-function: linear;
      animation-timing-function: linear;
      -webkit-animation-iteration-count: infinite;
      animation-iteration-count: infinite;
      -webkit-animation-name: moveIcon;
      animation-name: moveIcon; }

  @-webkit-keyframes moveIcon {
    from {
      -webkit-transform: translateX(0);
    }
    to { 
      -webkit-transform: translateX(250px);
    }
  }
  
.text-lbl{
  margin-top: 0px;
  margin-bottom: 2px;
  font-weight: 550;
  color: #fff;
}

label{
        color: #fff;
        
}

  
  

</style>






<script>
  $(function(){
    $('.datepicker').datepicker({
      format: "dd/mm/yyyy",
      autoclose: true,
      startDate: truncateDate(new Date())
    });
    $(".datepicker").datepicker().datepicker("setDate", new Date());

    function truncateDate(date) {
      return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    $('.input-tags').selectize({
        delimiter: ',',
        persist: false,
        create: function (input) {
            return {
                value: input,
                text: input
            }
        }
    });
  });
</script>


<script>
  $(document).ready(function() {
    // Store data on form submission
    $('.actionForm').on('submit', function() {
      var orderData = {
        service_name: $('select[name="service_id"] option:selected').text(), // Get the text of the selected service
        link: $('input[name="link"]').val(),
        quantity: $('input[name="quantity"]').val(),
        total_charge: $('input[name="total_charge"]').val() // Get the total charge from the hidden input
      };
      
      // Store the data in localStorage
      localStorage.setItem('orderData', JSON.stringify(orderData));
    });

    // Function to dynamically shorten the service name
    function getShortServiceName(serviceName) {
      // Convert the full service name to lowercase for easier matching
      const lowerServiceName = serviceName.toLowerCase();
      
      // Define potential platform keywords and service actions
      const platforms = ['tiktok', 'instagram', 'youtube', 'facebook', 'twitter'];
      const actions = ['views', 'likes', 'followers', 'subscribers', 'comments', 'shares'];
      
      // Initialize an empty shortened service name
      let shortServiceName = '';
      
      // Find matching platform and action dynamically
      const foundPlatform = platforms.find(platform => lowerServiceName.includes(platform));
      const foundAction = actions.find(action => lowerServiceName.includes(action));

      // Construct the shortened name dynamically
      if (foundPlatform && foundAction) {
        shortServiceName = `${foundPlatform.charAt(0).toUpperCase() + foundPlatform.slice(1)} ${foundAction.charAt(0).toUpperCase() + foundAction.slice(1)}`;
      } else {
        // If no match found, return the original name
        shortServiceName = serviceName;
      }

      return shortServiceName;
    }
    
    // Retrieve and show data in the modal on page load
    var savedOrderData = localStorage.getItem('orderData');
    if (savedOrderData) {
        savedOrderData = JSON.parse(savedOrderData);
        // Apply the shortening function
        var shortServiceName = getShortServiceName(savedOrderData.service_name);
        
        
        // Create a summary content
        var summaryContent = `
  <div class="order-summary p-4" style="background-color: ; color: #ecf0f1; animation: fadeIn 1s ease-in-out;">
    <div class="text-center mb-4">
      <i class="fa fa-check-circle bounce-icon" style="font-size: 60px; color: #2ecc71; animation: bounce 1.5s infinite;"></i>
    </div>
    <h5 class="text-center mb-3" style="color: #ecf0f1; font-size: 24px;">Order Confirmation</h5>
    <div class="order-details">
      <p><i class="fa fa-bell" style="color: #2ecc71; margin-right: 8px;"></i><strong>Service Name:</strong> <span style="color: #bdc3c7;">${shortServiceName}</span></p>
      <p><i class="fa fa-link" style="color: #2ecc71; margin-right: 8px;"></i><strong>Link:</strong> <span style="color: #bdc3c7;">${savedOrderData.link}</span></p>
      <p><i class="fa fa-long-arrow-up" aria-hidden="true" style="color: #2ecc71; margin-right: 8px;"></i><strong>Quantity:</strong> <span style="color: #bdc3c7;">${savedOrderData.quantity}</span></p>
      <p><i class="fa fa-usd" style="color: #2ecc71; margin-right: 8px;"></i><strong>Total Charge:</strong> <span style="color: #bdc3c7;">${savedOrderData.total_charge} PKR</span></p>
    </div>
  </div>

  <!-- Add CSS for animations -->
  <style>
    /* Fade-in animation */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Bounce animation for icon */
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-10px);
      }
      60% {
        transform: translateY(-5px);
      }
    }

    /* Bounce effect class */
    .bounce-icon {
      animation: bounce 2s infinite;
    }
  </style>
`;

        // Update the modal body with the summary content
        $('#orderSummary').html(summaryContent); // Use .html() to set the summary

        // Show the modal
        $('#orderConfirmationModal').modal('show'); 

        // Clear the data from local storage when the modal is closed
        $('#closeModalButton').on('click', function() {
            localStorage.removeItem('orderData'); // Clear the saved order data
        });
    }
  });
</script>

<!--<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">-->

<!-- Order Confirmation Modal -->
<div class="modal fade" id="orderConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="orderConfirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderConfirmationModalLabel">Order Confirmation</h5>
        <!-- <button type="button" class="close" data-dismiss="modal" aria-label="Close"> -->
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
      </div>
      <div class="modal-body">
        <div id="orderSummary"></div> <!-- The table will be inserted here -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="closeModalButton">Close</button>
      </div>
    </div>
  </div>
</div>

<?php
$show_vertical_image_modal = get_option('show_vertical_image_modal', 0);
$vertical_image_modal_url = get_option('vertical_image_modal_url', 'https://i.ibb.co/8LZvrpDK/file-000000006374622f80e6350155d31b37.png');
?>

<?php if ($show_vertical_image_modal && $vertical_image_modal_url): ?>
<!-- Minimal Vertical Image Modal -->
<style>
/* Keep modal content minimal and centered */
#verticalImageModal .modal-content {
  background: transparent;
  border: none;
  box-shadow: none;
  text-align: center;
  position: relative;
  padding: 0;
}

/* Make the modal dialog expand up to 95% of viewport width */
#verticalImageModal .modal-dialog {
  max-width: 95vw;
  width: auto;
  margin: 0 auto;
}

/* Image wrapper ensures vertical centering and prevents huge overflow */
#verticalImageModal .image-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 60vh; /* fallback height */
  width: 100%;
  padding: 12px;
}

/* Image scales to 95% of the viewport while respecting aspect ratio.
   max-height prevents going beyond viewport height. */
#verticalImageModal img {
  border-radius: 18px;
  width: 95vw;          /* primary size: 95% of viewport width */
  max-width: 100%;       /* ensure it doesn't exceed container */
  max-height: 95vh;     /* do not exceed viewport height */
  height: auto;         /* keep natural aspect ratio */
  object-fit: cover;
  box-shadow: 0 2px 24px rgba(0,0,0,0.18);
  display: block;
  margin: 0 auto;
}

/* Custom close button pinned to top-right with its own class */
.vertical-modal-close {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 1200;
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: none;
  background: rgba(0,0,0,0.6);
  color: #fff;
  font-size: 20px;
  line-height: 1;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  box-shadow: 0 4px 12px rgba(0,0,0,0.35);
  transition: transform .08s ease, background .08s ease;
  outline: none;
}

/* Hover / focus states */
.vertical-modal-close:hover,
.vertical-modal-close:focus {
  transform: scale(1.04);
  background: rgba(0,0,0,0.75);
  color: #fff;
}

/* Optional: slightly larger hit area on small screens */
@media (max-width: 480px) {
  .vertical-modal-close {
    width: 48px;
    height: 48px;
    top: 10px;
    right: 10px;
  }
}
</style>

<div class="modal fade" id="verticalImageModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content p-0">
      <!-- custom close button with accessible label -->
      <button type="button"
              class="vertical-modal-close"
              data-dismiss="modal"
              aria-label="Close vertical image modal">
        <!-- using a simple multiplication sign for the icon -->
        &times;
      </button>

      <div class="image-wrapper">
        <img src="<?= htmlspecialchars($vertical_image_modal_url) ?>" alt="Vertical Image" />
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function(){
    $('#verticalImageModal').modal('show');
  });
</script>
<?php endif; ?>

<script>
(function($){
  /* =========================================================
     PLATFORM ICON + SERVICE/ CATEGORY DISPLAY HELPERS
  ========================================================== */
  function pickPlatformIcon(text){
    if (!text) return '';
    var t = text.toLowerCase();

    // Image (gif/png) variants first
    if (t.includes('facebook'))  return 'img:https://storage.perfectcdn.com/etopvh/xk5ab1173935x41z.gif';
    if (t.includes('instagram')) return 'img:https://storage.perfectcdn.com/etopvh/r2726iff1gsgb78r.gif';
    if (t.includes('whatsapp'))  return 'img:https://i.ibb.co/846d9Whj/372108180-WHATSAPP-ICON-400.gif';
    if (t.includes('tiktok'))    return 'img:https://storage.perfectcdn.com/etopvh/p7pol1se08k6yc2x.gif';
    if (t.includes('youtube'))   return 'img:https://storage.perfectcdn.com/etopvh/duea6r011zfl9fo8.gif';
    if (t.includes('twitter') || t.match(/\bx\b/)) return 'img:https://storage.perfectcdn.com/etopvh/8d1btd44mgx8geie.gif';
    if (t.includes('snack'))     return 'img:https://i.ibb.co/rRzSFYtC/unnamed.png';
    if (t.includes('likee'))     return 'img:https://i.ibb.co/rRzSFYtC/unnamed.png';
    if (t.includes('linkedin'))  return 'img:https://i.ibb.co/KcX4v9Fb/372102050-LINKEDIN-ICON-TRANSPARENT-1080.gif';
    if (t.includes('snapchat'))  return 'img:https://i.ibb.co/23F0G4BY/images-7.jpg';

    // FontAwesome fallbacks
    if (t.includes('youtube'))   return 'fa-brands fa-youtube';
    if (t.includes('tiktok'))    return 'fa-brands fa-tiktok';
    if (t.includes('twitch'))    return 'fa-brands fa-twitch';
    if (t.includes('vimeo'))     return 'fa-brands fa-vimeo';

    if (t.includes('instagram')) return 'fa-brands fa-instagram';
    if (t.includes('twitter') || t.match(/\bx\b/)) return 'fa-brands fa-x-twitter';
    if (t.includes('linkedin'))  return 'fa-brands fa-linkedin';
    if (t.includes('snapchat'))  return 'fa-brands fa-snapchat';
    if (t.includes('pinterest')) return 'fa-brands fa-pinterest';
    if (t.includes('reddit'))    return 'fa-brands fa-reddit';
    if (t.includes('tumblr'))    return 'fa-brands fa-tumblr';
    if (t.includes('discord'))   return 'fa-brands fa-discord';
    if (t.includes('telegram'))  return 'fa-brands fa-telegram';

    if (t.includes('rental') || t.includes('rent')) return 'fa-solid fa-users-rectangle';
    if (t.includes('whatsapp')) return 'fa-brands fa-whatsapp';
    if (t.includes('messenger')) return 'fa-brands fa-facebook-messenger';
    if (t.includes('skype'))     return 'fa-brands fa-skype';
    if (t.includes('viber'))     return 'fa-brands fa-viber';
    if (t.includes('line'))      return 'fa-solid fa-comment';

    if (t.includes('slack'))     return 'fa-brands fa-slack';
    if (t.includes('teams'))     return 'fa-brands fa-microsoft';
    if (t.includes('zoom'))      return 'fa-solid fa-video';

    if (t.includes('wechat'))    return 'fa-brands fa-weixin';
    if (t.includes('weibo'))     return 'fa-brands fa-weibo';
    if (t.includes('qq'))        return 'fa-brands fa-qq';

    if (t.includes('spotify'))   return 'fa-brands fa-spotify';
    if (t.includes('soundcloud')) return 'fa-brands fa-soundcloud';
    if (t.includes('github'))    return 'fa-brands fa-github';
    if (t.includes('behance'))   return 'fa-brands fa-behance';
    if (t.includes('dribbble'))  return 'fa-brands fa-dribbble';
    if (t.includes('medium'))    return 'fa-brands fa-medium';
    if (t.includes('quora'))     return 'fa-brands fa-quora';
    if (t.includes('flickr'))    return 'fa-brands fa-flickr';
    if (t.includes('foursquare')) return 'fa-brands fa-foursquare';

    if (t.includes('tinder') || t.includes('bumble')) return 'fa-solid fa-heart';

    if (t.includes('vk') || t.includes('vkontakte')) return 'fa-brands fa-vk';
    if (t.includes('odnoklassniki')) return 'fa-brands fa-odnoklassniki';
    if (t.includes('xing'))      return 'fa-brands fa-xing';

    if (t.includes('live') || t.includes('stream')) return 'fa-solid fa-broadcast-tower';
    if (t.includes('video'))     return 'fa-solid fa-video';
    if (t.includes('photo') || t.includes('image')) return 'fa-solid fa-image';
    if (t.includes('music') || t.includes('audio')) return 'fa-solid fa-music';
    if (t.includes('podcast'))   return 'fa-solid fa-podcast';
    if (t.includes('blog'))      return 'fa-solid fa-blog';
    if (t.includes('news'))      return 'fa-solid fa-newspaper';
    if (t.includes('shopping') || t.includes('store')) return 'fa-solid fa-shopping-cart';
    if (t.includes('game') || t.includes('gaming')) return 'fa-solid fa-gamepad';

    return '';
  }

  function renderIconHtml(iconToken){
    if (!iconToken) return '';
    if (iconToken.indexOf && iconToken.indexOf('img:') === 0) {
      var url = iconToken.substring(4);
      return '<img src="' + url + '" alt="platform" class="cat-icon-img" style="width:18px;height:18px;vertical-align:middle;margin-right:8px;border-radius:3px;">';
    }
    return '<i class="' + iconToken + '" aria-hidden="true" style="margin-right:8px;"></i>';
  }

  /* =========================================================
     SELECT2 TEMPLATES
  ========================================================== */
  function formatService(option) {
    if (!option.id) return option.text;
    var $opt = $(option.element);
    var serviceId = $opt.val();
    var name = $opt.data('name') || option.text;
    var rate = $opt.data('rate');
    var min  = $opt.data('min');
    var max  = $opt.data('max');
    var drip = ($opt.data('dripfeed') == 1);
    var meta = [];
    if (rate) meta.push('PKR: ' + rate);
    if (min)  meta.push('Min: ' + min);
    if (max)  meta.push('Max: ' + max);
    if (drip) meta.push('Drip');
    var iconToken = pickPlatformIcon(name);
    var iconHtml = renderIconHtml(iconToken);
    return $(
      '<div class="svc-item">'+
        iconHtml +
        '<strong>ID: '+ serviceId + ' - ' + $('<span>').text(name).html() +'</strong><br>'+
        '<span class="svc-meta">'+ meta.join(' | ') +'</span>'+
      '</div>'
    );
  }

  function formatServiceSelection(option) {
    if (!option.id) return option.text;
    var $opt = $(option.element);
    var serviceId = $opt.val();
    var name = $opt.data('name') || option.text;
    var rate = $opt.data('rate');
    var iconToken = pickPlatformIcon(name);
    var iconHtml = renderIconHtml(iconToken);
    var label = 'ID: ' + serviceId + ' - ' + name + (rate ? ' (' + rate + ')' : '');
    return $(
      '<span class="svc-sel">'+
        iconHtml +
        $('<span>').text(label).html()+
      '</span>'
    );
  }

  function categoryTemplate(option){
    if (!option.id) return option.text;
    var txt = option.text || '';
    var iconToken = pickPlatformIcon(txt);
    var iconHtml = renderIconHtml(iconToken);
    return $(
      '<span class="cat-opt">'+
        iconHtml +
        $('<span>').text(txt).html()+
      '</span>'
    );
  }

  /* =========================================================
     INIT SELECT2
  ========================================================== */
  function initCategorySelect() {
    var $cat = $('#dropdowncategories');
    if (!$cat.length) return;
    if ($cat.hasClass('select2-hidden-accessible')) $cat.select2('destroy');
    $cat.select2({
      width: '100%',
      templateResult: categoryTemplate,
      templateSelection: categoryTemplate,
      escapeMarkup: function(m){ return m; },
      dropdownParent: $cat.closest('.category-select-wrapper').length ?
                      $cat.closest('.category-select-wrapper') : $cat.parent(),
      dropdownCssClass: 'custom-dropdown-height',
      dropdownAutoWidth: false
    }).on('change', function(){
      loadServicesForCategory($(this).val());
    });
  }

  function initServiceSelect(ctx) {
    var $svc = (ctx) ? $(ctx).find('#dropdownservices') : $('#dropdownservices');
    if (!$svc.length) return;
    if ($svc.hasClass('select2-hidden-accessible')) $svc.select2('destroy');
    $svc.select2({
      width: '100%',
      dropdownParent: $svc.closest('.service-select-wrapper').length ?
                      $svc.closest('.service-select-wrapper') : $svc.parent(),
      placeholder: 'Choose a service',
      templateResult: formatService,
      templateSelection: formatServiceSelection,
      escapeMarkup: function(m){ return m; },
      allowClear: false,
      dropdownCssClass: 'custom-dropdown-height',
      dropdownAutoWidth: false,
      minimumResultsForSearch: 5
    }).on('change', function(){
      fetchServiceDetails($(this).val(), $(this).data('url'), $(this));
    });
    requestAnimationFrame(function(){
      $svc.next('.select2').css('width','100%');
    });
  }

  /* =========================================================
     AJAX LOAD SERVICES
  ========================================================== */
  function loadServicesForCategory(categoryId){
    if (!categoryId) {
      $('#result_onChange').html(
        '<div class="service-select-wrapper">'+
          '<select id="dropdownservices" name="service_id" class="form-control square" data-url="<?=cn($module."/get_service/")?>">'+
            '<option value="">Choose a service</option>'+
          '</select>'+
        '</div>'
      );
      initServiceSelect('#result_onChange');
      resetServiceResume();
      return;
    }
    var url = $('#dropdowncategories').data('url');
    $.ajax({
      type: 'POST',
      url: url + categoryId,
      data: { token: (typeof token !== 'undefined') ? token : '' },
      success: function(html){
        $('#result_onChange').html(html);
        initServiceSelect('#result_onChange');
        resetServiceResume();
        // Auto-select first service
        setTimeout(function() {
          var $serviceDropdown = $('#result_onChange').find('#dropdownservices');
            if ($serviceDropdown.length) {
              var firstServiceOption = $serviceDropdown.find('option[value!=""]').first();
              if (firstServiceOption.length && firstServiceOption.val()) {
                $serviceDropdown.val(firstServiceOption.val()).trigger('change');
                var serviceId = firstServiceOption.val();
                var baseUrl = $serviceDropdown.data('url');
                if (serviceId && baseUrl) {
                  fetchServiceDetails(serviceId, baseUrl, $serviceDropdown);
                }
              }
            }
        }, 100);
      },
      error: function(xhr){
        console.error('Failed to load services', xhr.status, xhr.responseText);
        alert('Could not load services for this category.');
      }
    });
  }

  function fetchServiceDetails(serviceId, baseUrl, $select){
    if (!serviceId) { resetServiceResume(); return; }
    $.ajax({
      type: 'POST',
      url: baseUrl + serviceId,
      data: { token: (typeof token !== 'undefined') ? token : '' },
      success: function(fragment){
        $('#result_onChangeService').html(fragment);
        var price = $('#order_resume input[name=service_price]').val();
        var min   = $('#order_resume input[name=service_min]').val();
        var max   = $('#order_resume input[name=service_max]').val();
        if (price) $('#new_order input[name=service_price]').val(price);
        if (min)   $('#new_order input[name=service_min]').val(min);
        if (max)   $('#new_order input[name=service_max]').val(max);
        $('#service_id').val(serviceId);
        var $opt = $select.find('option:selected');
        applyServiceTypeUI($opt.data('type'), $opt.data('dripfeed'));
      },
      error: function(xhr){
        console.error('Failed to fetch service details', xhr.status, xhr.responseText);
        alert('Failed to fetch service details.');
      }
    });
  }

  function resetServiceResume(){
    $('#order_resume input[name=service_name]').val('');
    $('#order_resume input[name=service_min]').val('');
    $('#order_resume input[name=service_max]').val('');
    $('#order_resume input[name=service_price]').val('');
    $('#order_resume textarea[name=service_desc]').val('');
    $('#service_id').val('');
    $('#result_onChangeService').html('');
  }

  function applyServiceTypeUI(type, drip){
    if (drip == 1) {
      $("#new_order .drip-feed-option").removeClass("d-none");
    } else {
      $("#new_order .drip-feed-option").addClass("d-none");
    }
  }

  /* =========================================================
     CATEGORY ICON FILTER BAR (NEW)
  ========================================================== */
  var originalCategoryOptions = [];
  var categoryIndexed = false;

  function detectPlatform(txt){
    if(!txt) return 'other';
    var t = txt.toLowerCase();
    if (t.includes('tiktok')) return 'tiktok';
    if (t.includes('youtube') || t.includes('yt ')) return 'youtube';
    if (t.includes('insta')) return 'instagram';
    if (t.includes('telegram') || t.includes('tg ')) return 'telegram';
    if (t.includes('facebook') || t.includes('fb ')) return 'facebook';
    if (t.includes('twitter') || t.includes(' x ') || /\bx\b/.test(t)) return 'twitter';
    if (t.includes('whatsapp') || t.includes('wa ')) return 'whatsapp';
    if (t.includes('snap')) return 'snapchat';
    if (t.includes('linked')) return 'linkedin';
    return 'other';
  }

  function indexCategories(){
    if (categoryIndexed) return;
    var $cat = $('#dropdowncategories');
    originalCategoryOptions = [];
    $cat.find('option').each(function(){
      var $o = $(this);
      var val = $o.attr('value');
      if (!val) return; // skip placeholder
      var label = $o.text();
      originalCategoryOptions.push({
        value: val,
        text: label,
        platform: detectPlatform(label)
      });
    });
    categoryIndexed = true;
  }

  function rebuildCategorySelect(platform){
    var $cat = $('#dropdowncategories');
    var dataUrl = $cat.data('url'); // keep data-url
    var placeholderText = '<?=lang("choose_a_category")?>';

    // Keep current value to avoid flicker (optional)
    var oldVal = $cat.val();

    // Rebuild
    $cat.empty();
    $cat.append('<option value="" disabled selected hidden>'+placeholderText+'</option>');

    var filtered = [];
    if (platform === 'all') {
      filtered = originalCategoryOptions;
    } else if (platform === 'other') {
      filtered = originalCategoryOptions.filter(o => o.platform === 'other');
    } else {
      filtered = originalCategoryOptions.filter(o => o.platform === platform);
    }

    filtered.forEach(function(o){
      $cat.append('<option value="'+o.value+'">'+o.text+'</option>');
    });

    // Restore data-url
    if (dataUrl) $cat.attr('data-url', dataUrl);

    // Re-init
    if ($cat.hasClass('select2-hidden-accessible')) {
      $cat.select2('destroy');
    }
    initCategorySelect();

    // Auto select first in filtered (remove if you don't want this)
    if (filtered.length){
      $cat.val(filtered[0].value).trigger('change');
    }
  }

  function attachFilterButtons(){
    var $bar = $('#category-icon-filters');
    if (!$bar.length) return;
    $bar.on('click', '.catf-btn', function(){
      var $btn = $(this);
      if ($btn.hasClass('active')) return;
      $bar.find('.catf-btn').removeClass('active');
      $btn.addClass('active');
      indexCategories();
      var plat = $btn.data('platform');
      rebuildCategorySelect(plat);
    });
  }

  /* =========================================================
     BOOT
  ========================================================== */
  $(function(){
    initCategorySelect();
    initServiceSelect();

    var pre = $('#dropdownservices').val();
    if (pre) {
      fetchServiceDetails(pre, $('#dropdownservices').data('url'), $('#dropdownservices'));
    }

    // Delay indexing to allow initial Select2 setup
    setTimeout(function(){
      indexCategories();
      attachFilterButtons();
    }, 150);
  });

})(jQuery);
</script>
