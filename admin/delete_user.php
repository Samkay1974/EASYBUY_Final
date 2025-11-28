<?php
session_start();
require_once __DIR__ . '/../settings/core.php';

// Check if user is logged in and is superadmin (role = 2)
if (!isLoggedIn() || !check_user_role(2)) {
    header('Location: ../login/login.php');
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if (!$user_id) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: users.php");
    exit;
}

// Prevent deleting yourself
if ($user_id == $_SESSION['id']) {
    $_SESSION['error'] = "You cannot delete your own account.";
    header("Location: users.php");
    exit;
}

require_once __DIR__ . '/../controllers/user_controller.php';

$result = delete_user_ctr($user_id);

if ($result) {
    $_SESSION['success'] = "User deleted successfully.";
} else {
    $_SESSION['error'] = "Failed to delete user. Please try again.";
}

header("Location: users.php");
exit;

