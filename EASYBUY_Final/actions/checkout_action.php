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

$order = get_order_by_id_ctr($order_id);

if (!$order) {
    $_SESSION['error'] = "Order not found.";
    header("Location: ../view/cart.php");
    exit;
}

// Check if this is a collaboration order
$is_collaboration_order = !empty($order['collaboration_id']);

// For collaboration orders, check if user is a member
// For regular orders, check if user is the customer
if ($is_collaboration_order) {
    require_once __DIR__ . '/../controllers/collaboration_controller.php';
    if (!is_member_ctr($order['collaboration_id'], $customer_id)) {
        $_SESSION['error'] = "You are not a member of this collaboration.";
        header("Location: ../view/cart.php");
        exit;
    }
} else {
    if ($order['customer_id'] != $customer_id) {
        $_SESSION['error'] = "You don't have permission to pay for this order.";
        header("Location: ../view/cart.php");
        exit;
    }
}

if ($is_collaboration_order) {
    // For collaboration orders, check if this member has already paid
    require_once __DIR__ . '/../controllers/collaboration_controller.php';
    require_once __DIR__ . '/../controllers/order_controller.php';
    
    if (!is_member_ctr($order['collaboration_id'], $customer_id)) {
        $_SESSION['error'] = "You are not a member of this collaboration.";
        header("Location: ../view/cart.php");
        exit;
    }
    
    $member_payment = get_member_payment_status_ctr($order_id, $customer_id);
    if ($member_payment && $member_payment['payment_status'] == 'paid') {
        $_SESSION['success'] = "You have already paid your contribution for this order.";
        header("Location: checkout.php?order_id=" . $order_id);
        exit;
    }
} else {
    // For regular orders, check if already paid
    if ($order['payment_status'] == 'paid') {
        $_SESSION['success'] = "This order has already been paid.";
        header("Location: order_success.php?order_id=" . $order_id);
        exit;
    }
}

// Check if order is cancelled
if ($order['status'] == 'cancelled') {
    $_SESSION['error'] = "Cannot pay for a cancelled order.";
    header("Location: ../view/cart.php");
    exit;
}

// In a real application, you would integrate with a payment gateway here
// For now, we'll just update the payment status to 'paid'

if ($is_collaboration_order) {
    // For collaboration orders, redirect to Paystack payment
    require_once __DIR__ . '/../controllers/collaboration_controller.php';
    require_once __DIR__ . '/../controllers/user_controller.php';
    
    $members = get_collaboration_members_ctr($order['collaboration_id']);
    
    $member_contribution_percent = 0;
    foreach ($members as $member) {
        if ($member['user_id'] == $customer_id) {
            $member_contribution_percent = floatval($member['contribution_percent']);
            break;
        }
    }
    
    if ($member_contribution_percent > 0) {
        $member_subtotal = $order['total_amount'] * ($member_contribution_percent / 100);
        $member_fee = $member_subtotal * 0.01;
        $member_amount = $member_subtotal + $member_fee;
        
        // Get user email
        $user = get_user_by_id_ctr($customer_id);
        $user_email = $user ? $user['email'] : '';
        
        if (empty($user_email)) {
            $_SESSION['error'] = "Email address is required for payment. Please update your profile.";
            header("Location: checkout.php?order_id=" . $order_id);
            exit;
        }
        
        // Redirect to checkout page which will initiate Paystack payment via JavaScript
        // Store order info in session for Paystack
        $_SESSION['paystack_order_id'] = $order_id;
        $_SESSION['paystack_amount'] = $member_amount;
        
        header("Location: checkout.php?order_id=" . $order_id . "&init_payment=1");
        exit;
    } else {
        error_log("Member contribution percent is 0 or not found - Order ID: $order_id, User ID: $customer_id");
        $_SESSION['error'] = "Could not find your contribution percentage. Please contact support.";
        header("Location: checkout.php?order_id=" . $order_id);
        exit;
    }
} else {
    // Regular order payment - redirect to Paystack
    require_once __DIR__ . '/../controllers/user_controller.php';
    
    // Get user email
    $user = get_user_by_id_ctr($customer_id);
    $user_email = $user ? $user['email'] : '';
    
    if (empty($user_email)) {
        $_SESSION['error'] = "Email address is required for payment. Please update your profile.";
        header("Location: checkout.php?order_id=" . $order_id);
        exit;
    }
    
    // Store order info in session for Paystack
    $_SESSION['paystack_order_id'] = $order_id;
    $_SESSION['paystack_amount'] = $order['final_amount'];
    
    // Redirect to checkout page which will initiate Paystack payment via JavaScript
    header("Location: checkout.php?order_id=" . $order_id . "&init_payment=1");
    exit;
}
?>

