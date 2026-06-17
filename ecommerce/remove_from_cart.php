<?php
/** remove_from_cart.php — delete a single line from the current user's cart. */
require_once __DIR__ . '/includes/functions.php';

require_login('cart.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}
verify_csrf();

$productId = (int) ($_POST['product_id'] ?? 0);

$pdo->prepare('DELETE FROM cart_items WHERE user_id = ? AND product_id = ?')
    ->execute([current_user_id(), $productId]);

set_flash('info', 'Item removed from your cart.');
redirect('cart.php');
