<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/subscription_controller.php';
require_once __DIR__ . '/../settings/paystack_config.php';

if (!isLoggedIn() || !check_user_role(1)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = get_user_id();
$plan_type = isset($_POST['plan_type']) ? trim($_POST['plan_type']) : 'basic';

// Validate plan type
if (!in_array($plan_type, ['basic', 'premium'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid plan type']);
    exit;
}

// Set amounts (per 6 months)
$amounts = [
    'basic' => 50.00,    // 50 cedis per 6 months
    'premium' => 150.00  // 150 cedis per 6 months
];
$amount = $amounts[$plan_type];

// Get user email
require_once __DIR__ . '/../controllers/user_controller.php';
$user = get_user_by_id_ctr($user_id);
if (!$user || empty($user['email'])) {
    echo json_encode(['status' => 'error', 'message' => 'User email not found']);
    exit;
}

try {
    // Generate unique reference
    $reference = 'SUB-' . $user_id . '-' . time() . '-' . strtoupper(substr(uniqid(), -6));
    
    // Create subscription record (pending payment)
    $subscription_id = create_subscription_ctr($user_id, $plan_type, $amount, $reference);
    
    if (!$subscription_id) {
        throw new Exception("Failed to create subscription record");
    }
    
    // Initialize Paystack transaction
    $paystack_response = paystack_initialize_transaction($amount, $user['email'], $reference);
    
    if (!$paystack_response || !isset($paystack_response['status']) || $paystack_response['status'] !== true) {
        $error_message = $paystack_response['message'] ?? 'Payment gateway error';
        throw new Exception($error_message);
    }
    
    // Store subscription info in session
    $_SESSION['paystack_ref'] = $reference;
    $_SESSION['paystack_amount'] = $amount;
    $_SESSION['paystack_timestamp'] = time();
    $_SESSION['paystack_subscription_id'] = $subscription_id;
    $_SESSION['paystack_plan_type'] = $plan_type;
    
    error_log("Subscription payment initialized - Subscription ID: $subscription_id, Plan: $plan_type, Amount: $amount GHS");
    
    echo json_encode([
        'status' => 'success',
        'authorization_url' => $paystack_response['data']['authorization_url'],
        'reference' => $reference,
        'subscription_id' => $subscription_id,
        'message' => 'Redirecting to payment gateway...'
    ]);
    
} catch (Exception $e) {
    error_log("Error initializing subscription payment: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

