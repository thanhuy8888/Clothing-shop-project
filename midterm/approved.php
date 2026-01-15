<?php
include 'includes/db.php';
include 'includes/auth.php';
require_admin();

$page_title = 'Sản phẩm đã duyệt';

// Handle Actions (Reject/Delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = (int)$_POST['id'];
    
    // Check if column exists before trying to update
    $check_col = $conn->query("SHOW COLUMNS FROM products LIKE 'approval_status'");
    if ($check_col->num_rows > 0) {
        if ($action === 'reject') {
            $conn->query("UPDATE products SET approval_status = 'rejected' WHERE id = $id");
        }
    }
    
    if ($action === 'delete') {
        $conn->query("DELETE FROM products WHERE id = $id");
    }
    
    header("Location: approved.php");
    exit;
}

// Prepare Query with Error Handling for missing column
$result = false;
$error_db = '';
try {
    // Check if column exists first to avoid fatal error if exception mode not set
    $check_col = $conn->query("SHOW COLUMNS FROM products LIKE 'approval_status'");
    if ($check_col->num_rows > 0) {
        $result = $conn->query("SELECT * FROM products WHERE approval_status = 'approved' ORDER BY created_at DESC");
    } else {
        // Fallback: If no column, maybe show all? Or show empty? 
        // Showing empty is safer to avoid showing unapproved items.
        $error_db = "Database chưa được cập nhật. Vui lòng chạy <a href='update_db.php'>update_db.php</a> để thêm cột 'approval_status'.";
    }
} catch (Exception $e) {
    $error_db = "Lỗi truy vấn: " . $e->getMessage();
}

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

        <?php if ($error_db): ?>
            <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $error_db; ?>
            </div>
        <?php endif; ?>

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
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($p = $result->fetch_assoc()): 
                                $img = $p['image_url'] ?: 'https://via.placeholder.com/50';
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
                                    <span style="font-size: 12px; font-weight: 600; color: #28a745; background: #28a74520; padding: 4px 10px; border-radius: 20px;">
                                        Đã duyệt
                                    </span>
                                </td>
                                <td style="padding: 15px 20px; text-align: right;">
                                    <div style="display: flex; justify-content: flex-end; gap: 8px;">
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" name="action" value="reject" class="btn btn-sm" style="background:#dc3545; color:white; padding: 6px 10px;" title="Gỡ bỏ (Từ chối)"><i class="fas fa-times"></i></button>
                                        </form>
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
                            <tr><td colspan="6" style="padding: 40px; text-align: center; color: var(--text-secondary);">
                                <?php echo $error_db ? 'Vui lòng cập nhật Database.' : 'Không có sản phẩm nào đã duyệt'; ?>
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="public/script.js"></script>
</body>
</html>
