<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../login/login.php");
    exit();
}

// Get user info
$user_fullname = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 0; // 0 = Retailer, 1 = Wholesaler, 2 = Admin
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EasyBuy - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/homepage.css">
</head>

<body class="homepage-body">

    <!-- NAVIGATION BAR -->
    <nav class="navbar">
        <div class="nav-left">
            <h2 class="logo"><i class="fas fa-shopping-cart me-2"></i>EasyBuy</h2>
        </div>

        <ul class="nav-right">
            <li><a href="all_product.php"><i class="fas fa-box me-1"></i>All Products</a></li>
            
            <?php 
            require_once __DIR__ . '/../controllers/cart_controller.php';
            require_once __DIR__ . '/../controllers/order_controller.php';
            require_once __DIR__ . '/../controllers/collaboration_controller.php';
            $cartItemCount = get_cart_item_count_ctr();
            
            // Check for pending collaboration orders where user is a member and hasn't paid
            $user_id = get_user_id();
            $has_pending_collab_payment = false;
            $collaborations = get_user_collaborations_ctr($user_id);
            foreach ($collaborations as $collab) {
                $collab_order = get_collaboration_order_ctr($collab['collaboration_id']);
                if ($collab_order && $collab_order['payment_status'] == 'pending') {
                    $member_payment = get_member_payment_status_ctr($collab_order['order_id'], $user_id);
                    if (!$member_payment || $member_payment['payment_status'] != 'paid') {
                        // Only count this pending collaboration payment if the order's product
                        // is not already present in the user's cart (avoid double-counting)
                        $order_details = get_order_details_ctr($collab_order['order_id']);
                        $count_collab = true;
                        foreach ($order_details as $od) {
                            if (get_cart_item_quantity_ctr($od['product_id']) > 0) {
                                $count_collab = false;
                                break;
                            }
                        }
                        if ($count_collab) {
                            $has_pending_collab_payment = true;
                        }
                        break;
                    }
                }
            }
            
            $totalBadgeCount = $cartItemCount + ($has_pending_collab_payment ? 1 : 0);
            ?>
            <li>
                <a href="cart.php" class="position-relative">
                    <i class="fas fa-shopping-cart me-1"></i>Cart
                    <?php if ($totalBadgeCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="cartBadgeNav">
                            <?php echo $totalBadgeCount; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
            
            <?php if ($user_role == 0): ?>
                <li><a href="collaborations.php"><i class="fas fa-users me-1"></i>My Collaborations</a></li>
            <?php endif; ?>

            <!-- Wholesaler Only Links -->
            <?php if ($user_role == 1): ?>
                <?php 
                require_once __DIR__ . '/../controllers/order_controller.php';
                $unpaidOrdersCount = get_unpaid_orders_count_for_wholesaler_ctr($_SESSION['id']);
                ?>
                <li><a href="../admin/products.php"><i class="fas fa-cog me-1"></i>Manage Products</a></li>
                <li>
                    <a href="view_orders.php" class="position-relative">
                        <i class="fas fa-shopping-bag me-1"></i>View Orders
                        <?php if ($unpaidOrdersCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="ordersBadgeNav">
                                <?php echo $unpaidOrdersCount; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="../admin/brand.php"><i class="fas fa-tag me-1"></i>Brands</a></li>
                <li><a href="../admin/category.php"><i class="fas fa-folder me-1"></i>Categories</a></li>
            <?php endif; ?>

            <!-- Superadmin Only Links -->
            <?php if ($user_role == 2): ?>
                <?php 
                require_once __DIR__ . '/../controllers/report_controller.php';
                $pending_reports = get_pending_reports_count_ctr();
                ?>
                <li><a href="../admin/dashboard.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a></li>
                <li><a href="../admin/users.php"><i class="fas fa-users me-1"></i>Manage Users</a></li>
                <li><a href="../admin/brands.php"><i class="fas fa-tag me-1"></i>Manage Brands</a></li>
                <li><a href="../admin/categories.php"><i class="fas fa-folder me-1"></i>Manage Categories</a></li>
                <li>
                    <a href="../admin/reports.php" class="position-relative">
                        <i class="fas fa-flag me-1"></i>Reports
                        <?php if ($pending_reports > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="reportsBadgeNav">
                                <?php echo $pending_reports; ?>
                            </span>
                        <?php endif; ?>
                    </a>
                </li>
                <li><a href="../admin/analytics.php"><i class="fas fa-chart-line me-1"></i>Analytics</a></li>
            <?php endif; ?>

            <li class="profile-dropdown">
                <a href="#" class="profile-link" onclick="event.preventDefault(); toggleProfileDropdown();">
                    <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user_fullname); ?>
                    <i class="fas fa-chevron-down ms-1" style="font-size: 12px;"></i>
                </a>
                <div class="dropdown-menu" id="profileDropdown">
                    <a href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a>
                    <a href="my_orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a>
                    <a href="../view/forgot_password.php"><i class="fas fa-key me-2"></i>Change Password</a>
                    <hr style="margin: 8px 0; border-color: #e0e0e0;">
                    <a href="../login/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </div>
            </li>
        </ul>
    </nav>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-content">
            <div class="welcome-badge">
                <i class="fas fa-hand-sparkles me-2"></i>Welcome Back!
            </div>
            <h1 class="hero-title">Hello, <?php echo htmlspecialchars($user_fullname); ?>!</h1>
            
            <?php if ($user_role == 0): ?>
                <p class="hero-subtext">
                    EasyBuy empowers retailers like you to access wholesale prices through collaborative purchasing.  
                    Buy more with less, partner with others, and scale your business affordably.
                </p>
                <div class="hero-actions">
                    <a href="all_product.php" class="btn-hero-primary">
                        <i class="fas fa-shopping-bag me-2"></i>Browse Products
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($user_role == 1): ?>
                <p class="hero-subtext">
                    As a wholesaler, EasyBuy gives you access to a larger customer base, consistent orders,  
                    and improved visibility for your products across Ghana.
                </p>
                <div class="hero-actions">
                    <a href="../admin/products.php" class="btn-hero-primary">
                        <i class="fas fa-plus-circle me-2"></i>Add Product
                    </a>
                    <a href="all_product.php" class="btn-hero-secondary">
                        <i class="fas fa-eye me-2"></i>View All Products
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <div class="hero-decoration">
            <div class="floating-shape shape-1"></div>
            <div class="floating-shape shape-2"></div>
            <div class="floating-shape shape-3"></div>
        </div>
    </section>

    <!-- QUICK ACTIONS SECTION -->
    <section class="quick-actions">
        <div class="container-custom">
            <h3 class="section-title">Quick Actions</h3>
            <div class="action-cards">
                <a href="all_product.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <h4>Browse Products</h4>
                    <p>Explore all available products</p>
                </a>

                <?php if ($user_role == 0): ?>
                    <a href="collaborations.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h4>My Collaborations</h4>
                        <p>View your collaborative purchases</p>
                    </a>
                <?php endif; ?>

                <?php if ($user_role == 1): ?>
                    <a href="../admin/products.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <h4>My Products</h4>
                        <p>Manage your product listings</p>
                    </a>
                    <a href="view_orders.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h4>View Orders</h4>
                        <p>See all orders for your products</p>
                    </a>
                    <a href="../admin/brand.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-tag"></i>
                        </div>
                        <h4>Brands</h4>
                        <p>Manage product brands</p>
                    </a>
                    <a href="../admin/category.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <h4>Categories</h4>
                        <p>Organize product categories</p>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- CONTENT SECTION -->
    <section class="content-section">
        <div class="container-custom">
            <?php if ($user_role == 0): ?>  
                <!-- RETAILER EDUCATION SECTION -->
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <h2>How Collaborative Purchase Works</h2>
                        <p>
                            Retailers can combine funds to reach a wholesaler's minimum order quantity.  
                            Simply browse a product, click <strong>"Collaborative Purchase"</strong>,  
                            and join or create a buying group. Each participant contributes a minimum of 30%  
                            of the order until the group reaches 100%.
                        </p>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h2>Benefits for Retailers</h2>
                        <ul class="benefits-list">
                            <li><i class="fas fa-check-circle"></i> Pay less by accessing wholesale pricing</li>
                            <li><i class="fas fa-check-circle"></i> Reduce financial pressure by sharing costs</li>
                            <li><i class="fas fa-check-circle"></i> Grow your business with better profit margins</li>
                        </ul>
                    </div>
                </div>

            <?php endif; ?>

            <?php if ($user_role == 1): ?>
                <!-- WHOLESALER EDUCATION SECTION -->
                <div class="info-cards">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-store"></i>
                        </div>
                        <h2>Grow Your Wholesale Business</h2>
                        <p>
                            EasyBuy increases your visibility and connects you to verified retailers  
                            who need your products but cannot always meet MOQ.  
                            With collaborative purchasing, you receive full payment,  
                            while small buyers get access to wholesale deals.
                        </p>
                    </div>

                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <h2>Why Join as a Wholesaler?</h2>
                        <ul class="benefits-list">
                            <li><i class="fas fa-check-circle"></i> More orders from a wider customer base</li>
                            <li><i class="fas fa-check-circle"></i> Digital presence without marketing costs</li>
                            <li><i class="fas fa-check-circle"></i> Structured product management through your dashboard</li>
                        </ul>
                    </div>
                </div>

            <?php endif; ?>

            <!-- REPORT SECTION -->
            <div class="info-cards mt-5">
                <div class="info-card">
                    <div class="info-icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <h2>Report an Issue</h2>
                    <p>Found a bug, have a suggestion, or need help? Let us know!</p>
                    <button class="btn btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#reportModal">
                        <i class="fas fa-paper-plane me-2"></i>Submit Report
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit a Report</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../actions/submit_report_action.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Report Type</label>
                            <select name="report_type" class="form-select" required>
                                <option value="bug">Bug Report</option>
                                <option value="feature">Feature Request</option>
                                <option value="complaint">Complaint</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container-custom">
            <p>© <?php echo date("Y"); ?> EasyBuy. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const profileLink = document.querySelector('.profile-link');
            
            if (dropdown && !dropdown.contains(event.target) && !profileLink.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>

</html>
