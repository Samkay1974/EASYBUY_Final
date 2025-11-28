<?php
require_once __DIR__ . '/../controllers/report_controller.php';
$pending_reports = get_pending_reports_count_ctr();
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="logo">
        <i class="fas fa-shield-alt me-2"></i>Superadmin
    </div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
        </a>
        <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
            <i class="fas fa-users me-2"></i>Manage Users
        </a>
        <a class="nav-link <?php echo $current_page == 'brands.php' ? 'active' : ''; ?>" href="brands.php">
            <i class="fas fa-tag me-2"></i>Manage Brands
        </a>
        <a class="nav-link <?php echo $current_page == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
            <i class="fas fa-folder me-2"></i>Manage Categories
        </a>
        <a class="nav-link position-relative <?php echo $current_page == 'reports.php' ? 'active' : ''; ?>" href="reports.php">
            <i class="fas fa-flag me-2"></i>Reports
            <?php if ($pending_reports > 0): ?>
                <span class="badge-notification"><?php echo $pending_reports; ?></span>
            <?php endif; ?>
        </a>
        <a class="nav-link <?php echo $current_page == 'analytics.php' ? 'active' : ''; ?>" href="analytics.php">
            <i class="fas fa-chart-line me-2"></i>Analytics
        </a>
        <a class="nav-link" href="../view/homepage.php">
            <i class="fas fa-home me-2"></i>Homepage
        </a>
        <a class="nav-link" href="../actions/logout_action.php">
            <i class="fas fa-sign-out-alt me-2"></i>Logout
        </a>
    </nav>
</div>

