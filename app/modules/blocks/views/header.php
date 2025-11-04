<style>
  .search-box input.form-control{
    margin: -1px;
  }
  .search-box select.form-control{
    border-radius: 0px;
    border: 1px solid #fff;
  }
</style>
 <!--Not Sidenav-->
    
<div class="top-header">
  <div class="show-btn" onclick="openNav()" style="font-size: 45px; margin: 20px 0 0 20px; cursor: pointer; display: inline-block; position: absolute; color: #fff; z-index: 100">
    <span class="header-toggler-icon"></span>
  </div>
  
  
        <?php
          if (session('uid_tmp')) {
        ?>
        <div class="notifcation m-r-10" style="font-size: 25px; margin: 18px 0 0 310px; cursor: pointer; display: inline-block; position: absolute; color: #fff; z-index: 100">
          <a href="<?=cn("blocks/back_to_admin")?>" data-toggle="tooltip" data-placement="bottom" title="<?=lang('Back_to_Admin')?>" class="text-white ajaxBackToAdmin">
            <i class="fe fe-log-out"></i>
          </a>
        </div>
        <?php } ?>
  
  
        <div class="card-title text-center" data-aos="fade-down" data-aos-easing="ease-in" data-aos-delay="1000">
          <div class="site-logo">
            <a href="<?=cn('order/add')?>">
              <img src="<?=get_option('website_logo_white', BASE."assets/images/logo-white.png")?>" alt="website-logo" style="max-height: 40px; margin-top:15px;">
            </a>
          </div>
        </div>
        
        
        
</div>

<div id="overlay" onclick="closeNav()"></div>
  <div id="closeBtn" onclick="closeNav()">&times;</div>
    <div class="sidenav" id="mySidenav">
      <div class="sidenavHeader" style="color: #fff;">
        <h4><?=lang("Hi")?>, <span class="text-uppercase"><?php _echo(get_field(USERS, ["id" => session('uid')], 'first_name'))?></span></h4>
          <h6>
                <?php
                  // !get_role("admin")
                  if (!get_role("admin")) {
                    $balance = get_field(USERS, ["id" => session('uid')], 'balance');

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
                    if (empty($balance) || $balance == 0) {
                      $balance = 0.0000;
                    }else{
                      $balance = currency_format($balance,  get_option('currency_decimal', 2), $decimalpoint, $separator);
                    }
                    
                    // Get current currency for display
                    $current_currency = get_current_currency();
                ?>
                <?=lang("Balance")?>: <?=$current_currency['symbol']?><?=$balance?>
                <?php }else{?> 
                  <?=lang("Admin_account")?>
                <?php }?> 
              </h6>
              
              <?php if (!get_role("admin")) { 
                // Currency selector for non-admin users
                $active_currencies = get_active_currencies();
                $current_currency = get_current_currency();
              ?>
              <div class="currency-selector" style="margin-top: 10px;">
                <select id="currencySelector" class="form-control form-control-sm" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.2);">
                  <?php foreach ($active_currencies as $curr) { ?>
                    <option value="<?=$curr['code']?>" <?=($curr['code'] === $current_currency['code']) ? 'selected' : ''?>>
                      <?=$curr['code']?> - <?=$curr['name']?>
                    </option>
                  <?php } ?>
                </select>
              </div>
              <?php } ?>
        </div>
      <!--Below SideNavHeader-->
      <div id="main-container">
        <ul class="nav-tabs">
            
        <?php
// Start the session
session_start();
$user_id = $_SESSION['uid']; // Assuming user ID is stored in session

// Check if the user is an admin
if (!function_exists('get_role')) {
    // Only declare the function if it doesn't exist
    function get_role($role) {
        $user_roles = $_SESSION['roles']; // Assuming roles are stored in the session
        return in_array($role, $user_roles); // Check if the role exists in the user's roles
    }
}
?>

<!-- Sidebar link for Dashboard -->
<?php if (get_role('admin')): // Check if user is an admin ?>
    <a style=" margin-top: 2px;" href="<?= cn('statistics') ?>" class="nav-link <?=(segment(1) == 'statistics') ? "active" : ""?>">
        <div class="nav-item title" class="sidenavContent">
            <i class="fe fe-bar-chart-2"></i><?= lang("Dashboard") ?>
        </div>
    </a>
<?php endif; ?>

          
<a href="<?=cn('order/add')?>" class="nav-link <?=(segment(1) == 'order' && segment(2) == 'add') ? "active" : ""?>" style="margin-top: 5px;">
    <div class="nav-item" class="sidenavContent">
        <i class="fe fe-shopping-cart"></i><?= lang("New_order") ?>
    </div>
