<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id > 0) {
    if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
        $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ?');
        $stmt->bind_param('i', $order_id);
    } else {
        $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? AND user_id = ?');
        $stmt->bind_param('ii', $order_id, $_SESSION['user']['id']);
    }
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    $order = null;
}

include 'includes/header.php';
?>

<div class="card" style="max-width:600px; margin:40px auto; text-align:center;">
  <?php if ($order): ?>
    <div style="font-size:64px; margin-bottom:20px;">✅</div>
    <h1 style="color:var(--success); margin-bottom:15px;">Đặt hàng thành công!</h1>
    <p style="font-size:18px; margin-bottom:30px;">Cảm ơn bạn đã mua sắm tại Clothing Shop</p>
    
    <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin:20px 0; text-align:left;">
      <p><strong>Mã đơn hàng:</strong> #<?php echo $order['id']; ?></p>
      <p><strong>Tổng tiền:</strong> <?php echo number_format($order['total_amount']); ?>₫</p>
      <p><strong>Trạng thái:</strong> <?php echo htmlspecialchars($order['status']); ?></p>
      <p><strong>Địa chỉ giao hàng:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
      <p><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
    </div>
    
    <div style="margin-top:30px;">
      <a href="products.php" class="btn">Tiếp tục mua sắm</a>
      <a href="index.php" class="btn-secondary">Về trang chủ</a>
    </div>
  <?php else: ?>
    <div style="font-size:64px; margin-bottom:20px;">❌</div>
    <h1>Không tìm thấy đơn hàng</h1>
    <p>Đơn hàng không tồn tại hoặc bạn không có quyền xem.</p>
    <div style="margin-top:30px;">
      <a href="products.php" class="btn">Xem sản phẩm</a>
    </div>
  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

