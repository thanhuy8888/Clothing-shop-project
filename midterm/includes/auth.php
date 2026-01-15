<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Auto-Login from Cookie
if (!isset($_SESSION['user']) && isset($_COOKIE['remember_token'])) {
    global $conn; // Ensure $conn is available (it's included in db.php but auth.php is often included after it)
    // If $conn is not set, we might need to include db.php, but usually auth.php is included AFTER db.php.
    // However, to be safe, we check if $conn exists.
    if (isset($conn)) {
        list($uid, $hash_check) = explode(':', base64_decode($_COOKIE['remember_token']));
        if ($uid && $hash_check) {
            $stmt = $conn->prepare('SELECT id, name, email, password_hash, role FROM users WHERE id = ? LIMIT 1');
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $user_res = $stmt->get_result();
            if ($u = $user_res->fetch_assoc()) {
                // Verify hash matches
                if (hash('sha256', $u['password_hash'] . 'secret_key_salt') === $hash_check) {
                    $_SESSION['user'] = [
                        'id' => $u['id'],
                        'name' => $u['name'],
                        'email' => $u['email'],
                        'role' => $u['role'] ?? 'user'
                    ];
                }
            }
            $stmt->close();
        }
    }
}
function is_logged_in(){ return isset($_SESSION['user']); }
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if (!is_logged_in()){ header('Location: login.php?msg=Please+login+first'); exit; } }
function is_admin(){ return isset($_SESSION['user']) && ($_SESSION['user']['role'] ?? 'user') === 'admin'; }
function require_admin(){ 
    require_login(); 
    if (!is_admin()){ 
        header('Location: index.php'); exit; 
    } 
}
function csrf_token(){ if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(16)); } return $_SESSION['csrf']; }
function csrf_check($t){ return isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $t); }
?>
