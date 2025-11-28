<?php 
session_start();
require_once __DIR__ . '/../settings/core.php';

if(!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Delete Account</title>
<style>
.box {
    width: 40%;
    margin: 80px auto;
    text-align:center;
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 0 10px #aaa;
}

button, a {
    padding:10px 18px;
    border:none;
    border-radius:8px;
    font-size:17px;
    color:white;
    text-decoration:none;
}

.del { background:#cc0000; }
.cancel { background:#555; }
</style>
</head>

<body>

<div class="box">
    <h2>Are you sure?</h2>
    <p>This action will permanently delete your account and all associated data.</p>
    
    <?php if(isset($_SESSION['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="../actions/delete_acccount_action.php">
        <button class="del" type="submit" onclick="return confirm('Are you absolutely sure? This cannot be undone!');">Yes, Delete My Account</button>
        <a class="cancel" href="profile.php">Cancel</a>
    </form>
</div>

</body>
</html>
