<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    $address = trim($_POST['address'] ?? '');
    if (!$name || !$email || !$password){
        $alert = '<div class="alert alert-error">Vui lòng điền đầy đủ thông tin.</div>';
    } elseif ($password !== $confirm){
        $alert = '<div class="alert alert-error">Mật khẩu xác nhận không khớp.</div>';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash, address) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $address);
        if ($stmt->execute()){
            $_SESSION['user'] = ['id'=>$stmt->insert_id, 'name'=>$name, 'email'=>$email];
            header('Location: index.php'); exit;
        } else {
            if ($conn->errno === 1062){
                $alert = '<div class="alert alert-error">Email đã tồn tại.</div>';
            } else {
                $alert = '<div class="alert alert-error">Lỗi: '.htmlspecialchars($conn->error).'</div>';
            }
        }
        $stmt->close();
    }
}
include 'includes/header.php';
?>
<div class="auth-wrapper">
  <div class="card">
    <h2>Đăng Ký</h2>
    <?php echo $alert; ?>
    <form method="post">
      <div class="form-group">
        <label>Họ và tên</label>
        <input type="text" name="name" required placeholder="Nguyễn Văn A">
      </div>
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" required placeholder="you@example.com">
      </div>
      <div class="form-group">
        <label>Mật khẩu</label>
        <input type="password" name="password" required minlength="8" placeholder="Tối thiểu 8 ký tự">
      </div>
      <div class="form-group">
        <label>Xác nhận mật khẩu</label>
        <input type="password" name="confirm" required minlength="8" placeholder="Nhập lại mật khẩu">
      </div>
      <div class="form-group">
        <label>Địa chỉ</label>
        <input type="text" name="address" required placeholder="Số nhà, Tên đường, Quận/Huyện, Tỉnh/TP">
      </div>
      <button class="btn btn-primary" type="submit" style="width: 100%;">Tạo Tài Khoản</button>
    </form>
    <div class="auth-footer">
      <p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
