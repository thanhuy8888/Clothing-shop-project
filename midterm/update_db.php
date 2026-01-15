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


// Add approval_status column if it doesn't exist
$check_approval = $conn->query("SHOW COLUMNS FROM products LIKE 'approval_status'");
if ($check_approval->num_rows === 0) {
    $sql = "ALTER TABLE products ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'";
    if ($conn->query($sql) === TRUE) {
        echo "Column 'approval_status' added successfully.<br>";
    } else {
        echo "Error adding column 'approval_status': " . $conn->error . "<br>";
    }
} else {
    echo "Column 'approval_status' already exists.<br>";
}

echo "<div style='color:green; font-weight:bold; padding:20px;'>Database update complete! Column 'approval_status' added. <br><a href='admin_products.php'>Go to Admin Products</a></div>";
// Delete self for security? Maybe not yet.
?>
