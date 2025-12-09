<?php
include 'includes/db.php';
include 'includes/auth.php';
require_admin();

// Handle status update
if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $status, $order_id);
    
    if ($update_stmt->execute()) {
        $msg = "Cập nhật trạng thái đơn hàng #$order_id thành công!";
    } else {
        $error = "Lỗi cập nhật: " . $conn->error;
    }
}

// Filter logic
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$query = "
    SELECT o.id, o.total_amount, o.status, o.created_at, u.name as customer_name, u.email 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
";

if ($status_filter !== 'all') {
    $query .= " WHERE o.status = '" . $conn->real_escape_string($status_filter) . "'";
}

$query .= " ORDER BY o.created_at DESC";
$orders = $conn->query($query);

include 'includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="admin_dashboard.php" class="btn btn-outline" style="padding: 8px 12px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--text-main); margin: 0;">Quản Lý Đơn Hàng</h1>
        </div>
    </div>

    <?php if (isset($msg)): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Operations Bar -->
    <div class="card" style="padding: 20px; margin-bottom: 20px; display: flex; gap: 20px; align-items: center; flex-wrap: wrap;">
        <div style="font-weight: 600; color: var(--text-secondary);">Lọc theo trạng thái:</div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php
            $filters = [
                'all' => 'Tất cả',
                'pending' => 'Chờ xác nhận',
                'processing' => 'Đang xử lý',
                'shipped' => 'Đang giao',
                'delivered' => 'Đã giao',
                'cancelled' => 'Đã hủy'
            ];
            foreach ($filters as $key => $label): 
                $active = $status_filter === $key;
                $bg = $active ? 'var(--primary)' : '#f1f3f5';
                $color = $active ? 'white' : 'var(--text-main)';
            ?>
                <a href="?status=<?php echo $key; ?>" style="padding: 8px 16px; border-radius: 20px; background: <?php echo $bg; ?>; color: <?php echo $color; ?>; text-decoration: none; font-size: 14px; font-weight: 500; transition: 0.2s;">
                    <?php echo $label; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 15px 20px; text-align: left; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Mã ĐH</th>
                        <th style="padding: 15px 20px; text-align: left; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Khách Hàng</th>
                        <th style="padding: 15px 20px; text-align: right; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Tổng Tiền</th>
                        <th style="padding: 15px 20px; text-align: center; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Trạng Thái</th>
                        <th style="padding: 15px 20px; text-align: right; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Ngày Tạo</th>
                        <th style="padding: 15px 20px; text-align: center; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while($order = $orders->fetch_assoc()): 
                            $status_colors = [
                                'pending' => '#ffc107',
                                'processing' => '#17a2b8',
                                'shipped' => '#007bff',
                                'delivered' => '#28a745',
                                'cancelled' => '#dc3545'
                            ];
                            $st = $order['status'];
                            $color = $status_colors[$st] ?? '#6c757d';
                        ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 20px; font-weight: 600;">#<?php echo $order['id']; ?></td>
                            <td style="padding: 15px 20px;">
                                <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></div>
                                <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($order['email'] ?? ''); ?></div>
                            </td>
                            <td style="padding: 15px 20px; text-align: right; font-weight: 600;"><?php echo number_format($order['total_amount']); ?>₫</td>
                            <td style="padding: 15px 20px; text-align: center;">
                                <form method="POST" style="margin: 0;">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" style="padding: 4px 8px; border-radius: 12px; border: 1px solid <?php echo $color; ?>; color: <?php echo $color; ?>; background: white; font-size: 12px; font-weight: 600; cursor: pointer; outline: none; margin-right: 5px;">
                                        <option value="pending" <?php echo $st == 'pending' ? 'selected' : ''; ?>>Chờ xác nhận</option>
                                        <option value="processing" <?php echo $st == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                        <option value="shipped" <?php echo $st == 'shipped' ? 'selected' : ''; ?>>Đang giao</option>
                                        <option value="delivered" <?php echo $st == 'delivered' ? 'selected' : ''; ?>>Đã giao</option>
                                        <option value="cancelled" <?php echo $st == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                    </select>
                                    <?php if ($st === 'pending'): ?>
                                        <button type="submit" name="status" value="processing" style="padding: 4px 10px; border-radius: 12px; background: #28a745; color: white; border: none; font-size: 11px; font-weight: 600; cursor: pointer;">
                                            <i class="fas fa-check"></i> Xác nhận
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                            <td style="padding: 15px 20px; text-align: right; color: var(--text-secondary); font-size: 13px;">
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </td>
                            <td style="padding: 15px 20px; text-align: center;">
                                <a href="order_success.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                    <i class="fas fa-eye"></i> Xem
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="padding: 30px; text-align: center; color: var(--text-secondary);">Không tìm thấy đơn hàng nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
