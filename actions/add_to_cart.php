<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to add items to cart.']);
    exit;
}

if (empty($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required.']);
    exit;
}

$product_id = intval($_POST['product_id']);
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($quantity < 1) {
    echo json_encode(['status' => 'error', 'message' => 'Quantity must be at least 1.']);
    exit;
}

$result = add_to_cart_ctr($product_id, $quantity);

if ($result) {
    $cartCount = get_cart_count_ctr();
    echo json_encode([
        'status' => 'success',
        'message' => 'Product added to cart successfully!',
        'cart_count' => $cartCount
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add product to cart.']);
}

