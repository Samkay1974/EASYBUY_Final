<?php 
session_start();
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Change Password</title>
<style>
form {
    width: 40%;
    margin: 60px auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 0 10px #ccc;
}

input {
    width:100%;
    padding:10px;
    margin:10px 0;
    border-radius:8px;
    border:1px solid #aaa;
}

button{
    width:100%;
    padding:12px;
    background:#ff6600;
    color:white;
    border:none;
    border-radius:8px;
    font-size:16px;
}
</style>
</head>
<body>

<form action="../actions/change_password_action.php" method="POST">
    <h2>Change Password</h2>

    <input type="password" name="current_password" placeholder="Current Password" required>
    <input type="password" name="new_password" placeholder="New Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>

    <button type="submit">Update Password</button>
</form>

</body>
</html>
