<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/collaboration_controller.php';

// Ensure user is logged in and is a retailer (role 0)
if (!isLoggedIn() || !check_user_role(0)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Only retailers can create collaborations.']);
    exit;
}

$user_id = get_user_id();

// Validate input
if (empty($_POST['product_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required.']);
    exit;
}

$product_id = intval($_POST['product_id']);
$min_contribution = isset($_POST['min_contribution_percent']) ? intval($_POST['min_contribution_percent']) : 30;

// Validate minimum contribution (should be between 10 and 50)
if ($min_contribution < 10 || $min_contribution > 50) {
    echo json_encode(['status' => 'error', 'message' => 'Minimum contribution must be between 10% and 50%.']);
    exit;
}

$collaboration_id = create_collaboration_ctr($product_id, $user_id, $min_contribution);

if ($collaboration_id) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Collaboration group created successfully!',
        'collaboration_id' => $collaboration_id
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to create collaboration group.']);
}

