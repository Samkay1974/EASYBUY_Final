<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';

$cartCount = 0;
if (isLoggedIn()) {
    $cartCount = get_cart_count_ctr();
    
    // Check for pending collaboration orders where user is a member and hasn't paid
    require_once __DIR__ . '/../controllers/collaboration_controller.php';
    require_once __DIR__ . '/../controllers/order_controller.php';
    $user_id = get_user_id();
    
    // Get user's collaborations
    $collaborations = get_user_collaborations_ctr($user_id);
    
    foreach ($collaborations as $collab) {
        // Check if order exists and user hasn't paid
        $collab_order = get_collaboration_order_ctr($collab['collaboration_id']);
        if ($collab_order && $collab_order['payment_status'] == 'pending') {
            $member_payment = get_member_payment_status_ctr($collab_order['order_id'], $user_id);
            if (!$member_payment || $member_payment['payment_status'] != 'paid') {
                // User has a pending collaboration order payment, add to cart count
                $cartCount++;
                break; // Only count once (one pending payment = one badge)
            }
        }
    }
}

echo json_encode(['cart_count' => $cartCount]);

