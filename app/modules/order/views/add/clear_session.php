<?php
session_start();

// Clear order details from session
if (isset($_SESSION['order_details'])) {
    unset($_SESSION['order_details']);
}

// Return a success message (optional)
echo json_encode(['status' => 'success']);
?>