</a>

          
          <a href="<?=cn('order/log')?>" class="nav-link <?=(segment(1) == 'order' && segment(2) == 'log')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-shopping-cart"></i></i><?=lang("Orders")?></div>
          </a>
          
          <a href="<?=cn('refill/log')?>" class="nav-link <?=(segment(1) == 'order' && segment(2) == 'refill')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-recycle"></i></i><?=lang("Refill")?></div>
          </a>
          
          <?php
            if (get_role("admin") || get_role("supporter")) {
          ?>
          
          <a href="<?=cn('category')?>" class="nav-link <?=(segment(1) == 'category')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-table"></i><?=lang("Category")?></div>
          </a>
          <?php }?>

          <a href="<?=cn('services')?>" class="nav-link <?=(segment(1) == 'services')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fe fe-list"></i><?=lang('Services')?></div>
          </a>
          
          <?php
            if (get_role("user")) {
          ?>
          <a href="<?=cn('add_funds')?>" class="nav-link <?=(segment(1) == 'add_funds')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-money"></i><?=lang("Add_funds")?></div>
          </a>
          <?php }?>
          
          <?php
            if (get_role("admin")) {
          ?>
          <a href="<?=cn('add_funds')?>" class="nav-link <?=(segment(1) == 'add_funds')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-money"></i><?=lang("Add_funds")?></div>
          </a>
          <?php }?>
          
          <?php 
            if (get_option('enable_api_tab') && !get_role("admin")) {
          ?>      
          <a href="<?=cn('api/docs')?>" class="nav-link <?=(segment(2) == 'docs')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fe fe-share-2"></i><?=lang("API")?></div>
          </a>
          <?php }?>
          
          <?php
            if (get_role("user")) {
          ?>   
           
          <a href="<?=cn('tickets')?>" class="nav-link <?=(segment(1) == 'tickets')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-comments-o"></i><?=lang("Tickets")?>&nbsp;&nbsp;&nbsp;&nbsp;<span class="badge badge-info"><?=$total_unread_tickets?></span></div>
          </a>
          
          
          <?php }else{?>
          <a href="<?=cn('tickets')?>" class="nav-link <?=(segment(1) == 'tickets') ? "active": ""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-comments-o"></i><?=lang("tickets")?>&nbsp;&nbsp;&nbsp;&nbsp;<span class="badge badge-info"><?=$total_unread_tickets?></span></div>
          </a>
          <?php } ?>
          
          <?php if(get_option("enable_affiliate") == "1"){?>
          <a href="<?=cn('affiliate')?>" class="nav-link <?=(segment(1) == 'affiliate')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-money"></i><?=lang("Affiliate")?></div>
          </a>
          <?php }?>
          
          <?php if(get_option("is_childpanel_status") == "1"){?>
          <a href="<?=cn('childpanel/add')?>" class="nav-link <?=(segment(1) == 'childpanel')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-child"></i><?=lang("Child_Panel")?></div>
          </a>
          <?php }?>
          
          <a href="<?=cn('transactions')?>" class="nav-link <?=(segment(1) == 'transactions')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fe fe-calendar"></i><?=lang("Transaction_logs")?></div>
          </a>
          
          
          <?php if(get_role("admin") || get_role("supporter")){
            $user_manager = array(
              'users',
              'subscribers',
              'add_funds',
              'user_logs',
              'user_block_ip',
              'user_mail_logs',
            );
          ?>
          <div class="sidenavContentHeader">Admin Role</div>
          <a href="<?=cn('users')?>" class="nav-link <?=(segment(1) == 'users')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fe fe-users"></i><?=lang("Users")?></div>
          </a>
           
    <a style="margin-top: 2px;" href="<?= cn('api_provider/update_latest_orders') ?>" class="nav-link <?=(segment(1) == 'status_update') ? "active" : ""?>">
        <div class="nav-item title" class="sidenavContent">
            <i class="fe fe-users"></i>Status Updates
        </div>
    </a>
           <!-- New sidebar link for WhatsApp Listed Updated page -->
    <a style="margin-top: 2px;" href="<?= cn('whatsapp_listed_updated.php') ?>" class="nav-link <?=(segment(1) == 'whatsapp_listed_updated') ? "active" : ""?>">
        <div class="nav-item title" class="sidenavContent">
            <i class="fe fe-users"></i>WA Number Updates
        </div>
    </a>
          
          <a href="<?=cn('subscribers')?>" class="nav-link <?=(segment(1) == 'subscribers')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-user-circle-o"></i><?php echo lang("subscribers"); ?></div>
          </a>
          
          <a href="<?=cn('user_mail_logs')?>" class="nav-link <?=(segment(1) == 'user' && segment(2) == 'mail' && segment(3) == 'logs')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-envelope"></i><?=lang("User_Mail_Logs")?></div>
          </a>
          
          <a href="<?=cn('user_logs')?>" class="nav-link <?=(segment(1) == 'user' && segment(2) == 'mail' && segment(3) == 'logs')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-sort"></i><?=lang("user_activity_logs")?></div>
          </a>
          
          <a href="<?=cn('user_block_ip')?>" class="nav-link <?=(segment(1) == 'user' && segment(2) == 'mail' && segment(3) == 'logs')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-ban"></i><?=lang("banned_ip_address")?></div>
          </a>
          <?php }?>
          
          
          <?php if(get_role("admin") ||  get_role("supporter")){
            $setting_system = array(
              'setting',
              'api_provider',
              'news',
              'payments',
              'payments_bonuses',
              'faqs',
              'language',
              'module',
            );
          ?>
          <div class="sidenavContentHeader">Settings</div>
          <a href="<?=cn('setting')?>" class="nav-link <?=(segment(1) == 'setting')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-cog"></i><?=lang("System_Settings")?></div>
          </a>
          
          <a href="<?=cn('api_provider')?>" class="nav-link <?=(segment(1) == 'api_provider')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-share-alt"></i><?=lang("Services_Providers")?></div>
          </a>
          
          <a href="<?=cn('payments')?>" class="nav-link <?=(segment(1) == 'payments')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-credit-card"></i><?=lang("Payments")?></div>
          </a>
          
          <a href="<?=cn('payments_bonuses')?>" class="nav-link <?=(segment(1) == 'payments_bonuses')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-money"></i><?=lang("Payments_Bonuses")?></div>
          </a>
          <?php } ?>
          
          
          <?php if(get_role("admin")){?>
          <div class="sidenavContentHeader">Others</div>
          <a href="<?=cn('news')?>" class="nav-link <?=(segment(1) == 'news')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-bell"></i><?=lang("Announcement")?></div>
          </a>
          
          <a href="<?=cn('faqs')?>" class="nav-link <?=(segment(1) == 'faqs')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-book"></i>FAQs</div>
          </a>
          
          <a href="<?=cn('language')?>" class="nav-link <?=(segment(1) == 'language')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-language"></i><?=lang("Language")?></div>
          </a>
          
          <a href="https://codewithali.online" target="_blank" class="nav-link <?=(segment(1) == 'hqsmmscripts')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-diamond"></i><?=lang("Modules_&_Scripts")?></div>
          </a>
          <?php } ?>
          
          
          <a href="<?=cn('profile')?>" class="nav-link <?=(segment(1) == 'profile')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-user"></i><?=lang("Account")?></div>
          </a>
          
          <a href="<?=cn("auth/logout")?>" class="nav-link <?=(segment(1) == 'auth/logout')?"active":""?>">
            <div class="nav-item" class="sidenavContent"><i class="fa fa-power-off"></i><?=lang("Sign_Out")?></div>
          </a>
		  <br><br><br><br><br><br>
        </ul>
      </div>
    </div>  
 <script src="script.js"></script>
 
 

 

 
 
 
 
 
