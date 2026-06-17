<?php
/** products.php — all products with category filter, price sort and search. */
require_once __DIR__ . '/includes/functions.php';

$categories = $pdo->query('SELECT category_id, name FROM categories ORDER BY name')->fetchAll();

// ---- Read & sanitize filter inputs ($_GET) -------------------------------
$q          = trim((string) ($_GET['q'] ?? ''));
$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int) $_GET['category'] : null;
$sort       = $_GET['sort'] ?? '';

// ---- Build the query with bound parameters (no SQL injection) ------------
$sql = 'SELECT p.product_id, p.name, p.price, p.image_url, p.stock_quantity, c.name AS category
          FROM products p
          LEFT JOIN categories c ON c.category_id = p.category_id
         WHERE 1 = 1';
$params = [];

if ($q !== '') {
    $sql .= ' AND p.name LIKE ?';
    $params[] = '%' . $q . '%';
}
if ($categoryId !== null) {
    $sql .= ' AND p.category_id = ?';
    $params[] = $categoryId;
}

// Whitelisted sort options only.
switch ($sort) {
    case 'price_asc':  $sql .= ' ORDER BY p.price ASC';  break;
    case 'price_desc': $sql .= ' ORDER BY p.price DESC'; break;
    case 'name':       $sql .= ' ORDER BY p.name ASC';   break;
    default:           $sql .= ' ORDER BY p.product_id DESC';
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = 'Products';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head"><h2>All products</h2></div>

<form class="toolbar" method="get" action="products.php">
  <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
    <input type="search" name="q" placeholder="Search by name…" value="<?= e($q) ?>">
    <select name="category" aria-label="Filter by category">
      <option value="">All categories</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= (int) $c['category_id'] ?>" <?= $categoryId === (int) $c['category_id'] ? 'selected' : '' ?>>
          <?= e($c['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
    <select name="sort" aria-label="Sort">
      <option value="">Sort: newest</option>
      <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: low to high</option>
      <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
      <option value="name"       <?= $sort === 'name'       ? 'selected' : '' ?>>Name: A–Z</option>
    </select>
    <button class="btn btn-sm" type="submit">Apply</button>
    <?php if ($q !== '' || $categoryId !== null || $sort !== ''): ?>
      <a class="btn btn-ghost btn-sm" href="products.php">Reset</a>
    <?php endif; ?>
  </div>
  <span class="muted"><?= count($products) ?> item<?= count($products) === 1 ? '' : 's' ?></span>
</form>

<?php if (!$products): ?>
  <div class="empty">No products match your search.</div>
<?php else: ?>
  <div class="product-grid">
    <?php foreach ($products as $p): ?>
      <article class="product-card">
        <a class="product-thumb" href="product-detail.php?id=<?= (int) $p['product_id'] ?>">
          <img src="<?= e($p['image_url']) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        </a>
        <div class="product-body">
          <span class="product-cat"><?= e($p['category'] ?? 'Uncategorized') ?></span>
          <span class="product-name">
            <a href="product-detail.php?id=<?= (int) $p['product_id'] ?>"><?= e($p['name']) ?></a>
          </span>
          <div class="product-foot">
            <span class="product-price"><?= money($p['price']) ?></span>
            <?php if ((int) $p['stock_quantity'] > 0): ?>
              <span class="badge-in">In stock</span>
            <?php else: ?>
              <span class="badge-out">Out of stock</span>
            <?php endif; ?>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
