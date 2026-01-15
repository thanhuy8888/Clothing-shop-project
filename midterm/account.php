<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$user_id = $_SESSION['user']['id'];
$success_msg = '';
$error_msg = '';

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, address, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name)) {
        $error_msg = 'Tên không được để trống.';
    } else {
        // Update basic info
        $sql = "UPDATE users SET name = ?, address = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $address, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['user']['name'] = $name; // Update session
            $success_msg = 'Cập nhật thông tin thành công.';
            $user['name'] = $name;
            $user['address'] = $address;
        } else {
            $error_msg = 'Có lỗi xảy ra: ' . $conn->error;
        }
        $stmt->close();

        // Update password if provided
        if (!empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $error_msg = 'Mật khẩu xác nhận không khớp.';
            } elseif (strlen($new_password) < 8) {
                $error_msg = 'Mật khẩu phải có ít nhất 8 ký tự.';
            } else {
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                $stmt->bind_param("si", $hash, $user_id);
                if ($stmt->execute()) {
                    $success_msg .= ' Đã đổi mật khẩu.';
                } else {
                    $error_msg = 'Lỗi đổi mật khẩu: ' . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container" style="padding: 40px 20px;">
    <div class="auth-wrapper" style="margin: 0 auto; max-width: 600px;">
        <div class="card">
            <h2 style="text-align: center; margin-bottom: 30px;">Thông Tin Tài Khoản</h2>
            
            <?php if ($success_msg): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error_msg): ?>
                <div class="alert alert-error" style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <form method="post">
                <div class="form-group">
                    <label>Email (Không thể thay đổi)</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: #f8f9fa; cursor: not-allowed;">
                </div>

                <div class="form-group">
                    <label>Họ và tên</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Địa chỉ</label>
                    <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                </div>

                <div style="border-top: 1px solid var(--border-color); margin: 30px 0 20px; padding-top: 20px;">
                    <h3 style="font-size: 18px; margin-bottom: 15px;">Đổi Mật Khẩu (Để trống nếu không đổi)</h3>
                    
                    <div class="form-group">
                        <label>Mật khẩu mới</label>
                        <input type="password" name="new_password" placeholder="••••••••">
                    </div>

                    <div class="form-group">
                        <label>Xác nhận mật khẩu mới</label>
                        <input type="password" name="confirm_password" placeholder="••••••••">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Cập Nhật Thông Tin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
