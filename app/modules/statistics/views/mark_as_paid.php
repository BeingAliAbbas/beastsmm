<?php
// Include configuration and helpers
include_once 'config.php';
include_once 'app/helpers/common_helper.php';

// Start session
session_start();

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Get the transaction ID and sanitize it
    $transaction_id = isset($_POST['transaction_id']) ? htmlspecialchars($_POST['transaction_id']) : null;

    // Ensure CSRF token is valid (for frameworks like CodeIgniter, Laravel, etc.)
    if (!isset($_POST[$this->security->get_csrf_token_name()]) || $_POST[$this->security->get_csrf_token_name()] !== $this->security->get_csrf_hash()) {
        die('CSRF validation failed');
    }

    // Check if the user is an admin
    if (get_role('admin')) {
        // Mark transaction as paid in the database
        $query = "UPDATE general_transaction_logs SET status = 1 WHERE transaction_id = ?";
        $stmt = $mysqli->prepare($query);
        $stmt->bind_param('s', $transaction_id);
        
        if ($stmt->execute()) {
            echo "Transaction marked as paid.";
        } else {
            echo "Error updating transaction: " . $mysqli->error;
        }

        $stmt->close();
        $mysqli->close();
    } else {
        echo "You do not have permission to perform this action.";
    }
} else {
    echo "Invalid request.";
}
