<?php
// Database credentials
$host = 'localhost'; 
$dbname = 'beastsmm_ali'; 
$username = 'beastsmm_ali'; 
$password = 'ra6efcTo[4z#';

// Create PDO connection
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch users who have updated their WhatsApp number
    $stmt = $conn->prepare("SELECT id, first_name, last_name, whatsapp_number FROM general_users WHERE whatsapp_number_updated = 1");
    $stmt->execute();

    // Fetch all results
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Who Updated WhatsApp Number</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">

    <!-- Page Container -->
    <div class="container mx-auto py-10 px-4">

        <!-- Page Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-semibold">Users Who Updated Their WhatsApp Number</h1>
            <p class="text-lg text-gray-400">Here is the list of users who have successfully updated their WhatsApp numbers.</p>
        </div>

        <!-- Table for Displaying Users -->
        <div class="overflow-x-auto bg-gray-800 rounded-lg shadow-lg">
            <table class="min-w-full table-auto">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">#</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">First Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">Last Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-300">WhatsApp Number</th>
                    </tr>
                </thead>
                <tbody class="bg-gray-800">
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center text-gray-400">No users found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $index => $user): ?>
                            <tr class="hover:bg-gray-700">
                                <td class="px-6 py-4 text-sm"><?php echo $index + 1; ?></td>
                                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['first_name']); ?></td>
                                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['last_name']); ?></td>
                                <td class="px-6 py-4 text-sm"><?php echo htmlspecialchars($user['whatsapp_number']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>
