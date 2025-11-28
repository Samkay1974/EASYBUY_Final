<?php
session_start();
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is superadmin (role = 2)
if (!isLoggedIn() || !check_user_role(2)) {
    header('Location: ../login/login.php');
    exit;
}

require_once __DIR__ . '/../controllers/report_controller.php';

$status_filter = isset($_GET['status']) ? $_GET['status'] : null;
$reports = get_all_reports_ctr($status_filter);
$pending_reports = get_pending_reports_count_ctr();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Superadmin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
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
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .report-card {
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-reviewed { background: #17a2b8; color: #fff; }
        .status-resolved { background: #28a745; color: #fff; }
        .status-dismissed { background: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-flag me-2"></i>User Reports</h1>
            <div class="btn-group">
                <a href="?status=" class="btn btn-sm <?php echo $status_filter === null ? 'btn-primary' : 'btn-outline-primary'; ?>">All</a>
                <a href="?status=pending" class="btn btn-sm <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-outline-primary'; ?>">Pending</a>
                <a href="?status=reviewed" class="btn btn-sm <?php echo $status_filter === 'reviewed' ? 'btn-primary' : 'btn-outline-primary'; ?>">Reviewed</a>
                <a href="?status=resolved" class="btn btn-sm <?php echo $status_filter === 'resolved' ? 'btn-primary' : 'btn-outline-primary'; ?>">Resolved</a>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="content-card">
            <?php if (empty($reports)): ?>
                <p class="text-center text-muted">No reports found</p>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <div class="card report-card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h5 class="card-title mb-1">
                                        <?php echo htmlspecialchars($report['subject']); ?>
                                        <span class="status-badge status-<?php echo $report['status']; ?> ms-2">
                                            <?php echo ucfirst($report['status']); ?>
                                        </span>
                                    </h5>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($report['full_name'] ?? 'Unknown'); ?>
                                        <span class="ms-3"><i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($report['email'] ?? 'N/A'); ?></span>
                                        <span class="ms-3"><i class="fas fa-calendar me-1"></i><?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></span>
                                    </p>
                                </div>
                                <span class="badge bg-secondary"><?php echo ucfirst($report['report_type']); ?></span>
                            </div>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($report['description'])); ?></p>
                            <?php if ($report['admin_notes']): ?>
                                <div class="alert alert-info mt-2 mb-0">
                                    <strong>Admin Notes:</strong> <?php echo nl2br(htmlspecialchars($report['admin_notes'])); ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($report['status'] == 'pending'): ?>
                                <div class="mt-3">
                                    <a href="update_report.php?report_id=<?php echo $report['report_id']; ?>&status=reviewed" 
                                       class="btn btn-sm btn-info">Mark as Reviewed</a>
                                    <a href="update_report.php?report_id=<?php echo $report['report_id']; ?>&status=resolved" 
                                       class="btn btn-sm btn-success">Mark as Resolved</a>
                                    <a href="update_report.php?report_id=<?php echo $report['report_id']; ?>&status=dismissed" 
                                       class="btn btn-sm btn-secondary">Dismiss</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

