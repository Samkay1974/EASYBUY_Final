<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
<title>Forgot Password</title>
<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: Arial, sans-serif;
}
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
    box-sizing: border-box;
}
input {
    border: 1px solid #ddd;
}
button {
    background:#007bff;
    border:none;
    color:white;
    font-size:16px;
    cursor: pointer;
}
button:hover {
    background:#0056b3;
}
.alert {
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 15px;
}
.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
.alert-error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}
</style>
</head>

<body>

<form action="../actions/forgot_password_action.php" method="POST">
    <h2>Reset Password</h2>
    <p>Enter your email to receive a reset link.</p>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Reset Link</button>
    <p style="text-align: center; margin-top: 15px;">
        <a href="../login/login.php" style="color: #007bff; text-decoration: none;">Back to Login</a>
    </p>
</form>

</body>
</html>
