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
$cart_items = get_cart_items_ctr();

if (empty($cart_items)) {
    $_SESSION['error'] = "Your cart is empty. Please add items to cart first.";
    header("Location: ../view/cart.php");
    exit;
}

// Check if this is a collaboration order first
$collaboration_id = isset($_GET['collaboration_id']) ? intval($_GET['collaboration_id']) : null;

// Only check for duplicate products in pending orders if NOT a collaboration order
// For collaboration orders, multiple members can have pending payments for the same order
// For regular orders, only block if the SAME product is already in a pending order
if (!$collaboration_id) {
    // Get product IDs from cart
    $cart_product_ids = [];
    foreach ($cart_items as $item) {
        $cart_product_ids[] = $item['product_id'];
    }
    
    if (!empty($cart_product_ids)) {
        // Get all pending orders for this customer
        $pending_orders = get_pending_orders_for_customer_ctr($customer_id);
        
        if (!empty($pending_orders)) {
            // Check if any product in cart is already in a pending order
            $duplicate_products = [];
            foreach ($pending_orders as $pending_order) {
                $order_details = get_order_details_ctr($pending_order['order_id']);
                foreach ($order_details as $detail) {
                    if (in_array($detail['product_id'], $cart_product_ids)) {
                        // Found a duplicate product
                        $duplicate_products[] = [
                            'product_id' => $detail['product_id'],
                            'product_name' => $detail['product_name'],
                            'order_id' => $pending_order['order_id']
                        ];
                    }
                }
            }
            
            if (!empty($duplicate_products)) {
                // User has pending order with same product(s)
                $duplicate_product = $duplicate_products[0];
                $_SESSION['error'] = "You already have a pending order for '{$duplicate_product['product_name']}'. Please complete payment for order #{$duplicate_product['order_id']} first, or cancel it before placing a new order for this product.";
                header("Location: checkout.php?order_id=" . $duplicate_product['order_id']);
                exit;
            }
        }
    }
}


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

// Calculate total amount for the FULL order (not individual contribution)
foreach ($cart_items as $item) {
    // Get product to find wholesaler_id
    $product = get_one_product_ctr($item['product_id']);
    if ($product) {
        // Price is for 1 MOQ unit, multiply by number of MOQ units ordered
        // For collaboration, this is the FULL amount, not individual contribution
        $item_total = $item['wholesale_price'] * $item['moq_quantity'];
        $total_amount += $item_total;
    }
}

// No transaction fee - removed
$transaction_fee = 0.00;
$final_amount = $total_amount;

// Create order with FULL amount (customer_id is the person who placed the order - contact person)
$order_id = create_order_ctr($customer_id, $total_amount, $transaction_fee, $final_amount, $collaboration_id);

if (!$order_id) {
    $_SESSION['error'] = "Failed to create order. Please try again.";
    header("Location: ../view/cart.php");
    exit;
}

// Add order details with FULL quantity and amount
foreach ($cart_items as $item) {
    $product = get_one_product_ctr($item['product_id']);
    if ($product) {
        $wholesaler_id = $product['user_id']; // The wholesaler who created the product
        $quantity = $item['actual_units']; // Actual quantity ordered (FULL quantity)
        $unit_price = $item['wholesale_price']; // Price for 1 MOQ unit
        $subtotal = $unit_price * $item['moq_quantity']; // FULL subtotal
        
        add_order_details_ctr(
            $order_id,
            $item['product_id'],
            $wholesaler_id,
            $quantity,
            $unit_price,
            $subtotal
        );
    }
}

// If collaboration order, initialize payment records for all members
if ($collaboration_id && !empty($members)) {
    require_once __DIR__ . '/../settings/db_class.php';
    $db = new db_connection();
    $db->db_connect();
    
    foreach ($members as $member) {
        $member_contribution = floatval($member['contribution_percent']);
        // No transaction fee - removed
        $member_amount = $total_amount * ($member_contribution / 100);
        
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

// Don't clear cart here - it will be cleared after successful payment
// Cart will remain so user can still cancel the order and items will be in cart

$_SESSION['success'] = "Order placed successfully! The wholesaler will contact you via phone call for more information about your order before you make payment.";
header("Location: checkout.php?order_id=" . $order_id);
exit;
?>
