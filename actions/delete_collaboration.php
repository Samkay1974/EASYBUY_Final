<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';

// Ensure user is logged in and is a retailer (role 0)
if (!isLoggedIn() || !check_user_role(0)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only retailers can delete collaborations.']);
    exit;
}

$user_id = get_user_id();

// Validate input
if (empty($_POST['collaboration_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Collaboration ID is required.']);
    exit;
}

$collaboration_id = intval($_POST['collaboration_id']);

// Get collaboration to verify creator
$collab = get_collaboration_by_id_ctr($collaboration_id);
if (!$collab) {
    echo json_encode(['status' => 'error', 'message' => 'Collaboration not found.']);
    exit;
}

// Verify that the user is the creator
if ($collab['creator_id'] != $user_id) {
    echo json_encode(['status' => 'error', 'message' => 'Only the creator can delete this collaboration.']);
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
    echo json_encode(['status' => 'error', 'message' => 'Cannot delete collaboration after payment has been made.']);
    exit;
}

$result = delete_collaboration_ctr($collaboration_id, $user_id);

if ($result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Collaboration group deleted successfully.'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete collaboration.']);
}

