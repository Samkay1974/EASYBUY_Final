<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

// Ensure user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to delete a category."
    ]);
    exit;
}

if (empty($_POST['cat_id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Category ID is required."
    ]);
    exit;
}

$user_id = get_user_id();
$category_id = intval($_POST['cat_id']);

$result = delete_category_ctr($user_id, $category_id);

if ($result) {
    echo json_encode([
        "status" => "success",
        "message" => "Category deleted successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Category could not be deleted."
    ]);
}
?>
