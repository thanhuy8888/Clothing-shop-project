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
                
                // Remember Me Logic
                if (isset($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32)); // Create a secure token
                    // In a real app, store this token in DB linked to user
                    // For this simple version, we'll store basic encrypted info (less secure but works without DB changes)
                    $cookie_val = base64_encode($row['id'] . ':' . hash('sha256', $row['password_hash'] . 'secret_key_salt')); 
                    setcookie('remember_token', $cookie_val, time() + (86400 * 30), "/"); // 30 days
                }

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
      <div class="form-group" style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px;">
        <input type="checkbox" name="remember" id="remember" style="width: auto;">
        <label for="remember" style="margin: 0; font-weight: normal; font-size: 14px; user-select: none;">Ghi nhớ đăng nhập</label>
      </div>
      <button class="btn btn-primary" type="submit" style="width: 100%;">Đăng Nhập</button>
    </form>
    <div class="auth-footer">
      <p>Bạn chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
