<?php
// bulk_update_by_id.php

// -------- CONFIG ----------
$dbHost   = 'localhost';
$dbName   = 'beastsmm_ali';
$dbUser   = 'beastsmm_ali';
$dbPass   = 'ra6efcTo[4z#';
$dbCharset = 'utf8mb4';
// -------- END CONFIG ------

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset={$dbCharset}";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    exit("DB connection failed: " . $e->getMessage() . PHP_EOL);
}

// Local order IDs you want to mark completed
$orderIds = [
    22324, 22325, 22326, 22327,
    22650, 22673, 22678,
    22712, 22714,
    23124
];


$sql = "UPDATE orders SET status = 'completed', changed = :changed WHERE id = :id";
$stmt = $pdo->prepare($sql);

$updated = 0;
foreach ($orderIds as $id) {
    try {
        $stmt->execute([
            ':changed' => date('Y-m-d H:i:s'),
            ':id'      => $id
        ]);
        if ($stmt->rowCount() > 0) {
            echo "✅ order id {$id} marked as completed.\n";
            $updated++;
        } else {
            echo "⚠️ order id {$id} not found in DB.\n";
        }
    } catch (PDOException $e) {
        echo "❌ Error updating {$id}: " . $e->getMessage() . "\n";
    }
}

echo "\nSummary: {$updated} orders updated to completed.\n";
