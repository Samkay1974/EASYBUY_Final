<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';

// Ensure user is logged in and is a retailer (role 0)
if (!isLoggedIn() || !check_user_role(0)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only retailers can join collaborations.']);
    exit;
}

$user_id = get_user_id();

// Validate input
if (empty($_POST['collaboration_id']) || empty($_POST['contribution_percent'])) {
    echo json_encode(['status' => 'error', 'message' => 'Collaboration ID and contribution percentage are required.']);
    exit;
}

$collaboration_id = intval($_POST['collaboration_id']);
$contribution_percent = floatval($_POST['contribution_percent']);

// Validate contribution percentage
if ($contribution_percent < 1 || $contribution_percent > 100) {
    echo json_encode(['status' => 'error', 'message' => 'Contribution must be between 1% and 100%.']);
    exit;
}

// Check if user is already a member
if (is_member_ctr($collaboration_id, $user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'You are already a member of this collaboration.']);
    exit;
}

// Get collaboration to check minimum contribution
$collab = get_collaboration_by_id_ctr($collaboration_id);
if (!$collab) {
    echo json_encode(['status' => 'error', 'message' => 'Collaboration not found.']);
    exit;
}

if ($collab['status'] != 'open') {
    echo json_encode(['status' => 'error', 'message' => 'This collaboration is no longer open.']);
    exit;
}

// Check minimum contribution
if ($contribution_percent < $collab['min_contribution_percent']) {
    echo json_encode([
        'status' => 'error',
        'message' => "Minimum contribution is {$collab['min_contribution_percent']}%."
    ]);
    exit;
}

// Check if adding this contribution would exceed 100%
$current_total = get_total_contribution_ctr($collaboration_id);
if ($current_total + $contribution_percent > 100) {
    $remaining = 100 - $current_total;
    echo json_encode([
        'status' => 'error',
        'message' => "Only {$remaining}% remaining. Your contribution would exceed 100%."
    ]);
    exit;
}

$result = join_collaboration_ctr($collaboration_id, $user_id, $contribution_percent);

if ($result) {
    $new_total = get_total_contribution_ctr($collaboration_id);
    $message = 'Successfully joined the collaboration!';
    
    if ($new_total >= 100) {
        $message .= ' Collaboration is now complete!';
    } else {
        $remaining = 100 - $new_total;
        $message .= " {$remaining}% remaining to complete.";
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'total_contribution' => $new_total
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to join collaboration.']);
}

