<?php
session_start();
$token = isset($_GET["token"]) ? $_GET["token"] : '';

if(empty($token)){
    $_SESSION['error'] = "Invalid reset link.";
    header("Location: forgot_password.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<style>
form {
    width:40%;
    margin:80px auto;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 0 10px #aaa;
}
input, button {
    width:100%;
    padding:12px;
    margin:10px 0;
    border-radius:8px;
}
button {
    background:#28a745;
    border:none;
    color:white;
    font-size:16px;
}
</style>
</head>

<body>

<form action="../actions/reset_password_action.php" method="POST">
    <h2>Create New Password</h2>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

    <input type="password" name="new_password" placeholder="New Password (min 6 characters)" required minlength="6">
    <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6">

    <button type="submit">Reset Password</button>
    <p style="text-align: center; margin-top: 15px;">
        <a href="../login/login.php" style="color: #007bff; text-decoration: none;">Back to Login</a>
    </p>
</form>

</body>
</html>
