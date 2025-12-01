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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - EasyBuy</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
        }
        .reset-form {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .reset-form h2 {
            margin-top: 0;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }
        .reset-form input {
            width: 100%;
            padding: 12px 40px 12px 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            box-sizing: border-box;
        }
        .reset-form input:focus {
            outline: none;
            border-color: #0056b3;
            box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.1);
        }
        .reset-form button[type="submit"] {
            width: 100%;
            padding: 12px;
            margin: 15px 0 10px 0;
            border: none;
            border-radius: 8px;
            background: #28a745;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .reset-form button[type="submit"]:hover {
            background: #218838;
        }
        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <form class="reset-form" action="../actions/reset_password_action.php" method="POST">
        <h2>Create New Password</h2>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <input type="password" name="new_password" placeholder="New Password (min 6 characters)" required minlength="6">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required minlength="6">

        <button type="submit">Reset Password</button>
        
        <p class="back-link">
            <a href="../login/login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </p>
    </form>

</body>
</html>
