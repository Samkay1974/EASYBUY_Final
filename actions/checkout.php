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

// For collaboration orders, check if user is a member
$is_collaboration_order = !empty($order['collaboration_id']);
$is_authorized = false;

if ($is_collaboration_order) {
    require_once __DIR__ . '/../controllers/collaboration_controller.php';
    $is_authorized = is_member_ctr($order['collaboration_id'], $customer_id);
} else {
    $is_authorized = ($order && $order['customer_id'] == $customer_id);
}

if (!$order || !$is_authorized) {
    $_SESSION['error'] = "Order not found or you don't have permission to view this order.";
    header("Location: ../view/cart.php");
    exit;
}

// Check if order is already cancelled or completed
if ($order['status'] == 'cancelled') {
    $_SESSION['error'] = "This order has been cancelled.";
    header("Location: ../view/cart.php");
    exit;
}

// For collaboration orders, get member's payment info first
$member_payment = null;
$member_contribution_amount = 0;
if ($is_collaboration_order) {
    $member_payment = get_member_payment_status_ctr($order_id, $customer_id);
    // Always recalculate from order total_amount to ensure no transaction fees
    // This ensures consistency even if old records had fees
    require_once __DIR__ . '/../controllers/collaboration_controller.php';
    $members = get_collaboration_members_ctr($order['collaboration_id']);
    foreach ($members as $member) {
        if ($member['user_id'] == $customer_id) {
            $contribution_percent = floatval($member['contribution_percent']);
            // No transaction fee - always recalculate from order total_amount
            $member_contribution_amount = $order['total_amount'] * ($contribution_percent / 100);
            break;
        }
    }
    
    // Check if member has already paid
    if ($member_payment && $member_payment['payment_status'] == 'paid') {
        $_SESSION['success'] = "You have already paid your contribution for this order.";
        // Don't redirect, just show the message
    }
} else {
    if ($order['payment_status'] == 'paid') {
        $_SESSION['success'] = "This order has already been paid.";
        header("Location: order_success.php?order_id=" . $order_id);
        exit;
    }
}

$order_details = get_order_details_ctr($order_id);

// Get user email for Paystack payment
require_once __DIR__ . '/../controllers/user_controller.php';
$user = get_user_by_id_ctr($customer_id);
$user_email = $user ? $user['email'] : '';

