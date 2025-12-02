<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$user_id = $_SESSION['user']['id'];
$errors = [];
$success = '';

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!csrf_check($_POST['csrf'] ?? '')) { die('Invalid CSRF token'); }
    $product_id = intval($_POST['product_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    $size = trim($_POST['size'] ?? '');
    
    if ($product_id > 0 && $quantity > 0) {
        $check = $conn->prepare('SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size = ?');
        $check->bind_param('iis', $user_id, $product_id, $size);
        $check->execute();
        $result = $check->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $new_qty = $row['quantity'] + $quantity;
            $update = $conn->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
            $update->bind_param('ii', $new_qty, $row['id']);
            $update->execute();
            $update->close();
        } else {
            $insert = $conn->prepare('INSERT INTO cart (user_id, product_id, quantity, size) VALUES (?, ?, ?, ?)');
            $insert->bind_param('iiis', $user_id, $product_id, $quantity, $size);
            $insert->execute();
            $insert->close();
        }
        $check->close();
        $success = 'Đã thêm vào giỏ hàng!';
    }
}

// Handle update quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    if (!csrf_check($_POST['csrf'] ?? '')) { die('Invalid CSRF token'); }
    $cart_id = intval($_POST['cart_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if ($cart_id > 0) {
        if ($quantity > 0) {
            $stmt = $conn->prepare('UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?');
            $stmt->bind_param('iii', $quantity, $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $conn->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
            $stmt->bind_param('ii', $cart_id, $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $cart_id = intval($_GET['remove']);
    $stmt = $conn->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
    header('Location: cart.php');
    exit;
}

// Get cart items
$stmt = $conn->prepare('
    SELECT c.id, c.quantity, c.size, p.id as product_id, p.name, p.sku, p.price, p.sale_price, p.image_url, p.in_stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
    ORDER BY c.created_at DESC
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$total = 0;
$item_count = 0;

include 'includes/header.php';
?>

<div class="container" style="padding: 60px 20px;">
  <section class="section">
    <div class="section-header" style="text-align: center; margin-bottom: 40px;">
      <h2 class="section-title">Giỏ Hàng Của Bạn</h2>
      <p class="section-subtitle">Quản lý sản phẩm và thanh toán đơn hàng</p>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 30px; text-align: center;">
        <?php echo htmlspecialchars($success); ?>
      </div>
    <?php endif; ?>

    <?php if ($cart_items->num_rows > 0): ?>
      <div style="display: grid; grid-template-columns: 1.8fr 1fr; gap: 30px; margin-bottom: 40px;">
        
        <!-- Cart Items -->
        <div>
          <?php while ($item = $cart_items->fetch_assoc()): 
            $current_price = $item['sale_price'] ?: $item['price'];
            $item_total = $current_price * $item['quantity'];
            $total += $item_total;
            $item_count += $item['quantity'];
            $img = $item['image_url'] ?: 'https://picsum.photos/seed/p'.$item['product_id'].'/200/200';
          ?>
            <div class="card" style="margin-bottom: 16px; padding: 20px;">
              <div style="display: grid; grid-template-columns: 100px 1fr auto; gap: 20px; align-items: center;">
                
                <!-- Product Image -->
                <img src="<?php echo htmlspecialchars($img); ?>" alt="" style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                
                <!-- Product Info -->
                <div>
                  <a href="products.php?id=<?php echo $item['product_id']; ?>" style="text-decoration: none;">
                    <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 8px 0; color: var(--text-main);">
                      <?php echo htmlspecialchars($item['name']); ?>
                    </h3>
                  </a>
                  <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 8px;">
                    SKU: <?php echo htmlspecialchars($item['sku']); ?>
                    <?php if ($item['size']): ?>
                      | Size: <?php echo htmlspecialchars($item['size']); ?>
                    <?php endif; ?>
                  </div>
                  <div style="display: flex; align-items: center; gap: 16px;">
                    <div style="font-size: 18px; font-weight: 700; color: var(--primary);">
                      <?php if ($item['sale_price']): ?>
                        <?php echo number_format($item['sale_price']); ?>₫
                        <span style="text-decoration: line-through; font-size: 14px; color: var(--text-secondary); font-weight: 400; margin-left: 8px;">
                          <?php echo number_format($item['price']); ?>₫
                        </span>
                      <?php else: ?>
                        <?php echo number_format($item['price']); ?>₫
                      <?php endif; ?>
                    </div>
                    <form method="post" style="display: flex; align-items: center; gap: 8px;">
                      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                      <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                      <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['in_stock']; ?>" style="width: 70px; padding: 6px; border: 1px solid var(--border-color); border-radius: 4px; text-align: center;">
                      <button type="submit" name="update_cart" class="btn btn-sm" style="padding: 6px 12px; font-size: 13px;">Cập nhật</button>
                    </form>
                  </div>
                </div>
                
                <!-- Actions -->
                <div style="display: flex; flex-direction: column; gap: 12px; align-items: flex-end;">
                  <div style="font-size: 18px; font-weight: 700; color: var(--text-main);">
                    <?php echo number_format($item_total); ?>₫
                  </div>
                  <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                     onclick="return confirm('Xóa sản phẩm này khỏi giỏ hàng?');" 
                     style="color: var(--danger); font-size: 13px; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                    <i class="fas fa-trash"></i> Xóa
                  </a>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
        
        <!-- Order Summary -->
        <div style="position: sticky; top: 100px;">
          <div class="card" style="padding: 24px;">
            <h3 style="font-size: 20px; font-weight: 700; margin: 0 0 20px 0; color: var(--primary);">Tóm Tắt Đơn Hàng</h3>
            
            <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px;">
              <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="color: var(--text-secondary);">Tổng sản phẩm:</span>
                <span style="font-weight: 600;"><?php echo $item_count; ?> sản phẩm</span>
              </div>
              <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span style="color: var(--text-secondary);">Tạm tính:</span>
                <span style="font-weight: 600;"><?php echo number_format($total); ?>₫</span>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="color: var(--text-secondary);">Phí vận chuyển:</span>
                <span style="font-weight: 600; color: var(--success);">
                  <?php echo $total >= 299000 ? 'Miễn phí' : '30,000₫'; ?>
                </span>
              </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 24px; font-size: 20px;">
              <span style="font-weight: 700;">Tổng cộng:</span>
              <span style="font-weight: 700; color: var(--accent);">
                <?php echo number_format($total + ($total >= 299000 ? 0 : 30000)); ?>₫
              </span>
            </div>
            
            <a href="checkout.php" class="btn btn-primary" style="width: 100%; text-align: center; padding: 14px; margin-bottom: 12px;">
              Tiến Hành Thanh Toán
            </a>
            <a href="products.php" class="btn btn-outline" style="width: 100%; text-align: center; padding: 12px; background: white; border: 1px solid var(--border-color);">
              Tiếp Tục Mua Sắm
            </a>
            
            <?php if ($total >= 299000): ?>
              <div style="margin-top: 16px; padding: 12px; background: rgba(40, 167, 69, 0.1); border-radius: 8px; text-align: center; font-size: 13px; color: var(--success);">
                <i class="fas fa-check-circle"></i> Bạn được miễn phí vận chuyển!
              </div>
            <?php else: ?>
              <div style="margin-top: 16px; padding: 12px; background: rgba(255, 193, 7, 0.1); border-radius: 8px; text-align: center; font-size: 13px; color: #856404;">
                Mua thêm <?php echo number_format(299000 - $total); ?>₫ để được miễn phí vận chuyển
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
    <?php else: ?>
      <div class="card" style="padding: 80px 40px; text-align: center;">
        <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">🛒</div>
        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 12px; color: var(--text-main);">Giỏ Hàng Trống</h3>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">Bạn chưa có sản phẩm nào trong giỏ hàng</p>
        <a href="products.php" class="btn btn-primary" style="padding: 14px 32px;">
          Khám Phá Sản Phẩm
        </a>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php include 'includes/footer.php'; ?>
