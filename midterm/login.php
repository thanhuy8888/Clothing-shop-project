<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$msg = $_GET['msg'] ?? '';
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password){
        $alert = '<div class="alert alert-error">Email and password is required .</div>';
    } else {
        $stmt = $conn->prepare('SELECT id, name, email, password_hash FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()){
            if (password_verify($password, $row['password_hash'])){
                $_SESSION['user'] = ['id'=>$row['id'], 'name'=>$row['name'], 'email'=>$row['email']];
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
<div class="card">
  <h2>Đăng nhập</h2>
  <?php if ($msg) echo '<div class="alert alert-error">'.htmlspecialchars($msg).'</div>'; ?>
  <?php echo $alert; ?>
  <form method="post">
    <label>Email</label>
    <input type="email" name="email" required placeholder="you@example.com">
    <label> Password </label>
    <input type="password" name="password" required placeholder="••••••••">
    <button class="btn" type="submit"> Log in </button>
  </form>
  <p> You don't have account? <a href="register.php"> Register</a></p>
</div>
<?php include 'includes/footer.php'; ?>
