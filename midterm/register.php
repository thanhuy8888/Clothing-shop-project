<?php
include 'includes/db.php';
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$alert = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if (!$name || !$email || !$password){
        $alert = '<div class="alert alert-error">Please write all information.</div>';
    } elseif ($password !== $confirm){
        $alert = '<div class="alert alert-error"> Password is not correct .</div>';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $name, $email, $hash);
        if ($stmt->execute()){
            $_SESSION['user'] = ['id'=>$stmt->insert_id, 'name'=>$name, 'email'=>$email];
            header('Location: index.php'); exit;
        } else {
            if ($conn->errno === 1062){
                $alert = '<div class="alert alert-error">Email has exist.</div>';
            } else {
                $alert = '<div class="alert alert-error">Lỗi: '.htmlspecialchars($conn->error).'</div>';
            }
        }
        $stmt->close();
    }
}
include 'includes/header.php';
?>
<div class="card">
  <h2> Register </h2>
  <?php echo $alert; ?>
  <form method="post">
    <label> Full Name</label>
    <input type="text" name="name" required placeholder="Nguyễn Văn A">
    <label>Email</label>
    <input type="email" name="email" required placeholder="you@example.com">
    <label> Password</label>
    <input type="password" name="password" required minlength="8" placeholder="Tối thiểu 8 ký tự">
    <label> Confirm Password</label>
    <input type="password" name="confirm" required minlength="8">
    <button class="btn" type="submit"> Create account</button>
  </form>
  <p> Already have ? <a href="login.php"> Log in </a></p>
</div>
<?php include 'includes/footer.php'; ?>
