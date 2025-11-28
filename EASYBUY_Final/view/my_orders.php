<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/user_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';

if(!isLoggedIn()){
    header("Location: ../login/login.php");
    exit;
}

$user_id = get_user_id();
$orders = get_orders_by_user_ctr($user_id);
?>

<!DOCTYPE html>
<html>
<head>
<title>My Orders</title>
<style>
.container {
    width: 70%;
    margin: 40px auto;
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 0 10px #ccc;
}

.order-card {
    border:1px solid #ddd;
    padding:15px;
    margin-bottom:10px;
    border-radius:8px;
}
a.button {
    display: inline-block;
    padding: 10px 16px;
    background: black;
    color: blue;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}
</style>
</head>
<body>
<a class="button" style="position: absolute; top: 20px; right: 20px;" href="homepage.php">Back to Home</a>

<div class="container">
    <h2>My Orders</h2>

    <?php if(empty($orders)): ?>
        <p>No orders found.</p>
    <?php else: ?>

        <?php foreach($orders as $order): ?>
            <div class="order-card">
                <p><strong>Order ID:</strong> #<?= $order['order_id']; ?></p>
                <p><strong>Total Amount:</strong> GH₵ <?= number_format($order['final_amount'], 2); ?></p>
                <p><strong>Date:</strong> <?= date('F d, Y h:i A', strtotime($order['created_at'])); ?></p>
                <p><strong>Status:</strong> 
                    <span style="padding: 5px 10px; border-radius: 5px; background: <?php 
                        echo $order['status'] == 'completed' ? '#28a745' : 
                            ($order['status'] == 'cancelled' ? '#dc3545' : 
                            ($order['status'] == 'processing' ? '#17a2b8' : '#ffc107')); 
                    ?>; color: white;">
                        <?= ucfirst($order['status']); ?>
                    </span>
                </p>
                <p><strong>Payment Status:</strong> 
                    <span style="padding: 5px 10px; border-radius: 5px; background: <?php 
                        echo $order['payment_status'] == 'paid' ? '#28a745' : '#ffc107'; 
                    ?>; color: white;">
                        <?= ucfirst($order['payment_status']); ?>
                    </span>
                </p>
                <p><strong>Items:</strong> <?= $order['item_count'] ?? 0; ?> product(s)</p>
            </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

</body>
</html>
