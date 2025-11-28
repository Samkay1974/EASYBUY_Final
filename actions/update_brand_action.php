<?php
// actions/update_category_action.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to update a brand."
    ]);
    exit;
}

// Validate input
if (empty($_POST['brand_id']) || empty($_POST['brand_name'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Brand ID and name are required."
    ]);
    exit;
}

$user_id = get_user_id();
$brand_id = intval($_POST['brand_id']);
$brand_name = trim($_POST['brand_name']);

// Update brand
$result = update_brand_ctr($user_id, $brand_id, $brand_name);
if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Brand updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Brand could not be updated."
    ]);
}
?>
