<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (!isLoggedIn()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized.']);
    exit;
}

$result = clear_cart_ctr();

if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Cart cleared successfully!',
        'cart_count' => 0
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to clear cart.']);
}

