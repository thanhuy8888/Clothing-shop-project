<?php
$DB_HOST = 'sql105.infinityfree.com';
$DB_USER = 'if0_40493501';
$DB_PASS = 'ronaldo36363636';
$DB_NAME = 'if0_40493501_dataweb';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) { die('Connection failed: ' . $conn->connect_error); }
if (!$conn->set_charset("utf8mb4")) {
    // If utf8mb4 is not supported, try utf8
    $conn->set_charset("utf8");
}

// Auto-fix: Add role column if missing
try {
    $conn->query("SELECT role FROM users LIMIT 1");
} catch (Exception $e) {
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'");
}

// Auto-fix: Add missing columns to products table
try {
    $conn->query("SELECT created_by FROM products LIMIT 1");
} catch (Exception $e) {
    $conn->query("ALTER TABLE products ADD COLUMN created_by INT DEFAULT NULL");
}

try {
    $conn->query("SELECT updated_at FROM products LIMIT 1");
} catch (Exception $e) {
    $conn->query("ALTER TABLE products ADD COLUMN updated_at DATETIME DEFAULT NULL");
}
?>