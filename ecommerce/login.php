<?php
/** login.php — show the login form (GET) and authenticate (POST). */
require_once __DIR__ . '/includes/functions.php';

// Already signed in? Send them somewhere useful.
if (is_logged_in()) {
    redirect(is_admin() ? 'admin/dashboard.php' : 'index.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $pdo->prepare('SELECT user_id, full_name, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Prevent session fixation, then store identity.
            session_regenerate_id(true);
            $_SESSION['user_id']   = (int) $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role']      = $user['role'];

            $dest = $_SESSION['redirect_after_login'] ?? null;
            unset($_SESSION['redirect_after_login']);

            if ($user['role'] === 'admin') {
                redirect('admin/dashboard.php');
            }
            redirect($dest ?: 'index.php');
        }
        $error = 'Invalid email or password.';
    }
}

$page_title = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap card">
  <h2 class="center">Welcome back</h2>
  <p class="center muted">Sign in to your <?= e(APP_NAME) ?> account.</p>

  <?php if ($error): ?>
    <div class="flash flash-error" style="margin:12px 0;"><?= e($error) ?></div>
  <?php endif; ?>

  <form class="form-grid" method="post" action="login.php">
    <?= csrf_field() ?>
    <div>
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required value="<?= e($email) ?>">
    </div>
    <div>
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
    </div>
    <button class="btn btn-block" type="submit">Sign in</button>
  </form>

  <p class="center" style="margin-top:14px;">New here? <a href="register.php">Create an account</a></p>
  <p class="center help">Demo admin: admin@codenest.test / Admin@123</p>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
