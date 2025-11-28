<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

if (empty($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID and quantity are required.']);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

$result = update_cart_ctr($product_id, $quantity);

if ($result) {
    $cartCount = get_cart_count_ctr();
    $cartTotal = get_cart_total_ctr();
    echo json_encode([
        'status' => 'success',
        'message' => 'Cart updated successfully!',
        'cart_count' => $cartCount,
        'cart_total' => $cartTotal
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to update cart.']);
}

