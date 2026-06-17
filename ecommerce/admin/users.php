<?php
/** admin/users.php — list users, change role, delete (admin only). */
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$me = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $userId = (int) ($_POST['user_id'] ?? 0);

    if ($action === 'set_role') {
        $role = (string) ($_POST['role'] ?? '');
        if (!in_array($role, ['customer', 'admin'], true)) {
            set_flash('error', 'Invalid role.');
        } elseif ($userId === $me) {
            set_flash('error', "You can't change your own role.");
        } else {
            $pdo->prepare('UPDATE users SET role = ? WHERE user_id = ?')->execute([$role, $userId]);
            set_flash('success', 'Role updated.');
        }
        redirect('users.php');
    }

    if ($action === 'delete') {
        if ($userId === $me) {
            set_flash('error', "You can't delete your own account.");
            redirect('users.php');
        }
        // Only allowed if the user has no orders.
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM orders WHERE user_id = ?');
        $stmt->execute([$userId]);
        if ((int) $stmt->fetchColumn() > 0) {
            set_flash('error', 'Cannot delete a user who has placed orders.');
        } else {
            $pdo->prepare('DELETE FROM users WHERE user_id = ?')->execute([$userId]);
            set_flash('success', 'User deleted.');
        }
        redirect('users.php');
    }
}

$users = $pdo->query(
    'SELECT u.user_id, u.full_name, u.email, u.role, u.created_at,
            (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) AS order_count
       FROM users u ORDER BY u.user_id ASC'
)->fetchAll();

$BASE = '../';
$page_title = 'Admin · Users';
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../includes/admin_tabs.php';
?>

<div class="section-head"><h2>Users (<?= count($users) ?>)</h2></div>

<div class="table-wrap">
  <table class="data">
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Orders</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($users as $u): ?>
        <tr>
          <td><?= (int) $u['user_id'] ?></td>
          <td><?= e($u['full_name']) ?><?= (int) $u['user_id'] === $me ? ' <span class="muted">(you)</span>' : '' ?></td>
          <td><?= e($u['email']) ?></td>
          <td>
            <form class="inline-form" method="post" action="users.php">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="set_role">
              <input type="hidden" name="user_id" value="<?= (int) $u['user_id'] ?>">
              <select name="role" onchange="this.form.submit()" <?= (int) $u['user_id'] === $me ? 'disabled' : '' ?>>
                <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>customer</option>
                <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
              </select>
            </form>
          </td>
          <td><?= (int) $u['order_count'] ?></td>
          <td>
            <?php if ((int) $u['user_id'] === $me): ?>
              <span class="muted">—</span>
            <?php elseif ((int) $u['order_count'] > 0): ?>
              <span class="muted" title="Users with orders can't be deleted">Has orders</span>
            <?php else: ?>
              <form class="inline-form" method="post" action="users.php"
                    data-confirm="Delete &quot;<?= e($u['full_name']) ?>&quot;?">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" value="<?= (int) $u['user_id'] ?>">
                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
