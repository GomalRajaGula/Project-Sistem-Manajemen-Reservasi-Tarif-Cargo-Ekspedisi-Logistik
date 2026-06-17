<?php
// config/connection.php - Database connection setup

$host = 'localhost';
$dbName = 'db_logistik_cargo';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

try {
    $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    $conn = $pdo; // Set both $pdo and $conn for maximum compatibility
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Koneksi database gagal: " . $e->getMessage());
}
