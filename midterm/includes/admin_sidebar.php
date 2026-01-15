<div class="admin-sidebar">
    <div class="sidebar-header">
        <a href="admin_dashboard.php" style="text-decoration: none; color: white; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-shield-alt" style="font-size: 24px;"></i>
            <span style="font-weight: 700; font-size: 18px;">Admin Panel</span>
        </a>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-label">TỔNG QUAN</div>
        <a href="admin_dashboard.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <div class="menu-label">QUẢN LÝ SẢN PHẨM</div>
        <a href="admin_products.php" class="menu-item <?php echo (basename($_SERVER['PHP_SELF']) == 'admin_products.php' && !isset($_GET['status'])) ? 'active' : ''; ?>">
            <i class="fas fa-box"></i> Tất cả ý tưởng
        </a>
        <a href="admin_products.php?status=pending" class="menu-item <?php echo (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Mới gửi <span class="badge-count pending-count-badge">Wait</span>
        </a>
        <a href="approved.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'approved.php' ? 'active' : ''; ?>">
            <i class="fas fa-check-circle"></i> Đã duyệt
        </a>

        <div class="menu-label">HỆ THỐNG</div>
        <a href="admin_users.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Quản lý tài khoản
        </a>
        <a href="admin_orders.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''; ?>">
            <i class="fas fa-shopping-cart"></i> Đơn hàng
        </a>
        <a href="admin_reports.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Thống kê báo cáo
        </a>
        <a href="admin_settings.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'admin_settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Cấu hình chung
        </a>

        <div class="menu-label">CÁ NHÂN</div>
        <a href="index.php" class="menu-item">
            <i class="fas fa-home"></i> Xem trang chủ
        </a>
        <a href="logout.php" class="menu-item" style="color: #ff6b6b;">
            <i class="fas fa-sign-out-alt"></i> Đăng xuất
        </a>
    </div>
</div>

<style>
.admin-sidebar {
    width: 260px;
    background: #1a1c23;
    color: #a0aec0;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    overflow-y: auto;
    z-index: 1000;
    display: flex;
    flex-direction: column;
}

.sidebar-header {
    padding: 24px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-menu {
    padding: 20px 0;
    flex: 1;
}

.menu-label {
    padding: 0 24px;
    margin-bottom: 10px;
    margin-top: 20px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #718096;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 12px 24px;
    color: inherit;
    text-decoration: none;
    transition: all 0.2s;
    font-size: 14px;
    font-weight: 500;
    border-left: 3px solid transparent;
}

.menu-item:hover, .menu-item.active {
    background: rgba(255,255,255,0.05);
    color: white;
}

.menu-item.active {
    border-left-color: var(--primary);
    background: rgba(59, 130, 246, 0.1);
}

.menu-item i {
    width: 24px;
    margin-right: 10px;
    font-size: 16px;
}

.badge-count {
    margin-left: auto;
    background: var(--danger);
    color: white;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
}

/* Base changes for Admin Pages */
body.admin-page {
    background-color: #f3f4f6;
    margin: 0;
}

.admin-main {
    margin-left: 260px;
    padding: 30px;
    min-height: 100vh;
}

@media (max-width: 768px) {
    .admin-sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s;
    }
    .admin-sidebar.open {
        transform: translateX(0);
    }
    .admin-main {
        margin-left: 0;
    }
}
</style>
