<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$msg = $_GET['msg'] ?? '';
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password){
        $alert = '<div class="alert alert-error">yeu cau email .</div>';
    } else {
        $stmt = $conn->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()){
            if (password_verify($password, $row['password_hash'])){
                $_SESSION['user'] = [
                    'id'=>$row['id'], 
                    'name'=>$row['name'], 
                    'email'=>$row['email'],
                    'role'=>$row['role'] ?? 'user'
                ];
                header('Location: index.php'); exit;
            } else {
                $alert = '<div class="alert alert-error"> WRONG INFORMATION .</div>';
            }
        } else {
            $alert = '<div class="alert alert-error"> DONT FOUND ACCOUNT.</div>';
        }
        $stmt->close();
    }
}
include 'includes/header.php';
?>
<div class="auth-wrapper">
  <div class="card">
    <h2>Đăng Nhập</h2>
    <?php if ($msg) echo '<div class="alert alert-error">'.htmlspecialchars($msg).'</div>'; ?>
    <?php echo $alert; ?>
    <form method="post">
      <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label>Mật khẩu</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button class="btn btn-primary" type="submit" style="width: 100%;">Đăng Nhập</button>
    </form>
    <div class="auth-footer">
      <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
