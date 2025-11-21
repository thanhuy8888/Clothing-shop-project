<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$token = $_GET['csrf'] ?? '';
if (!$id || !csrf_check($token)){ header('Location: products.php'); exit; }
$stmt = $conn->prepare('DELETE FROM products WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute(); $stmt->close();
header('Location: products.php'); exit;
