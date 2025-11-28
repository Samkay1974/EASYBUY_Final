<?php
session_start();
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is superadmin (role = 2)
if (!isLoggedIn() || !check_user_role(2)) {
    header('Location: ../login/login.php');
    exit;
}

$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;
$status = isset($_GET['status']) ? $_GET['status'] : '';

if (!$report_id || !in_array($status, ['reviewed', 'resolved', 'dismissed'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: reports.php");
    exit;
}

require_once __DIR__ . '/../controllers/report_controller.php';

$result = update_report_status_ctr($report_id, $status, $_SESSION['id']);

if ($result) {
    $_SESSION['success'] = "Report status updated successfully.";
} else {
    $_SESSION['error'] = "Failed to update report status.";
}

header("Location: reports.php");
exit;

