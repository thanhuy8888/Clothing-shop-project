<?php
include 'includes/db.php';
include 'includes/header.php';
?>

<div class="container" style="padding: 80px 20px; text-align: center; min-height: 60vh;">
  <div style="max-width: 600px; margin: 0 auto;">
    <!-- Icon -->
    <div style="font-size: 120px; margin-bottom: 24px; line-height: 1;">🚫</div>
    
    <!-- Title -->
    <h1 style="font-size: 36px; font-weight: 800; margin-bottom: 16px; color: var(--text-main);">
      Bạn không có quyền truy cập
    </h1>
    
    <!-- Message -->
    <p style="font-size: 18px; color: var(--text-secondary); margin-bottom: 32px; line-height: 1.6;">
      Trang này chỉ dành cho quản trị viên.<br>
      Nếu bạn vừa được cấp quyền Admin, vui lòng <strong>đăng xuất</strong> và đăng nhập lại để cập nhật quyền.
    </p>
    
    <!-- Actions -->
    <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
      <a href="index.php" class="btn btn-outline" style="min-width: 160px;">
        ← Về trang chủ
      </a>
      <a href="logout.php" class="btn btn-primary" style="min-width: 160px;">
        Đăng xuất & Đăng nhập lại
      </a>
    </div>
    
    <!-- Additional info -->
    <div style="margin-top: 48px; padding: 24px; background: var(--bg-secondary); border-radius: var(--radius-lg); text-align: left;">
      <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 12px; color: var(--text-main);">
        💡 Thông tin
      </h3>
      <ul style="list-style: disc; padding-left: 24px; color: var(--text-secondary); font-size: 14px; line-height: 1.8;">
        <li>Chỉ tài khoản <strong>Admin</strong> mới có thể truy cập trang này.</li>
        <li>Nếu bạn cần quyền truy cập, vui lòng liên hệ quản trị viên.</li>
        <li>Sau khi được cấp quyền, nhớ đăng xuất và đăng nhập lại.</li>
      </ul>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
