0<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();
include 'includes/permissions.php';
require_permission(PERM_PRODUCT_MANAGE);

$action = $_GET['action'] ?? 'create';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$name = $sku = $price = $sale_price = $size = $category = $subcategory = $description = $image_url = '';
$featured = 0;
$in_stock = 0;

if ($action === 'edit'){
    if ($id <= 0){ header('Location: products.php'); exit; }
    $stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()){
        $name = $row['name']; $sku = $row['sku']; $price = $row['price'];
        $sale_price = $row['sale_price']; $size = $row['size']; 
        $category = $row['category']; $subcategory = $row['subcategory'] ?? '';
        $description = $row['description']; $image_url = $row['image_url'];
        $featured = $row['featured'] ?? 0;
        $in_stock = $row['in_stock'] ?? 0;
    } else {
        header('Location: products.php'); exit;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    if (!csrf_check($_POST['csrf'] ?? '')){ die('Invalid CSRF token'); }
    $name = trim($_POST['name'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : NULL;
    $size = trim($_POST['size'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $subcategory = trim($_POST['subcategory'] ?? '');
    $description = trim($_POST['description'] ?? '');
    // Handle Image URL
    $image_url = trim($_POST['image_url'] ?? '');
    
    if (!$name) $errors[] = 'Tên sản phẩm là bắt buộc';
    if (!$sku) $errors[] = 'SKU là bắt buộc';
    if ($price < 0) $errors[] = 'Giá phải >= 0';
    if ($sale_price !== NULL && $sale_price >= $price) $errors[] = 'Giá khuyến mãi phải nhỏ hơn giá gốc';

    if (empty($errors)){
        if ($action === 'edit'){
            $stmt = $conn->prepare('UPDATE products SET name=?, sku=?, price=?, sale_price=?, size=?, category=?, subcategory=?, description=?, image_url=?, featured=?, in_stock=?, updated_at=NOW() WHERE id=?');
            $stmt->bind_param('ssddsssssiii', $name, $sku, $price, $sale_price, $size, $category, $subcategory, $description, $image_url, $featured, $in_stock, $id);
            if ($stmt->execute()) {
                $stmt->close();
                header('Location: products.php?id=' . $id); exit;
            } else {
                $errors[] = 'Lỗi database: ' . $stmt->error;
            }
        } else {
            $uid = $_SESSION['user']['id'];
            $stmt = $conn->prepare('INSERT INTO products (name, sku, price, sale_price, size, category, subcategory, description, image_url, featured, in_stock, created_by, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->bind_param('ssddsssssiii', $name, $sku, $price, $sale_price, $size, $category, $subcategory, $description, $image_url, $featured, $in_stock, $uid);
            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                $stmt->close();
                header('Location: products.php?id=' . $new_id); exit;
            } else {
                $errors[] = 'Lỗi database: ' . $stmt->error;
            }
        }
    }
}
include 'includes/header.php';
?>
<div class="admin-form-wrapper">
  <div class="card">
    <h2><?php echo ($action === 'edit') ? 'Chỉnh sửa sản phẩm' : 'Thêm sản phẩm mới'; ?></h2>
    
    <?php if ($errors): ?>
      <div class="alert alert-error">
        <strong>Đã có lỗi xảy ra:</strong><br>
        <?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?>
      </div>
    <?php endif; ?>

    <form method="post" class="product-form">
      <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
      
      <div class="form-grid">
        <!-- Basic Info -->
        <div class="form-group">
          <label>Tên sản phẩm <span style="color: var(--danger)">*</span></label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Ví dụ: Áo Thun Polo Premium" required>
        </div>
        
        <div class="form-group">
          <label>Mã SKU <span style="color: var(--danger)">*</span></label>
          <input type="text" name="sku" value="<?php echo htmlspecialchars($sku); ?>" placeholder="Ví dụ: POLO-001" required>
        </div>

        <!-- Pricing -->
        <div class="form-group">
          <label>Giá gốc (₫) <span style="color: var(--danger)">*</span></label>
          <input type="number" step="1000" min="0" name="price" value="<?php echo htmlspecialchars($price); ?>" placeholder="0" required>
        </div>
        
        <div class="form-group">
          <label>Giá khuyến mãi (₫)</label>
          <input type="number" step="1000" min="0" name="sale_price" value="<?php echo htmlspecialchars($sale_price); ?>" placeholder="Để trống nếu không giảm giá">
        </div>

        <!-- Categories -->
        <div class="form-group">
          <label>Danh mục chính <span style="color: var(--danger)">*</span></label>
          <select name="category" required>
            <option value="">-- Chọn danh mục --</option>
            <option value="Áo" <?php echo $category === 'Áo' ? 'selected' : ''; ?>>Áo</option>
            <option value="Quần" <?php echo $category === 'Quần' ? 'selected' : ''; ?>>Quần</option>
          </select>
        </div>
        
        <div class="form-group">
          <label>Danh mục phụ</label>
          <input type="text" name="subcategory" placeholder="Ví dụ: Áo Polo, Quần Jeans..." value="<?php echo htmlspecialchars($subcategory); ?>">
        </div>

        <!-- Inventory & Variants -->
        <div class="form-group">
          <label>Size (cách nhau bởi dấu phẩy)</label>
          <input type="text" name="size" placeholder="S, M, L, XL" value="<?php echo htmlspecialchars($size); ?>">
        </div>
        
        <div class="form-group">
          <label>Số lượng tồn kho</label>
          <input type="number" min="0" name="in_stock" value="<?php echo htmlspecialchars($in_stock); ?>" placeholder="0">
        </div>

        <!-- Description -->
        <div class="form-group form-full-width">
          <label>Mô tả chi tiết</label>
          <textarea name="description" rows="6" placeholder="Mô tả đặc điểm, chất liệu, hướng dẫn bảo quản..."><?php echo htmlspecialchars($description); ?></textarea>
        </div>

        <!-- Image URL -->
        <div class="form-group form-full-width">
          <label>Link hình ảnh sản phẩm</label>
          <input type="text" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>" placeholder="https://example.com/image.jpg">
          <?php if ($image_url): ?>
            <div style="margin-top: 10px; text-align: center;">
              <img src="<?php echo htmlspecialchars($image_url); ?>" alt="Preview" style="max-height: 150px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
              <p style="font-size: 12px; color: var(--text-secondary); margin-top: 5px;">Ảnh hiện tại</p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Options -->
        <div class="form-group form-full-width">
          <label class="checkbox-wrapper" style="justify-content: center;">
            <input type="checkbox" name="featured" value="1" <?php echo $featured ? 'checked' : ''; ?>>
            <span>Đánh dấu là <strong>Sản phẩm nổi bật</strong></span>
          </label>
        </div>
      </div>

      <div class="form-actions">
        <a class="btn btn-outline" href="products.php">Hủy bỏ</a>
        <button class="btn btn-primary" type="submit">
          <?php echo ($action === 'edit') ? 'Lưu thay đổi' : 'Tạo sản phẩm'; ?>
        </button>
      </div>
    </form>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
