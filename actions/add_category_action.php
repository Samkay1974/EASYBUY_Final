<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to add a category."
    ]);
    exit;
}

// Validate input
if (empty($_POST['cat_name'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Please provide a category name."
    ]);
    exit;
}

$user_id = get_user_id();
$category_name = trim($_POST['cat_name']);


$existing = get_category_by_name_ctr($category_name);
if ($existing) {
    echo json_encode([
        "status" => "error",
        "message" => "This category already exists! You can use it instead of creating another."
    ]);
    exit;
}


$result = add_category_ctr($user_id, $category_name);

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Category added successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Category could not be added due to a server error."
    ]);
}
?>
