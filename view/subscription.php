<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/subscription_controller.php';

if (!isLoggedIn() || !check_user_role(1)) {
    header('Location: ../login/login.php');
    exit;
}

$user_id = get_user_id();
$active_subscription = get_active_subscription_ctr($user_id);
$product_count = get_user_product_count_ctr($user_id);
$subscriptions = get_user_subscriptions_ctr($user_id);
$can_create = can_create_product_ctr($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription - EasyBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .subscription-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
            margin: auto;
            max-width: 1000px;
        }
        .subscription-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .plan-card {
            border: 2px solid #e8e8f0;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        .plan-card:hover {
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transform: translateY(-5px);
        }
        .plan-card.premium {
            border-color: #ffc107;
            background: linear-gradient(135deg, #fff9e6 0%, #ffffff 100%);
        }
        .plan-card.active {
            border-color: #28a745;
            background: linear-gradient(135deg, #e8f5e9 0%, #ffffff 100%);
        }
        .price-badge {
            font-size: 2.5rem;
            font-weight: bold;
            color: #667eea;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active { background: #28a745; color: #fff; }
        .status-pending { background: #ffc107; color: #000; }
        .status-expired { background: #dc3545; color: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <div class="subscription-container">
            <div class="subscription-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-crown me-2"></i>Subscription Plans</h2>
                        <p class="mb-0 opacity-75">Manage your subscription to create unlimited products</p>
                    </div>
                    <a href="all_product.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Back
                    </a>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
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

            <?php if (isset($_GET['payment']) && $_GET['payment'] == 'success'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Payment Successful!</strong> Your subscription is now active. You can now create unlimited products.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Current Status -->
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Current Status</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Products Created:</strong> <?php echo $product_count; ?> / 3 (Free)</p>
            <?php if ($active_subscription): ?>
                <p class="mb-2"><strong>Active Subscription:</strong> 
                    <span class="badge bg-success"><?php echo ucfirst($active_subscription['plan_type']); ?></span>
                </p>
                <p class="mb-2"><strong>Status:</strong> 
                    <span class="status-badge status-active">Active</span>
                </p>
                <?php if ($active_subscription['expires_at']): ?>
                    <p class="mb-0"><strong>Expires:</strong> 
                        <?php echo date('F d, Y', strtotime($active_subscription['expires_at'])); ?>
                    </p>
                <?php endif; ?>
            <?php else: ?>
                <p class="mb-0"><strong>Subscription:</strong> 
                    <span class="badge bg-secondary">None</span>
                </p>
            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if (!$can_create): ?>
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Limit Reached!</strong> You have reached the free limit of 3 products. Subscribe to create more products.
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-check-circle me-2"></i>
                                    You can still create <?php echo (3 - $product_count); ?> more product(s) for free.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subscription Plans -->
            <h4 class="mb-4">Choose Your Plan</h4>
            <div class="row">
                <!-- Basic Plan -->
                <div class="col-md-6">
                    <div class="plan-card <?php echo ($active_subscription && $active_subscription['plan_type'] == 'basic') ? 'active' : ''; ?>">
                        <div class="text-center mb-4">
                            <h3>Basic Plan</h3>
                            <div class="price-badge">GH₵ 50</div>
                            <p class="text-muted">Per 6 months</p>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited product creation</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Standard listing</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Full platform access</li>
                        </ul>
                        <?php if ($active_subscription && $active_subscription['plan_type'] == 'basic'): ?>
                            <button class="btn btn-success w-100" disabled>
                                <i class="fas fa-check-circle me-2"></i>Current Plan
                            </button>
                        <?php else: ?>
                            <button class="btn btn-primary w-100" onclick="subscribe('basic')">
                                <i class="fas fa-credit-card me-2"></i>Subscribe Now
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Premium Plan -->
                <div class="col-md-6">
                    <div class="plan-card premium <?php echo ($active_subscription && $active_subscription['plan_type'] == 'premium') ? 'active' : ''; ?>">
                        <div class="text-center mb-4">
                            <h3>Premium Plan</h3>
                            <div class="price-badge text-warning">GH₵ 150</div>
                            <p class="text-muted">Per 6 months</p>
                        </div>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Unlimited product creation</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Priority listing in search</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Premium badge</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Full platform access</li>
                        </ul>
                        <div class="alert alert-info mb-3">
                            <small><i class="fas fa-info-circle me-1"></i>Premium features coming soon!</small>
                        </div>
                        <?php if ($active_subscription && $active_subscription['plan_type'] == 'premium'): ?>
                            <button class="btn btn-success w-100" disabled>
                                <i class="fas fa-check-circle me-2"></i>Current Plan
                            </button>
                        <?php else: ?>
                            <button class="btn btn-warning w-100" onclick="subscribe('premium')">
                                <i class="fas fa-crown me-2"></i>Subscribe Now
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Subscription History -->
            <?php if (!empty($subscriptions)): ?>
                <div class="mt-5">
                    <h5 class="mb-3"><i class="fas fa-history me-2"></i>Subscription History</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscriptions as $sub): ?>
                                    <tr>
                                        <td><?php echo ucfirst($sub['plan_type']); ?></td>
                                        <td>GH₵ <?php echo number_format($sub['amount'], 2); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo $sub['status']; ?>">
                                                <?php echo ucfirst($sub['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo $sub['payment_status']; ?>">
                                                <?php echo ucfirst($sub['payment_status']); ?>
                                            </span>
                                        </td>
                                    <td>
                                        <?php echo date('M d, Y', strtotime($sub['created_at'])); ?>
                                        <?php if ($sub['expires_at']): ?>
                                            <br><small class="text-muted">Expires: <?php echo date('M d, Y', strtotime($sub['expires_at'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        async function subscribe(planType) {
            try {
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const formData = new FormData();
                formData.append('plan_type', planType);

                const response = await fetch('../actions/subscribe_action.php', {
                    method: 'POST',
                    body: formData
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
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred. Please try again.', 'error');
            }
        }
    </script>
</body>
</html>

