<?php
// actions/update_category_action.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to update a category."
    ]);
    exit;
}

// Validate input
if (empty($_POST['cat_id']) || empty($_POST['cat_name'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Category ID and name are required."
    ]);
    exit;
}

$user_id = get_user_id();
$category_id = intval($_POST['cat_id']);
$category_name = trim($_POST['cat_name']);

// Update category
$result = update_category_ctr($user_id, $category_id, $category_name);

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Category updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "An error occurred",
        "message" => "Category could not be updated."
    ]);
}
?>
