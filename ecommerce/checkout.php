<?php
/**
 * checkout.php — turn the current user's cart into an order.
 *
 * Steps (wrapped in a transaction):
 *   1. Calculate the total from the cart items.
 *   2. Insert a row into `orders` (shipping address taken from the user).
 *   3. Copy each cart item into `order_items` (capturing unit_price).
 *   4. Decrement product stock.
 *   5. Delete the user's cart items.
 *   6. Redirect to the order confirmation page.
 */
require_once __DIR__ . '/includes/functions.php';

require_login('cart.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}
verify_csrf();

$userId = current_user_id();

// Load cart items joined with current product price/stock.
$stmt = $pdo->prepare(
    'SELECT ci.product_id, ci.quantity, p.price, p.stock_quantity, p.name
       FROM cart_items ci
       JOIN products p ON p.product_id = ci.product_id
      WHERE ci.user_id = ?'
);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

if (!$items) {
    set_flash('error', 'Your cart is empty.');
    redirect('cart.php');
}

// Pull the shipping address from the user's profile.
$stmt = $pdo->prepare('SELECT address FROM users WHERE user_id = ?');
$stmt->execute([$userId]);
$shippingAddress = (string) ($stmt->fetchColumn() ?: '');

// Compute the total.
$total = 0.0;
foreach ($items as $it) {
    $total += (float) $it['price'] * (int) $it['quantity'];
}
$total = round($total * (1 + TAX_RATE), 2);

try {
    $pdo->beginTransaction();

    // 2. Create the order.
    $stmt = $pdo->prepare(
        'INSERT INTO orders (user_id, total_amount, status, shipping_address)
         VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$userId, $total, 'paid', $shippingAddress]);
    $orderId = (int) $pdo->lastInsertId();

    // 3 + 4. Copy items and decrement stock.
    $insItem = $pdo->prepare(
        'INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)'
    );
    // CASE expression keeps stock at or above 0 and is portable (MySQL + SQLite).
    $decStock = $pdo->prepare(
        'UPDATE products
            SET stock_quantity = CASE WHEN stock_quantity - ? < 0 THEN 0 ELSE stock_quantity - ? END
          WHERE product_id = ?'
    );
    foreach ($items as $it) {
        $qty = (int) $it['quantity'];
        $insItem->execute([$orderId, (int) $it['product_id'], $qty, (float) $it['price']]);
        $decStock->execute([$qty, $qty, (int) $it['product_id']]);
    }

    // 5. Clear the cart.
    $pdo->prepare('DELETE FROM cart_items WHERE user_id = ?')->execute([$userId]);

    $pdo->commit();
} catch (Throwable $ex) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('error', 'We could not process your order. Please try again.');
    redirect('cart.php');
}

set_flash('success', 'Thank you! Your order has been placed.');
redirect('order-confirmation.php?order=' . $orderId);
