<?php
// Direct database connection settings
$host = 'localhost';
$dbname = 'beastsmm_bulk_mailer';
$username = 'beastsmm_bulk_mailer';
$password = '3?I;b_6_%si=';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
