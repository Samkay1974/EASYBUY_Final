<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/order_controller.php';

if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$wholesaler_id = get_user_id();
$orders = get_orders_for_wholesaler_ctr($wholesaler_id);
$unpaidOrdersCount = get_unpaid_orders_count_for_wholesaler_ctr($wholesaler_id);

// Group orders by order_id for better display
$grouped_orders = [];
foreach ($orders as $order) {
    $order_id = $order['order_id'];
    if (!isset($grouped_orders[$order_id])) {
        $grouped_orders[$order_id] = [
            'order_id' => $order_id,
            'collaboration_id' => $order['collaboration_id'] ?? null,
            'customer_name' => $order['customer_name'],
            'customer_email' => $order['customer_email'],
            'customer_phone' => $order['customer_phone'],
            'status' => $order['status'],
            'payment_status' => $order['payment_status'],
            'order_date' => $order['order_date'],
            'total_amount' => $order['total_amount'],
            'final_amount' => $order['final_amount'],
            'items' => []
        ];
    }
    $grouped_orders[$order_id]['items'][] = $order;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Orders - EasyBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }
        .orders-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
            margin: auto;
        }
        .orders-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .order-card {
            border: 2px solid #e8e8f0;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f8f9ff;
        }
        .order-card-header {
            border-bottom: 2px solid #e8e8f0;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .order-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .product-image-order {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-processing { background: #17a2b8; color: #fff; }
        .status-completed { background: #28a745; color: #fff; }
        .status-cancelled { background: #dc3545; color: #fff; }
        .payment-paid { background: #28a745; color: #fff; }
        .payment-pending { background: #ffc107; color: #000; }
    </style>
</head>
<body>
    <div class="container">
        <div class="orders-container">
            <div class="orders-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-shopping-bag me-2"></i>Orders for Your Products</h2>
                        <p class="mb-0 opacity-75">View all orders placed for products you created</p>
                    </div>
                    <?php if ($unpaidOrdersCount > 0): ?>
                        <div>
                            <span class="badge bg-danger rounded-pill" style="font-size: 1rem; padding: 10px 20px;">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <?php echo $unpaidOrdersCount; ?> Unpaid Order<?php echo $unpaidOrdersCount > 1 ? 's' : ''; ?>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($grouped_orders)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x mb-3" style="color: #667eea; opacity: 0.5;"></i>
                    <h4>No Orders Yet</h4>
                    <p class="text-muted">You haven't received any orders for your products yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($grouped_orders as $order): ?>
                    <div class="order-card <?= $order['status'] == 'cancelled' ? 'border-danger' : '' ?>" 
                         style="<?= $order['status'] == 'cancelled' ? 'opacity: 0.8; background: #fff5f5;' : '' ?>">
                        <?php if ($order['status'] == 'cancelled'): ?>
                            <div class="alert alert-danger mb-3">
                                <i class="fas fa-times-circle me-2"></i>
                                <strong>This order has been cancelled</strong>
                            </div>
                        <?php endif; ?>
                        <div class="order-card-header">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-1">Order #<?= $order['order_id'] ?></h5>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('F d, Y h:i A', strtotime($order['order_date'])) ?>
                                    </p>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            Status: <?= $order['status'] == 'completed' ? 'Success' : ucfirst($order['status']) ?>
                                        </span>
                                    <span class="status-badge payment-<?= $order['payment_status'] ?>">
                                        Payment: <?= ucfirst($order['payment_status']) ?>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php 
                        // Check if this is a collaboration order
                        $is_collaboration = !empty($order['collaboration_id']);
                        $collaboration_members = [];
                        
                        if ($is_collaboration) {
                            require_once __DIR__ . '/../controllers/collaboration_controller.php';
                            $collaboration_members = get_collaboration_members_ctr($order['collaboration_id']);
                        }
                        ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <?php if ($is_collaboration && !empty($collaboration_members)): ?>
                                    <h6><i class="fas fa-users me-2"></i>Collaboration Members (<?= count($collaboration_members) ?>)</h6>
                                    <div class="mt-2">
                                        <?php foreach ($collaboration_members as $member): ?>
                                            <div class="mb-2 p-2 bg-light rounded">
                                                <p class="mb-1"><strong><?= htmlspecialchars($member['full_name']) ?></strong></p>
                                                <p class="mb-1 small"><strong>Email:</strong> <?= htmlspecialchars($member['email'] ?? 'N/A') ?></p>
                                                <p class="mb-0 small"><strong>Phone:</strong> <?= htmlspecialchars($member['phone'] ?? 'N/A') ?></p>
                                                <p class="mb-0 small"><strong>Contribution:</strong> <?= $member['contribution_percent'] ?>%</p>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <h6><i class="fas fa-user me-2"></i>Customer Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'N/A') ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?= htmlspecialchars($order['customer_email'] ?? 'N/A') ?></p>
                                    <p class="mb-0"><strong>Phone:</strong> <?= htmlspecialchars($order['customer_phone'] ?? 'N/A') ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 text-end">
                                <h6>Order Summary</h6>
                                <p class="mb-0">Total: <strong class="text-primary">GH₵ <?= number_format($order['total_amount'], 2) ?></strong></p>
                            </div>
                        </div>

                        <h6 class="mb-3"><i class="fas fa-box me-2"></i>Order Items</h6>
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        <img src="../uploads/products/<?= htmlspecialchars($item['product_image']) ?>" 
                                             class="product-image-order"
                                             onerror="this.src='https://via.placeholder.com/60?text=No+Image'">
                                    </div>
                                    <div class="col-md-5">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <p class="text-muted small mb-0">Unit Price: GH₵ <?= number_format($item['unit_price'], 2) ?></p>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <small class="text-muted">Quantity</small>
                                        <p class="mb-0"><strong><?= $item['quantity'] ?></strong></p>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <small class="text-muted">Subtotal</small>
                                        <p class="mb-0"><strong class="text-primary">GH₵ <?= number_format($item['subtotal'], 2) ?></strong></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

