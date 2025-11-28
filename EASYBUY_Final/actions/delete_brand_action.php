<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to delete a brand."
    ]);
    exit;
}

if (empty($_POST['brand_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Brand ID is required."
    ]);
    exit;
}

$user_id = get_user_id();
$brand_id = intval($_POST['brand_id']);

$result = delete_brand_ctr($user_id, $brand_id);

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Brand deleted successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Brand could not be deleted."
    ]);
}
?>
