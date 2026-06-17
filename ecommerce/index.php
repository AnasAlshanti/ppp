<?php
/** index.php — home page: hero, featured products, categories. */
require_once __DIR__ . '/includes/functions.php';

// Featured products (latest 8 in stock-first order) — fetched from the DB.
$stmt = $pdo->query(
    'SELECT p.product_id, p.name, p.price, p.image_url, p.stock_quantity, c.name AS category
       FROM products p
       LEFT JOIN categories c ON c.category_id = p.category_id
      ORDER BY (p.stock_quantity > 0) DESC, p.created_at DESC, p.product_id DESC
      LIMIT 8'
);
$featured = $stmt->fetchAll();

$categories = $pdo->query('SELECT category_id, name FROM categories ORDER BY name')->fetchAll();

$page_title = 'Home';
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div>
    <h1>Tech that keeps up with you.</h1>
    <p>Discover audio, wearables and workspace gear hand-picked for everyday life — with fast shipping and a 30-day return promise.</p>
    <p style="margin-top:22px;">
      <a class="btn" href="products.php">Shop all products →</a>
    </p>
  </div>
  <div class="hero-art" aria-hidden="true">🛍️</div>
</section>

<?php if ($categories): ?>
<div class="section-head"><h2>Browse by category</h2></div>
<div class="toolbar" style="gap:10px;">
  <?php foreach ($categories as $c): ?>
    <a class="btn btn-ghost btn-sm" href="products.php?category=<?= (int) $c['category_id'] ?>">
      <?= e($c['name']) ?>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="section-head">
  <h2>Featured products</h2>
  <a href="products.php">View all</a>
</div>

<?php if (!$featured): ?>
  <div class="empty">No products yet. Please check back soon.</div>
<?php else: ?>
  <div class="product-grid">
    <?php foreach ($featured as $p): ?>
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
              <a class="btn btn-sm" href="product-detail.php?id=<?= (int) $p['product_id'] ?>">View</a>
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
