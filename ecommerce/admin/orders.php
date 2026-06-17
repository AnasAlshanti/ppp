<?php
/** admin/orders.php — list orders, change status, view line items (admin only). */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

const ORDER_STATUSES = ['pending', 'paid', 'shipped', 'delivered', 'cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'set_status') {
        $orderId = (int) ($_POST['order_id'] ?? 0);
        $status  = (string) ($_POST['status'] ?? '');
        if (in_array($status, ORDER_STATUSES, true)) {
            $pdo->prepare('UPDATE orders SET status = ? WHERE order_id = ?')->execute([$status, $orderId]);
            set_flash('success', "Order #$orderId set to $status.");
        } else {
            set_flash('error', 'Invalid status.');
        }
    }
    redirect('orders.php');
}

$orders = $pdo->query(
    'SELECT o.order_id, o.order_date, o.total_amount, o.status, u.full_name, u.email
       FROM orders o JOIN users u ON u.user_id = o.user_id
      ORDER BY o.order_date DESC, o.order_id DESC'
)->fetchAll();

// Optionally show the line items for one order.
$viewOrder = null;
$viewItems = [];
if (isset($_GET['view'])) {
    $vid = (int) $_GET['view'];
    $stmt = $pdo->prepare(
        'SELECT o.*, u.full_name FROM orders o JOIN users u ON u.user_id = o.user_id WHERE o.order_id = ?'
    );
    $stmt->execute([$vid]);
    $viewOrder = $stmt->fetch() ?: null;

    if ($viewOrder) {
        $stmt = $pdo->prepare(
            'SELECT oi.quantity, oi.unit_price, COALESCE(p.name, \'(removed product)\') AS name
               FROM order_items oi LEFT JOIN products p ON p.product_id = oi.product_id
              WHERE oi.order_id = ?'
        );
        $stmt->execute([$vid]);
        $viewItems = $stmt->fetchAll();
    }
}

$BASE = '../';
$page_title = 'Admin · Orders';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_tabs.php';
?>

<div class="section-head"><h2>Orders (<?= count($orders) ?>)</h2></div>

<?php if ($viewOrder): ?>
  <div class="card" style="margin-bottom:18px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <h3 style="margin:0;">Order #<?= (int) $viewOrder['order_id'] ?> · <?= e($viewOrder['full_name']) ?></h3>
      <a class="btn btn-ghost btn-sm" href="orders.php">Close</a>
    </div>
    <p class="muted"><?= e($viewOrder['order_date']) ?> · Ship to: <?= e($viewOrder['shipping_address']) ?></p>
    <table class="data">
      <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total</th></tr></thead>
      <tbody>
        <?php foreach ($viewItems as $it): ?>
          <tr>
            <td><?= e($it['name']) ?></td>
            <td><?= (int) $it['quantity'] ?></td>
            <td><?= money($it['unit_price']) ?></td>
            <td><?= money($it['unit_price'] * $it['quantity']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div class="summary-total"><span>Order total</span><span><?= money($viewOrder['total_amount']) ?></span></div>
  </div>
<?php endif; ?>

<?php if (!$orders): ?>
  <div class="empty">No orders yet.</div>
<?php else: ?>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>#</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?= (int) $o['order_id'] ?></td>
            <td><?= e($o['full_name']) ?><br><span class="muted" style="font-size:.82rem;"><?= e($o['email']) ?></span></td>
            <td><?= e($o['order_date']) ?></td>
            <td><?= money($o['total_amount']) ?></td>
            <td>
              <form class="inline-form" method="post" action="orders.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="set_status">
                <input type="hidden" name="order_id" value="<?= (int) $o['order_id'] ?>">
                <select name="status" onchange="this.form.submit()">
                  <?php foreach (ORDER_STATUSES as $s): ?>
                    <option value="<?= e($s) ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                  <?php endforeach; ?>
                </select>
                <noscript><button class="btn btn-sm" type="submit">Set</button></noscript>
              </form>
            </td>
            <td><a class="btn btn-ghost btn-sm" href="orders.php?view=<?= (int) $o['order_id'] ?>">View details</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
