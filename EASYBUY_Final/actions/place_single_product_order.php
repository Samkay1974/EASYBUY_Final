<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/order_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';

if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$customer_id = get_user_id();
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;

if (!$product_id) {
    $_SESSION['error'] = "Invalid product ID.";
    header("Location: ../view/cart.php");
    exit;
}

// Get cart items and filter for this specific product
$all_cart_items = get_cart_items_ctr();
$cart_items = [];
foreach ($all_cart_items as $item) {
    if ($item['product_id'] == $product_id) {
        $cart_items[] = $item;
        break;
    }
}

if (empty($cart_items)) {
    $_SESSION['error'] = "This product is not in your cart.";
    header("Location: ../view/cart.php");
    exit;
}

// Check if this product is already in a pending order
$pending_orders = get_pending_orders_for_customer_ctr($customer_id);
if (!empty($pending_orders)) {
    foreach ($pending_orders as $pending_order) {
        $order_details = get_order_details_ctr($pending_order['order_id']);
        foreach ($order_details as $detail) {
            if ($detail['product_id'] == $product_id) {
                // Found this product in a pending order
                $_SESSION['error'] = "You already have a pending order for '{$detail['product_name']}'. Please complete payment for order #{$pending_order['order_id']} first, or cancel it before placing a new order for this product.";
                header("Location: ../actions/checkout.php?order_id=" . $pending_order['order_id']);
                exit;
            }
        }
    }
}

// Check if this is a collaboration order
$collaboration_id = isset($_GET['collaboration_id']) ? intval($_GET['collaboration_id']) : null;

$total_amount = 0;
$user_contribution_percent = null;
$members = [];

// If this is a collaboration order
if ($collaboration_id) {
    // Verify user is a member of this collaboration
    if (!is_member_ctr($collaboration_id, $customer_id)) {
        $_SESSION['error'] = "You are not a member of this collaboration.";
        header("Location: ../view/cart.php");
        exit;
    }
    
    // Get collaboration details
    $collab = get_collaboration_by_id_ctr($collaboration_id);
    if (!$collab || $collab['status'] != 'completed') {
        $_SESSION['error'] = "This collaboration is not ready for ordering.";
        header("Location: ../view/cart.php");
        exit;
    }
    
    // Check if order already exists for this collaboration
    $existing_order = get_collaboration_order_ctr($collaboration_id);
    if ($existing_order) {
        $_SESSION['info'] = "An order has already been placed for this collaboration. You can proceed to checkout to pay your contribution.";
        header("Location: ../actions/checkout.php?order_id=" . $existing_order['order_id']);
        exit;
    }
    
    // Get all members
    $members = get_collaboration_members_ctr($collaboration_id);
    foreach ($members as $member) {
        if ($member['user_id'] == $customer_id) {
            $user_contribution_percent = floatval($member['contribution_percent']);
            break;
        }
    }
    
    if (!$user_contribution_percent) {
        $_SESSION['error'] = "Could not find your contribution percentage.";
        header("Location: ../view/cart.php");
        exit;
    }
}

// Calculate total amount for this single product
$item = $cart_items[0];
$product = get_one_product_ctr($item['product_id']);
if ($product) {
    $item_total = $item['wholesale_price'] * $item['moq_quantity'];
    $total_amount = $item_total;
}

$transaction_fee = $total_amount * 0.01;
$final_amount = $total_amount + $transaction_fee;

// Create order with this single product
$order_id = create_order_ctr($customer_id, $total_amount, $transaction_fee, $final_amount, $collaboration_id);

if (!$order_id) {
    $_SESSION['error'] = "Failed to create order. Please try again.";
    header("Location: ../view/cart.php");
    exit;
}

// Add order detail for this single product
$product = get_one_product_ctr($item['product_id']);
if ($product) {
    $wholesaler_id = $product['user_id'];
    $quantity = $item['actual_units'];
    $unit_price = $item['wholesale_price'];
    $subtotal = $unit_price * $item['moq_quantity'];
    
    add_order_details_ctr(
        $order_id,
        $item['product_id'],
        $wholesaler_id,
        $quantity,
        $unit_price,
        $subtotal
    );
}

// If collaboration order, initialize payment records for all members
if ($collaboration_id && !empty($members)) {
    require_once __DIR__ . '/../settings/db_class.php';
    $db = new db_connection();
    $db->db_connect();
    
    foreach ($members as $member) {
        $member_contribution = floatval($member['contribution_percent']);
        $member_amount = ($total_amount * ($member_contribution / 100)) + (($total_amount * ($member_contribution / 100)) * 0.01);
        
        // Initialize payment record (pending)
        $init_sql = "INSERT INTO collaboration_order_payments 
                    (order_id, collaboration_id, user_id, contribution_percent, amount, payment_status, created_at)
                    VALUES (:order_id, :collab_id, :user_id, :percent, :amount, 'pending', NOW())
                    ON DUPLICATE KEY UPDATE created_at = NOW()";
        $init_stmt = $db->db->prepare($init_sql);
        $init_stmt->execute([
            ':order_id' => $order_id,
            ':collab_id' => $collaboration_id,
            ':user_id' => $member['user_id'],
            ':percent' => $member_contribution,
            ':amount' => $member_amount
        ]);
    }
}

// Don't remove product from cart here - it will remain in cart until payment is completed
// This allows users to open the order and make payment later
// Cart will be cleared after successful payment in paystack_verify_payment.php
// If order is cancelled, product will remain in cart for re-ordering

$_SESSION['success'] = "Order placed successfully! The wholesaler will contact you via phone call for more information about your order before you make payment.";
header("Location: checkout.php?order_id=" . $order_id);
exit;
?>

