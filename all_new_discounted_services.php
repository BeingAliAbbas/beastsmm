<?php
include('db_connect.php'); // Include database connection

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the email from the query string
$email = isset($_GET['email']) ? $_GET['email'] : null;

if ($email) {
    try {
        // Prepare the SQL statement to update the 'is_opened' field
        $stmt = $pdo->prepare("UPDATE progress_tracker SET is_opened = 1, open_count = open_count + 1, last_opened = NOW() WHERE email = ?");
        $stmt->execute([$email]);

        // Log success or failure
        if ($stmt->rowCount() > 0) {
            error_log("Email open tracked for: " . $email); // Log success
        } else {
            error_log("No matching email found for: " . $email); // Log failure
        }
    } catch (Exception $e) {
        error_log("Error updating email open: " . $e->getMessage()); // Log exception
    }
} else {
    error_log("No email parameter provided in the request."); // Log missing parameter
}

// Redirect to the actual site
header("Location: https://beastsmm.xyz");
exit;
