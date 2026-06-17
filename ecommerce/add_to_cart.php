<?php
/** add_to_cart.php — add/increase a product in the logged-in user's cart. */
require_once __DIR__ . '/includes/functions.php';

$isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

/** Emit a JSON response (AJAX) or fall back to a flash + redirect. */
function respond(bool $ok, string $message, ?int $cartCount, bool $isAjax, string $back): void
{
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $ok, 'message' => $message, 'cart_count' => $cartCount]);
        exit;
    }
    set_flash($ok ? 'success' : 'error', $message);
    redirect($back);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('products.php');
}
verify_csrf();

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity  = (int) ($_POST['quantity'] ?? 1);
$back      = 'product-detail.php?id=' . $productId;

if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $back;
    respond(false, 'Please sign in to add items to your cart.', null, $isAjax, 'login.php');
}

if ($quantity < 1) {
    $quantity = 1;
}

// Validate the product and stock.
$stmt = $pdo->prepare('SELECT product_id, name, stock_quantity FROM products WHERE product_id = ?');
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    respond(false, 'That product no longer exists.', cart_count($pdo), $isAjax, 'products.php');
}
if ((int) $product['stock_quantity'] < 1) {
    respond(false, 'Sorry, that product is out of stock.', cart_count($pdo), $isAjax, $back);
}

$userId = current_user_id();

// Upsert cart_items (portable: look up existing row, then update or insert).
$stmt = $pdo->prepare('SELECT cart_id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?');
$stmt->execute([$userId, $productId]);
$existing = $stmt->fetch();

$desired = ($existing ? (int) $existing['quantity'] : 0) + $quantity;
$capped  = min($desired, (int) $product['stock_quantity']); // never exceed stock

if ($existing) {
    $pdo->prepare('UPDATE cart_items SET quantity = ? WHERE cart_id = ?')
        ->execute([$capped, $existing['cart_id']]);
} else {
    $pdo->prepare('INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)')
        ->execute([$userId, $productId, $capped]);
}

$message = $capped < $desired
    ? 'Cart updated to the maximum available stock.'
    : 'Added to your cart.';

respond(true, $message, cart_count($pdo), $isAjax, 'cart.php');
