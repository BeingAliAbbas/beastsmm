<!-- Font Awesome CDN -->

<?php
session_start(); // Start the session

// Assuming the user's session has a role
$user_id = session('uid'); // Get the current user's ID
$user_role = get_field('general_users', ["id" => $user_id], 'role'); // Fetch user role from the database

// Check if the user is not an admin
if ($user_role !== 'admin') {
    // Redirect to order/add page if the user is not an admin
    header("Location: " . cn('order/add'));
    exit; // Ensure the script stops here
}
  $data_tickets_log = $data_log->data_tickets;
  $data_orders_log  = $data_log->data_orders;

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
  $currency_symbol = get_option('currency_symbol',"$");
?>

<?php if (get_option('dashboard_text','') != '') { ?>
<div class="col-sm-12 col-sm-12">
  <div class="row">
    <div class="card">
      <div class="card-body">
          <?=get_option('dashboard_text','')?>
      </div>
    </div>
  </div>
</div>
<?php }?>

<?php
// Assuming the user's avatar URL and balance data are set
$user_avatar_url = "path/to/avatar.jpg"; // Example avatar URL
$user_balance = isset($data_log->user_balance) ? $data_log->user_balance : 0; // User's balance
$low_balance_threshold = 100; // Low balance threshold
$currency_symbol = get_option('currency_symbol', "PKR");
?>

<!-- User Details Box -->
<div class="user-details-box mb-3 version-1">
    <div class="user-details-content">
        <!-- User Avatar -->
        <img src="<?php echo BASE; ?>assets/images/avatar.png" alt="User Avatar" class="user-avatar-icon">

        <div class="user-info">
            <!-- Welcome message with full name -->
            <h5 class="user-greeting">
                Welcome, 
                <?php 
                $first_name = get_field(USERS, ["id" => session('uid')], 'first_name');
                $last_name = get_field(USERS, ["id" => session('uid')], 'last_name');
                echo $first_name . ' ' . $last_name;?>!</h5>

            <!-- User Role and Status -->
<p class="user-role-status">
    Role: 
    <?php 
    // Fetch user role
    $user_role = get_field('general_users', ["id" => session('uid')], 'role'); // Adjust to your table name
    echo ucfirst($user_role); // Capitalize first letter

    // Fetch account creation date and determine status
    $account_creation_date = get_field('general_users', ["id" => session('uid')], 'created'); // Use 'created' for the correct column
    $current_date = new DateTime();
    $creation_date = new DateTime($account_creation_date);
    $interval = $current_date->diff($creation_date);

    // Determine status based on account age
    $status = ($interval->m >= 1 || $interval->y > 0) ? "Senior" : "Junior"; // Ternary for cleaner code
    ?> 
    | Status: <?= $status; ?>
</p>


            <!-- User Balance -->
            <h6 class="user-balance">
                            <i class="fa fa-credit-card" aria-hidden="true"></i>
                Your Balance: <?= htmlspecialchars($currency_symbol) ?><?= number_format($user_balance, 2) ?>
            </h6>

            <!-- Low balance warning and add funds button -->
            <?php if ($user_balance < $low_balance_threshold): ?>
                <small class="low-balance-alert">
                    <i class="fa fa-exclamation-triangle warning-icon"></i>
                    Your balance is low! Please consider adding funds.
                </small>
                <a href="add_funds" class="add-funds-button">Add Funds</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
// Include your configuration file
include_once 'config.php'; // Adjust the path accordingly
include_once 'app/helpers/common_helper.php'; // Ensure common_helper.php is included once

// Create connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Start session
session_start();
$user_id = $_SESSION['uid']; // Adjust this to your session handling, using session variable for user ID

// Check if the user is an admin
if (!function_exists('get_role')) {
    // Only declare the function if it doesn't exist
    function get_role($role) {
        $user_roles = $_SESSION['roles']; // Assuming roles are stored in the session
        return in_array($role, $user_roles);
    }
}

// Add Font Awesome 4 CDN for icons
echo '<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />';

