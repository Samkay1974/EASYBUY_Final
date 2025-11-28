<?php
session_start();
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is superadmin (role = 2)
if (!isLoggedIn() || !check_user_role(2)) {
    header('Location: ../login/login.php');
    exit;
}

$user_fullname = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '';
require_once __DIR__ . '/../controllers/user_controller.php';
require_once __DIR__ . '/../controllers/order_controller.php';
require_once __DIR__ . '/../controllers/report_controller.php';

// Get statistics
$total_users = count(get_all_users_ctr());
$total_orders = 0; // Will be calculated
$pending_reports = get_pending_reports_count_ctr();
$monthly_orders = get_monthly_paid_orders_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Superadmin Dashboard - EasyBuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
        }
        .sidebar .logo {
            padding: 20px;
            font-size: 24px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            margin-bottom: 20px;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            padding: 15px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card .icon {
            font-size: 40px;
            color: #667eea;
        }
        .stat-card h3 {
            font-size: 32px;
            margin: 10px 0;
            color: #333;
        }
        .stat-card p {
            color: #666;
            margin: 0;
        }
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .badge-notification {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Superadmin Dashboard</h1>
            <div>
                <span class="text-muted">Welcome, <?php echo htmlspecialchars($user_fullname); ?></span>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-shopping-cart"></i></div>
                    <h3><?php echo count($monthly_orders); ?></h3>
                    <p>Paid Orders (Last 12 Months)</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-flag"></i></div>
                    <h3><?php echo $pending_reports; ?></h3>
                    <p>Pending Reports</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="icon"><i class="fas fa-chart-bar"></i></div>
                    <h3><?php echo array_sum(array_column($monthly_orders, 'order_count')); ?></h3>
                    <p>Total Orders</p>
                </div>
            </div>
        </div>

        <div class="chart-container">
            <h3 class="mb-4">Monthly Paid Orders</h3>
            <canvas id="ordersChart" height="80"></canvas>
        </div>
    </div>

    <script>
        const monthlyData = <?php echo json_encode($monthly_orders); ?>;
        const labels = monthlyData.map(item => {
            const [year, month] = item.month.split('-');
            const date = new Date(year, month - 1);
            return date.toLocaleString('default', { month: 'short', year: 'numeric' });
        }).reverse();
        const orderCounts = monthlyData.map(item => parseInt(item.order_count)).reverse();
        const revenues = monthlyData.map(item => parseFloat(item.total_revenue)).reverse();

        const ctx = document.getElementById('ordersChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Orders',
                    data: orderCounts,
                    backgroundColor: 'rgba(102, 126, 234, 0.6)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

