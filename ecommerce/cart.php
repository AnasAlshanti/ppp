<?php
/** cart.php — the logged-in user's shopping cart. */
require_once __DIR__ . '/includes/functions.php';

// Must be logged in; remember to come back here after login.
require_login('cart.php');

$stmt = $pdo->prepare(
    'SELECT ci.product_id, ci.quantity, p.name, p.price, p.image_url, p.stock_quantity
       FROM cart_items ci
       JOIN products p ON p.product_id = ci.product_id
      WHERE ci.user_id = ?
      ORDER BY ci.added_at DESC, ci.cart_id DESC'
);
$stmt->execute([current_user_id()]);
$items = $stmt->fetchAll();

$subtotal = 0.0;
foreach ($items as $it) {
    $subtotal += (float) $it['price'] * (int) $it['quantity'];
}
$tax   = round($subtotal * TAX_RATE, 2);
$total = $subtotal + $tax;

$page_title = 'Your cart';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head"><h2>Your cart</h2></div>

<?php if (!$items): ?>
  <div class="empty">
    <h3>Your cart is empty</h3>
    <p>Browse our catalogue and add something you like.</p>
    <a class="btn" href="products.php">Shop products</a>
  </div>
<?php else: ?>
  <div class="cart-layout">
    <div class="card">
      <?php foreach ($items as $it): ?>
        <div class="cart-line">
          <img src="<?= e($it['image_url']) ?>" alt="<?= e($it['name']) ?>">
          <div>
            <div class="product-name">
              <a href="product-detail.php?id=<?= (int) $it['product_id'] ?>"><?= e($it['name']) ?></a>
            </div>
            <div class="muted"><?= money($it['price']) ?> each</div>

            <form class="inline-form" action="update_cart.php" method="post" style="margin-top:8px;">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= (int) $it['product_id'] ?>">
              <input type="number" name="quantity" value="<?= (int) $it['quantity'] ?>"
                     min="1" max="<?= (int) $it['stock_quantity'] ?>" style="width:78px;">
              <button class="btn btn-ghost btn-sm" type="submit">Update</button>
            </form>
          </div>
          <div style="text-align:right;">
            <div class="product-price"><?= money($it['price'] * $it['quantity']) ?></div>
            <form action="remove_from_cart.php" method="post" style="margin-top:8px;">
              <?= csrf_field() ?>
              <input type="hidden" name="product_id" value="<?= (int) $it['product_id'] ?>">
              <button class="btn btn-ghost btn-sm" type="submit">Remove</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <aside class="card">
      <h3>Order summary</h3>
      <div class="summary-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
      <div class="summary-row"><span>Tax (<?= (int) round(TAX_RATE * 100) ?>%)</span><span><?= money($tax) ?></span></div>
      <div class="summary-total"><span>Total</span><span><?= money($total) ?></span></div>

      <form action="checkout.php" method="post" style="margin-top:18px;">
        <?= csrf_field() ?>
        <button class="btn btn-block" type="submit">Checkout</button>
      </form>
      <p class="help center" style="margin-top:10px;">Ships to: <?= e($_SESSION['full_name'] ?? '') ?></p>
    </aside>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
