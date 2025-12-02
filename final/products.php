<?php
include 'includes/db.php';
include 'includes/header.php';
include_once 'includes/permissions.php';

$view_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($view_id > 0){
    $stmt = $conn->prepare('SELECT p.*, u.name as owner_name FROM products p LEFT JOIN users u ON u.id = p.created_by WHERE p.id = ?');
    $stmt->bind_param('i', $view_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()): 
      $img = $row['image_url'] ?: 'https://picsum.photos/seed/p'.intval($row['id']).'/800/600';
      $current_price = $row['sale_price'] ?: $row['price'];
      $discount = $row['sale_price'] ? round((($row['price'] - $row['sale_price']) / $row['price']) * 100) : 0;
?>
        <div class="product-detail">
          <div class="product-detail-grid">
            <div class="product-detail-image">
              <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
              <?php if ($discount > 0): ?>
                <div class="product-badges">
                  <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                </div>
              <?php endif; ?>
            </div>
            <div class="product-detail-info">
              <div class="product-category"><?php echo htmlspecialchars($row['category']); ?> / <?php echo htmlspecialchars($row['subcategory'] ?? 'N/A'); ?></div>
              <h1><?php echo htmlspecialchars($row['name']); ?></h1>
              <div class="product-sku">SKU: <?php echo htmlspecialchars($row['sku']); ?></div>
              
              <div class="product-price-section">
                <?php if ($row['sale_price']): ?>
                  <span class="price-sale"><?php echo number_format($row['sale_price']); ?>₫</span>
                  <span class="price-old"><?php echo number_format($row['price']); ?>₫</span>
                  <span class="discount-text">Tiết kiệm <?php echo number_format($row['price'] - $row['sale_price']); ?>₫</span>
                <?php else: ?>
                  <span class="price"><?php echo number_format($row['price']); ?>₫</span>
                <?php endif; ?>
              </div>

              <div class="product-specs">
                <div class="spec-item">
                  <strong>Size:</strong> <?php echo htmlspecialchars($row['size'] ?: 'N/A'); ?>
                </div>
                <div class="spec-item">
                  <strong>Tình trạng:</strong> 
                  <span class="<?php echo $row['in_stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                    <?php echo $row['in_stock'] > 0 ? 'Còn hàng (' . $row['in_stock'] . ')' : 'Hết hàng'; ?>
                  </span>
                </div>
              </div>

              <div class="product-description">
                <h3>Mô tả sản phẩm</h3>
                <p><?php echo nl2br(htmlspecialchars($row['description'] ?: 'Chưa có mô tả.')); ?></p>
              </div>

              <?php if (!empty($_SESSION['user']) && $row['in_stock'] > 0): ?>
                <form method="post" action="cart.php" class="add-to-cart-form">
                  <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
                  <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                  <div class="form-group">
                    <label>Size:</label>
                    <select name="size" required>
                      <option value="">Chọn size</option>
                      <?php 
                      $sizes = explode(',', $row['size']);
                      foreach ($sizes as $size): 
                        $size = trim($size);
                        if ($size): ?>
                          <option value="<?php echo htmlspecialchars($size); ?>"><?php echo htmlspecialchars($size); ?></option>
                      <?php endif; endforeach; ?>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Số lượng:</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $row['in_stock']; ?>" required>
                  </div>
                  <button type="submit" name="add_to_cart" class="btn btn-primary">🛒 Thêm vào giỏ hàng</button>
                </form>
              <?php elseif (empty($_SESSION['user'])): ?>
                <p><a href="login.php" class="btn">Đăng nhập để mua hàng</a></p>
              <?php else: ?>
                <p class="out-of-stock-msg">Sản phẩm hiện đã hết hàng</p>
              <?php endif; ?>

              <div class="product-actions">
                <a class="btn-secondary" href="products.php">← Quay lại danh sách</a>
                <?php if (can_manage_products()): ?>
                  <a class="btn-secondary" href="product_form.php?action=edit&id=<?php echo $row['id']; ?>">✏️ Chỉnh sửa</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
<?php else: ?>
        <div class="card"><div class="alert alert-error">Sản phẩm không tồn tại.</div></div>
<?php 
    endif; $stmt->close();
} else {
    $q = trim($_GET['q'] ?? '');
    $category = trim($_GET['category'] ?? '');
    $subcategory = trim($_GET['subcategory'] ?? '');
    $featured = isset($_GET['featured']) ? 1 : 0;
    $sale = isset($_GET['sale']) ? 1 : 0;
    
    $conditions = [];
    $params = [];
    $types = '';
    
    if ($q) {
        $conditions[] = "(p.name LIKE ? OR p.sku LIKE ? OR p.category LIKE ? OR p.description LIKE ?)";
        $like = '%' . $q . '%';
        $params = array_merge($params, [$like, $like, $like, $like]);
        $types .= 'ssss';
    }
    if ($category) {
        $conditions[] = "p.category = ?";
        $params[] = $category;
        $types .= 's';
    }
    if ($subcategory) {
        $conditions[] = "p.subcategory = ?";
        $params[] = $subcategory;
        $types .= 's';
    }
    if ($featured) {
        $conditions[] = "p.featured = 1";
    }
    if ($sale) {
        $conditions[] = "p.sale_price IS NOT NULL AND p.sale_price > 0";
    }
    
    $where = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    $sql = "SELECT p.id, p.name, p.sku, p.price, p.sale_price, p.size, p.category, p.subcategory, p.image_url, p.featured, p.in_stock, p.updated_at 
            FROM products p $where ORDER BY p.featured DESC, p.updated_at DESC";
    
    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    ?>
    <div class="page-header">
      <h1>
        <?php 
        if ($sale) echo 'Sản phẩm khuyến mãi';
        elseif ($featured) echo 'Sản phẩm nổi bật';
        elseif ($category) echo htmlspecialchars($category);
        else echo 'Tất cả sản phẩm';
        ?>
      </h1>
    </div>
    
    <div class="card">
      <form method="get" class="search-filters">
        <div class="filter-row">
          <input type="text" name="q" placeholder="Tìm kiếm tên, SKU, mô tả..." value="<?php echo htmlspecialchars($q); ?>" class="search-input">
          <button class="btn" type="submit">🔍 Tìm kiếm</button>
        </div>
        <div class="filter-tags">
          <a href="products.php" class="filter-tag <?php echo !$category && !$featured && !$sale ? 'active' : ''; ?>">Tất cả</a>
          <a href="products.php?featured=1" class="filter-tag <?php echo $featured ? 'active' : ''; ?>">Nổi bật</a>
          <a href="products.php?sale=1" class="filter-tag <?php echo $sale ? 'active' : ''; ?>">Khuyến mãi</a>
          <a href="products.php?category=Áo" class="filter-tag <?php echo $category === 'Áo' ? 'active' : ''; ?>">Áo</a>
          <a href="products.php?category=Quần" class="filter-tag <?php echo $category === 'Quần' ? 'active' : ''; ?>">Quần</a>
        </div>
        <?php if (can_manage_products()): ?>
          <div style="margin-top: 12px;">
            <a class="btn-secondary" href="product_form.php?action=create">+ Thêm sản phẩm</a>
          </div>
        <?php endif; ?>
      </form>
      
      <?php if ($res->num_rows > 0): ?>
        <div class="products-grid">
          <?php while($row = $res->fetch_assoc()): 
            $thumb = !empty($row['image_url']) ? $row['image_url'] : ('https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=400&q=80');
            $current_price = $row['sale_price'] ?: $row['price'];
            $discount = $row['sale_price'] ? round((($row['price'] - $row['sale_price']) / $row['price']) * 100) : 0;
          ?>
            <div class="product-card">
              <div class="product-image-wrapper">
                <img src="<?php echo htmlspecialchars($thumb); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="product-image">
                <div class="product-badges">
                  <?php if ($row['featured']): ?>
                    <span class="badge badge-new">Nổi bật</span>
                  <?php endif; ?>
                  <?php if ($discount > 0): ?>
                    <span class="badge badge-sale">-<?php echo $discount; ?>%</span>
                  <?php endif; ?>
                </div>
                <div class="product-actions-overlay">
              <a href="products.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Xem Chi Tiết</a>
              <?php if (can_manage_products()): ?>
                <div style="display: flex; gap: 8px; margin-top: 8px;">
                  <a href="product_form.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm" style="background: white; color: var(--text-main);">Sửa</a>
                  <a href="product_delete.php?id=<?php echo $row['id']; ?>&csrf=<?php echo csrf_token(); ?>" class="btn btn-danger btn-sm" style="background: var(--danger); color: white;" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?');">Xóa</a>
                </div>
              <?php endif; ?>
            </div>
              </div>
              <div class="product-info">
                <span class="product-category"><?php echo htmlspecialchars($row['category']); ?></span>
                <h3 class="product-title"><a href="products.php?id=<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></a></h3>
                <div class="product-price">
                  <?php if ($row['sale_price']): ?>
                    <span class="price-current"><?php echo number_format($row['sale_price']); ?>₫</span>
                    <span class="price-old"><?php echo number_format($row['price']); ?>₫</span>
                  <?php else: ?>
                    <span class="price-current"><?php echo number_format($row['price']); ?>₫</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <p>Không tìm thấy sản phẩm nào.</p>
          <a href="products.php" class="btn">Xem tất cả sản phẩm</a>
        </div>
      <?php endif; ?>
    </div>
<?php }
include 'includes/footer.php';
?>
