<?php
/**
 * includes/header.php — site chrome (head, top navigation, flash messages).
 *
 * Pages should set, before including this file:
 *   $BASE        string  URL prefix to the app root ('' for root pages,
 *                        '../' for pages inside /admin). Defaults to ''.
 *   $page_title  string  <title> text. Defaults to the app name.
 *
 * The navigation adapts to login status and role.
 */

if (!function_exists('e')) {
    require_once __DIR__ . '/functions.php';
}

$BASE       = $BASE ?? '';
$page_title = $page_title ?? APP_NAME;
$current    = basename($_SERVER['SCRIPT_NAME'] ?? '');
$flashes    = get_flashes();

/** Mark a nav link active when it points at the current page. */
function nav_active(string $file, string $current): string
{
    return $file === $current ? ' class="active"' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($page_title) ?> — <?= e(APP_NAME) ?></title>
  <link rel="stylesheet" href="<?= e($BASE) ?>css/style.css">
</head>
<body>
<header class="site-header">
  <div class="container nav-bar">
    <a class="brand" href="<?= e($BASE) ?>index.php">
      <span class="brand-mark">◆</span> <?= e(APP_NAME) ?>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <nav class="nav-links" id="navLinks">
      <form class="nav-search" action="<?= e($BASE) ?>products.php" method="get" role="search">
        <input type="search" name="q" placeholder="Search products…"
               value="<?= e($_GET['q'] ?? '') ?>" aria-label="Search products">
        <button type="submit" aria-label="Search">⌕</button>
      </form>

      <a href="<?= e($BASE) ?>index.php"<?= nav_active('index.php', $current) ?>>Home</a>
      <a href="<?= e($BASE) ?>products.php"<?= nav_active('products.php', $current) ?>>Products</a>
      <a href="<?= e($BASE) ?>contact.php"<?= nav_active('contact.php', $current) ?>>Contact</a>

      <?php if (is_admin()): ?>
        <a href="<?= e($BASE) ?>admin/dashboard.php" class="nav-admin">Admin</a>
        <a href="<?= e($BASE) ?>logout.php" class="nav-cta">Logout</a>
      <?php elseif (is_logged_in()): ?>
        <a href="<?= e($BASE) ?>cart.php" class="nav-cart"<?= nav_active('cart.php', $current) ?>>
          Cart<span class="cart-badge"><?= (int) cart_count($pdo) ?></span>
        </a>
        <a href="<?= e($BASE) ?>profile.php"<?= nav_active('profile.php', $current) ?>>Profile</a>
        <a href="<?= e($BASE) ?>logout.php" class="nav-cta">Logout</a>
      <?php else: ?>
        <a href="<?= e($BASE) ?>cart.php" class="nav-cart"<?= nav_active('cart.php', $current) ?>>Cart</a>
        <a href="<?= e($BASE) ?>login.php"<?= nav_active('login.php', $current) ?>>Login</a>
        <a href="<?= e($BASE) ?>register.php" class="nav-cta">Sign up</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<?php if (!empty($flashes)): ?>
  <div class="container flash-stack">
    <?php foreach ($flashes as $f): ?>
      <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<main class="container page">
