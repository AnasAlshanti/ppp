<?php
/** admin/products.php — list + create + edit + delete products (admin only). */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $productId   = (int) ($_POST['product_id'] ?? 0);
        $name        = trim((string) ($_POST['name'] ?? ''));
        $description = trim((string) ($_POST['description'] ?? ''));
        $price       = (float) ($_POST['price'] ?? 0);
        $stock       = (int) ($_POST['stock_quantity'] ?? 0);
        $imageUrl    = trim((string) ($_POST['image_url'] ?? ''));
        $categoryId  = ($_POST['category_id'] ?? '') !== '' ? (int) $_POST['category_id'] : null;

        if ($name === '' || $price < 0 || $stock < 0) {
            set_flash('error', 'Please provide a valid name, price and stock.');
            redirect('products.php');
        }

        if ($productId > 0) {
            $stmt = $pdo->prepare(
                'UPDATE products SET name = ?, description = ?, price = ?, stock_quantity = ?, image_url = ?, category_id = ?
                  WHERE product_id = ?'
            );
            $stmt->execute([$name, $description, $price, $stock, $imageUrl, $categoryId, $productId]);
            set_flash('success', 'Product updated.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO products (name, description, price, stock_quantity, image_url, category_id)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$name, $description, $price, $stock, $imageUrl, $categoryId]);
            set_flash('success', 'Product added.');
        }
        redirect('products.php');
    }

    if ($action === 'delete') {
        $productId = (int) ($_POST['product_id'] ?? 0);
        // FKs: cart_items cascade; order_items.product_id is ON DELETE SET NULL (order history preserved).
        $pdo->prepare('DELETE FROM products WHERE product_id = ?')->execute([$productId]);
        set_flash('success', 'Product deleted.');
        redirect('products.php');
    }
}

$categories = $pdo->query('SELECT category_id, name FROM categories ORDER BY name')->fetchAll();

// Editing? Load the product to prefill the form.
$editing = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE product_id = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $editing = $stmt->fetch() ?: null;
}

$products = $pdo->query(
    'SELECT p.product_id, p.name, p.price, p.stock_quantity, c.name AS category
       FROM products p LEFT JOIN categories c ON c.category_id = p.category_id
      ORDER BY p.product_id DESC'
)->fetchAll();

$BASE = '../';
$page_title = 'Admin · Products';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_tabs.php';
?>

<div class="grid-2" style="grid-template-columns: 1fr 1.3fr;">
  <div class="card">
    <h3><?= $editing ? 'Edit product' : 'Add product' ?></h3>
    <form class="form-grid" method="post" action="products.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="product_id" value="<?= (int) ($editing['product_id'] ?? 0) ?>">
      <div>
        <label for="name">Name</label>
        <input type="text" id="name" name="name" required value="<?= e($editing['name'] ?? '') ?>">
      </div>
      <div>
        <label for="description">Description</label>
        <textarea id="description" name="description" style="min-height:90px;"><?= e($editing['description'] ?? '') ?></textarea>
      </div>
      <div class="form-row">
        <div>
          <label for="price">Price</label>
          <input type="number" id="price" name="price" step="0.01" min="0" required value="<?= e((string) ($editing['price'] ?? '')) ?>">
        </div>
        <div>
          <label for="stock_quantity">Stock</label>
          <input type="number" id="stock_quantity" name="stock_quantity" min="0" required value="<?= e((string) ($editing['stock_quantity'] ?? '0')) ?>">
        </div>
      </div>
      <div>
        <label for="image_url">Image URL</label>
        <input type="text" id="image_url" name="image_url" value="<?= e($editing['image_url'] ?? '') ?>">
      </div>
      <div>
        <label for="category_id">Category</label>
        <select id="category_id" name="category_id">
          <option value="">— None —</option>
          <?php foreach ($categories as $c): ?>
            <option value="<?= (int) $c['category_id'] ?>" <?= (int) ($editing['category_id'] ?? 0) === (int) $c['category_id'] ? 'selected' : '' ?>>
              <?= e($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="display:flex; gap:10px;">
        <button class="btn" type="submit"><?= $editing ? 'Save changes' : 'Add product' ?></button>
        <?php if ($editing): ?><a class="btn btn-ghost" href="products.php">Cancel</a><?php endif; ?>
      </div>
    </form>
  </div>

  <div>
    <div class="section-head"><h2>Products (<?= count($products) ?>)</h2></div>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>ID</th><th>Name</th><th>Price</th><th>Stock</th><th>Category</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($products as $p): ?>
            <tr>
              <td><?= (int) $p['product_id'] ?></td>
              <td><?= e($p['name']) ?></td>
              <td><?= money($p['price']) ?></td>
              <td><?= (int) $p['stock_quantity'] ?></td>
              <td><?= e($p['category'] ?? '—') ?></td>
              <td style="white-space:nowrap;">
                <a class="btn btn-ghost btn-sm" href="products.php?edit=<?= (int) $p['product_id'] ?>">Edit</a>
                <form class="inline-form" method="post" action="products.php"
                      data-confirm="Delete &quot;<?= e($p['name']) ?>&quot;? This cannot be undone.">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="delete">
                  <input type="hidden" name="product_id" value="<?= (int) $p['product_id'] ?>">
                  <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
