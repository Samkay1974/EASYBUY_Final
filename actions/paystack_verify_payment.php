<?php
/**
 * Paystack Callback Handler & Verification
 * Handles payment verification after user returns from Paystack gateway
 */

header('Content-Type: application/json');

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';

error_log("=== PAYSTACK CALLBACK/VERIFICATION ===");

// Check if user is logged in
if (!is_logged_in()) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Session expired. Please login again.'
    ]);
    exit();
}

// Get verification reference from POST data
$input = json_decode(file_get_contents('php://input'), true);
$reference = isset($input['reference']) ? trim($input['reference']) : null;
$cart_items = isset($input['cart_items']) ? $input['cart_items'] : null;
$total_amount = isset($input['total_amount']) ? floatval($input['total_amount']) : 0;

if (!$reference) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No payment reference provided'
    ]);
    exit();
}

// Optional: Verify reference matches session
if (isset($_SESSION['paystack_ref']) && $_SESSION['paystack_ref'] !== $reference) {
    error_log("Reference mismatch - Expected: {$_SESSION['paystack_ref']}, Got: $reference");
    // Allow to proceed anyway, but log it
}

try {
    error_log("Verifying Paystack transaction - Reference: $reference");
    
    // Verify transaction with Paystack
    $verification_response = paystack_verify_transaction($reference);
    
    if (!$verification_response) {
        throw new Exception("No response from Paystack verification API");
    }
    
    error_log("Paystack verification response: " . json_encode($verification_response));
    
    // Check if verification was successful
    if (!isset($verification_response['status']) || $verification_response['status'] !== true) {
        $error_msg = $verification_response['message'] ?? 'Payment verification failed';
        error_log("Payment verification failed: $error_msg");
        
        echo json_encode([
            'status' => 'error',
            'message' => $error_msg,
            'verified' => false
        ]);
        exit();
    }
    
    // Extract transaction data
    $transaction_data = $verification_response['data'] ?? [];
    $payment_status = $transaction_data['status'] ?? null;
    $amount_paid = isset($transaction_data['amount']) ? $transaction_data['amount'] / 100 : 0; // Convert from pesewas
    $customer_email = $transaction_data['customer']['email'] ?? '';
    $authorization = $transaction_data['authorization'] ?? [];
    $authorization_code = $authorization['authorization_code'] ?? '';
    $payment_method = $authorization['channel'] ?? 'card';
    $auth_last_four = $authorization['last_four'] ?? 'XXXX';
    
    error_log("Transaction status: $payment_status, Amount: $amount_paid GHS");
    
    // Validate payment status
    if ($payment_status !== 'success') {
        error_log("Payment status is not successful: $payment_status");
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Payment was not successful. Status: ' . ucfirst($payment_status),
            'verified' => false,
            'payment_status' => $payment_status
        ]);
        exit();
    }
    
    // Check if this is a subscription payment, collaboration order payment, or existing regular order
    $subscription_id = isset($_SESSION['paystack_subscription_id']) ? intval($_SESSION['paystack_subscription_id']) : 0;
    $existing_order_id = isset($_SESSION['paystack_order_id']) ? intval($_SESSION['paystack_order_id']) : 0;
    $is_subscription_payment = false;
    $is_collaboration_order = false;
    $is_existing_regular_order = false;
    $order = null;
    $subscription = null;
    
    // Check for subscription payment first
    if ($subscription_id > 0) {
        require_once '../controllers/subscription_controller.php';
        $subscription = get_subscription_by_id_ctr($subscription_id);
        if ($subscription && $subscription['payment_reference'] == $reference) {
            $is_subscription_payment = true;
            error_log("This is a subscription payment - Subscription ID: $subscription_id");
        }
    }
    
    // If not subscription, check for order payment
    if (!$is_subscription_payment && $existing_order_id > 0) {
        require_once '../controllers/order_controller.php';
        $order = get_order_by_id_ctr($existing_order_id);
        if ($order) {
            if (!empty($order['collaboration_id'])) {
                $is_collaboration_order = true;
                error_log("This is a collaboration order payment - Order ID: $existing_order_id");
            } else {
                $is_existing_regular_order = true;
                error_log("This is an existing regular order payment - Order ID: $existing_order_id");
            }
        }
    }
    
    // For subscription payments, get expected amount from subscription
    // For collaboration orders, get expected amount from order
    // For existing regular orders, get expected amount from order
    // For new regular orders, calculate from cart
    if ($is_subscription_payment && $subscription) {
        // Subscription payment - get expected amount from subscription
        $expected_amount = floatval($subscription['amount']);
        if ($total_amount <= 0) {
            $total_amount = round($expected_amount, 2);
        }
        error_log("Subscription payment - Expected: $expected_amount GHS");
    } elseif ($is_collaboration_order && $order) {
        require_once '../controllers/collaboration_controller.php';
        require_once '../controllers/order_controller.php';
        
        $customer_id = get_user_id();
        $members = get_collaboration_members_ctr($order['collaboration_id']);
        
        $member_contribution_percent = 0;
        foreach ($members as $member) {
            if ($member['user_id'] == $customer_id) {
                $member_contribution_percent = floatval($member['contribution_percent']);
                break;
            }
        }
        
        if ($member_contribution_percent > 0) {
            // No transaction fee - removed
            $expected_amount = $order['total_amount'] * ($member_contribution_percent / 100);
            
            if ($total_amount <= 0) {
                $total_amount = round($expected_amount, 2);
            }
            
            error_log("Collaboration order - Member contribution: $member_contribution_percent%, Expected: $expected_amount GHS");
        } else {
            throw new Exception("Could not find member contribution percentage");
        }
    } elseif ($is_existing_regular_order && $order) {
        // Existing regular order - get expected amount from order (no transaction fee)
        $expected_amount = floatval($order['total_amount']);
        if ($total_amount <= 0) {
            $total_amount = round($expected_amount, 2);
        }
        error_log("Existing regular order - Expected: $expected_amount GHS");
    } else {
        // New regular order - calculate from cart
        require_once '../controllers/cart_controller.php';
        if (!$cart_items || count($cart_items) == 0) {
            $cart_items = get_user_cart_ctr(get_user_id());
        }

        $calculated_total = 0.00;
        if ($cart_items && count($cart_items) > 0) {
            foreach ($cart_items as $ci) {
                if (isset($ci['subtotal'])) {
                    $calculated_total += floatval($ci['subtotal']);
                } elseif (isset($ci['product_price']) && isset($ci['qty'])) {
                    $calculated_total += floatval($ci['product_price']) * intval($ci['qty']);
                }
            }
        }

        if ($total_amount <= 0) {
            $total_amount = round($calculated_total, 2);
        }
        error_log("New regular order from cart - Calculated: $calculated_total GHS");
    }

    error_log("Expected payment total (server): $total_amount GHS");

    // Verify amount matches (with 1 pesewa tolerance) - skip for subscription (handled separately)
    if (!$is_subscription_payment && abs($amount_paid - $total_amount) > 0.01) {
        error_log("Amount mismatch - Expected: $total_amount GHS, Paid: $amount_paid GHS");

        echo json_encode([
            'status' => 'error',
            'message' => 'Payment amount does not match order total',
            'verified' => false,
            'expected' => number_format($total_amount, 2),
            'paid' => number_format($amount_paid, 2)
        ]);
        exit();
    }
    
    // Payment is verified! Now process the payment
    require_once '../controllers/cart_controller.php';
    require_once '../controllers/order_controller.php';
    require_once '../controllers/collaboration_controller.php';
    require_once '../controllers/subscription_controller.php';
    require_once '../settings/db_class.php';
    
    $customer_id = get_user_id();
    $customer_name = get_user_name();
    $order_date = date('Y-m-d');
    
    // Handle subscription payments first
    if ($is_subscription_payment && $subscription) {
        error_log("Processing subscription payment - Subscription ID: {$subscription['subscription_id']}");
        
        // Verify amount matches subscription amount
        $expected_amount = floatval($subscription['amount']);
        if (abs($amount_paid - $expected_amount) > 0.01) {
            error_log("Subscription amount mismatch - Expected: $expected_amount GHS, Paid: $amount_paid GHS");
            echo json_encode([
                'status' => 'error',
                'message' => 'Payment amount does not match subscription amount',
                'verified' => false,
                'expected' => number_format($expected_amount, 2),
                'paid' => number_format($amount_paid, 2)
            ]);
            exit();
        }
        
        // Update subscription payment status
        $result = update_subscription_payment_status_ctr($subscription['subscription_id'], 'paid', $reference);
        
        if (!$result) {
            throw new Exception("Failed to update subscription payment status");
        }
        
        // Record payment
        record_subscription_payment_ctr(
            $subscription['subscription_id'],
            $customer_id,
            $expected_amount,
            $reference,
            'paid'
        );
        
        error_log("Subscription payment recorded - Subscription ID: {$subscription['subscription_id']}, Amount: $expected_amount GHS");
        
        // Clear session payment data
        unset($_SESSION['paystack_ref']);
        unset($_SESSION['paystack_amount']);
        unset($_SESSION['paystack_timestamp']);
        unset($_SESSION['paystack_subscription_id']);
        unset($_SESSION['paystack_plan_type']);
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'message' => 'Subscription payment successful! Your subscription is now active.',
            'subscription_id' => $subscription['subscription_id'],
            'plan_type' => $subscription['plan_type'],
            'amount' => number_format($expected_amount, 2),
            'currency' => 'GHS',
            'payment_reference' => $reference,
            'payment_method' => ucfirst($payment_method),
            'customer_email' => $customer_email,
            'is_subscription' => true
        ]);
        
    } elseif ($is_collaboration_order && $order) {
        // For collaboration orders, record member payment
        error_log("Processing collaboration order payment - Order ID: {$order['order_id']}");
        
        $members = get_collaboration_members_ctr($order['collaboration_id']);
        $member_contribution_percent = 0;
        foreach ($members as $member) {
            if ($member['user_id'] == $customer_id) {
                $member_contribution_percent = floatval($member['contribution_percent']);
                break;
            }
        }
        
        if ($member_contribution_percent <= 0) {
            throw new Exception("Could not find member contribution percentage");
        }
        
        // Record collaboration payment
        $result = record_collaboration_payment_ctr(
            $order['order_id'],
            $order['collaboration_id'],
            $customer_id,
            $member_contribution_percent,
            $total_amount
        );
        
        if (!$result) {
            throw new Exception("Failed to record collaboration payment");
        }
        
        error_log("Collaboration payment recorded - Order ID: {$order['order_id']}, Amount: $total_amount GHS");
        
        // Clear session payment data
        unset($_SESSION['paystack_ref']);
        unset($_SESSION['paystack_amount']);
        unset($_SESSION['paystack_timestamp']);
        unset($_SESSION['paystack_order_id']);
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'message' => 'Payment successful! Your contribution has been recorded. The order will be marked as paid once all members complete their payments.',
            'order_id' => $order['order_id'],
            'total_amount' => number_format($total_amount, 2),
            'currency' => 'GHS',
            'order_date' => date('F j, Y', strtotime($order_date)),
            'customer_name' => $customer_name,
            'payment_reference' => $reference,
            'payment_method' => ucfirst($payment_method),
            'customer_email' => $customer_email,
            'is_collaboration' => true
        ]);
        
    } elseif ($is_existing_regular_order && $order) {
        // Existing regular order - update payment status
        error_log("Processing existing regular order payment - Order ID: {$order['order_id']}");
        
        // Update payment status to paid
        $result = update_payment_status_ctr($order['order_id'], 'paid');
        
        if (!$result) {
            throw new Exception("Failed to update payment status");
        }
        
        // Update order status to completed when payment is made
        update_order_status_ctr($order['order_id'], 'completed');
        
        // Remove only the products that were in this order from cart
        // Don't clear entire cart as user may have other products
        require_once '../controllers/cart_controller.php';
        $order_details = get_order_details_ctr($order['order_id']);
        if (!empty($order_details)) {
            foreach ($order_details as $detail) {
                remove_from_cart_ctr($detail['product_id']);
                error_log("Removed product {$detail['product_id']} from cart after payment");
            }
        }
        
        error_log("Regular order payment recorded - Order ID: {$order['order_id']}, Amount: $total_amount GHS");
        
        // Clear session payment data
        unset($_SESSION['paystack_ref']);
        unset($_SESSION['paystack_amount']);
        unset($_SESSION['paystack_timestamp']);
        unset($_SESSION['paystack_order_id']);
        
        // Generate invoice number for display
        $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Return success response
        echo json_encode([
            'status' => 'success',
            'verified' => true,
            'message' => 'Payment successful! Order confirmed.',
            'order_id' => $order['order_id'],
            'invoice_no' => $invoice_no,
            'total_amount' => number_format($total_amount, 2),
            'currency' => 'GHS',
            'order_date' => date('F j, Y', strtotime($order['created_at'])),
            'customer_name' => $customer_name,
            'payment_reference' => $reference,
            'payment_method' => ucfirst($payment_method),
            'customer_email' => $customer_email,
            'is_collaboration' => false
        ]);
        
    } else {
        // New regular order - create new order from cart
        // Get fresh cart items if not provided
        if (!$cart_items || count($cart_items) == 0) {
            $cart_items = get_user_cart_ctr($customer_id);
        }
        
        if (!$cart_items || count($cart_items) == 0) {
            throw new Exception("Cart is empty");
        }
        
        // Create database connection for transaction
        $db = new db_connection();
        $conn = $db->db_conn();
        
        // Begin database transaction
        mysqli_begin_transaction($conn);
        error_log("Database transaction started");
        
        try {
            // Calculate totals
            $calculated_total = 0.00;
            foreach ($cart_items as $item) {
                if (isset($item['subtotal'])) {
                    $calculated_total += floatval($item['subtotal']);
                } elseif (isset($item['product_price']) && isset($item['qty'])) {
                    $calculated_total += floatval($item['product_price']) * intval($item['qty']);
                }
            }
            
            // No transaction fee - removed
            $transaction_fee = 0.00;
            $final_amount = $calculated_total;
            
            // Create order in database
            $order_id = create_order_ctr($customer_id, $calculated_total, $transaction_fee, $final_amount, null);
            
            if (!$order_id) {
                throw new Exception("Failed to create order in database");
            }
            
            error_log("Order created - ID: $order_id");
            
            // Add order details for each cart item
            require_once '../controllers/product_controller.php';
            foreach ($cart_items as $item) {
                $product = get_one_product_ctr($item['product_id']);
                if ($product) {
                    $wholesaler_id = $product['user_id'];
                    $quantity = isset($item['actual_units']) ? $item['actual_units'] : (isset($item['qty']) ? $item['qty'] : 1);
                    $unit_price = isset($item['wholesale_price']) ? $item['wholesale_price'] : $product['wholesale_price'];
                    $subtotal = isset($item['subtotal']) ? $item['subtotal'] : ($unit_price * $quantity);
                    
                    $detail_result = add_order_details_ctr($order_id, $item['product_id'], $wholesaler_id, $quantity, $unit_price, $subtotal);
                    
                    if (!$detail_result) {
                        throw new Exception("Failed to add order details for product: {$item['product_id']}");
                    }
                    
                    error_log("Order detail added - Product: {$item['product_id']}, Qty: $quantity");
                }
            }
            
            // Update order payment status to paid
            update_payment_status_ctr($order_id, 'paid');
            // Update order status to completed when payment is made
            update_order_status_ctr($order_id, 'completed');
            
            // Generate invoice number for display
            $invoice_no = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            error_log("Payment processed - Order ID: $order_id, Reference: $reference, Amount: $total_amount GHS");
            
            // Empty the customer's cart
            $empty_result = empty_cart_ctr($customer_id);
            
            if (!$empty_result) {
                throw new Exception("Failed to empty cart");
            }
            
            error_log("Cart emptied for customer: $customer_id");
            
            // Commit database transaction
            mysqli_commit($conn);
            error_log("Database transaction committed successfully");
            
            // Clear session payment data
            unset($_SESSION['paystack_ref']);
            unset($_SESSION['paystack_amount']);
            unset($_SESSION['paystack_timestamp']);
            unset($_SESSION['paystack_order_id']);
            
            // Log user activity
            if (function_exists('log_user_activity')) {
                log_user_activity("Completed payment via Paystack - Order ID: $order_id, Amount: GHS $total_amount, Reference: $reference");
            }
            
            // Return success response
            echo json_encode([
                'status' => 'success',
                'verified' => true,
                'message' => 'Payment successful! Order confirmed.',
                'order_id' => $order_id,
                'invoice_no' => $invoice_no,
                'total_amount' => number_format($total_amount, 2),
                'currency' => 'GHS',
                'order_date' => date('F j, Y', strtotime($order_date)),
                'customer_name' => $customer_name,
                'item_count' => count($cart_items),
                'payment_reference' => $reference,
                'payment_method' => ucfirst($payment_method),
                'customer_email' => $customer_email,
                'is_collaboration' => false
            ]);
        
        } catch (Exception $e) {
            // Rollback database transaction on error
            mysqli_rollback($conn);
            error_log("Database transaction rolled back: " . $e->getMessage());
            
            throw $e;
        }
    }
    
} catch (Exception $e) {
    error_log("Error in Paystack callback/verification: " . $e->getMessage());
    
    echo json_encode([
        'status' => 'error',
        'verified' => false,
        'message' => 'Payment processing error: ' . $e->getMessage()
    ]);
}
?>
