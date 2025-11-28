<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';

// Ensure user is logged in and is a retailer (role 0)
if (!isLoggedIn() || !check_user_role(0)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only retailers can leave collaborations.']);
    exit;
}

$user_id = get_user_id();

// Validate input
if (empty($_POST['collaboration_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Collaboration ID is required.']);
    exit;
}

$collaboration_id = intval($_POST['collaboration_id']);

// Check if user is a member
if (!is_member_ctr($collaboration_id, $user_id)) {
    echo json_encode(['status' => 'error', 'message' => 'You are not a member of this collaboration.']);
    exit;
}

// Get collaboration to check status
$collab = get_collaboration_by_id_ctr($collaboration_id);
if (!$collab) {
    echo json_encode(['status' => 'error', 'message' => 'Collaboration not found.']);
    exit;
}

// Check if there are paid orders for this collaboration
require_once __DIR__ . '/../settings/db_class.php';
$db = new db_connection();
$db->db_connect();
$orderCheck = $db->db->prepare("SELECT COUNT(*) as order_count FROM orders WHERE collaboration_id = :cid AND payment_status = 'paid'");
$orderCheck->execute([':cid' => $collaboration_id]);
$orderResult = $orderCheck->fetch(PDO::FETCH_ASSOC);

if ($orderResult && $orderResult['order_count'] > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot leave collaboration after payment has been made.']);
    exit;
}

$result = leave_collaboration_ctr($collaboration_id, $user_id);

if ($result) {
    $new_total = get_total_contribution_ctr($collaboration_id);
    $message = 'Successfully left the collaboration.';
    
    if ($new_total < 100 && $collab['status'] == 'completed') {
        $message .= ' The collaboration has been reopened.';
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => $message,
        'total_contribution' => $new_total
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to leave collaboration.']);
}





