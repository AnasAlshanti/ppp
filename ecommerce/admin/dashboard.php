<?php
/** admin/dashboard.php — summary cards + quick links (admin only). */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$totalProducts = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$totalOrders   = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$totalUsers    = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$unreadMsgs    = (int) $pdo->query('SELECT COUNT(*) FROM contacts WHERE is_read = 0')->fetchColumn();
$revenue       = (float) $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE status <> 'cancelled'")->fetchColumn();

// A few recent orders for context.
$recent = $pdo->query(
    'SELECT o.order_id, o.order_date, o.total_amount, o.status, u.full_name
       FROM orders o JOIN users u ON u.user_id = o.user_id
      ORDER BY o.order_date DESC, o.order_id DESC LIMIT 5'
)->fetchAll();

$BASE = '../';
$page_title = 'Admin · Dashboard';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_tabs.php';
?>

<div class="section-head"><h2>Dashboard</h2></div>

<div class="stat-grid">
  <div class="stat-card"><div class="num"><?= $totalProducts ?></div><div class="label">Products</div></div>
  <div class="stat-card"><div class="num"><?= $totalOrders ?></div><div class="label">Orders</div></div>
  <div class="stat-card"><div class="num"><?= $totalUsers ?></div><div class="label">Users</div></div>
  <div class="stat-card"><div class="num"><?= $unreadMsgs ?></div><div class="label">Unread messages</div></div>
</div>

<div class="grid-2" style="margin-top:24px;">
  <div class="card">
    <h3>Quick actions</h3>
    <div style="display:flex; flex-wrap:wrap; gap:10px;">
      <a class="btn btn-ghost" href="products.php">Manage products</a>
      <a class="btn btn-ghost" href="orders.php">Manage orders</a>
      <a class="btn btn-ghost" href="users.php">Manage users</a>
      <a class="btn btn-ghost" href="messages.php">View messages</a>
    </div>
    <p class="muted" style="margin-top:16px;">Lifetime revenue (excl. cancelled): <strong><?= money($revenue) ?></strong></p>
  </div>

  <div class="card">
    <h3>Recent orders</h3>
    <?php if (!$recent): ?>
      <p class="muted">No orders yet.</p>
    <?php else: ?>
      <table class="data">
        <thead><tr><th>#</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($recent as $o): ?>
            <tr>
              <td>#<?= (int) $o['order_id'] ?></td>
              <td><?= e($o['full_name']) ?></td>
              <td><?= money($o['total_amount']) ?></td>
              <td><span class="pill pill-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
