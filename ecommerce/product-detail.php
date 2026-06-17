<?php
/** product-detail.php — single product view (?id=N) with add-to-cart. */
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$stmt = $pdo->prepare(
    'SELECT p.*, c.name AS category
       FROM products p
       LEFT JOIN categories c ON c.category_id = p.category_id
      WHERE p.product_id = ?'
);
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(404);
    $page_title = 'Not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty"><h2>Product not found</h2><p>It may have been removed.</p>'
       . '<a class="btn" href="products.php">Back to products</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$inStock = (int) $product['stock_quantity'] > 0;
$page_title = $product['name'];
require __DIR__ . '/includes/header.php';
?>

<p class="muted" style="margin:8px 0 16px;">
  <a href="products.php">Products</a> ›
  <a href="products.php?category=<?= (int) $product['category_id'] ?>"><?= e($product['category'] ?? 'All') ?></a>
  › <?= e($product['name']) ?>
</p>

<div class="detail">
  <div class="detail-media">
    <img src="<?= e($product['image_url']) ?>" alt="<?= e($product['name']) ?>">
  </div>
  <div>
    <span class="product-cat"><?= e($product['category'] ?? 'Uncategorized') ?></span>
    <h1><?= e($product['name']) ?></h1>
    <div class="price-lg"><?= money($product['price']) ?></div>

    <?php if ($inStock): ?>
      <span class="badge-in"><?= (int) $product['stock_quantity'] ?> in stock</span>
    <?php else: ?>
      <span class="badge-out">Out of stock</span>
    <?php endif; ?>

    <p style="margin-top:16px;"><?= nl2br(e($product['description'])) ?></p>

    <?php if ($inStock): ?>
      <form class="js-add-to-cart" action="add_to_cart.php" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="product_id" value="<?= (int) $product['product_id'] ?>">
        <div class="qty-row">
          <label for="qty" style="margin:0;">Qty</label>
          <input type="number" id="qty" name="quantity" value="1"
                 min="1" max="<?= (int) $product['stock_quantity'] ?>" step="1">
          <button class="btn" type="submit">Add to cart</button>
        </div>
      </form>
      <p class="help">You'll need to sign in to complete your purchase.</p>
    <?php else: ?>
      <div class="qty-row">
        <button class="btn" disabled>Add to cart</button>
      </div>
    <?php endif; ?>

    <p style="margin-top:18px;"><a href="products.php">← Continue shopping</a></p>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
