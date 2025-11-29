<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';
require_once __DIR__ . '/../controllers/cart_controller.php';

if (empty($_GET['id'])) {
    header('Location: all_product.php');
    exit;
}

$product_id = intval($_GET['id']);
$product = get_one_product_ctr($product_id);

if (!$product) {
    header('Location: all_product.php');
    exit;
}

// Get product with full details
$allProducts = get_all_products_ctr();
$product_details = null;
foreach ($allProducts as $p) {
    if ($p['product_id'] == $product_id) {
        $product_details = $p;
        break;
    }
}

if (!$product_details) {
    $product_details = $product;
}

// Get collaborations for this product
$collaborations = get_collaborations_by_product_ctr($product_id);

$user_id = isLoggedIn() ? get_user_id() : null;
$user_role = isLoggedIn() ? ($_SESSION['role'] ?? 0) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product_details['product_name']); ?> - EasyBuy</title>
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
        .product-header {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .product-image {
            flex: 0 0 400px;
            max-width: 100%;
        }
        .product-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .product-info {
            flex: 1;
            min-width: 300px;
        }
        .product-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }
        .product-price {
            font-size: 2rem;
            color: #667eea;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .collaboration-card {
            background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
            border: 2px solid #e8e8f0;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .collaboration-card:hover {
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.15);
            transform: translateY(-3px);
        }
        .progress-bar-custom {
            height: 30px;
            border-radius: 15px;
            background: #e8e8f0;
            overflow: hidden;
            margin: 15px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            transition: width 0.3s ease;
        }
        .btn-collaborate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: transform 0.2s ease;
        }
        .btn-collaborate:hover {
            transform: scale(1.05);
            color: white;
        }
        .member-list {
            list-style: none;
            padding: 0;
            margin: 15px 0 0 0;
        }
        .member-list li {
            padding: 10px;
            background: white;
            border-radius: 8px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="mb-4">
                <a href="all_product.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Products
                </a>
            </div>

            <!-- Product Details -->
            <div class="product-header">
                <div class="product-image">
                    <img src="../uploads/products/<?php echo htmlspecialchars($product_details['product_image']); ?>" 
                         alt="<?php echo htmlspecialchars($product_details['product_name']); ?>"
                         onerror="this.src='https://via.placeholder.com/400x400?text=No+Image'">
                </div>
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($product_details['product_name']); ?></h1>
                    <div class="mb-3">
                        <span class="badge bg-secondary me-2">
                            <i class="fas fa-tag me-1"></i><?php echo htmlspecialchars($product_details['brand_name'] ?? 'N/A'); ?>
                        </span>
                        <span class="badge bg-info">
                            <i class="fas fa-folder me-1"></i><?php echo htmlspecialchars($product_details['cat_name'] ?? 'N/A'); ?>
                        </span>
                    </div>
                    <p class="text-muted mb-3">
                        <i class="fas fa-shopping-cart me-2"></i>MOQ: <?php echo (int)$product_details['moq']; ?> units
                    </p>
                    <?php if (isset($product_details['wholesaler_name'])): ?>
                        <p class="text-muted mb-3">
                            <i class="fas fa-store me-2"></i>Wholesaler: <?php echo htmlspecialchars($product_details['wholesaler_name']); ?>
                        </p>
                    <?php endif; ?>
                    <p class="product-price">GH₵ <?php echo number_format($product_details['wholesale_price'], 2); ?></p>
                    
                    <?php if (isLoggedIn()): ?>
                        <div class="mt-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">MOQ Units:</label>
                                <input type="number" class="form-control" id="product_qty" 
                                       value="1" min="1" 
                                       style="max-width: 200px;">
                                <small class="text-muted">
                                    1 MOQ unit = <?php echo $product_details['moq']; ?> items
                                    <br>
                                    <span id="unitDisplay">Total: <?php echo $product_details['moq']; ?> items</span>
                                </small>
                            </div>
                            <button class="btn btn-success btn-lg" onclick="addToCartFromDetails(<?php echo $product_id; ?>, <?php echo $product_details['moq']; ?>)">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Collaborations Section -->
            <div class="mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users me-2"></i>Collaborative Purchases</h2>
                    <?php if (isLoggedIn() && $user_role == 0): ?>
                        <button class="btn btn-collaborate" data-bs-toggle="modal" data-bs-target="#createCollabModal">
                            <i class="fas fa-plus-circle me-2"></i>Create New Collaboration
                        </button>
                    <?php endif; ?>
                </div>

                <?php if (empty($collaborations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-users fa-3x mb-3" style="color: #667eea; opacity: 0.5;"></i>
                        <h4>No Active Collaborations</h4>
                        <p>Be the first to start a collaborative purchase for this product!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($collaborations as $collab): ?>
                        <?php
                        $members = get_collaboration_members_ctr($collab['collaboration_id']);
                        $total_contribution = floatval($collab['total_contribution'] ?? 0);
                        $remaining = 100 - $total_contribution;
                        $is_member = isLoggedIn() ? is_member_ctr($collab['collaboration_id'], $user_id) : false;
                        $is_creator = isLoggedIn() ? ($collab['creator_id'] == $user_id) : false;
                        
                        // Check if there are paid orders for this collaboration (once per collaboration)
                        $has_paid_orders = false;
                        if (isLoggedIn()) {
                            require_once __DIR__ . '/../settings/db_class.php';
                            $db = new db_connection();
                            $db->db_connect();
                            $orderCheck = $db->db->prepare("SELECT COUNT(*) as order_count FROM orders WHERE collaboration_id = :cid AND payment_status = 'paid'");
                            $orderCheck->execute([':cid' => $collab['collaboration_id']]);
                            $orderResult = $orderCheck->fetch(PDO::FETCH_ASSOC);
                            $has_paid_orders = $orderResult && $orderResult['order_count'] > 0;
                        }
                        ?>
                        <div class="collaboration-card">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5><i class="fas fa-users me-2"></i>Collaboration Group</h5>
                                    <p class="text-muted small mb-0">
                                        <?php if ($is_creator): ?>
                                            <i class="fas fa-crown me-1"></i>You created this
                                        <?php else: ?>
                                            Created by <strong><?php echo htmlspecialchars($collab['creator_name']); ?></strong>
                                        <?php endif; ?>
                                        on <?php echo date('M d, Y', strtotime($collab['created_at'])); ?>
                                    </p>
                                    <p class="text-muted small mb-0">
                                        Minimum contribution: <strong><?php echo $collab['min_contribution_percent']; ?>%</strong>
                                    </p>
                                </div>
                                <div class="d-flex flex-column align-items-end gap-2">
                                    <?php if ($is_creator && !$has_paid_orders): ?>
                                        <button onclick="deleteCollaboration(<?php echo $collab['collaboration_id']; ?>)" 
                                                class="btn btn-sm btn-danger" 
                                                title="Delete this collaboration group">
                                            <i class="fas fa-trash me-1"></i>Delete
                                        </button>
                                    <?php endif; ?>
                                    <span class="badge bg-<?php echo $total_contribution >= 100 ? 'success' : 'warning'; ?>">
                                        <?php echo $total_contribution >= 100 ? 'Complete' : 'Open'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="progress-bar-custom">
                                <div class="progress-fill" style="width: <?php echo min($total_contribution, 100); ?>%;">
                                    <?php echo number_format($total_contribution, 1); ?>%
                                </div>
                            </div>

                            <p class="text-center mb-3">
                                <strong><?php echo number_format($remaining, 1); ?>%</strong> remaining to complete
                            </p>

                            <?php if (!empty($members)): ?>
                                <div>
                                    <strong>Members (<?php echo count($members); ?>):</strong>
                                    <ul class="member-list">
                                        <?php foreach ($members as $member): ?>
                                            <li>
                                                <div>
                                                    <div>
                                                        <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($member['full_name']); ?>
                                                    </div>
                                                    <?php if (!empty($member['phone'])): ?>
                                                        <small class="text-muted ms-4">
                                                            <i class="fas fa-phone me-1"></i>
                                                            <?php echo htmlspecialchars($member['phone']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="badge bg-primary"><?php echo $member['contribution_percent']; ?>%</span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>

                            <?php if (isLoggedIn() && $user_role == 0 && !$is_member && $total_contribution < 100): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-collaborate w-100" 
                                            onclick="showJoinModal(<?php echo $collab['collaboration_id']; ?>, <?php echo $collab['min_contribution_percent']; ?>, <?php echo $remaining; ?>)">
                                        <i class="fas fa-hand-holding-usd me-2"></i>Join This Collaboration
                                    </button>
                                </div>
                            <?php elseif ($is_member): ?>
                                <?php 
                                // Get user's contribution
                                $user_contribution = null;
                                foreach ($members as $member) {
                                    if ($member['user_id'] == $user_id) {
                                        $user_contribution = $member['contribution_percent'];
                                        break;
                                    }
                                }
                                ?>
                                <div class="alert alert-info mt-3">
                                    <i class="fas fa-check-circle me-2"></i>You are a member of this collaboration
                                    <?php if ($user_contribution): ?>
                                        <br><strong>Your contribution: <?php echo $user_contribution; ?>%</strong>
                                    <?php endif; ?>
                                    <?php if (!$has_paid_orders): ?>
                                        <button onclick="leaveCollaboration(<?php echo $collab['collaboration_id']; ?>)" 
                                                class="btn btn-sm btn-danger float-end">
                                            <i class="fas fa-sign-out-alt me-1"></i>Leave Group
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <?php 
                                // Check if order exists for this collaboration
                                require_once __DIR__ . '/../controllers/order_controller.php';
                                $collab_order = get_collaboration_order_ctr($collab['collaboration_id']);
                                $member_paid = false;
                                if ($collab_order && $is_member) {
                                    $member_payment = get_member_payment_status_ctr($collab_order['order_id'], $user_id);
                                    $member_paid = $member_payment && $member_payment['payment_status'] == 'paid';
                                }
                                ?>
                                <?php if ($total_contribution >= 100 && $collab['status'] == 'completed'): ?>
                                    <?php if ($collab_order): ?>
                                        <!-- Order has been automatically placed, show checkout for all members -->
                                        <div class="mt-3">
                                            <?php if ($member_paid): ?>
                                                <div class="alert alert-success mb-0">
                                                    <i class="fas fa-check-circle me-2"></i>You have paid your contribution!
                                                </div>
                                            <?php elseif ($is_member): ?>
                                                <a href="../actions/checkout.php?order_id=<?php echo $collab_order['order_id']; ?>" 
                                                   class="btn btn-primary w-100">
                                                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <!-- Order should be created automatically, but if not, show message -->
                                        <?php if ($is_member): ?>
                                            <div class="alert alert-info mt-3 mb-0">
                                                <i class="fas fa-info-circle me-2"></i>Order is being processed...
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <!-- Create Collaboration Modal -->
    <?php if (isLoggedIn() && $user_role == 0): ?>
    <div class="modal fade" id="createCollabModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Create Collaboration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="createCollabForm">
                    <div class="modal-body">
                        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                        <div class="mb-3">
                            <label class="form-label">Minimum Contribution Percentage</label>
                            <input type="number" name="min_contribution_percent" class="form-control" 
                                   value="30" min="10" max="50" required>
                            <small class="text-muted">Each participant must contribute at least this percentage (10-50%)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-collaborate">Create Collaboration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Join Collaboration Modal -->
    <div class="modal fade" id="joinCollabModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-hand-holding-usd me-2"></i>Join Collaboration</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="joinCollabForm">
                    <div class="modal-body">
                        <input type="hidden" name="collaboration_id" id="join_collab_id">
                        <div class="mb-3">
                            <label class="form-label">Your Contribution Percentage</label>
                            <input type="number" name="contribution_percent" id="contribution_percent" 
                                   class="form-control" step="0.1" min="1" required>
                            <small class="text-muted">
                                Minimum: <span id="min_contribution">30</span>% | 
                                Maximum: <span id="max_contribution">100</span>%
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-collaborate">Join Collaboration</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        async function addToCartFromDetails(productId, moq) {
            const qtyInput = document.getElementById('product_qty');
            const moqQuantity = parseInt(qtyInput.value) || 1; // MOQ units
            
            if (moqQuantity < 1) {
                Swal.fire('Error!', 'Quantity must be at least 1 MOQ unit', 'error');
                return;
            }
            
            const actualUnits = moqQuantity * moq;
            
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', moqQuantity); // Store MOQ quantity
            
            try {
                const res = await fetch('../actions/add_to_cart.php', {
                    method: 'POST',
                    body: formData
                });
                const json = await res.json();
                
                if (json.status === 'success') {
                    Swal.fire('Success!', json.message + ' (' + actualUnits + ' items)', 'success');
                } else {
                    Swal.fire('Error!', json.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error!', 'An error occurred. Please try again.', 'error');
            }
        }
        
        // Update unit display when quantity changes
        document.getElementById('product_qty')?.addEventListener('input', function() {
            const moq = <?php echo $product_details['moq']; ?>;
            const moqQuantity = parseInt(this.value) || 1;
            const totalUnits = moqQuantity * moq;
            document.getElementById('unitDisplay').textContent = 'Total: ' + totalUnits + ' items';
        });
        
        // Create Collaboration
        document.getElementById('createCollabForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            const res = await fetch('../actions/add_collaboration.php', {
                method: 'POST',
                body: formData
            });
            const json = await res.json();
            
            if (json.status === 'success') {
                Swal.fire('Success!', json.message, 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Error!', json.message, 'error');
            }
        });

        // Join Collaboration
        function showJoinModal(collabId, minPercent, remaining) {
            document.getElementById('join_collab_id').value = collabId;
            document.getElementById('min_contribution').textContent = minPercent;
            document.getElementById('max_contribution').textContent = remaining.toFixed(1);
            document.getElementById('contribution_percent').value = minPercent;
            document.getElementById('contribution_percent').max = remaining;
            document.getElementById('contribution_percent').min = minPercent;
            
            const modal = new bootstrap.Modal(document.getElementById('joinCollabModal'));
            modal.show();
        }

        document.getElementById('joinCollabForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            const res = await fetch('../actions/join_collaboration.php', {
                method: 'POST',
                body: formData
            });
            const json = await res.json();
            
            if (json.status === 'success') {
                Swal.fire('Success!', json.message, 'success').then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire('Error!', json.message, 'error');
            }
        });
        
        // Leave collaboration function
        // Delete collaboration function (creator only)
        function deleteCollaboration(collaborationId) {
            Swal.fire({
                title: 'Delete Collaboration?',
                text: 'Are you sure you want to delete this collaboration group? This will remove all members and cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const formData = new FormData();
                    formData.append('collaboration_id', collaborationId);
                    
                    return fetch('../actions/delete_collaboration.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            return data;
                        } else {
                            throw new Error(data.message || 'Failed to delete collaboration');
                        }
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: result.value.message || 'Collaboration group has been deleted.',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Error!',
                    text: error.message || 'Failed to delete collaboration.',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        }

        function leaveCollaboration(collaborationId) {
            Swal.fire({
                title: 'Leave Collaboration?',
                text: 'Are you sure you want to leave this collaboration group?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, leave!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const formData = new FormData();
                    formData.append('collaboration_id', collaborationId);
                    
                    return fetch('../actions/leave_collaboration.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            return data;
                        } else {
                            throw new Error(data.message || 'Failed to leave collaboration');
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage('Error: ' + error.message);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('Success!', result.value.message, 'success').then(() => {
                        window.location.reload();
                    });
                }
            });
        }
        
        // Place collaboration order function
        function placeCollaborationOrder(collaborationId, productId) {
            Swal.fire({
                title: 'Place Collaboration Order?',
                html: 'This will add the product to your cart and place an order for your contribution percentage.<br><br><strong>Note:</strong> After placing your order, the wholesaler will contact you via phone call for more information about your order before you make payment.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#667eea',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, place order!',
                cancelButtonText: 'Cancel',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    // Add product to cart first
                    return fetch('../actions/add_to_cart.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'product_id=' + productId + '&quantity=1'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Redirect to place order with collaboration ID
                            window.location.href = '../actions/place_order_action.php?collaboration_id=' + collaborationId;
                        } else {
                            Swal.showValidationMessage('Failed to add product to cart: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        Swal.showValidationMessage('Error: ' + error);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        }
    </script>
</body>
</html>

