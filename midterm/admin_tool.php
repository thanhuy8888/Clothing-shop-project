<?php
include 'includes/db.php';

// Handle role update
if (isset($_POST['user_id']) && isset($_POST['role'])) {
    $uid = intval($_POST['user_id']);
    $role = $_POST['role'] === 'admin' ? 'admin' : 'user';
    
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $uid);
    if ($stmt->execute()) {
        $msg = "Đã cập nhật quyền thành công!";
    } else {
        $error = "Lỗi: " . $conn->error;
    }
}

// Get all users
$result = $conn->query("SELECT id, name, email, role FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Setup Tool</title>
    <style>
        body { font-family: sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        .btn { padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; color: white; }
        .btn-blue { background: #007bff; }
        .btn-gray { background: #6c757d; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-admin { background: #28a745; color: white; }
        .badge-user { background: #e9ecef; color: #333; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background: #d4edda; color: #155724; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; border: 1px solid #ffeeba; }
    </style>
</head>
<body>
    <h1>Quản Lý Quyền User</h1>
    
    <div class="warning">
        <strong>⚠️ QUAN TRỌNG:</strong> File này cho phép thay đổi quyền Admin. <br>
        Vui lòng <strong>XÓA FILE NÀY</strong> khỏi server ngay sau khi sử dụng xong để bảo mật!
    </div>

    <?php if (isset($msg)): ?>
        <div class="alert alert-success"><?php echo $msg; ?></div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Email</th>
                <th>Quyền hiện tại</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td>
                    <span class="badge <?php echo $row['role'] === 'admin' ? 'badge-admin' : 'badge-user'; ?>">
                        <?php echo strtoupper($row['role']); ?>
                    </span>
                </td>
                <td>
                    <form method="post" style="margin:0;">
                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                        <?php if ($row['role'] !== 'admin'): ?>
                            <input type="hidden" name="role" value="admin">
                            <button type="submit" class="btn btn-blue">Set Admin</button>
                        <?php else: ?>
                            <input type="hidden" name="role" value="user">
                            <button type="submit" class="btn btn-gray">Hủy Admin</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
