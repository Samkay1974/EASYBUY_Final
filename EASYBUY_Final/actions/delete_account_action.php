<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/user_controller.php';

if(!isLoggedIn()){
    header("Location: ../login/login.php");
    exit;
}

$user_id = get_user_id();

if(delete_user_ctr($user_id)){
    // Also delete cart items
    require_once __DIR__ . '/../controllers/cart_controller.php';
    clear_cart_ctr();
    
    session_unset();
    session_destroy();

    $_SESSION['success'] = "Account deleted successfully.";
    header("Location: ../login/login.php");
    exit;
} else {
    $_SESSION['error'] = "Failed to delete account. Please try again.";
    header("Location: ../view/delete_account.php");
    exit;
}
