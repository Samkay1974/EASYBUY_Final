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

if (!$order || $order['customer_id'] != $customer_id) {
    $_SESSION['error'] = "Order not found.";
    header("Location: ../view/cart.php");
    exit;
}

$order_details = get_order_details_ctr($order_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - EasyBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .success-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 40px;
            max-width: 700px;
            margin: auto;
            text-align: center;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 50px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            <h2 class="text-success mb-3">Order Placed Successfully!</h2>
            <p class="text-muted mb-4">Thank you for your order. Your order has been received and is being processed.</p>
            
            <div class="card mb-4">
                <div class="card-body text-start">
                    <h5 class="card-title">Order Details</h5>
                    <p class="mb-1"><strong>Order ID:</strong> #<?= $order_id ?></p>
                    <p class="mb-1"><strong>Total Amount:</strong> GH₵ <?= number_format($order['final_amount'], 2) ?></p>
                    <p class="mb-1"><strong>Payment Status:</strong> 
                        <span class="badge bg-success"><?= ucfirst($order['payment_status']) ?></span>
                    </p>
                    <p class="mb-0"><strong>Order Status:</strong> 
                        <span class="badge bg-info"><?= ucfirst($order['status']) ?></span>
                    </p>
                </div>
            </div>

            <div class="d-grid gap-2">
                <a href="../view/all_product.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
                <a href="../view/cart.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Cart
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

