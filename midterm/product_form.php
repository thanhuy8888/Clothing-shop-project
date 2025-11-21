<?php
include 'includes/db.php';
include 'includes/auth.php';
require_login();

$action = $_GET['action'] ?? 'create';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$name = $sku = $price = $size = $category = $description = $image_url = '';

if ($action === 'edit'){
    if ($id <= 0){ header('Location: products.php'); exit; }
    $stmt = $conn->prepare('SELECT * FROM products WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()){
        $name = $row['name']; $sku = $row['sku']; $price = $row['price'];
        $size = $row['size']; $category = $row['category']; $description = $row['description']; $image_url = $row['image_url'];
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
    $size = trim($_POST['size'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $image_url = trim($_POST['image_url'] ?? '');

    if (!$name) $errors[] = 'Name is required';
    if (!$sku) $errors[] = 'SKU is required';
    if ($price < 0) $errors[] = 'Price must be >= 0';

    if (empty($errors)){
        if ($action === 'edit'){
            $stmt = $conn->prepare('UPDATE products SET name=?, sku=?, price=?, size=?, category=?, description=?, image_url=?, updated_at=NOW() WHERE id=?');
            $stmt->bind_param('ssdssssi', $name, $sku, $price, $size, $category, $description, $image_url, $id);
            $stmt->execute(); $stmt->close();
            header('Location: products.php?id=' . $id); exit;
        } else {
            $uid = $_SESSION['user']['id'];
            $stmt = $conn->prepare('INSERT INTO products (name, sku, price, size, category, description, image_url, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssdssssi', $name, $sku, $price, $size, $category, $description, $image_url, $uid);
            $stmt->execute(); $new_id = $stmt->insert_id; $stmt->close();
            header('Location: products.php?id=' . $new_id); exit;
        }
    }
}
include 'includes/header.php';
?>
<div class="card">
  <h2><?php echo ($action === 'edit') ? 'Edit Product' : 'Create Product'; ?></h2>
  <?php if ($errors): ?>
    <div class="alert alert-error"><?php echo implode('<br>', array_map('htmlspecialchars', $errors)); ?></div>
  <?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token()); ?>">
    <label>Name</label>
    <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
    <label>SKU</label>
    <input type="text" name="sku" value="<?php echo htmlspecialchars($sku); ?>" required>
    <label>Price</label>
    <input type="number" step="0.01" min="0" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
    <label>Size</label>
    <input type="text" name="size" placeholder="S, M, L, XL..." value="<?php echo htmlspecialchars($size); ?>">
    <label>Category</label>
    <input type="text" name="category" placeholder="Shirt, Pants, Dress..." value="<?php echo htmlspecialchars($category); ?>">
    <label>Image URL (optional)</label>
    <input type="url" name="image_url" value="<?php echo htmlspecialchars($image_url); ?>" placeholder="https://...">
    <label>Description</label>
    <textarea name="description" rows="4" placeholder=" Description..."><?php echo htmlspecialchars($description); ?></textarea>
    <button class="btn" type="submit"><?php echo ($action === 'edit') ? 'Save Changes' : 'Create'; ?></button>
    <a class="btn-secondary" href="products.php">Cancel</a>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
