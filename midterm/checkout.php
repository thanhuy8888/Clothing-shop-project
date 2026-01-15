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


<div class="container" style="padding: 60px 20px; max-width: 1200px;">
  
  <div style="margin-bottom: 30px;">
    <h1 style="font-size: 28px; font-weight: 700; color: var(--text-main); margin-bottom: 10px;">Thanh Toán</h1>
    <p style="color: var(--text-secondary);">Vui lòng kiểm tra kỹ thông tin trước khi đặt hàng.</p>
  </div>

  <?php if ($errors): ?>
    <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
    </div>
  <?php endif; ?>

  <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; position: relative;">
    
    <!-- Left Column: Form -->
    <div>
        <div class="card" style="padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
            <h2 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                <i class="fas fa-map-marker-alt" style="color: var(--primary); margin-right: 8px;"></i> Thông tin giao hàng
            </h2>
            
            <form method="post">
                <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Họ tên người nhận <span style="color:red">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; outline: none;"
                           onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#ddd'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Số điện thoại <span style="color:red">*</span></label>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; outline: none;"
                           onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#ddd'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Địa chỉ nhận hàng <span style="color:red">*</span></label>
                    <textarea name="address" rows="3" required
                              style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; outline: none; resize: vertical;"
                              onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#ddd'"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>

                <div style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px;">Ghi chú (Tùy chọn)</label>
                    <textarea name="notes" rows="2" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao..."
                              style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.2s; outline: none; resize: vertical;"
                              onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='#ddd'"></textarea>
                </div>

                <button type="submit" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;">
                    Xác nhận đặt hàng
                </button>
            </form>
        </div>
    </div>

    <!-- Right Column: Summary -->
    <div>
        <div class="card" style="padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); position: sticky; top: 20px;">
            <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                <span>Đơn hàng của bạn</span>
                <span style="font-size: 12px; font-weight: 500; background: #eee; padding: 4px 8px; border-radius: 10px;"><?php echo count($items); ?> sản phẩm</span>
            </h3>

            <!-- Item List -->
            <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 20px; max-height: 300px; overflow-y: auto; padding-right: 5px;">
                <?php foreach ($items as $item): 
                    $current_price = $item['sale_price'] ?: $item['price'];
                    $item_total = $current_price * $item['quantity'];
                ?>
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--text-main);">
                            <?php echo htmlspecialchars($item['name']); ?>
                        </div>
                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                            Size: <?php echo htmlspecialchars($item['size']); ?> | SL: x<?php echo $item['quantity']; ?>
                        </div>
                    </div>
                    <div style="font-size: 14px; font-weight: 600; color: var(--text-main);">
                        <?php echo number_format($item_total); ?>₫
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="border-top: 1px dashed #eee; margin: 0 -25px; padding: 0 25px;"></div>

            <!-- Totals -->
            <div style="margin-top: 20px; display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; justify-content: space-between; font-size: 14px; color: var(--text-secondary);">
                    <span>Tạm tính</span>
                    <span><?php echo number_format($total); ?>₫</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 14px; color: var(--text-secondary);">
                    <span>Phí vận chuyển</span>
                    <span><?php echo $shipping > 0 ? number_format($shipping) . '₫' : 'Miễn phí'; ?></span>
                </div>
                <div style="border-top: 1px solid #eee; padding-top: 15px; margin-top: 5px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-weight: 700; font-size: 16px; color: var(--text-main);">Tổng cộng</span>
                    <span style="font-weight: 800; font-size: 20px; color: var(--primary);">
                        <?php echo number_format($grand_total); ?>₫
                    </span>
                </div>
            </div>

            <!-- Features -->
            <div style="margin-top: 25px; background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">
                    <i class="fas fa-shield-alt" style="color: #28a745;"></i> Bảo mật thanh toán 100%
                </div>
                <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text-secondary);">
                    <i class="fas fa-truck" style="color: #007bff;"></i> Giao hàng toàn quốc
                </div>
            </div>
            
        </div>
    </div>
    
  </div>
</div>

<?php include 'includes/footer.php'; ?>

