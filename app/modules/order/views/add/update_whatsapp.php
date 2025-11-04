<?php
// Predefined database credentials
$host = 'localhost';
$dbname = 'beastsmm';
$username = 'root';
$password = '';

// Create a connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['uid'], $_POST['whatsapp_number']) && is_numeric($_POST['uid'])) {
    $uid = intval($_POST['uid']);
    $whatsapp_number = $_POST['whatsapp_number'];

    // Validate WhatsApp number (e.g., ensure it's in a valid format)
    if (preg_match('/^\+?[0-9]{10,15}$/', $whatsapp_number)) {
        // Update the WhatsApp number in the database
        $query = "UPDATE general_users SET whatsapp_number = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $whatsapp_number, $uid);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update WhatsApp number.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid WhatsApp number format.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}

$conn->close();
?>
