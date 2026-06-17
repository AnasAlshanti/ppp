<?php
/** register.php — show the signup form (GET) and create an account (POST). */
require_once __DIR__ . '/includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$errors = [];
$old    = ['full_name' => '', 'email' => '', 'phone' => '', 'address' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $old['full_name'] = trim((string) ($_POST['full_name'] ?? ''));
    $old['email']     = trim((string) ($_POST['email'] ?? ''));
    $old['phone']     = trim((string) ($_POST['phone'] ?? ''));
    $old['address']   = trim((string) ($_POST['address'] ?? ''));
    $password         = (string) ($_POST['password'] ?? '');
    $confirm          = (string) ($_POST['confirm_password'] ?? '');

    // Server-side validation.
    if ($old['full_name'] === '')                          { $errors[] = 'Full name is required.'; }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) { $errors[] = 'A valid email is required.'; }
    if (strlen($password) < 6)                             { $errors[] = 'Password must be at least 6 characters.'; }
    if ($password !== $confirm)                            { $errors[] = 'Passwords do not match.'; }

    // Reject duplicate emails.
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetchColumn()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, password, phone, address, role)
             VALUES (?, ?, ?, ?, ?, \'customer\')'
        );
        $stmt->execute([$old['full_name'], $old['email'], $hash, $old['phone'], $old['address']]);

        set_flash('success', 'Account created! Please sign in.');
        redirect('login.php');
    }
}

$page_title = 'Create account';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap card">
  <h2 class="center">Create your account</h2>

  <?php if ($errors): ?>
    <div class="flash flash-error" style="margin:12px 0;"><?= e(implode(' ', $errors)) ?></div>
  <?php endif; ?>

  <form class="form-grid" method="post" action="register.php">
    <?= csrf_field() ?>
    <div>
      <label for="full_name">Full name</label>
      <input type="text" id="full_name" name="full_name" required maxlength="100" value="<?= e($old['full_name']) ?>">
    </div>
    <div>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required maxlength="100" value="<?= e($old['email']) ?>">
    </div>
    <div class="form-row">
      <div>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required minlength="6">
      </div>
      <div>
        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
      </div>
    </div>
    <div>
      <label for="phone">Phone</label>
      <input type="tel" id="phone" name="phone" maxlength="20" value="<?= e($old['phone']) ?>">
    </div>
    <div>
      <label for="address">Address</label>
      <textarea id="address" name="address" style="min-height:80px;"><?= e($old['address']) ?></textarea>
    </div>
    <button class="btn btn-block" type="submit">Create account</button>
  </form>

  <p class="center" style="margin-top:14px;">Already have an account? <a href="login.php">Sign in</a></p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