// CSS styles for better presentation
// CSS styles for better presentation
echo "<style>
    .order-container {
        background-color: #061d2b;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
        margin: 20px auto;
        width: 100%;
        max-width: 1200px;
        border: 1px solid #00ffff;
        position: relative;
        overflow-x: auto;
    }

    h2 {
        color: #00ffff;
        text-align: center;
        margin-bottom: 15px;
    }

    table {
        width: 100%;
        border-collapse: collapse; /* Ensures borders are merged */
        margin-top: 10px;
    }

    th {
        padding: 12px;
        text-align: left;
        background-color: #05d0a1;
        color: #061d2b;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
    }

    td {
        padding: 12px;
        text-align: left;
        border: 1px solid #fff; /* Adds border only to td elements */
        color: #ffffff;
    }

    /* Added styles for row hover effect */
    tr:hover {
        background-color: rgba(255, 255, 255, 0.1); /* Light hover effect */
    }

    a {
        text-decoration: none;
        color: #467fcf;
    }

    a:hover {
        text-decoration: underline;
    }

    .status-label {
        color: #ffb911;
        font-weight: bold;
    }

    /* Responsive Styles */
    @media (max-width: 768px) {
        .order-container {
            width: 100%;
            padding: 15px;
        }

        h2 {
            font-size: 1.5em;
        }

        th, td {
            padding: 8px;
        }

        table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
    }

    @media (max-width: 480px) {
        h2 {
            font-size: 1.3em;
        }

        th, td {
            padding: 6px;
        }
    }
</style>";


