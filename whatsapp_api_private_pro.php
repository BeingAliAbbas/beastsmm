<?php
session_start();

// Database connection
$host = 'localhost';
$user = 'beastsmm_ali';
$pass = 'ra6efcTo[4z#';
$db = 'beastsmm_ali';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch current WhatsApp API settings
$query = "SELECT * FROM whatsapp_config WHERE id = 1";
$result = $conn->query($query);
$current_settings = $result->fetch_assoc();

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key = $_POST['api_key'];
    $url = $_POST['url'];

    // Update the database
    if ($current_settings) {
        $update_query = "UPDATE whatsapp_config SET api_key = '$api_key', url = '$url' WHERE id = 1";
        $conn->query($update_query);
    } else {
        $insert_query = "INSERT INTO whatsapp_config (api_key, url) VALUES ('$api_key', '$url')";
        $conn->query($insert_query);
    }

    // Redirect to the same page to show updated values
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp API Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.3/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-white">
    <div class="max-w-xl mx-auto p-6">
        <div class="bg-gray-800 p-8 rounded-lg shadow-lg">
            <h2 class="text-2xl font-semibold text-center mb-6">WhatsApp API Settings</h2>

            <!-- Show current settings -->
            <form method="POST" action="" class="space-y-6">
                <div class="flex justify-between items-center">
                    <label for="api_key" class="block text-gray-300">API Token</label>
                    <input type="text" name="api_key" id="api_key" value="<?= htmlspecialchars($current_settings['api_key'] ?? '') ?>"
                        class="mt-1 block w-full px-4 py-2 border border-gray-600 rounded-md bg-gray-700 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                </div>

                <div class="flex justify-between items-center">
                    <label for="url" class="block text-gray-300">Phone Number ID</label>
                    <input type="text" name="url" id="url" value="<?= htmlspecialchars($current_settings['url'] ?? '') ?>"
                        class="mt-1 block w-full px-4 py-2 border border-gray-600 rounded-md bg-gray-700 text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                        required>
                </div>

                <div class="mt-6 text-center">
                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Save Settings
                    </button>
                </div>
            </form>

            <!-- Success Message -->
            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                <div class="mt-6 text-center">
                    <p class="text-green-600">Settings have been updated successfully!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
