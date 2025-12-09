<?php
include 'includes/db.php';
include 'includes/auth.php';
require_admin();

// Handle role update
if (isset($_POST['user_id']) && isset($_POST['role'])) {
    // Prevent CSRF (simplified for now as per existing pattern, but ideally should use tokens)
    $uid = intval($_POST['user_id']);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    
    // Prevent self-demotion
    if ($uid == current_user()['id'] && $role == 'user') {
        $error = "Bạn không thể tự hủy quyền Admin của chính mình!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $uid);
        if ($stmt->execute()) {
            $msg = "Đã cập nhật quyền thành công!";
        } else {
            $error = "Lỗi: " . $conn->error;
        }
    }
}

// Get all users
$result = $conn->query("SELECT id, name, email, role, created_at FROM users ORDER BY id DESC");
include 'includes/header.php';
?>

<div class="container" style="padding: 40px 20px; max-width: 1200px;">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="admin_dashboard.php" class="btn btn-outline" style="padding: 8px 12px; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 style="font-size: 28px; font-weight: 700; color: var(--text-main); margin: 0;">Quản Lý Người Dùng</h1>
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

    <!-- Users Table -->
    <div class="card" style="padding: 0; overflow: hidden;">
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th style="padding: 15px 20px; text-align: left; font-size: 13px; color: var(--text-secondary); font-weight: 600;">ID</th>
                        <th style="padding: 15px 20px; text-align: left; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Thông tin</th>
                        <th style="padding: 15px 20px; text-align: left; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Vai Trò</th>
                        <th style="padding: 15px 20px; text-align: right; font-size: 13px; color: var(--text-secondary); font-weight: 600;">Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr style="border-bottom: 1px solid #eee;">
                            <td style="padding: 15px 20px; color: var(--text-secondary);">#<?php echo $row['id']; ?></td>
                            <td style="padding: 15px 20px;">
                                <div style="font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($row['name']); ?></div>
                                <div style="font-size: 13px; color: var(--text-secondary);"><?php echo htmlspecialchars($row['email']); ?></div>
                            </td>
                            <td style="padding: 15px 20px;">
                                <?php if ($row['role'] === 'admin'): ?>
                                    <span style="padding: 4px 10px; border-radius: 20px; background: #28a74520; color: #28a745; font-size: 12px; font-weight: 600;">Admin</span>
                                <?php else: ?>
                                    <span style="padding: 4px 10px; border-radius: 20px; background: #6c757d20; color: #6c757d; font-size: 12px; font-weight: 600;">User</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 15px 20px; text-align: right;">
                                <form method="post" style="display: inline-block;">
                                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                    <?php if ($row['role'] !== 'admin'): ?>
                                        <input type="hidden" name="role" value="admin">
                                        <button type="submit" class="btn btn-primary" style="padding: 6px 12px; font-size: 12px;">
                                            Thăng làm Admin
                                        </button>
                                    <?php else: ?>
                                        <input type="hidden" name="role" value="user">
                                        <?php if ($row['id'] == current_user()['id']): ?>
                                            <button type="button" class="btn btn-outline" disabled style="padding: 6px 12px; font-size: 12px; opacity: 0.5; cursor: not-allowed;">
                                                Hủy quyền
                                            </button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; border-color: #dc3545; color: #dc3545;">
                                                Hủy quyền
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="padding: 30px; text-align: center; color: var(--text-secondary);">Không tìm thấy người dùng nào</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
