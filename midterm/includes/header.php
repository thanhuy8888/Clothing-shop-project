<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Clothing Shop</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="public/styles.css">
</head>
<body>
  <div class="nav">
    <div class="container">
      <a href="index.php"><strong>👕 Clothing Shop</strong></a>
      <a href="products.php">Products</a>
      <?php if (!empty($_SESSION['user'])): ?>
        <a href="product_form.php?action=create">Add Product</a>
        <span class="badge">Hi, <?php echo htmlspecialchars($_SESSION['user']['name']); ?></span>
        <a class="right" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="" href="register.php">Register</a>
        <a class="right" href="login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="container">
