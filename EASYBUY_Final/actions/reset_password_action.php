<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/user_controller.php';

$token = isset($_POST["token"]) ? $_POST["token"] : '';
$new_password = isset($_POST["new_password"]) ? $_POST["new_password"] : '';
$confirm = isset($_POST["confirm_password"]) ? $_POST["confirm_password"] : '';

if(empty($token) || empty($new_password) || empty($confirm)){
    $_SESSION['error'] = "All fields are required.";
    header("Location: ../view/reset_password.php?token=" . urlencode($token));
    exit;
}

if($new_password !== $confirm){
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: ../view/reset_password.php?token=" . urlencode($token));
    exit;
}

if(strlen($new_password) < 6){
    $_SESSION['error'] = "Password must be at least 6 characters long.";
    header("Location: ../view/reset_password.php?token=" . urlencode($token));
    exit;
}

$data = get_token_details_ctr($token);

if(!$data){
    $_SESSION['error'] = "Invalid or expired reset token.";
    header("Location: ../view/forgot_password.php");
    exit;
}

if(strtotime($data["expires_at"]) < time()){
    $_SESSION['error'] = "Reset token has expired. Please request a new one.";
    delete_token_ctr($token); // Clean up expired token
    header("Location: ../view/forgot_password.php");
    exit;
}

// hash new password
$hash = password_hash($new_password, PASSWORD_DEFAULT);

// update user
$result = update_password_by_email_ctr($data["email"], $hash);

if($result){
    // delete token
    delete_token_ctr($token);
    
    $_SESSION['success'] = "Password reset successful. You may now log in.";
    header("Location: ../login/login.php");
    exit;
} else {
    $_SESSION['error'] = "Failed to reset password. Please try again.";
    header("Location: ../view/reset_password.php?token=" . urlencode($token));
    exit;
}
