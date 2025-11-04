<?php
// Database connection
$host = 'localhost';       // Database host
$user = 'root';            // Database username
$password = '';            // Database password
$db_name = 'beastsmm';     // Database name

// Create connection
$conn = new mysqli($host, $user, $password, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    // Print a message indicating a successful connection
    echo 'Database connected successfully.<br>';
}

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Get user ID from POST request
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($user_id > 0) {
    // Query to fetch transactions for the user
    $sql = "SELECT * FROM general_transaction_logs WHERE uid = $user_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Create a table to display the transactions
        $html = '<table class="table table-striped">';
        $html .= '<thead><tr><th>Transaction ID</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead><tbody>';

        // Output the data of each transaction
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            $html .= '<td>' . $row['transaction_id'] . '</td>';
            $html .= '<td>' . $row['amount'] . '</td>';
            $html .= '<td>' . $row['created'] . '</td>';
            $html .= '<td>' . ($row['status'] == 1 ? 'Completed' : 'Pending') . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        echo $html; // Return the HTML content for modal display
    } else {
        echo '<p>No transactions found.</p>';
    }
} else {
    echo '<p>Invalid user ID.</p>';
}

// Close the connection
$conn->close();
?>
