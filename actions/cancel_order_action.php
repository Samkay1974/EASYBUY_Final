<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/order_controller.php';

if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$customer_id = get_user_id();

if (!$order_id) {
    $_SESSION['error'] = "Invalid order ID.";
    header("Location: ../view/cart.php");
    exit;
}

// Verify order belongs to customer or user is a member of collaboration
$order = get_order_by_id_ctr($order_id);
if (!$order) {
    $_SESSION['error'] = "Order not found.";
    header("Location: ../view/cart.php");
    exit;
}

// Check if this is a collaboration order
$is_collaboration_order = !empty($order['collaboration_id']);
$is_authorized = false;

if ($is_collaboration_order) {
    // For collaboration orders, check if user is a member
    require_once __DIR__ . '/../controllers/collaboration_controller.php';
    $is_authorized = is_member_ctr($order['collaboration_id'], $customer_id);
} else {
    // For regular orders, check if user is the customer
    $is_authorized = ($order['customer_id'] == $customer_id);
}

if (!$is_authorized) {
    $_SESSION['error'] = "Order not found or you don't have permission to cancel this order.";
    header("Location: ../view/cart.php");
    exit;
}

// Check if order can be cancelled (only if not paid)
if ($order['payment_status'] == 'paid') {
    $_SESSION['error'] = "Cannot cancel order that has already been paid.";
    header("Location: checkout.php?order_id=" . $order_id);
    exit;
}

if ($order['status'] == 'cancelled') {
    $_SESSION['info'] = "This order has already been cancelled.";
    header("Location: ../view/cart.php");
    exit;
}

// Cancel the order
// For collaboration orders, allow any member to cancel (don't restrict by customer_id)
// For regular orders, restrict to the customer
if ($is_collaboration_order) {
    $result = cancel_order_ctr($order_id, null);
    
    // If order is cancelled, automatically remove the user from the collaboration
    if ($result) {
        require_once __DIR__ . '/../controllers/collaboration_controller.php';
        $collaboration_id = $order['collaboration_id'];
        
        // Remove user from collaboration (they cancelled the order)
        leave_collaboration_ctr($collaboration_id, $customer_id);
    }
} else {
    $result = cancel_order_ctr($order_id, $customer_id);
}

if ($result) {
    $message = "Order #{$order_id} has been cancelled successfully.";
    if ($is_collaboration_order) {
        $message .= " You have been removed from the collaboration group.";
    }
    $_SESSION['success'] = $message;
} else {
    $_SESSION['error'] = "Failed to cancel order. Please try again.";
}

header("Location: ../view/cart.php");
exit;
?>
