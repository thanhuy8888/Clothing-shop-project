<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$user_id = $_SESSION['user']['id'];
$errors = [];
$success = '';

// Get cart items
$stmt = $conn->prepare('
    SELECT c.id, c.quantity, c.size, p.id as product_id, p.name, p.price, p.sale_price, p.in_stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();

if ($cart_items->num_rows == 0) {
    header('Location: cart.php');
    exit;
}

$total = 0;
$items = [];
while ($item = $cart_items->fetch_assoc()) {
    $current_price = $item['sale_price'] ?: $item['price'];
    $item_total = $current_price * $item['quantity'];
    $total += $item_total;
    $items[] = $item;
}

$shipping = $total >= 299000 ? 0 : 30000;
$grand_total = $total + $shipping;

// Get user info
$user_stmt = $conn->prepare('SELECT name, email, phone, address FROM users WHERE id = ?');
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? '')) { die('Invalid CSRF token'); }
    
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    if (!$name) $errors[] = 'Tên người nhận là bắt buộc';
    if (!$phone) $errors[] = 'Số điện thoại là bắt buộc';
    if (!$address) $errors[] = 'Địa chỉ là bắt buộc';
    
    if (empty($errors)) {
        // Create order
        $order_stmt = $conn->prepare('INSERT INTO orders (user_id, total_amount, shipping_address, phone, notes) VALUES (?, ?, ?, ?, ?)');
        $order_stmt->bind_param('idsss', $user_id, $grand_total, $address, $phone, $notes);
        $order_stmt->execute();
        $order_id = $order_stmt->insert_id;
        $order_stmt->close();
        
        // Create order items
        foreach ($items as $item) {
            $current_price = $item['sale_price'] ?: $item['price'];
            $item_stmt = $conn->prepare('INSERT INTO order_items (order_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?)');
            $item_stmt->bind_param('iiids', $order_id, $item['product_id'], $item['quantity'], $current_price, $item['size']);
            $item_stmt->execute();
            $item_stmt->close();
        }
        
        // Clear cart
        $clear_stmt = $conn->prepare('DELETE FROM cart WHERE user_id = ?');
        $clear_stmt->bind_param('i', $user_id);
        $clear_stmt->execute();
        $clear_stmt->close();
        
        header('Location: order_success.php?order_id=' . $order_id);
        exit;
    }
}

include 'includes/header.php';
?>

<div class="page-header">
  <h1>Thanh toán</h1>
</div>

<?php if ($errors): ?>
  <div class="alert alert-error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
<?php endif; ?>

<div class="checkout-container">
  <div class="checkout-form">
    <div class="card">
      <h2>Thông tin giao hàng</h2>
      <form method="post">
        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
        
        <div class="form-group">
          <label>Họ và tên *</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
          <label>Số điện thoại *</label>
          <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
        </div>
        
        <div class="form-group">
          <label>Địa chỉ giao hàng *</label>
          <textarea name="address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-group">
          <label>Ghi chú (tùy chọn)</label>
          <textarea name="notes" rows="3" placeholder="Ghi chú cho người giao hàng..."></textarea>
        </div>
        
        <button type="submit" class="btn btn-checkout">Xác nhận đơn hàng</button>
      </form>
    </div>
  </div>
  
  <div class="checkout-summary">
    <div class="summary-card">
      <h3>Đơn hàng của bạn</h3>
      <div class="order-items">
        <?php foreach ($items as $item): 
          $current_price = $item['sale_price'] ?: $item['price'];
          $item_total = $current_price * $item['quantity'];
        ?>
          <div class="order-item">
            <div class="order-item-name">
              <strong><?php echo htmlspecialchars($item['name']); ?></strong>
              <span class="order-item-qty">x<?php echo $item['quantity']; ?></span>
            </div>
            <div class="order-item-price">
              <?php echo number_format($item_total); ?>₫
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      
      <div class="summary-divider"></div>
      
      <div class="summary-row">
        <span>Tạm tính:</span>
        <span><?php echo number_format($total); ?>₫</span>
      </div>
      <div class="summary-row">
        <span>Phí vận chuyển:</span>
        <span><?php echo $shipping > 0 ? number_format($shipping) . '₫' : 'Miễn phí'; ?></span>
      </div>
      <div class="summary-divider"></div>
      <div class="summary-row summary-total">
        <span><strong>Tổng cộng:</strong></span>
        <span><strong><?php echo number_format($grand_total); ?>₫</strong></span>
      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>

