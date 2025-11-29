<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';

if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$user_id = get_user_id();
$user_role = $_SESSION['role'] ?? 0;

// Get all open collaborations or user's collaborations based on role
if ($user_role == 0) {
    // Retailers see their collaborations
    $collaborations = get_user_collaborations_ctr($user_id);
} else {
    // Others see all open collaborations
    $collaborations = get_all_open_collaborations_ctr();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Collaborations - EasyBuy</title>
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
        .collaboration-card {
            background: white;
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
        .product-image-small {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
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
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <div class="page-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-users me-2"></i>My Collaborations</h2>
                        <p class="mb-0 opacity-75">Track your collaborative purchases</p>
                    </div>
                    <a href="homepage.php" class="btn btn-light">
                        <i class="fas fa-home me-2"></i>Home
                    </a>
                </div>
            </div>

            <?php 
            // Debug: Check what we got
            error_log("Collaborations page - User ID: $user_id, Role: $user_role, Count: " . count($collaborations));
            if (!empty($collaborations)) {
                error_log("First collaboration: " . print_r($collaborations[0], true));
            }
            ?>
            
            <?php if (empty($collaborations)): ?>
                <div class="empty-state">
                    <i class="fas fa-users fa-4x mb-3" style="color: #667eea; opacity: 0.5;"></i>
                    <h4>No Active Collaborations</h4>
                    <p>Start collaborating on products to see them here!</p>
                    <a href="all_product.php" class="btn btn-primary mt-3">
                        <i class="fas fa-shopping-bag me-2"></i>Browse Products
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($collaborations as $collab): ?>
                        <?php
                        $members = get_collaboration_members_ctr($collab['collaboration_id']);
                        $total_contribution = floatval($collab['total_contribution'] ?? 0);
                        $remaining = 100 - $total_contribution;
                        $is_creator = ($collab['creator_id'] == $user_id);
                        $user_member = null;
                        foreach ($members as $member) {
                            if ($member['user_id'] == $user_id) {
                                $user_member = $member;
                                break;
                            }
                        }
                        
                        // Check if there are paid orders for this collaboration (once per collaboration)
                        require_once __DIR__ . '/../settings/db_class.php';
                        $db = new db_connection();
                        $db->db_connect();
                        $orderCheck = $db->db->prepare("SELECT COUNT(*) as order_count FROM orders WHERE collaboration_id = :cid AND payment_status = 'paid'");
                        $orderCheck->execute([':cid' => $collab['collaboration_id']]);
                        $orderResult = $orderCheck->fetch(PDO::FETCH_ASSOC);
                        $has_paid_orders = $orderResult && $orderResult['order_count'] > 0;
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="collaboration-card">
                                <div class="d-flex gap-3 mb-3">
                                    <?php if (!empty($collab['product_image'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($collab['product_image']); ?>" 
                                             class="product-image-small"
                                             onerror="this.src='https://via.placeholder.com/100?text=No+Image'">
                                    <?php endif; ?>
                                    <div class="flex-grow-1">
                                        <h5><?php echo htmlspecialchars($collab['product_name']); ?></h5>
                                        <?php if (!empty($collab['brand_name'])): ?>
                                            <p class="text-muted small mb-1">
                                                <i class="fas fa-tag me-1"></i>Brand: <strong><?php echo htmlspecialchars($collab['brand_name']); ?></strong>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($collab['cat_name'])): ?>
                                            <p class="text-muted small mb-1">
                                                <i class="fas fa-folder me-1"></i>Category: <strong><?php echo htmlspecialchars($collab['cat_name']); ?></strong>
                                            </p>
                                        <?php endif; ?>
                                        <p class="text-muted small mb-1">
                                            <i class="fas fa-box me-1"></i>MOQ: <strong><?php echo (int)$collab['moq']; ?> units</strong>
                                        </p>
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-dollar-sign me-1"></i>Price: <strong>GH₵ <?php echo number_format($collab['wholesale_price'], 2); ?></strong>
                                        </p>
                                    </div>
                                    <span class="badge bg-<?php echo $total_contribution >= 100 ? 'success' : 'warning'; ?> align-self-start">
                                        <?php echo $total_contribution >= 100 ? 'Complete' : 'Open'; ?>
                                    </span>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <?php if ($is_creator): ?>
                                                <i class="fas fa-crown me-1"></i>You created this
                                            <?php else: ?>
                                                Created by <?php echo htmlspecialchars($collab['creator_name']); ?>
                                            <?php endif; ?>
                                        </small>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if ($is_creator && !$has_paid_orders): ?>
                                                <button onclick="deleteCollaboration(<?php echo $collab['collaboration_id']; ?>)" 
                                                        class="btn btn-danger btn-sm" 
                                                        title="Delete this collaboration group">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </button>
                                            <?php endif; ?>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($collab['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="progress-bar-custom">
                                        <div class="progress-fill" style="width: <?php echo min($total_contribution, 100); ?>%;">
                                            <?php echo number_format($total_contribution, 1); ?>%
                                        </div>
                                    </div>
                                    <p class="text-center mb-0">
                                        <strong><?php echo number_format($remaining, 1); ?>%</strong> remaining
                                    </p>
                                </div>

                                <?php if ($user_member): ?>
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Your contribution: <strong><?php echo $user_member['contribution_percent']; ?>%</strong>
                                        <?php if (!$has_paid_orders): ?>
                                            <button onclick="leaveCollaboration(<?php echo $collab['collaboration_id']; ?>)" 
                                                    class="btn btn-sm btn-danger float-end">
                                                <i class="fas fa-sign-out-alt me-1"></i>Leave Group
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="mb-3">
                                    <strong>Members (<?php echo count($members); ?>):</strong>
                                    <div class="mt-2">
                                        <?php foreach ($members as $member): ?>
                                            <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-light rounded">
                                                <div>
                                                    <div>
                                                        <i class="fas fa-user me-2"></i>
                                                        <?php echo htmlspecialchars($member['full_name']); ?>
                                                        <?php if ($member['user_id'] == $user_id): ?>
                                                            <span class="badge bg-primary">You</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <?php if (!empty($member['phone'])): ?>
                                                        <small class="text-muted ms-4">
                                                            <i class="fas fa-phone me-1"></i>
                                                            <?php echo htmlspecialchars($member['phone']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <span class="badge bg-primary"><?php echo $member['contribution_percent']; ?>%</span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <?php 
                                // Check if order exists for this collaboration
                                require_once __DIR__ . '/../controllers/order_controller.php';
                                $collab_order = get_collaboration_order_ctr($collab['collaboration_id']);
                                $member_paid = false;
                                if ($collab_order && $user_member) {
                                    $member_payment = get_member_payment_status_ctr($collab_order['order_id'], $user_id);
                                    $member_paid = $member_payment && $member_payment['payment_status'] == 'paid';
                                }
                                ?>
                                <?php if ($total_contribution >= 100 && $collab['status'] == 'completed'): ?>
                                    <?php if ($collab_order): ?>
                                        <!-- Order has been automatically placed, show checkout for all members -->
                                        <div class="d-grid gap-2">
                                            <?php if ($is_creator && !$has_paid_orders): ?>
                                                <button onclick="deleteCollaboration(<?php echo $collab['collaboration_id']; ?>)" 
                                                        class="btn btn-danger" 
                                                        title="Delete this collaboration group">
                                                    <i class="fas fa-trash me-2"></i>Delete Collaboration Group
                                                </button>
                                            <?php endif; ?>
                                            <?php if ($member_paid): ?>
                                                <div class="alert alert-success mb-0">
                                                    <i class="fas fa-check-circle me-2"></i>You have paid your contribution!
                                                </div>
                                            <?php else: ?>
                                                <a href="../actions/checkout.php?order_id=<?php echo $collab_order['order_id']; ?>" 
                                                   class="btn btn-primary">
                                                    <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                                </a>
                                            <?php endif; ?>
                                            <a href="product_details.php?id=<?php echo $collab['product_id']; ?>" class="btn btn-outline-primary">
                                                <i class="fas fa-eye me-2"></i>View Details
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <!-- Order should be created automatically, but if not, show message -->
                                        <div class="alert alert-info mb-0">
                                            <i class="fas fa-info-circle me-2"></i>Order is being processed...
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="d-grid gap-2">
                                        <?php if ($is_creator && !$has_paid_orders): ?>
                                            <button onclick="deleteCollaboration(<?php echo $collab['collaboration_id']; ?>)" 
                                                    class="btn btn-danger w-100" 
                                                    title="Delete this collaboration group">
                                                <i class="fas fa-trash me-2"></i>Delete Collaboration Group
                                            </button>
                                        <?php endif; ?>
                                        <a href="product_details.php?id=<?php echo $collab['product_id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-eye me-2"></i>View Details
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/order.js"></script>
    <script>
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

        // Leave collaboration function
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
        
        // Override placeCollaborationOrder to add product to cart first
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

