<?php
include 'includes/db.php';
$result = $conn->query("SHOW COLUMNS FROM products");
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
