<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

if (empty($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required.']);
    exit;
}

$product_id = intval($_POST['product_id']);
$customer_id = get_user_id();

// Check if there's a pending order for this product and cancel it
$pending_order = get_pending_order_for_product_ctr($customer_id, $product_id);
if ($pending_order) {
    // Cancel the pending order since product is being removed from cart
    cancel_order_ctr($pending_order['order_id'], $customer_id);
}

// Remove product from cart
$result = remove_from_cart_ctr($product_id);

if ($result) {
    $cartCount = get_cart_count_ctr();
    $cartTotal = get_cart_total_ctr();
    $message = 'Product removed from cart!';
    if ($pending_order) {
        $message .= ' Pending order #' . $pending_order['order_id'] . ' has been cancelled.';
    }
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'cart_count' => $cartCount,
        'cart_total' => $cartTotal
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to remove product from cart.']);
}

