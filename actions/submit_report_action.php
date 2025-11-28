<?php
session_start();
require_once __DIR__ . '/../settings/core.php';

if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit;
}

$report_type = isset($_POST['report_type']) ? trim($_POST['report_type']) : '';
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';

if (empty($report_type) || empty($subject) || empty($description)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../view/homepage.php");
    exit;
}

if (!in_array($report_type, ['bug', 'feature', 'complaint', 'other'])) {
    $_SESSION['error'] = "Invalid report type.";
    header("Location: ../view/homepage.php");
    exit;
}

require_once __DIR__ . '/../controllers/report_controller.php';

$result = create_report_ctr(get_user_id(), $report_type, $subject, $description);

if ($result) {
    $_SESSION['success'] = "Report submitted successfully. Thank you for your feedback!";
} else {
    $_SESSION['error'] = "Failed to submit report. Please try again.";
}

header("Location: ../view/homepage.php");
exit;

