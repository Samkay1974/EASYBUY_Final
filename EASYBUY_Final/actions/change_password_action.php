<?php
session_start();
require_once "../controllers/user_controller.php";  // you already have this

$user_id = $_SESSION["user_id"];
$current_password = $_POST["current_password"];
$new_password = $_POST["new_password"];
$confirm_password = $_POST["confirm_password"];

// 1. Check if new passwords match
if($new_password !== $confirm_password){
    echo "Passwords do not match!";
    exit;
}

// 2. Get old password hash
$user = get_user_by_id_ctr($user_id);
$old_hash = $user["password"];

// 3. Verify current password
if(!password_verify($current_password, $old_hash)){
    echo "Current password is incorrect!";
    exit;
}

// 4. Hash new password
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// 5. Save new password
if(update_password_ctr($user_id, $new_hash)){
    echo "Password updated successfully!";
    header("Location: ../admin/profile.php");
} else {
    echo "Failed to update password.";
}