// Only display the orders container if the user is an admin
if (get_role("admin")) {

    // Fetch the latest 5 orders for admin, including the new columns
    $query = "SELECT id, link, quantity, status, charge, created, start_counter, remains FROM orders ORDER BY created DESC LIMIT 5";

    // Prepare the statement
    $stmt = $mysqli->prepare($query);

    // Execute the prepared statement
    $stmt->execute();
    $result = $stmt->get_result(); // Get the result set

    // Check if there are any orders
    if ($result->num_rows > 0) {
        echo "<div class='order-container'>
                <h2>Last 5 Orders</h2>";
        
        // Create a table for the orders
        echo "<table>
        <thead>
            <tr>
                <th><i class='fa fa-shopping-cart'></i> Order ID</th>
                <th><i class='fa fa-info-circle'></i> Status</th>
                <th><i class='fa fa-link'></i> Link</th>
                <th><i class='fa fa-cubes'></i> Quantity</th>
                <th><i class='fa fa-hourglass-start'></i> Start Counter</th>
                <th><i class='fa fa-hourglass-end'></i> Remains</th>
                <th><i class='fa fa-money'></i> Amount Charged</th>
                <th><i class='fa fa-calendar'></i> Date</th>
            </tr>
        </thead>
        <tbody>";

    // Fetch each order and display it in the table, but hide orders with status "Complete"
    while ($row = $result->fetch_assoc()) {
        if ($row['status'] === "Complete") {
            // Skip the current iteration if the status is "Complete"
            continue;
        }

        echo "<tr>
                <td>" . htmlspecialchars($row['id']) . "</td>
                <td><span class='status-label'>" . htmlspecialchars($row['status']) . "</span></td>
                <td><a href='" . htmlspecialchars($row['link']) . "' target='_blank' style='color: #467fcf !important;'>" . htmlspecialchars($row['link']) . "</a></td>
                <td>" . htmlspecialchars($row['quantity']) . "</td>
                <td>" . htmlspecialchars($row['start_counter']) . "</td>
                <td>" . htmlspecialchars($row['remains']) . "</td>
                <td>" . htmlspecialchars($row['charge']) . " PKR</td>
                <td>" . htmlspecialchars($row['created']) . "</td>
              </tr>";
    }


        echo "</tbody></table></div>";
    } else {
        // No orders found
        echo "<div class='order-container'>
                <h2>No Orders Found</h2>
              </div>";
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$mysqli->close();
?>

<?php
// Include your configuration file
include_once 'config.php'; // Adjust the path accordingly

// Create connection
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Start session
session_start();
$user_id = $_SESSION['uid']; // Get user ID from session

// Fetch the latest pending transaction and the user email
$query = "
    SELECT gtl.* 
    FROM general_transaction_logs gtl
    WHERE gtl.uid = ? 
    AND gtl.status = 0 
    AND gtl.created >= NOW() - INTERVAL 24 HOUR 
    ORDER BY gtl.created DESC 
    LIMIT 1"; // status = 0 for pending, created within the last 48 hours

$stmt = $mysqli->prepare($query); // Prepare the statement
$stmt->bind_param('i', $user_id); // Bind the user ID as an integer parameter
$stmt->execute(); // Execute the prepared statement
$result = $stmt->get_result(); // Get the result set

// CSS styles for better presentation
echo "<style>
    .transaction-container {
    background-color: #061d2b; /* Darker blue background for the container */
    padding: 20px; /* Padding inside the container */
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
    margin: 20px auto; /* Center the container with automatic horizontal margins */
    width: 100%; /* Responsive width */
    max-width: 1200px; /* Maximum width for larger screens */
    border: 1px solid #00ffff; /* Outer border with specified color */
    position: relative; /* Position relative for the clock image */
}

h2 {
    color: #00ffff; /* Teal color for the heading */
    text-align: center; /* Centered heading */
}
h3 {
    margin: 0 0 5px;
    color: #ffffff; /* White for the transaction ID */
}
p {
    margin: 2px 0;
    color: #ffffff; /* White for other text */
}
.status-pending {
    color: #ffb911; /* Teal for pending status */
    font-weight: bold;
}

/* Button Styles */
.request-approval-btn {
    display: block;
    margin: 20px auto;
    padding: 10px 20px;
    background-color: #05d0a1;
    color: #ffffff;
    text-align: center;
    font-size: 16px;
    font-weight: bold;
    text-decoration: none;
    border-radius: 5px;
    transition: background-color 0.3s ease;
}

.request-approval-btn:hover {
    background-color: #03b189; /* Darker shade on hover */
    text-decoration: none;

}

/* Clock image */
.clock-icon {
    position: absolute;
    right: 40px; /* Place it 20px from the right */
    top: 50px; /* Place it 20px from the top */
    width: 100px; /* Size of the clock icon */
    height: auto; /* Maintain aspect ratio */
    animation: heartbeat 2.5s infinite; /* Heartbeat animation */
}

/* Heartbeat animation */
@keyframes heartbeat {
    0% {
        transform: scale(1);
    }
    20% {
        transform: scale(1.1);
    }
    40% {
        transform: scale(1);
    }
    60% {
        transform: scale(1.1);
    }
    80% {
        transform: scale(1);
    }
    100% {
        transform: scale(1);
    }
}

/* Responsive Styles */
@media (max-width: 768px) { /* For tablets and smaller screens */
    .transaction-container {
        padding: 15px; /* Reduced padding */
    }

    h2 {
        font-size: 1.5em; /* Slightly smaller heading on mobile */
    }

    h3 {
        font-size: 1.2em; /* Smaller font size for transaction ID */
    }

    p {
        font-size: 1em; /* Consistent font size */
    }

    .transaction {
        padding: 8px; /* Adjust padding inside each transaction box */
    }

    .clock-icon {
        width: 80px; /* Reduce clock size for tablets */
        top: 15px; /* Adjust top positioning */
        right: 15px; /* Adjust right positioning */
    }
}

@media (max-width: 480px) { /* For mobile phones */
    .transaction-container {
        padding: 10px; /* Further reduced padding */
    }

    h2 {
        font-size: 1.3em; /* Further smaller heading on mobile */
    }

    h3 {
        font-size: 1.1em; /* Further smaller font size for transaction ID */
    }

    p {
        font-size: 0.9em; /* Smaller font size for paragraphs */
    }

    .transaction {
        padding: 5px; /* Further adjusted padding inside each transaction box */
        height: auto; /* Allow height to adjust based on content */
    }

    .clock-icon {
        width: 60px; /* Further reduce clock size for mobile phones */
        top: 50px; /* Adjust top positioning */
        right: 20px; /* Adjust right positioning */
    }
}

</style>";

// Check if there are any pending transactions
if ($result->num_rows > 0) {
    echo "<div class='transaction-container'>
            <h2>Pending Transaction</h2>";

    // Fetch the pending transaction
    while ($row = $result->fetch_assoc()) {
        // Transaction details
        $transaction_id = htmlspecialchars($row['transaction_id']);
        $transaction_type = htmlspecialchars($row['type']);
        $transaction_amount = htmlspecialchars($row['amount']);
        $transaction_created = htmlspecialchars($row['created']);
        $user_email = htmlspecialchars($row['email']); // Fetch user email

        // WhatsApp number from settings
        $whatsapp_number = get_option('whatsapp_number'); // Fetch WhatsApp number from settings

        // WhatsApp message with emojis
        $whatsapp_message = "Please approve the following transaction:\n\n"
            . "🔢 *Transaction ID:* $transaction_id\n"  // Added number emoji
            . "💸 *Amount:* $transaction_amount PKR\n"   // Added money emoji
            . "💳 *Payment Method:* $transaction_type\n"  // Added credit card emoji
            . "⏰ *Created:* $transaction_created";        // Added clock emoji

        // Encode the message for use in the URL
        $whatsapp_url = "https://wa.me/" . $whatsapp_number . "?text=" . urlencode($whatsapp_message);

        // Check if the transaction is at least 1 minute old
        $created_time = strtotime($row['created']);
        $current_time = time();
        $time_diff_in_seconds = $current_time - $created_time;

        // Display transaction details
        echo "<div class='transaction'>
                <img src='assets/images/clock.png' alt='Clock Icon' class='clock-icon'>
                <h3>Transaction ID: " . $transaction_id . "</h3>
                <p><strong>Transaction Type:</strong> " . $transaction_type . "</p>
                <p><strong>Amount:</strong> " . $transaction_amount . " PKR</p>
                <p><strong>Status:</strong> <span class='status-pending'>Pending</span></p>
                <p><strong>Date:</strong> " . $transaction_created . "</p>";
        
        // Only show the approval button if the transaction is older than 1 minute
        if ($time_diff_in_seconds >= 3600) {
            echo "<a href='" . $whatsapp_url . "' target='_blank' class='request-approval-btn'>Request Approval on WhatsApp</a>";
        }
        
        echo "</div>";
    }
    echo "</div>";
} else {
    // No pending transactions
}

// Close the statement and connection
$stmt->close();
$mysqli->close();
?>




<!-- Add relevant CSS for role and status -->
<style>
    .user-role-status {
        margin-top: 5px;
        font-size: 1em; /* Standard font size */
        color: #f8f9fa; /* Clean white color for text */
        font-weight: 600; /* Make the text slightly bold */
    }

    .balance-icon {
        margin-right: 8px;
    }

    .warning-icon {
        color: #dc3545;
        margin-right: 6px;
    }

    .add-funds-button {
        margin-top: 10px;
        padding: 10px 20px;
        background-color: #05d0a1;
        color: #00ffff;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
    }
    
    .add-funds-button:hover {
        background-color: #0a141b;
    }
</style>


<style>
 .user-avatar-icon {
    width: 60px; /* Default avatar size */
    height: 60px;
    margin-bottom: 10px;
    border-radius: 50%;
    border: 2px solid #00ffff; 
    padding: 5px;
    background-color: #06324d;
}

.user-details-box {
    border: 1px solid #00ffff;
    border-radius: 10px;
    padding: 20px;
    background-color: #061d2b;
    text-align: center;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
    margin: 20px auto;
    width: 90%; /* Changed from fixed to responsive width */
    max-width: 350px; /* Ensure it doesn't get too large */
}

.user-avatar {
    font-size: 60px;
    color: #00ffff;
    margin-bottom: 10px;
}

.user-info h5 {
    font-family: 'Arial', sans-serif;
    font-size: 1.5em;
    color: #00ffff;
}

.user-info h6 {
    font-family: 'Arial', sans-serif;
    color: #f8f9fa;
}

.low-balance-alert {
    display: block; /* Make sure it's a block-level element */
    color: #dc3545;
    margin-bottom: 10px; /* Add some space below the alert */
}

.add-funds-button {
    display: inline-block; /* Ensure it's not taking the full width */
    margin-top: 10px; /* Keep margin for spacing */
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    background-color: #05d0a1;
    color: #00ffff;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s, transform 0.2s;
    /* If needed, add margin or padding adjustments */
}

.add-funds-button:hover {
    background-color: #0a141b;
    transform: scale(1.05);
}

/* Responsive Breakpoints */

/* Small devices (phones) */
@media only screen and (max-width: 600px) {
    .user-details-box {
        padding: 15px;
        width: 100%; /* Make the box take up most of the screen */
    }

    .user-avatar-icon {
        width: 50px; /* Smaller avatars on small screens */
        height: 50px;
    }

    .user-info h5 {
        font-size: 1.2em; /* Smaller text */
    }

    .user-info h6 {
        font-size: 1em;
    }

    .add-funds-button {
        padding: 8px 16px; /* Smaller buttons for mobile */
    }
}

/* Medium devices (tablets, smaller desktops) */
@media only screen and (min-width: 601px) and (max-width: 1024px) {
    .user-details-box {
        padding: 20px;
        width: 80%; /* Slightly larger width for tablets */
    }

    .user-avatar-icon {
        width: 55px; /* Slightly larger avatars for tablets */
        height: 55px;
    }

    .user-info h5 {
        font-size: 1.4em;
    }

    .user-info h6 {
        font-size: 1.1em;
    }

    .add-funds-button {
        padding: 10px 18px;
    }
}

/* Large devices (desktops, laptops) */
@media only screen and (min-width: 1025px) {
    .user-details-box {
        width: 350px; /* Larger fixed width for large screens */
    }

    .user-avatar-icon {
        width: 60px; /* Default size */
        height: 60px;
    }

    .user-info h5 {
        font-size: 1.5em;
    }

    .user-info h6 {
        font-size: 1.2em;
    }

    .add-funds-button {
        padding: 12px 24px; /* Bigger buttons on large screens */
    }
}

/* Large devices (desktops, laptops) */
@media only screen and (min-width: 1025px) {
    .user-details-box {
        width: 100%; /* Set width to full */
        max-width: 1100px; /* Max width up to 1000px on large screens */
        margin: 20px auto; /* Center the box horizontally */
    }

    .user-avatar-icon {
        width: 60px; /* Default size */
        height: 60px;
    }

    .user-info h5 {
        font-size: 1.5em;
    }

    .user-info h6 {
        font-size: 1.2em;
    }

    .add-funds-button {
        padding: 12px 24px; /* Bigger buttons on large screens */
    }
}


</style>
<!-- Add your other dashboard code here -->


<div class="row justify-content-center row-card2 statistics">
  <div class="col-sm-12">
    <div class="row">
      <?php
        if (get_role("admin")) {
      ?>
      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-success-gradient text-white mr-3">
              <i class="fe fe-users"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 style="color: #fff;" class="m-0 text-right number"><?=$data_log->total_users?></h4>
                <small class="text-muted "><?=lang("total_users")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php }else{ ?>
      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-success-gradient text-white mr-3">
              <i class="fe fe-dollar-sign"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=get_option('currency_symbol',"$")?><?=(!empty($data_log->user_balance)) ? currency_format($data_log->user_balance, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0?></h4>
                <small class="text-muted "><?=lang("your_balance")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-info-gradient text-white mr-3">
              <i class="fe fe-dollar-sign"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=get_option('currency_symbol',"$")?><?=(!empty($data_log->total_spent_receive)) ? currency_format($data_log->total_spent_receive, get_option('currency_decimal', 2), $decimalpoint, $separator) : 0?></h4>
                <small class="text-muted ">
                  <?=(get_role("admin") ? lang("total_amount_recieved") : lang("total_amount_spent"))?>
                </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-warning-gradient text-white mr-3">
              <i class="fe fe-shopping-cart"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->total?></h4>
                <small class="text-muted "><?=lang("total_orders")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-danger-gradient text-white mr-3">
              <i class="fa fa-ticket"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->total?></h4>
                <small class="text-muted "><?=lang("total_tickets")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php
      if (get_role("admin")) {
    ?>
    <div class="row">
      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-success-gradient text-white mr-3">
              <i class="icon-fa fa fa-money"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->users_balance, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_users_balance"); ?> </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-info-gradient text-white mr-3">
              <i class="icon-fa fa fa-balance-scale"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->providers_balance, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_providers_balance"); ?> </small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-warning-gradient text-white mr-3">
              <i class="icon-fa fa fa-calculator"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->last_profit_30_days, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_profit_30_days"); ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-3">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md bg-danger-gradient text-white mr-3">
              <i class="icon-fa fa fa-calculator"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?php echo $currency_symbol.number_format($data_log->profit_today, 2, '.', ','); ?></h4>
                <small class="text-muted "><?php echo lang("total_profit_today"); ?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php } ?>
    <div class="row">
      <!-- Order -->
      <div class="col-sm-12 charts">
        <div class="card4">
          <div class="card-header">
            <h3 class="card4-title"><?=lang("recent_orders")?></h3>
          </div>
          <div class="row">
            <div class="col-sm-8">
              <div class="p-4 card4">
                <div id="orders_chart_spline" style="height: 20rem;"></div>
              </div>
            </div>
            <div class="col-sm-4">
              <div class="p-4 card4">
                <div id="orders_chart_pie" style="height: 20rem;"></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-list"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->total?></h4>
                <small class="text-muted "><?=lang("total_orders")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-check"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 number"><?=$data_orders_log->completed?></h4>
                <small class="text-muted"><?=lang("Completed")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-trending-up"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->processing?></h4>
                <small class="text-muted "><?=lang("Processing")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-loader"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->inprogress?></h4>
                <small class="text-muted "><?=lang("In_progress")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-pie-chart"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->pending?></h4>
                <small class="text-muted "><?=lang("Pending")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fa fa-hourglass-half"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->partial?></h4>
                <small class="text-muted "><?=lang("Partial")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>    

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-x-square"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->canceled?></h4>
                <small class="text-muted "><?=lang("Canceled")?></small>
              </div>
            </div>
          </div>
        </div>
      </div> 

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-rotate-ccw"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_orders_log->refunded?></h4>
                <small class="text-muted "><?=lang("Refunded")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>    
      <?php
        if (get_role('admin')) {
      ?>
      <!-- tickets -->


      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fa fa-ticket"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->total?></h4>
                <small class="text-muted "><?=lang("total_tickets")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-mail"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 number"><?=$data_tickets_log->new?></h4>
                <small class="text-muted"><?=lang("New")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-pie-chart"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->pending?></h4>
                <small class="text-muted "><?=lang("Pending")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-sm-6 col-lg-3 item">
        <div class="card2 p-4">
          <div class="d-flex align-items-center">
            <span class="stamp stamp-md mr-3 text-info">
              <i class="fe fe-check"></i>
            </span>
            <div class="d-flex order-lg-2 ml-auto">
              <div class="ml-2 d-lg-block text-right">
                <h4 class="m-0 text-right number"><?=$data_tickets_log->closed?></h4>
                <small class="text-muted "><?=lang("Closed")?></small>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php }?>
    </div>
  </div>
</div>


<div class="row justify-content-center">
  
  <!-- Top Best Sellers -->
  <div class="col-md-12">
    <?php

      if (get_role('admin')) {
        $columns_best_seller = array(
          "name"             => lang("Name"),
          "total_orders"     => lang("total_orders"),
          "add_type"         => lang("Type"),
          "provider"         => lang("api_provider"),
          "api_service_id"   => lang("api_service_id"),
          "price"            => lang("rate_per_1000")."(".get_option("currency_symbol","").")",
          "min_max"          => lang("min__max_order"),
          "desc"             => lang("Description"),
          "status"           => lang("Status"),
        );
      }else{
        $columns_best_seller = array(
          "name"             => lang("Name"),
          "price"            => lang("rate_per_1000")."(".get_option("currency_symbol","").")",
          "min_max"          => lang("min__max_order"),
          "desc"             => lang("Description"),
        );
      } 
      $data = array(
        'services' => $top_bestsellers,
        'columns'  => $columns_best_seller
      );
      $this->load->view('top_bestsellers', $data);
    ?>
  </div>  
  
  <?php 
    if (get_role('admin')) {
  ?>
  <!-- Last 5 Newest Users -->
  <div class="col-md-12">
    <?php 
      $data = array(
        'users' => $last_5_users,
        'columns'  => array(
            "name"                   => lang("name"),
            "Email"                  => lang("Email"),
            "type"                   => lang("Type"),
            "balance"                => lang('Funds'),
            "last_ip_address"        => 'Last IP Address',
            "created"                => lang("Created"),
            "status"                 => lang('Status'),
          )
      );
      $this->load->view('last_5_users', $data);
    ?>
  </div>
  
  <!-- Last 5 order -->
  <div class="col-md-12">
    <?php 
      $data = array(
        'order_logs' => $last_5_orders,
        'columns'  => array(
            "order_id"            => lang("order_id"),
            "uid"                 => lang("User"),
            "name"                => lang("name"),
            "type"                => lang("Type"),
            "link"                => lang("Link"),
            "quantity"            => lang("Quantity"),
            "amount"              => lang("Amount"),
            "created"             => lang("Created"),
            "status"              => lang("Status"),
          )
      );
      $this->load->view('last_5_orders', $data);
    ?>
  </div>
  <?php } ?>

</div>

<style>
    .th{
      color: white;
    }
    h4 {
    
      color: white;
    }
    h3{
      color: white;
    }
    .text-muted {
      color: white;
    }

   
  </style>



<script>
  $(document).ready(function(){

    Chart_template.chart_spline('#orders_chart_spline', <?=$data_orders_log->data_orders_chart_spline?>);
    Chart_template.chart_pie('#orders_chart_pie', <?=$data_orders_log->data_orders_chart_pie?>);

    Chart_template.chart_spline('#tickets_chart_spline', <?=$data_tickets_log->data_tickets_chart_spline?>);
    Chart_template.chart_pie('#tickets_chart_pie', <?=$data_tickets_log->data_tickets_chart_pie?>);
  });
</script>

