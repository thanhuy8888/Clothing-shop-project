<?php
include 'includes/db.php';
include 'includes/auth.php';
require_admin();

// --- CALCULATE STATISTICS ---
// 1. Total Revenue (only from non-cancelled orders)
$revenue_query = "SELECT SUM(total_amount) as total_revenue FROM orders WHERE status != 'cancelled'";
$revenue_result = $conn->query($revenue_query);
$revenue = $revenue_result->fetch_assoc()['total_revenue'] ?? 0;

// 2. Total Orders
$orders_count_query = "SELECT COUNT(*) as total_orders FROM orders";
$orders_count_result = $conn->query($orders_count_query);
$total_orders = $orders_count_result->fetch_assoc()['total_orders'] ?? 0;

// 3. Total Products
$products_count_query = "SELECT COUNT(*) as total_products FROM products";
$products_count_result = $conn->query($products_count_query);
$total_products = $products_count_result->fetch_assoc()['total_products'] ?? 0;

// 4. Total Customers (users with role 'user')
$customers_count_query = "SELECT COUNT(*) as total_customers FROM users WHERE role = 'user'";
$customers_count_result = $conn->query($customers_count_query);
$total_customers = $customers_count_result->fetch_assoc()['total_customers'] ?? 0;

// --- GET RECENT ORDERS ---
$recent_orders_query = "
    SELECT o.id, o.total_amount, o.status, o.created_at, u.name as customer_name 
    FROM orders o 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 10
";
$recent_orders = $conn->query($recent_orders_query);

include 'includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <!-- Dashboard Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--text-main); margin: 0;">Dashboard Quản Trị</h1>
            <p style="color: var(--text-secondary); margin-top: 5px;">Xin chào, <?php echo htmlspecialchars(current_user()['name']); ?>!</p>
        </div>
        <div>
            <a href="product_form.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm Sản Phẩm
            </a>
        </div>
    </div>

    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px;">
        <!-- Revenue Card -->
        <div class="card" style="padding: 24px; display: flex; align-items: center; gap: 20px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(40, 167, 69, 0.1); display: flex; align-items: center; justify-content: center; color: #28a745; font-size: 24px;">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <div style="font-size: 14px; color: var(--text-secondary); font-weight: 500;">Tổng Doanh Thu</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                    <?php echo number_format($revenue); ?>₫
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <a href="admin_orders.php" class="card" style="padding: 24px; display: flex; align-items: center; gap: 20px; text-decoration: none; transition: transform 0.2s;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(0, 123, 255, 0.1); display: flex; align-items: center; justify-content: center; color: #007bff; font-size: 24px;">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div>
                <div style="font-size: 14px; color: var(--text-secondary); font-weight: 500;">Tổng Đơn Hàng</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                    <?php echo number_format($total_orders); ?>
                </div>
            </div>
        </a>

        <!-- Products Card -->
        <div class="card" style="padding: 24px; display: flex; align-items: center; gap: 20px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(255, 193, 7, 0.1); display: flex; align-items: center; justify-content: center; color: #ffc107; font-size: 24px;">
                <i class="fas fa-tshirt"></i>
            </div>
            <div>
                <div style="font-size: 14px; color: var(--text-secondary); font-weight: 500;">Sản Phẩm</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                    <?php echo number_format($total_products); ?>
                </div>
            </div>
        </div>

        <!-- Customers Card -->
        <a href="admin_users.php" class="card" style="padding: 24px; display: flex; align-items: center; gap: 20px; text-decoration: none; transition: transform 0.2s;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(108, 117, 125, 0.1); display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 24px;">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <div style="font-size: 14px; color: var(--text-secondary); font-weight: 500;">Khách Hàng</div>
                <div style="font-size: 24px; font-weight: 700; color: var(--text-main); margin-top: 4px;">
                    <?php echo number_format($total_customers); ?>
                </div>
            </div>
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
        <!-- Recent Orders Table -->
        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 18px; font-weight: 700; margin: 0;">Đơn Hàng Gần Đây</h3>
                <a href="admin_orders.php" style="font-size: 14px; color: var(--primary); text-decoration: none;">Xem tất cả</a>
            </div>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th style="padding: 15px 20px; text-align: left; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Mã ĐH</th>
                            <th style="padding: 15px 20px; text-align: left; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Khách Hàng</th>
                            <th style="padding: 15px 20px; text-align: right; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Tổng Tiền</th>
                            <th style="padding: 15px 20px; text-align: center; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Trạng Thái</th>
                            <th style="padding: 15px 20px; text-align: right; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Ngày Tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <?php while($order = $recent_orders->fetch_assoc()): 
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
                                $st = $order['status'];
                                $color = $status_colors[$st] ?? '#6c757d';
                                $label = $status_labels[$st] ?? $st;
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px 20px; font-weight: 600;">#<?php echo $order['id']; ?></td>
                                <td style="padding: 15px 20px;"><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                                <td style="padding: 15px 20px; text-align: right; font-weight: 600;"><?php echo number_format($order['total_amount']); ?>₫</td>
                                <td style="padding: 15px 20px; text-align: center;">
                                    <span style="padding: 4px 10px; border-radius: 20px; background: <?php echo $color; ?>20; color: <?php echo $color; ?>; font-size: 12px; font-weight: 600;">
                                        <?php echo $label; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px 20px; text-align: right; color: var(--text-secondary); font-size: 13px;">
                                    <?php echo date('d/m/Y', strtotime($order['created_at'])); ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="padding: 30px; text-align: center; color: var(--text-secondary);">Chưa có đơn hàng nào</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Links -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card" style="padding: 20px;">
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 15px;">Quản Lý Nhanh</h3>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="product_form.php?action=create" style="display: flex; align-items: center; gap: 10px; padding: 12px; background: #f8f9fa; border-radius: 8px; text-decoration: none; color: var(--text-main); transition: 0.2s;">
                        <i class="fas fa-plus-circle" style="color: var(--primary);"></i>
                        <span style="font-weight: 500;">Thêm sản phẩm mới</span>
                    </a>
                    <a href="admin_users.php" style="display: flex; align-items: center; gap: 10px; padding: 12px; background: #f8f9fa; border-radius: 8px; text-decoration: none; color: var(--text-main); transition: 0.2s;">
                        <i class="fas fa-users-cog" style="color: var(--warning);"></i>
                        <span style="font-weight: 500;">Quản lý Người dùng</span>
                    </a>
                     <!-- Placeholder links -->
                    <a href="#" style="display: flex; align-items: center; gap: 10px; padding: 12px; background: #f8f9fa; border-radius: 8px; text-decoration: none; color: var(--text-main); transition: 0.2s; opacity: 0.6; cursor: not-allowed;">
                        <i class="fas fa-chart-bar" style="color: #6c757d;"></i>
                        <span style="font-weight: 500;">Báo cáo chi tiết (Coming soon)</span>
                    </a>
                </div>
            </div>

            <div class="card" style="padding: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 10px;">Mẹo quản trị</h3>
                <p style="font-size: 13px; line-height: 1.6; opacity: 0.9; margin: 0;">
                    Thường xuyên kiểm tra đơn hàng "Đang xử lý" để đảm bảo giao hàng đúng hạn cho khách hàng.
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