<?php
if (get_option("enable_news_announcement") == 1) {
?>
  <a href="<?=cn("news/ajax_notification")?>" style="position: fixed; bottom: 8px; right: 8px; font-size: 20px; padding-top: 3px; text-align: center; z-index: 10000000;" data-toggle="tooltip" data-placement="bottom" title="News & Announcement" class="ajaxModal text-white">
    <div class="bell-fix">
    <i class="fa fa-bell"></i>
    <div class="test">
       <span class="nav-unread <?=(isset($_COOKIE["news_annoucement"]) && $_COOKIE["news_annoucement"] == "clicked") ? "" : "change_color"?>"></span>
    </div>
    </div>
  </a>
<?php }?>
        

<script>
// Currency selector functionality
document.addEventListener('DOMContentLoaded', function() {
  var currencySelector = document.getElementById('currencySelector');
  
  if (currencySelector) {
    currencySelector.addEventListener('change', function() {
      var selectedCurrency = this.value;
      
      // Send AJAX request to set currency
      var xhr = new XMLHttpRequest();
      xhr.open('POST', '<?=cn("currencies/set_currency")?>', true);
      xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      
      xhr.onload = function() {
        if (xhr.status === 200) {
          // Reload page to apply currency change
          window.location.reload();
        }
      };
      
      // Get CSRF token from page (if available)
      var csrfToken = '';
      var csrfInput = document.querySelector('input[name="<?=csrf_token()?>"]');
      if (csrfInput) {
        csrfToken = '&<?=csrf_token()?>='+encodeURIComponent(csrfInput.value);
      }
      
      xhr.send('currency_code='+encodeURIComponent(selectedCurrency)+csrfToken);
    });
  }
});
</script>

<?php
if (get_option("enable_news_announcement") == 1) {
?>
<a href="https://wa.me/<?=get_option('whatsapp_number')?>/?text=Hello, I have a question" 
   style="position: fixed; bottom: 8px; left: 8px; font-size: 24px; padding-top: 3px; text-align: center; z-index: 10000000;" 
   title="Chat" class="text-white">
    <div class="custom-whatsapp-button">
        <img src="<?php echo BASE; ?>assets/images/whatsapp.png" alt="WhatsApp" class="custom-whatsapp-icon">
    </div>
</a>

<style>
  .custom-whatsapp-button {
      position: relative;
      display: inline-block;
      width: 40px; /* Adjust the size as needed */
      height: 40px; /* Adjust the size as needed */
      margin-top: 10px;
  }

  .custom-whatsapp-icon {
      width: 100%; /* The image will fill the div */
      height: 100%;
  }
</style>

<?php }?>
