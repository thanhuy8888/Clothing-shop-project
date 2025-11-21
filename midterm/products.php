<?php
include 'includes/db.php';
include 'includes/header.php';

$view_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($view_id > 0){
    $stmt = $conn->prepare('SELECT p.*, u.name as owner_name FROM products p LEFT JOIN users u ON u.id = p.created_by WHERE p.id = ?');
    $stmt->bind_param('i', $view_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()): 
      $img = $row['image_url'] ?: 'https://picsum.photos/seed/p'.intval($row['id']).'/800/600';
?>
        <div class="card">
          <h2><?php echo htmlspecialchars($row['name']); ?> <span class="badge"><?php echo htmlspecialchars($row['category']); ?></span></h2>
          <img class="product-media" src="<?php echo htmlspecialchars($img); ?>" alt="">
          <p><strong>SKU:</strong> <?php echo htmlspecialchars($row['sku']); ?></p>
          <p><strong>Price:</strong> $<?php echo number_format((float)$row['price'],2); ?></p>
          <p><strong>Size:</strong> <?php echo htmlspecialchars($row['size']); ?></p>
          <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
          <p><small>Owner: <?php echo htmlspecialchars($row['owner_name'] ?? 'N/A'); ?> • Updated at: <?php echo htmlspecialchars($row['updated_at']); ?></small></p>
          <p>
            <a class="btn-secondary" href="products.php">Back</a>
            <?php if (!empty($_SESSION['user'])): ?>
              <a class="btn" href="product_form.php?action=edit&id=<?php echo $row['id']; ?>">Edit</a>
              <a class="btn" href="product_delete.php?id=<?php echo $row['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf'] ?? ''); ?>" onclick="return confirm('Delete this product?');">Delete</a>
            <?php endif; ?>
          </p>
        </div>
<?php else: ?>
        <div class="card"><div class="alert alert-error">Product not found.</div></div>
<?php 
    endif; $stmt->close();
} else {
    $q = trim($_GET['q'] ?? '');
    if ($q){
        $like = '%' . $q . '%';
        $stmt = $conn->prepare('SELECT id, name, sku, price, size, category, image_url, updated_at FROM products WHERE name LIKE ? OR sku LIKE ? OR category LIKE ? ORDER BY updated_at DESC');
        $stmt->bind_param('sss', $like, $like, $like);
    } else {
        $stmt = $conn->prepare('SELECT id, name, sku, price, size, category, image_url, updated_at FROM products ORDER BY updated_at DESC');
    }
    $stmt->execute();
    $res = $stmt->get_result();
    ?>
    <div class="card">
      <h2>Products</h2>
      <form method="get">
        <input type="text" name="q" placeholder="Search name / SKU / category..." value="<?php echo htmlspecialchars($q); ?>">
        <button class="btn" type="submit">Search</button>
        <?php if (!empty($_SESSION['user'])): ?>
          <a class="btn-secondary" href="product_form.php?action=create">Add Product</a>
        <?php endif; ?>
      </form>
      <table class="table">
        <thead><tr><th>Item</th><th>SKU</th><th>Price</th><th>Size</th><th>Category</th><th>Updated</th><th>Actions</th></tr></thead>
        <tbody>
        <?php while($row = $res->fetch_assoc()): 
            $thumb = !empty($row['image_url']) ? $row['image_url'] : ('https://picsum.photos/seed/p'.intval($row['id']).'/160/160'); ?>
          <tr>
            <td>
              <div style="display:flex; gap:10px; align-items:center">
                <img class="thumb" src="<?php echo htmlspecialchars($thumb); ?>" alt="">
                <div>
                  <a href="products.php?id=<?php echo $row['id']; ?>"><strong><?php echo htmlspecialchars($row['name']); ?></strong></a><br>
                  <span class="sku">#<?php echo htmlspecialchars($row['id']); ?></span>
                </div>
              </div>
            </td>
            <td><?php echo htmlspecialchars($row['sku']); ?></td>
            <td>$<?php echo number_format((float)$row['price'], 2); ?></td>
            <td><?php echo htmlspecialchars($row['size']); ?></td>
            <td><span class="badge"><?php echo htmlspecialchars($row['category']); ?></span></td>
            <td><?php echo htmlspecialchars($row['updated_at']); ?></td>
            <td>
              <a href="products.php?id=<?php echo $row['id']; ?>">View</a>
              <?php if (!empty($_SESSION['user'])): ?>
                | <a href="product_form.php?action=edit&id=<?php echo $row['id']; ?>">Edit</a>
                | <a href="product_delete.php?id=<?php echo $row['id']; ?>&csrf=<?php echo htmlspecialchars($_SESSION['csrf'] ?? ''); ?>" onclick="return confirm('Delete this product?');">Delete</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
<?php }
include 'includes/footer.php';
?>