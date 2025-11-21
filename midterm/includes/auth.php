<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
function is_logged_in(){ return isset($_SESSION['user']); }
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if (!is_logged_in()){ header('Location: login.php?msg=Please+login+first'); exit; } }
function csrf_token(){ if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); } return $_SESSION['csrf']; }
function csrf_check($t){ return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t); }
?>