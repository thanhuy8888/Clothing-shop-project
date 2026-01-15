<?php
include 'includes/db.php';
include 'includes/auth.php';
require_admin();

$status = $_GET['status'] ?? 'all';
$page_title = 'Tất cả ý tưởng';
if ($status == 'pending') $page_title = 'Sản phẩm chờ duyệt';
if ($status == 'approved') $page_title = 'Sản phẩm đã duyệt';

// Handle Actions (Approve/Reject/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)$_POST['id'];
    
    if ($action === 'approve') {
        $conn->query("UPDATE products SET approval_status = 'approved' WHERE id = $id");
    } elseif ($action === 'reject') {
        $conn->query("UPDATE products SET approval_status = 'rejected' WHERE id = $id");
    } elseif ($action === 'delete') {
        $conn->query("DELETE FROM products WHERE id = $id");
    }
    
    header("Location: admin_products.php?status=$status");
    exit;
}

// Prepare Query
$where = "1=1";
if ($status == 'pending') $where .= " AND approval_status = 'pending'";
if ($status == 'approved') $where .= " AND approval_status = 'approved'";

$result = $conn->query("SELECT * FROM products WHERE $where ORDER BY created_at DESC");
?>

<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title><?php echo $page_title; ?> - Admin</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="public/styles.css">
</head>
<body class="admin-page">

<?php include 'includes/admin_sidebar.php'; ?>

<div class="admin-main">
    <div class="container-fluid">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-size: 24px; font-weight: 700; margin: 0;"><?php echo $page_title; ?></h1>
            <a href="product_form.php?action=create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm mới
            </a>
        </div>

        <div class="card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f8f9fa; border-bottom: 1px solid #eee;">
                        <tr>
                            <th style="padding: 15px 20px; text-align: left; width: 60px;">ID</th>
                            <th style="padding: 15px 20px; text-align: left; width: 80px;">Ảnh</th>
                            <th style="padding: 15px 20px; text-align: left;">Tên sản phẩm</th>
                            <th style="padding: 15px 20px; text-align: right;">Giá</th>
                            <th style="padding: 15px 20px; text-align: center;">Trạng thái</th>
                            <th style="padding: 15px 20px; text-align: right;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($p = $result->fetch_assoc()): 
                                $img = $p['image_url'] ?: 'https://via.placeholder.com/50';
                                $st = $p['approval_status'] ?? 'approved'; // Default to approved if column missing/old
                                $st_color = match($st) { 'pending' => '#ffc107', 'approved' => '#28a745', 'rejected' => '#dc3545', default => '#28a745' };
                                $st_label = match($st) { 'pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Bị từ chối', default => 'Đã duyệt' };
                            ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px 20px;">#<?php echo $p['id']; ?></td>
                                <td style="padding: 15px 20px;">
                                    <img src="<?php echo htmlspecialchars($img); ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td style="padding: 15px 20px;">
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-secondary);"><?php echo htmlspecialchars($p['category']); ?></div>
                                </td>
                                <td style="padding: 15px 20px; text-align: right;">
                                    <?php echo number_format($p['price']); ?>₫
                                </td>
                                <td style="padding: 15px 20px; text-align: center;">
                                    <span style="font-size: 12px; font-weight: 600; color: <?php echo $st_color; ?>; background: <?php echo $st_color; ?>20; padding: 4px 10px; border-radius: 20px;">
                                        <?php echo $st_label; ?>
                                    </span>
                                </td>
                                <td style="padding: 15px 20px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                        <?php if ($st == 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-sm" style="background:#28a745; color:white; padding: 6px 10px;" title="Duyệt"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                <button type="submit" name="action" value="reject" class="btn btn-sm" style="background:#dc3545; color:white; padding: 6px 10px;" title="Từ chối"><i class="fas fa-times"></i></button>
                                            </form>
                                        <?php endif; ?>
                                        <a href="product_form.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Xóa sản phẩm này?')">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger" style="background:transparent; color:#dc3545; border:1px solid #dc3545;" title="Xóa"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="padding: 40px; text-align: center; color: var(--text-secondary);">Không có dữ liệu</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
// Check URL to highlight sidebar (handled by PHP in sidebar but updated status requires refresh)
</script>
</body>
</html>
