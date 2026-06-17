<?php
/** order-confirmation.php — shows a placed order to its owner (?order=N). */
require_once __DIR__ . '/includes/functions.php';

require_login();

$orderId = isset($_GET['order']) ? (int) $_GET['order'] : 0;

// Fetch the order, scoped to the current user (so people can't view others').
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ?');
$stmt->execute([$orderId, current_user_id()]);
$order = $stmt->fetch();

if (!$order) {
    http_response_code(404);
    $page_title = 'Order not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty"><h2>Order not found</h2><a class="btn" href="profile.php">Go to profile</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare(
    'SELECT oi.quantity, oi.unit_price, COALESCE(p.name, \'(removed product)\') AS name
       FROM order_items oi
       LEFT JOIN products p ON p.product_id = oi.product_id
      WHERE oi.order_id = ?'
);
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

$page_title = 'Order confirmed';
require __DIR__ . '/includes/header.php';
?>

<div class="card" style="max-width:680px; margin:24px auto;">
  <div class="center" style="margin-bottom:12px;">
    <div style="font-size:3rem;">✅</div>
    <h2>Order #<?= (int) $order['order_id'] ?> confirmed</h2>
    <p class="muted">Placed on <?= e($order['order_date']) ?> · Status:
      <span class="pill pill-<?= e($order['status']) ?>"><?= e($order['status']) ?></span>
    </p>
  </div>

  <div class="table-wrap" style="box-shadow:none;">
    <table class="data">
      <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
          <tr>
            <td><?= e($it['name']) ?></td>
            <td><?= (int) $it['quantity'] ?></td>
            <td><?= money($it['unit_price']) ?></td>
            <td><?= money($it['unit_price'] * $it['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="summary-total"><span>Total paid</span><span><?= money($order['total_amount']) ?></span></div>
  <p class="muted" style="margin-top:10px;">Shipping to: <?= e($order['shipping_address']) ?></p>

  <div style="margin-top:18px; display:flex; gap:10px;">
    <a class="btn" href="products.php">Continue shopping</a>
    <a class="btn btn-ghost" href="profile.php">View order history</a>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
