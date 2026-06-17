<?php
/** profile.php — account details, password change and order history. */
require_once __DIR__ . '/includes/functions.php';

require_login('profile.php');
$userId = current_user_id();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $phone    = trim((string) ($_POST['phone'] ?? ''));
        $address  = trim((string) ($_POST['address'] ?? ''));

        if ($fullName === '') {
            set_flash('error', 'Full name cannot be empty.');
        } else {
            $stmt = $pdo->prepare('UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?');
            $stmt->execute([$fullName, $phone, $address, $userId]);
            $_SESSION['full_name'] = $fullName;
            set_flash('success', 'Profile updated.');
        }
        redirect('profile.php');
    }

    if ($action === 'change_password') {
        $current = (string) ($_POST['current_password'] ?? '');
        $new     = (string) ($_POST['new_password'] ?? '');
        $confirm = (string) ($_POST['confirm_password'] ?? '');

        $stmt = $pdo->prepare('SELECT password FROM users WHERE user_id = ?');
        $stmt->execute([$userId]);
        $hash = (string) $stmt->fetchColumn();

        if (!password_verify($current, $hash)) {
            set_flash('error', 'Your current password is incorrect.');
        } elseif (strlen($new) < 6) {
            set_flash('error', 'New password must be at least 6 characters.');
        } elseif ($new !== $confirm) {
            set_flash('error', 'New passwords do not match.');
        } else {
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE user_id = ?');
            $stmt->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
            set_flash('success', 'Password changed.');
        }
        redirect('profile.php');
    }
}

// Load the user and their orders.
$stmt = $pdo->prepare('SELECT full_name, email, phone, address FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

$stmt = $pdo->prepare(
    'SELECT order_id, order_date, total_amount, status
       FROM orders WHERE user_id = ? ORDER BY order_date DESC, order_id DESC'
);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

$page_title = 'My profile';
require __DIR__ . '/includes/header.php';
?>

<div class="section-head"><h2>My account</h2></div>

<div class="grid-2">
  <div class="card">
    <h3>Profile details</h3>
    <form class="form-grid" method="post" action="profile.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update_profile">
      <div>
        <label for="full_name">Full name</label>
        <input type="text" id="full_name" name="full_name" required value="<?= e($user['full_name']) ?>">
      </div>
      <div>
        <label>Email</label>
        <input type="email" value="<?= e($user['email']) ?>" disabled>
        <span class="help">Email can't be changed.</span>
      </div>
      <div>
        <label for="phone">Phone</label>
        <input type="tel" id="phone" name="phone" value="<?= e($user['phone']) ?>">
      </div>
      <div>
        <label for="address">Address</label>
        <textarea id="address" name="address" style="min-height:80px;"><?= e($user['address']) ?></textarea>
      </div>
      <button class="btn" type="submit">Save changes</button>
    </form>
  </div>

  <div class="card">
    <h3>Change password</h3>
    <form class="form-grid" method="post" action="profile.php">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="change_password">
      <div>
        <label for="current_password">Current password</label>
        <input type="password" id="current_password" name="current_password" required>
      </div>
      <div>
        <label for="new_password">New password</label>
        <input type="password" id="new_password" name="new_password" required minlength="6">
      </div>
      <div>
        <label for="confirm_password">Confirm new password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
      </div>
      <button class="btn btn-ghost" type="submit">Update password</button>
    </form>
  </div>
</div>

<div class="section-head" style="margin-top:36px;"><h2>Order history</h2></div>
<?php if (!$orders): ?>
  <div class="empty">You haven't placed any orders yet. <a href="products.php">Start shopping</a>.</div>
<?php else: ?>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($orders as $o): ?>
          <tr>
            <td>#<?= (int) $o['order_id'] ?></td>
            <td><?= e($o['order_date']) ?></td>
            <td><?= money($o['total_amount']) ?></td>
            <td><span class="pill pill-<?= e($o['status']) ?>"><?= e($o['status']) ?></span></td>
            <td><a class="btn btn-ghost btn-sm" href="order-confirmation.php?order=<?= (int) $o['order_id'] ?>">View details</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
