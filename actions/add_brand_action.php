<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/brand_controller.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to add a brand."
    ]);
    exit;
}

// Validate input
if (empty($_POST['brand_name'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Please provide a brand name."
    ]);
    exit;
}

$user_id = get_user_id();
$brand_name = trim($_POST['brand_name']);


$existing = get_brand_by_name_ctr($brand_name);
if ($existing) {
    echo json_encode([
        "status" => "error",
        "message" => "This brand already exists! You can use it instead of creating another."
    ]);
    exit;
}


$result = add_brand_ctr($user_id, $brand_name);

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Brand added successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Brand could not be added due to a server error."
    ]);
}
?>
