<?php
include 'includes/db.php';
include 'includes/header.php';

// Get featured products
$featured = $conn->query("SELECT id, name, price, sale_price, image_url, category, subcategory, featured FROM products WHERE featured = 1 ORDER BY updated_at DESC LIMIT 8");

// Get sale products
$sale = $conn->query("SELECT id, name, price, sale_price, image_url, category, subcategory FROM products WHERE sale_price IS NOT NULL AND sale_price > 0 ORDER BY updated_at DESC LIMIT 8");

// Get new products
$new = $conn->query("SELECT id, name, price, sale_price, image_url, category, subcategory FROM products ORDER BY created_at DESC LIMIT 8");
?>

<!-- Hero Section -->
<section class="hero-section">
  <img src="https://images.unsplash.com/photo-1441984904996-e0b6ba687e04?q=80&w=2070&auto=format&fit=crop" alt="Hero Background" class="hero-bg">
  <div class="hero-overlay"></div>
  <div class="container hero-content">
    <h1 class="hero-title">Nâng Tầm<br>Phong Cách Của Bạn</h1>
    <p class="hero-subtitle">Khám phá bộ sưu tập thời trang mới nhất với thiết kế hiện đại, tinh tế và chất lượng vượt trội.</p>
    <div style="display:flex; gap:16px;">
      <a href="products.php" class="btn btn-primary">Khám Phá Ngay</a>
      <a href="products.php?sale=1" class="btn btn-outline" style="color:white; border-color:white;">Xem Khuyến Mãi</a>
    </div>
  </div>
</section>

<div class="container">
  
  <!-- Featured Products -->
  <?php if ($featured->num_rows > 0): ?>
  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Sản Phẩm Nổi Bật</h2>
      <p class="section-subtitle">Những thiết kế được yêu thích nhất mùa này</p>
    </div>
    
    <div class="products-grid">
      <?php while($p = $featured->fetch_assoc()): 
        $img = $p['image_url'] ?: 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=400&q=80';
        $discount = $p['sale_price'] ? round((($p['price'] - $p['sale_price']) / $p['price']) * 100) : 0;
      ?>
      <div class="product-card">
        <div class="product-image-wrapper">
          <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="product-image">
          <div class="product-badges">
            <span class="badge badge-new">Nổi bật</span>
            <?php if ($discount > 0): ?>
              <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
            <?php endif; ?>
          </div>
          <div class="product-actions-overlay">
            <a href="products.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
            <?php if (!empty($_SESSION['user'])): ?>
              <div style="display: flex; gap: 8px; margin-top: 8px;">
                <a href="product_form.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm" style="background: white; color: var(--text-main);">Sửa</a>
                <a href="product_delete.php?id=<?php echo $p['id']; ?>&csrf=<?php echo csrf_token(); ?>" class="btn btn-danger btn-sm" style="background: var(--danger); color: white;" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="product-info">
          <span class="product-category"><?php echo htmlspecialchars($p['category']); ?></span>
          <h3 class="product-title"><a href="products.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a></h3>
          <div class="product-price">
            <?php if ($p['sale_price']): ?>
              <span class="price-current"><?php echo number_format($p['sale_price']); ?>₫</span>
              <span class="price-old"><?php echo number_format($p['price']); ?>₫</span>
            <?php else: ?>
              <span class="price-current"><?php echo number_format($p['price']); ?>₫</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- Sale Banner -->
  <section style="background-color: #1a1a1a; color: white; padding: 60px; border-radius: 12px; margin: 80px 0; text-align: center; position: relative; overflow: hidden;">
    <div style="position: relative; z-index: 2;">
      <h2 style="font-size: 36px; font-weight: 800; margin-bottom: 16px;">GIẢM GIÁ GIỮA MÙA</h2>
      <p style="font-size: 18px; opacity: 0.8; margin-bottom: 32px;">Ưu đãi lên đến 50% cho các sản phẩm được chọn</p>
      <a href="products.php?sale=1" class="btn btn-accent">Săn Sale Ngay</a>
    </div>
  </section>

  <!-- New Products -->
  <?php if ($new->num_rows > 0): ?>
  <section class="section">
    <div class="section-header">
      <h2 class="section-title">Sản Phẩm Mới</h2>
      <p class="section-subtitle">Cập nhật xu hướng thời trang mới nhất</p>
    </div>
    
    <div class="products-grid">
      <?php while($p = $new->fetch_assoc()): 
        $img = $p['image_url'] ?: 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=400&q=80';
        $discount = $p['sale_price'] ? round((($p['price'] - $p['sale_price']) / $p['price']) * 100) : 0;
      ?>
      <div class="product-card">
        <div class="product-image-wrapper">
          <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="product-image">
          <div class="product-badges">
            <span class="badge badge-new">Mới</span>
            <?php if ($discount > 0): ?>
              <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
            <?php endif; ?>
          </div>
          <div class="product-actions-overlay">
            <a href="products.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
            <?php if (!empty($_SESSION['user'])): ?>
              <div style="display: flex; gap: 8px; margin-top: 8px;">
                <a href="product_form.php?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-secondary btn-sm" style="background: white; color: var(--text-main);">Sửa</a>
                <a href="product_delete.php?id=<?php echo $p['id']; ?>&csrf=<?php echo csrf_token(); ?>" class="btn btn-danger btn-sm" style="background: var(--danger); color: white;" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <div class="product-info">
          <span class="product-category"><?php echo htmlspecialchars($p['category']); ?></span>
          <h3 class="product-title"><a href="products.php?id=<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?></a></h3>
          <div class="product-price">
            <?php if ($p['sale_price']): ?>
              <span class="price-current"><?php echo number_format($p['sale_price']); ?>₫</span>
              <span class="price-old"><?php echo number_format($p['price']); ?>₫</span>
            <?php else: ?>
              <span class="price-current"><?php echo number_format($p['price']); ?>₫</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </section>
  <?php endif; ?>

</div>


<!-- Deploy Test: <?php echo date('Y-m-d H:i:s'); ?> -->
<?php include 'includes/footer.php'; ?>

