<?php
include 'includes/db.php';
include 'includes/header.php';
$feat = $conn->query("SELECT id, name, price, image_url, category FROM products ORDER BY updated_at DESC LIMIT 4");
?>
<div class="actions">
  <a class="btn" href="products.php"> PRODUCT</a>

  <?php if (empty($_SESSION['user'])): ?>
    <!-- Chỉ hiện khi CHƯA đăng nhập -->
    <a class="btn-secondary" href="register.php"> CREATE ACCOUNT </a>
  <?php else: ?>
    <!-- Đã đăng nhập: có thể thay bằng nút khác, tuỳ chọn -->
    <!-- <a class="btn-secondary" href="account.php"> MY ACCOUNT </a> -->
  <?php endif; ?>
</div>

<div class="card">
  <h2> NEW PRODUCT </h2>
  <div class="products-grid">
    <?php while($p = $feat->fetch_assoc()): 
      $img = $p['image_url'] ?: 'https://picsum.photos/seed/p'.intval($p['id']).'/640/480';
    ?>
    <a class="product-card" href="products.php?id=<?php echo $p['id']; ?>">
      <img class="product-media" src="<?php echo htmlspecialchars($img); ?>" alt="">
      <div class="product-body">
        <div class="kicker"><?php echo htmlspecialchars($p['category'] ?: 'Apparel'); ?></div>
        <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
          <div><strong><?php echo htmlspecialchars($p['name']); ?></strong></div>
          <div class="price">$<?php echo number_format((float)$p['price'],2); ?></div>
        </div>
      </div>
    </a>
    <?php endwhile; ?>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
