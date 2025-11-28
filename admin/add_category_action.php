<?php
session_start();
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is superadmin (role = 2)
if (!isLoggedIn() || !check_user_role(2)) {
    header('Location: ../login/login.php');
    exit;
}

$category_name = isset($_POST['category_name']) ? trim($_POST['category_name']) : '';

if (empty($category_name)) {
    $_SESSION['error'] = "Category name is required.";
    header("Location: categories.php");
    exit;
}

require_once __DIR__ . '/../controllers/category_controller.php';

// Superadmin can add categories without user_id restriction
$result = add_category_ctr($_SESSION['id'], $category_name);

if ($result) {
    $_SESSION['success'] = "Category added successfully.";
} else {
    $_SESSION['error'] = "Failed to add category. Category may already exist.";
}

header("Location: categories.php");
exit;

