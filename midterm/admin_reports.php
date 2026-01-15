<?php
include 'includes/db.php';
include 'includes/auth.php';
require_admin();
?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Báo Cáo Thống Kê - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="public/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="admin-page">

<?php include 'includes/admin_sidebar.php'; ?>

<div class="admin-main">
    <div class="container-fluid">
        <h1 style="font-size: 24px; font-weight: 700; margin-bottom: 30px;">Thống Kê & Báo Cáo</h1>
        
        <div class="card" style="padding: 40px; text-align: center;">
            <i class="fas fa-chart-line" style="font-size: 48px; color: var(--primary); margin-bottom: 20px;"></i>
            <h3>Tính năng đang được phát triển</h3>
            <p style="color: var(--text-secondary);">Biểu đồ doanh thu và phân tích sẽ sớm được cập nhật.</p>
        </div>
    </div>
</div>

<script src="public/script.js"></script>
</body>
</html>
