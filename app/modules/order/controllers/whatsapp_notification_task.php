<?php
// Define database credentials
$host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'beastsmm';

// Create a PDO instance to connect to the database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Get the parameters passed from the main script
$order_id = $argv[1];
$total_charge = $argv[2];

// Function to send WhatsApp notification
function sendWhatsAppNotification($phoneNumber, $message) {
    $apiUrl = "https://testapi-y0pb.onrender.com/send-message"; // Replace with your Node.js server URL

    // Prepare data for the POST request
    $data = [
        "phoneNumber" => $phoneNumber,
        "message" => $message
    ];

    // Initialize cURL
    $ch = curl_init($apiUrl);

    // Set the headers
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    // Set the POST method and attach the data
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Set options to return the response
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    // Execute the cURL request
    curl_exec($ch);

    // Close cURL session
    curl_close($ch);
}

// Fetch user details
$stmt = $pdo->prepare("SELECT email, whatsapp_number FROM users WHERE id = :uid");
$stmt->execute(['uid' => 1]); // Replace with the correct user ID
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $user_email = $user['email'];
    $user_whatsapp_number = ltrim($user['whatsapp_number'], '+');
}

// Define message templates
$message_templates = [
    "*Order Confirmation*\n\n" . 
    "*Order ID:* #{$order_id}\n" . 
    "*Total Charge:* " . get_option("currency_symbol", "") . $total_charge . "\n" . 
    "*Website:* " . get_option("website_name", "SmartPanel") . "\n" . 
    "\nThank you for your order! We appreciate your business.",

    "*Order Received*\n\n" . 
    "*Order ID:* #{$order_id}\n" . 
    "*Amount:* " . get_option("currency_symbol", "") . $total_charge . "\n" . 
    "*Site:* " . get_option("website_name", "SmartPanel") . "\n" . 
    "\nYour order is being processed, thank you!",

    "*Order Alert*\n\n" . 
    "*ID of Order:* #{$order_id}\n" . 
    "*Total Payment:* " . get_option("currency_symbol", "") . $total_charge . "\n" . 
    "*Platform:* " . get_option("website_name", "SmartPanel") . "\n" . 
    "\nWe value your trust in us!"
];

// Send message to the user
$user_message = $message_templates[array_rand($message_templates)];
sendWhatsAppNotification($user_whatsapp_number, $user_message);

// Delay for 10 seconds
sleep(10);

// Fetch admin details
$admin_whatsapp_number = '923483469617'; // Replace with the actual admin number

// Define admin message
$admin_message = "*New Order Received*\n\n" .
    "*Order ID:* #{$order_id}\n" . 
    "*Total Charge:* " . get_option("currency_symbol", "") . $total_charge . "\n" .
    "*User Email:* {$user_email}\n" .
    "*Website:* " . get_option("website_name", "SmartPanel") . "\n" . 
    "\nPlease review the order details.";

// Send message to the admin
sendWhatsAppNotification($admin_whatsapp_number, $admin_message);
?>
