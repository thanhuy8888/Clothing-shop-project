<?php 
if (session_status() === PHP_SESSION_NONE) { session_start(); } 
header('Content-Type: text/html; charset=utf-8');
include_once __DIR__ . '/db.php';
include_once __DIR__ . '/auth.php';
// Get cart count
$cart_count = 0;
if (!empty($_SESSION['user'])) {
    $cart_stmt = $conn->prepare('SELECT SUM(quantity) as total FROM cart WHERE user_id = ?');
    $cart_stmt->bind_param('i', $_SESSION['user']['id']);
    $cart_stmt->execute();
    $cart_result = $cart_stmt->get_result();
    if ($row = $cart_result->fetch_assoc()) {
        $cart_count = (int)($row['total'] ?? 0);
    }
    $cart_stmt->close();
}
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Clothing Shop - Thời Trang Nam Nữ Cao Cấp</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Cửa hàng thời trang nam nữ cao cấp với nhiều sản phẩm đa dạng, chất lượng tốt">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap&subset=vietnamese" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="public/styles.css?v=<?php echo time(); ?>">
</head>
<body>
  <!-- Top Bar -->
  <div class="top-bar">
    <div class="container">
      <span>Miễn phí giao hàng cho đơn hàng từ 700,000₫</span>
    </div>
  </div>

  <!-- Main Navigation -->
  <nav class="main-nav">
    <div class="container">
      <div class="nav-brand">
        <a href="index.php">
          <img src="public/logo_new.png" alt="WEB2 SHOP" style="height: 50px;">
        </a>
      </div>
      
      <ul class="nav-menu">
        <li><a href="index.php" class="nav-link">Trang Chủ</a></li>
        <li class="dropdown">
          <a href="products.php?category=Áo" class="nav-link">Áo ▾</a>
          <div class="dropdown-menu">
            <a href="products.php?category=Áo&subcategory=Áo Polo" class="dropdown-item">Áo Polo</a>
            <a href="products.php?category=Áo&subcategory=Áo Sơ Mi" class="dropdown-item">Áo Sơ Mi</a>
            <a href="products.php?category=Áo&subcategory=Áo Thun" class="dropdown-item">Áo Thun</a>
            <a href="products.php?category=Áo&subcategory=Áo Khoác" class="dropdown-item">Áo Khoác</a>
            <a href="products.php?category=Áo&subcategory=Áo Sweater" class="dropdown-item">Áo Sweater</a>
          </div>
        </li>
        <li class="dropdown">
          <a href="products.php?category=Quần" class="nav-link">Quần ▾</a>
          <div class="dropdown-menu">
            <a href="products.php?category=Quần&subcategory=Quần Tây" class="dropdown-item">Quần Tây</a>
            <a href="products.php?category=Quần&subcategory=Quần Jeans" class="dropdown-item">Quần Jeans</a>
            <a href="products.php?category=Quần&subcategory=Quần Khaki" class="dropdown-item">Quần Khaki</a>
            <a href="products.php?category=Quần&subcategory=Quần Short" class="dropdown-item">Quần Short</a>
          </div>
        </li>
        <li><a href="products.php?featured=1" class="nav-link">Bộ Sưu Tập</a></li>
        <li><a href="products.php?sale=1" class="nav-link" style="color: var(--danger);">Khuyến Mãi</a></li>
        <?php if (!empty($_SESSION['user'])): ?>
          <li><a href="admin_dashboard.php" class="nav-link">Quản Lý</a></li>
        <?php endif; ?>
      </ul>

      <div class="nav-actions">
        <div class="nav-icons">
          <a href="#" class="nav-icon-btn"><i class="fas fa-search"></i></a>
          
          <?php if (is_logged_in()): ?>
            <!-- Cart Menu -->
            <a href="cart.php" class="nav-icon-btn" style="position: relative;" title="Giỏ hàng">
              <i class="fas fa-shopping-cart"></i>
              <?php if ($cart_count > 0): ?>
                <span class="cart-count"><?php echo $cart_count; ?></span>
              <?php endif; ?>
            </a>
            
            <!-- Orders Menu with Badge -->
            <a href="orders.php" class="nav-icon-btn" style="position: relative;" title="Đơn hàng">
              <i class="fas fa-list-alt"></i>
              <span class="cart-count">1</span>
            </a>
            
            <!-- User Dropdown -->
            <div class="user-dropdown">
              <button class="user-dropdown-btn">
                <div class="user-avatar">
                  <?php echo strtoupper(substr(current_user()['name'], 0, 1)); ?>
                </div>
                <span class="user-name"><?php echo htmlspecialchars(current_user()['name']); ?></span>
                <?php if (is_admin()): ?>
                  <span class="admin-badge">Admin</span>
                <?php endif; ?>
                <i class="fas fa-chevron-down" style="font-size: 12px; margin-left: 4px;"></i>
              </button>
              <div class="user-dropdown-menu">
                <div class="dropdown-header">
                  <div class="user-avatar-large">
                    <?php echo strtoupper(substr(current_user()['name'], 0, 1)); ?>
                  </div>
                  <div>
                    <div class="dropdown-user-name"><?php echo htmlspecialchars(current_user()['name']); ?></div>
                    <div class="dropdown-user-email"><?php echo htmlspecialchars(current_user()['email']); ?></div>
                  </div>
                </div>
                <div class="dropdown-divider"></div>
                <a href="account.php" class="dropdown-item">
                  <i class="fas fa-user-circle"></i>
                  <span>Tài khoản của tôi</span>
                </a>
                <?php if (is_admin()): ?>
                  <div class="dropdown-divider"></div>
                  <a href="admin_dashboard.php" class="dropdown-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                  </a>
                  <a href="product_form.php" class="dropdown-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Thêm sản phẩm</span>
                  </a>
                <?php endif; ?>
                <div class="dropdown-divider"></div>
                <a href="logout.php" class="dropdown-item dropdown-item-danger">
                  <i class="fas fa-sign-out-alt"></i>
                  <span>Đăng xuất</span>
                </a>
              </div>
            </div>
          <?php else: ?>
            <a href="login.php" class="nav-icon-btn"><i class="fas fa-user"></i></a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>

  <div class="main-content">
