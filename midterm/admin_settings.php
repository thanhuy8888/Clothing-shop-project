<?php
include 'includes/db.php';
include 'includes/auth.php';
require_admin();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Cấu Hình Chung - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="public/styles.css">
</head>
<body class="admin-page">

<?php include 'includes/admin_sidebar.php'; ?>

<div class="admin-main">
    <div class="container-fluid">
        <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 30px;">Cấu Hình Hệ Thống</h1>
        
        <div class="card" style="padding: 30px;">
            <form>
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Tên Website</label>
                    <input type="text" class="form-control" value="Clothing Shop" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Email Liên Hệ</label>
                    <input type="email" class="form-control" value="contact@example.com" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                </div>
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600;">Số Điện Thoại</label>
                    <input type="text" class="form-control" value="0123 456 789" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" checked>
                        <span>Cho phép đăng ký thành viên mới</span>
                    </label>
                </div>

                <button type="button" class="btn btn-primary" onclick="alert('Đã lưu cấu hình (Demo)')">Lưu Thay Đổi</button>
            </form>
        </div>
    </div>
</div>

<script src="public/script.js"></script>
</body>
</html>
