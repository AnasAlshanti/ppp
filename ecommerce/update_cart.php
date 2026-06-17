<?php
/** update_cart.php — change the quantity of a cart line for the current user. */
require_once __DIR__ . '/includes/functions.php';

require_login('cart.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}
verify_csrf();

$productId = (int) ($_POST['product_id'] ?? 0);
$quantity  = (int) ($_POST['quantity'] ?? 0);
$userId    = current_user_id();

if ($quantity < 1) {
    // Treat "0 or less" as a removal.
    $pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?')
        ->execute([$userId, $productId]);
    set_flash('info', 'Item removed from your cart.');
    redirect('cart.php');
}

// Cap the quantity to the available stock.
$stmt = $pdo->prepare('SELECT stock_quantity FROM products WHERE product_id = ?');
$stmt->execute([$productId]);
$stock = $stmt->fetchColumn();

if ($stock === false) {
    set_flash('error', 'That product no longer exists.');
    redirect('cart.php');
}

$quantity = min($quantity, max(1, (int) $stock));

$pdo->prepare('UPDATE cart_items SET quantity = ? WHERE user_id = ? AND product_id = ?')
    ->execute([$quantity, $userId, $productId]);

set_flash('success', 'Cart updated.');
redirect('cart.php');
