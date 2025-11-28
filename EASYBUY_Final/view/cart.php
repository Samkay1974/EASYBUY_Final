<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/cart_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';

if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$cartItems = get_cart_items_ctr();
$cartTotal = get_cart_total_ctr();
$cartCount = get_cart_count_ctr();
$cartItemCount = get_cart_item_count_ctr();

// Check if user has pending unpaid orders
$customer_id = get_user_id();
$pending_orders = get_pending_orders_for_customer_ctr($customer_id);
$has_pending_order = !empty($pending_orders);
$latest_pending_order = $has_pending_order ? $pending_orders[0] : null;

// Get collaboration information for each product in cart
require_once __DIR__ . '/../controllers/collaboration_controller.php';
$product_collaborations = [];
$product_pending_orders = []; // Track pending orders per product

foreach ($cartItems as $item) {
    $product_id = $item['product_id'];
    
    // Check if this product has a pending unpaid order (for regular orders only)
    $pending_order = get_pending_order_for_product_ctr($customer_id, $product_id);
    if ($pending_order) {
        $product_pending_orders[$product_id] = $pending_order;
    }
    
    // Check if this product has a completed collaboration with an order
    $collabs = get_collaborations_by_product_ctr($product_id);
    foreach ($collabs as $collab) {
        if ($collab['status'] == 'completed' && is_member_ctr($collab['collaboration_id'], $customer_id)) {
            $collab_order = get_collaboration_order_ctr($collab['collaboration_id']);
            if ($collab_order) {
                $member_payment = get_member_payment_status_ctr($collab_order['order_id'], $customer_id);
                $product_collaborations[$product_id] = [
                    'collaboration_id' => $collab['collaboration_id'],
                    'order_id' => $collab_order['order_id'],
                    'order' => $collab_order,
                    'member_paid' => $member_payment && $member_payment['payment_status'] == 'paid',
                    'all_paid' => $collab_order['payment_status'] == 'paid'
                ];
                break; // Found the collaboration for this product
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - EasyBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../css/styles.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .main-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            padding: 30px;
            margin-top: 30px;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .cart-item {
            border-bottom: 1px solid #e8e8f0;
            padding: 20px 0;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .product-image-cart {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        .summary-card {
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            border: 2px solid #e8e8f0;
            border-radius: 15px;
            padding: 25px;
            position: sticky;
            top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h2>
                        <p class="mb-0 opacity-75"><?php echo $cartCount; ?> total units | <?php echo count($cartItems); ?> product(s)</p>
                    </div>
                    <a href="all_product.php" class="btn btn-light">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>

            <?php if (empty($cartItems)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-4x mb-3" style="color: #667eea; opacity: 0.5;"></i>
                    <h4>Your cart is empty</h4>
                    <p class="text-muted">Start adding products to your cart!</p>
                    <a href="all_product.php" class="btn btn-primary mt-3">
                        <i class="fas fa-shopping-bag me-2"></i>Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item">
                                <div class="row align-items-center">
                                    <div class="col-md-2 col-4">
                                        <img src="../uploads/products/<?php echo htmlspecialchars($item['product_image']); ?>" 
                                             class="product-image-cart"
                                             onerror="this.src='https://via.placeholder.com/100?text=No+Image'">
                                    </div>
                                    <div class="col-md-3 col-8">
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($item['product_name']); ?>
                                            <?php if (isset($product_collaborations[$item['product_id']])): ?>
                                                <span class="badge bg-warning text-dark ms-1" title="Collaboration Purchase">
                                                    <i class="fas fa-users"></i> Collaboration
                                                </span>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="text-muted small mb-0">
                                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($item['brand_name']); ?></span>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($item['cat_name']); ?></span>
                                        </p>
                                        <p class="text-muted small mb-0">MOQ: <?php echo $item['moq']; ?> units</p>
                                        <?php if (isset($product_collaborations[$item['product_id']]) && $product_collaborations[$item['product_id']]['order_id']): ?>
                                            <p class="text-muted small mb-0 mt-1">
                                                <strong>Order #<?php echo $product_collaborations[$item['product_id']]['order_id']; ?></strong>
                                            </p>
                                        <?php elseif (isset($product_pending_orders[$item['product_id']])): ?>
                                            <p class="text-muted small mb-0 mt-1">
                                                <strong>Pending Order #<?php echo $product_pending_orders[$item['product_id']]['order_id']; ?></strong>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-2 col-6">
                                        <label class="form-label small">MOQ Units:</label>
                                        <input type="number" class="form-control form-control-sm qty-input" 
                                               data-product-id="<?php echo $item['product_id']; ?>"
                                               data-moq="<?php echo $item['moq']; ?>"
                                               value="<?php echo $item['moq_quantity']; ?>" 
                                               min="1">
                                        <small class="text-muted">= <?php echo $item['actual_units']; ?> items</small>
                                    </div>
                                    <div class="col-md-2 col-6 text-center">
                                        <strong class="text-primary">GH₵ <?php echo number_format($item['subtotal'], 2); ?></strong>
                                        <p class="text-muted small mb-0">
                                            GH₵ <?php echo number_format($item['wholesale_price'], 2); ?> per MOQ
                                        </p>
                                    </div>
                                    <div class="col-md-3 col-12 mt-2 mt-md-0">
                                        <?php 
                                        $is_collab_product = isset($product_collaborations[$item['product_id']]);
                                        $collab_info = $is_collab_product ? $product_collaborations[$item['product_id']] : null;
                                        ?>
                                        <?php if ($is_collab_product && $collab_info): ?>
                                            <!-- Collaboration Product Actions -->
                                            <div class="d-grid gap-2">
                                                <?php if ($collab_info['all_paid']): ?>
                                                    <div class="alert alert-success mb-0 p-2">
                                                        <small><i class="fas fa-check-circle me-1"></i>All members paid! Product will be removed.</small>
                                                    </div>
                                                <?php elseif ($collab_info['member_paid']): ?>
                                                    <div class="alert alert-info mb-0 p-2">
                                                        <small><i class="fas fa-check-circle me-1"></i>You have paid your contribution!</small>
                                                        <small class="d-block mt-1">Order #<?php echo $collab_info['order_id']; ?></small>
                                                    </div>
                                                <?php else: ?>
                                                    <a href="../actions/checkout.php?order_id=<?php echo $collab_info['order_id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-credit-card me-1"></i>Proceed to Checkout
                                                    </a>
                                                    <small class="text-muted text-center">Order #<?php echo $collab_info['order_id']; ?></small>
                                                <?php endif; ?>
                                                <?php if (!$collab_info['all_paid']): ?>
                                                    <button class="btn btn-sm btn-danger" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                                        <i class="fas fa-trash me-1"></i>Remove
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <!-- Regular Product Actions -->
                                            <?php 
                                            $has_pending_order = isset($product_pending_orders[$item['product_id']]);
                                            $pending_order_id = $has_pending_order ? $product_pending_orders[$item['product_id']]['order_id'] : null;
                                            ?>
                                            <div class="d-grid gap-2">
                                                <?php if ($has_pending_order): ?>
                                                    <!-- Product has pending order - disable Place Order button -->
                                                    <button class="btn btn-sm btn-success" disabled title="You already have a pending order for this product">
                                                        <i class="fas fa-shopping-bag me-1"></i>Order Placed
                                                    </button>
                                                    <a href="../actions/checkout.php?order_id=<?php echo $pending_order_id; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-credit-card me-1"></i>Pay Now
                                                    </a>
                                                    <small class="text-muted text-center">Order #<?php echo $pending_order_id; ?></small>
                                                <?php else: ?>
                                                    <!-- No pending order - allow placing new order -->
                                                    <button class="btn btn-sm btn-success" onclick="placeSingleProductOrder(<?php echo $item['product_id']; ?>)">
                                                        <i class="fas fa-shopping-bag me-1"></i>Place Order
                                                    </button>
                                                    <button class="btn btn-sm btn-primary" onclick="proceedToCheckoutSingle(<?php echo $item['product_id']; ?>)">
                                                        <i class="fas fa-credit-card me-1"></i>Checkout
                                                    </button>
                                                <?php endif; ?>
                                                <button class="btn btn-sm btn-danger" onclick="removeFromCart(<?php echo $item['product_id']; ?>)">
                                                    <i class="fas fa-trash me-1"></i>Remove
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="col-lg-4">
                        <div class="summary-card">
                            <h5 class="mb-4">Order Summary</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Subtotal:</span>
                                <strong>GH₵ <?php echo number_format($cartTotal, 2); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Total Items:</span>
                                <strong><?php echo $cartCount; ?> units</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Products:</span>
                                <strong><?php echo count($cartItems); ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <h5>Total:</h5>
                                <h5 class="text-primary">GH₵ <?php echo number_format($cartTotal, 2); ?></h5>
                            </div>
                            
                            <?php if ($has_pending_order): ?>
                                <div class="alert alert-info mb-3">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong><br>
                                    <small>You have pending order(s). Each product can be ordered independently. Use the buttons next to each product to place orders.</small>
                                </div>
                            <?php endif; ?>
                            
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <small>Each product has its own order buttons. You can place orders for different products independently.</small>
                            </div>
                            
                            <button class="btn btn-outline-danger w-100" onclick="clearCart()">
                                <i class="fas fa-trash me-2"></i>Clear Cart
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/order.js"></script>
    <script>
        // Update quantity
        document.querySelectorAll('.qty-input').forEach(input => {
            input.addEventListener('change', function() {
                const productId = this.dataset.productId;
                const moq = parseInt(this.dataset.moq) || 1;
                const moqQuantity = parseInt(this.value) || 1; // MOQ units
                
                if (moqQuantity < 1) {
                    Swal.fire('Error!', 'Quantity must be at least 1 MOQ unit', 'error');
                    this.value = 1;
                    return;
                }
                
                // Update the small text showing actual units
                const actualUnits = moqQuantity * moq;
                const smallText = this.nextElementSibling;
                if (smallText && smallText.tagName === 'SMALL') {
                    smallText.textContent = '= ' + actualUnits + ' items';
                }
                
                updateCart(productId, moqQuantity);
            });
        });

        async function updateCart(productId, moqQuantity) {
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', moqQuantity); // MOQ quantity, not actual units
            
            try {
                const res = await fetch('../actions/update_cart.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();
                
                if (json.status === 'success') {
                    Swal.fire('Updated!', json.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error!', json.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error!', 'An error occurred.', 'error');
            }
        }

        async function removeFromCart(productId) {
            Swal.fire({
                title: 'Remove item?',
                text: 'Are you sure you want to remove this item from cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('product_id', productId);
                    
                    try {
                        const res = await fetch('../actions/remove_from_cart.php', {
                            method: 'POST',
                            body: formData
                        });
                        const json = await res.json();
                        
                        if (json.status === 'success') {
                            Swal.fire('Removed!', json.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error!', json.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error!', 'An error occurred.', 'error');
                    }
                }
            });
        }


        async function clearCart() {
            Swal.fire({
                title: 'Clear cart?',
                text: 'Are you sure you want to remove all items from cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, clear it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const res = await fetch('../actions/clear_cart.php', {
                            method: 'POST'
                        });
                        const json = await res.json();
                        
                        if (json.status === 'success') {
                            Swal.fire('Cleared!', json.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Error!', json.message, 'error');
                        }
                    } catch (error) {
                        Swal.fire('Error!', 'An error occurred.', 'error');
                    }
                }
            });
        }

        function placeSingleProductOrder(productId) {
            Swal.fire({
                title: 'Place Order?',
                html: 'Are you sure you want to place an order for this product?<br><br><strong>Note:</strong> After placing your order, the wholesaler will contact you via phone call for more information about your order before you make payment.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, place order!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../actions/place_single_product_order.php?product_id=' + productId;
                }
            });
        }

        function proceedToCheckoutSingle(productId) {
            // First place the order, then redirect to checkout
            Swal.fire({
                title: 'Place Order & Checkout?',
                html: 'This will place an order for this product and take you to checkout.<br><br><strong>Note:</strong> After placing your order, the wholesaler will contact you via phone call for more information about your order before you make payment.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, proceed!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../actions/place_single_product_order.php?product_id=' + productId;
                }
            });
        }
    </script>
</body>
</html>