// Check if we should initiate Paystack payment
$init_payment = isset($_GET['init_payment']) && $_GET['init_payment'] == 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - EasyBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .checkout-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
            max-width: 800px;
            margin: auto;
        }
        .checkout-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .order-item {
            border-bottom: 1px solid #e8e8f0;
            padding: 15px 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .product-image-checkout {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        .summary-box {
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            border: 2px solid #e8e8f0;
            border-radius: 15px;
            padding: 25px;
        }
        .btn-pay {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            font-size: 18px;
            font-weight: bold;
        }
        .btn-cancel {
            background: #dc3545;
            border: none;
            padding: 12px 30px;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="checkout-container">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['payment']) && $_GET['payment'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    Payment successful! Your contribution has been recorded. The order will be marked as paid once all members complete their payments.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['info'])): ?>
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>
                    <?php echo $_SESSION['info']; unset($_SESSION['info']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <div class="checkout-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-credit-card me-2"></i>Checkout</h2>
                        <p class="mb-0 opacity-75">Order #<?= $order_id ?></p>
                    </div>
                    <a href="../view/cart.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <h5 class="mb-3">Order Items</h5>
                    <?php if (!empty($order_details)): ?>
                        <?php foreach ($order_details as $detail): ?>
                            <div class="order-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2">
                                        <img src="../uploads/products/<?= htmlspecialchars($detail['product_image']) ?>" 
                                             class="product-image-checkout"
                                             onerror="this.src='https://via.placeholder.com/80?text=No+Image'">
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-1"><?= htmlspecialchars($detail['product_name']) ?></h6>
                                        <p class="text-muted small mb-0">
                                            Wholesaler: <?= htmlspecialchars($detail['wholesaler_name']) ?>
                                        </p>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <small class="text-muted">Quantity</small>
                                        <p class="mb-0"><strong><?= $detail['quantity'] ?></strong></p>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <small class="text-muted">Subtotal</small>
                                        <p class="mb-0"><strong>GH₵ <?= number_format($detail['subtotal'], 2) ?></strong></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No items found in this order.</p>
                    <?php endif; ?>
                </div>

                <div class="col-lg-4">
                    <div class="summary-box">
                        <h5 class="mb-4"><?= $is_collaboration_order ? 'Your Contribution' : 'Order Summary' ?></h5>
                        
                        <?php if ($is_collaboration_order): ?>
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-users me-2"></i>
                                <strong>Collaboration Order</strong><br>
                                <small>This is a collaborative purchase. You are paying your contribution percentage.</small>
                            </div>
                            
                            <?php if ($member_payment && $member_payment['payment_status'] == 'paid'): ?>
                                <div class="alert alert-success mb-3">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <strong>Payment Completed</strong><br>
                                    <small>You have already paid your contribution for this order.</small>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Your Contribution:</span>
                                    <strong>GH₵ <?= number_format($member_contribution_amount, 2) ?></strong>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Total Order Amount:</span>
                                    <small class="text-muted">GH₵ <?= number_format($order['total_amount'], 2) ?></small>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-4">
                                    <h5>Amount to Pay:</h5>
                                    <h5 class="text-primary">GH₵ <?= number_format($member_contribution_amount, 2) ?></h5>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <h5>Total Amount:</h5>
                                <h5 class="text-primary">GH₵ <?= number_format($order['total_amount'], 2) ?></h5>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2">
                            <?php if ($is_collaboration_order && $member_payment && $member_payment['payment_status'] == 'paid'): ?>
                                <button class="btn btn-success btn-pay" disabled>
                                    <i class="fas fa-check-circle me-2"></i>Payment Completed
                                </button>
                            <?php else: ?>
                                <?php if ($is_collaboration_order): ?>
                                    <button onclick="initiatePaystackPayment(<?= $order_id ?>, <?= $member_contribution_amount ?>, '<?= htmlspecialchars($user_email, ENT_QUOTES) ?>', true)" class="btn btn-primary btn-pay">
                                        <i class="fas fa-credit-card me-2"></i>Pay Your Contribution
                                    </button>
                                <?php else: ?>
                                    <button onclick="initiatePaystackPayment(<?= $order_id ?>, <?= $order['total_amount'] ?>, '<?= htmlspecialchars($user_email, ENT_QUOTES) ?>', false)" class="btn btn-primary btn-pay">
                                        <i class="fas fa-credit-card me-2"></i>Pay Now
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if (!$is_collaboration_order || ($is_collaboration_order)): ?>
                                <button onclick="cancelOrder(<?= $order_id ?>)" class="btn btn-danger btn-cancel">
                                    <i class="fas fa-times me-2"></i>Cancel Order
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="mt-3">
                            <div class="alert alert-info">
                                <i class="fas fa-phone me-2"></i>
                                <strong>Important:</strong> The wholesaler will contact you via phone call for more information about your order before you make payment.
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                You can cancel this order before payment.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/order.js"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        // Auto-initiate Paystack payment if requested
        <?php if ($init_payment): ?>
            <?php if ($is_collaboration_order && !($member_payment && $member_payment['payment_status'] == 'paid')): ?>
            document.addEventListener('DOMContentLoaded', function() {
                initiatePaystackPayment(<?= $order_id ?>, <?= $member_contribution_amount ?>, '<?= htmlspecialchars($user_email, ENT_QUOTES) ?>', true);
            });
            <?php elseif (!$is_collaboration_order && $order['payment_status'] != 'paid'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                initiatePaystackPayment(<?= $order_id ?>, <?= $order['total_amount'] ?>, '<?= htmlspecialchars($user_email, ENT_QUOTES) ?>', false);
            });
            <?php endif; ?>
        <?php endif; ?>

        // Function to initiate Paystack payment
        async function initiatePaystackPayment(orderId, amount, customerEmail, isCollaboration) {
            try {
                // Show loading
                Swal.fire({
                    title: 'Initializing Payment',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Validate email
                if (!customerEmail || customerEmail === '') {
                    Swal.close();
                    const { value: email } = await Swal.fire({
                        title: 'Enter Your Email',
                        input: 'email',
                        inputLabel: 'Email is required for payment',
                        inputPlaceholder: 'Enter your email address',
                        showCancelButton: true,
                        confirmButtonText: 'Continue',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to enter your email!';
                            }
                            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                                return 'Please enter a valid email address!';
                            }
                        }
                    });
                    
                    if (!email) {
                        return;
                    }
                    customerEmail = email;
                }

                // Initialize Paystack transaction
                const response = await fetch('../actions/paystack_init_transaction.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount,
                        email: customerEmail,
                        order_id: orderId,
                        is_collaboration: isCollaboration ? 1 : 0
                    })
                });

                const data = await response.json();
                Swal.close();

                if (data.status === 'success' && data.authorization_url) {
                    // Redirect to Paystack
                    window.location.href = data.authorization_url;
                } else {
                    Swal.fire('Error', data.message || 'Failed to initialize payment', 'error');
                }
            } catch (error) {
                Swal.close();
                console.error('Payment error:', error);
                Swal.fire('Error', 'An error occurred while processing payment. Please try again.', 'error');
            }
        }

        // Make function available globally for button clicks
        window.initiatePaystackPayment = initiatePaystackPayment;
    </script>
</body>
</html>
