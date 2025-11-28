<?php
session_start();
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is superadmin (role = 2)
if (!isLoggedIn() || !check_user_role(2)) {
    header('Location: ../login/login.php');
    exit;
}

$brand_name = isset($_POST['brand_name']) ? trim($_POST['brand_name']) : '';

if (empty($brand_name)) {
    $_SESSION['error'] = "Brand name is required.";
    header("Location: brands.php");
    exit;
}

require_once __DIR__ . '/../controllers/brand_controller.php';

// Superadmin can add brands without user_id restriction
$result = add_brand_ctr($_SESSION['id'], $brand_name);

if ($result) {
    $_SESSION['success'] = "Brand added successfully.";
} else {
    $_SESSION['error'] = "Failed to add brand. Brand may already exist.";
}

header("Location: brands.php");
exit;

