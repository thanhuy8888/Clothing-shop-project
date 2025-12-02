<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$user_id = $_SESSION['user']['id'];

// Get user's orders with order items
$stmt = $conn->prepare('
    SELECT o.id, o.total_amount, o.status, o.created_at, o.shipping_address, o.phone,
           COUNT(oi.id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

include 'includes/header.php';
?>

<div class="container" style="padding: 60px 20px;">
  <section class="section">
    <div class="section-header" style="text-align: center; margin-bottom: 40px;">
      <h2 class="section-title">Lịch Sử Đơn Hàng</h2>
      <p class="section-subtitle">Quản lý và theo dõi đơn hàng của bạn</p>
    </div>

    <?php if ($orders->num_rows > 0): ?>
      <div style="max-width: 1000px; margin: 0 auto;">
        <?php while ($order = $orders->fetch_assoc()): 
          // Get order items for this order
          $item_stmt = $conn->prepare('
              SELECT oi.quantity, oi.price, oi.size, p.name, p.image_url
              FROM order_items oi
              JOIN products p ON oi.product_id = p.id
              WHERE oi.order_id = ?
          ');
          $item_stmt->bind_param('i', $order['id']);
          $item_stmt->execute();
          $order_items = $item_stmt->get_result();
          
          // Status color mapping
          $status_colors = [
              'pending' => '#ffc107',
              'processing' => '#17a2b8',
              'shipped' => '#007bff',
              'delivered' => '#28a745',
              'cancelled' => '#dc3545'
          ];
          $status_labels = [
              'pending' => 'Chờ xác nhận',
              'processing' => 'Đang xử lý',
              'shipped' => 'Đang giao',
              'delivered' => 'Đã giao',
              'cancelled' => 'Đã hủy'
          ];
          $status = $order['status'];
          $status_color = $status_colors[$status] ?? '#6c757d';
          $status_label = $status_labels[$status] ?? ucfirst($status);
        ?>
          <div class="card" style="margin-bottom: 24px; padding: 0; overflow: hidden;">
            <!-- Order Header -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px 24px; color: white;">
              <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                <div>
                  <h3 style="font-size: 18px; font-weight: 700; margin: 0 0 8px 0;">
                    Đơn hàng #<?php echo $order['id']; ?>
                  </h3>
                  <div style="font-size: 14px; opacity: 0.9;">
                    <i class="far fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                  </div>
                </div>
                <div style="text-align: right;">
                  <div style="display: inline-block; padding: 6px 16px; background: rgba(255,255,255,0.2); border-radius: 20px; font-size: 14px; font-weight: 600; margin-bottom: 8px;">
                    <?php echo $status_label; ?>
                  </div>
                  <div style="font-size: 20px; font-weight: 700;">
                    <?php echo number_format($order['total_amount']); ?>₫
                  </div>
                </div>
              </div>
            </div>

            <!-- Order Items -->
            <div style="padding: 24px;">
              <h4 style="font-size: 15px; font-weight: 600; margin: 0 0 16px 0; color: var(--text-main);">
                Chi tiết đơn hàng (<?php echo $order['item_count']; ?> sản phẩm)
              </h4>
              
              <div style="display: grid; gap: 12px;">
                <?php while ($item = $order_items->fetch_assoc()): 
                  $img = $item['image_url'] ?: 'https://picsum.photos/seed/order/200/200';
                ?>
                  <div style="display: grid; grid-template-columns: 60px 1fr auto; gap: 16px; align-items: center; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                    <div>
                      <div style="font-size: 14px; font-weight: 600; color: var(--text-main); margin-bottom: 4px;">
                        <?php echo htmlspecialchars($item['name']); ?>
                      </div>
                      <div style="font-size: 13px; color: var(--text-secondary);">
                        <?php if ($item['size']): ?>
                          Size: <?php echo htmlspecialchars($item['size']); ?> | 
                        <?php endif; ?>
                        Số lượng: <?php echo $item['quantity']; ?>
                      </div>
                    </div>
                    <div style="font-size: 15px; font-weight: 700; color: var(--primary); text-align: right;">
                      <?php echo number_format($item['price'] * $item['quantity']); ?>₫
                    </div>
                  </div>
                <?php endwhile; 
                $item_stmt->close();
                ?>
              </div>

              <!-- Shipping Info -->
              <div style="margin-top: 20px; padding: 16px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid var(--primary);">
                <h4 style="font-size: 14px; font-weight: 600; margin: 0 0 8px 0; color: var(--text-main);">
                  <i class="fas fa-map-marker-alt"></i> Thông tin giao hàng
                </h4>
                <div style="font-size: 13px; color: var(--text-secondary); line-height: 1.6;">
                  <div><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></div>
                  <div><strong>Số điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></div>
                </div>
              </div>

              <!-- Actions -->
              <div style="margin-top: 20px; text-align: right;">
                <a href="order_success.php?order_id=<?php echo $order['id']; ?>" class="btn btn-outline" style="padding: 10px 24px; font-size: 14px; background: white; border: 1px solid var(--primary); color: var(--primary);">
                  <i class="fas fa-eye"></i> Xem chi tiết
                </a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </div>

    <?php else: ?>
      <div class="card" style="padding: 80px 40px; text-align: center; max-width: 600px; margin: 0 auto;">
        <div style="font-size: 80px; margin-bottom: 20px; opacity: 0.3;">📦</div>
        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 12px; color: var(--text-main);">Chưa Có Đơn Hàng</h3>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">Bạn chưa có đơn hàng nào. Hãy bắt đầu mua sắm ngay!</p>
        <a href="products.php" class="btn btn-primary" style="padding: 14px 32px;">
          <i class="fas fa-shopping-bag"></i> Khám Phá Sản Phẩm
        </a>
      </div>
    <?php endif; ?>
  </section>
</div>

<?php include 'includes/footer.php'; ?>
