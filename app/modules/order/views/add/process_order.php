<?php 
session_start(); // Start session to store data

// Capture form data
$service_name = $_POST['service_name'];
$link = $_POST['link'];
$quantity = $_POST['quantity'];
$total_charge = $_POST['total_charge'];

// Save the order details in the session
$_SESSION['order_details'] = [
    'service_name' => $service_name,
    'link' => $link,
    'quantity' => $quantity,
    'total_charge' => $total_charge
];

// Redirect back to order.php (or the page where you want to show the modal)
header('Location: order.php');
exit(); // Exit to prevent further execution
