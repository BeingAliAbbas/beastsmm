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

$response = ['status' => 'error', 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['uid'], $_POST['whatsapp_number'])) {
    $uid = intval($_POST['uid']);
    $whatsapp_number = $_POST['whatsapp_number'];

    // Validate WhatsApp number format
    if (preg_match('/^\+?[0-9]{10,15}$/', $whatsapp_number)) {
        // Update the WhatsApp number in the database
        $query = "UPDATE general_users SET whatsapp_number = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $whatsapp_number, $uid);

        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'WhatsApp number updated successfully.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to update WhatsApp number.'];
        }

        $stmt->close();
    } else {
        $response = ['status' => 'error', 'message' => 'Invalid WhatsApp number format.'];
    }
}

$conn->close();
echo json_encode($response);
?>
