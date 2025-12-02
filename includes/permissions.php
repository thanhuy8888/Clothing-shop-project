<?php
// Define permission constants
define('PERM_PRODUCT_MANAGE', 'product_manage');
define('PERM_USER_MANAGE', 'user_manage');

/**
 * Check if current user has a specific permission
 */
function has_permission($permission) {
    if (!is_logged_in()) {
        return false;
    }
    
    $user = current_user();
    $role = $user['role'] ?? 'user';
    
    // Admin has all permissions
    if ($role === 'admin') {
        return true;
    }
    
    // Define role-based permissions
    $role_permissions = [
        'user' => [],
        // Add other roles here if needed
    ];
    
    $permissions = $role_permissions[$role] ?? [];
    return in_array($permission, $permissions);
}

/**
 * Check if user can manage products (add/edit/delete)
 */
function can_manage_products() {
    return has_permission(PERM_PRODUCT_MANAGE);
}

/**
 * Require permission or redirect/exit
 */
function require_permission($permission) {
    require_login();
    if (!has_permission($permission)) {
        header('Location: unauthorized.php');
        exit;
    }
}
?>
