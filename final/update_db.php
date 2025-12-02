<?php
include 'includes/db.php';

// Add role column if it doesn't exist
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($check->num_rows === 0) {
    $sql = "ALTER TABLE users ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'user'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'role' added successfully.<br>";
    } else {
        echo "Error adding column: " . $conn->error . "<br>";
    }
} else {
    echo "Column 'role' already exists.<br>";
}

// Set specific user as admin (optional, for testing)
// You can change this email to your test account
$admin_email = 'admin@example.com'; 
$conn->query("UPDATE users SET role='admin' WHERE email='$admin_email'");

echo "Database update complete.";
?>
