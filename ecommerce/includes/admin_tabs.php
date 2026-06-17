<?php
/** includes/admin_tabs.php — admin navigation tabs. Expects $current (basename). */
$current = $current ?? basename($_SERVER['SCRIPT_NAME'] ?? '');
$tabs = [
    'dashboard.php' => 'Dashboard',
    'products.php'  => 'Products',
    'orders.php'    => 'Orders',
    'users.php'     => 'Users',
    'messages.php'  => 'Messages',
];
?>
<nav class="admin-tabs">
  <?php foreach ($tabs as $file => $label): ?>
    <a href="<?= e($file) ?>"<?= $file === $current ? ' class="active"' : '' ?>><?= e($label) ?></a>
  <?php endforeach; ?>
</nav>
