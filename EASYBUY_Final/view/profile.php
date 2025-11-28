<?php 
session_start();
require_once __DIR__ . '/../settings/core.php';

if(!isLoggedIn()) {
    header("Location: ../login/login.php");
    exit;
}

$user_id = get_user_id();
$username = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';

// Get user details from database
require_once __DIR__ . '/../controllers/user_controller.php';
$user = null;
$email = '';
if ($user_id) {
    $user = get_user_by_id_ctr($user_id);
    if ($user) {
        $email = $user['email'];
        $username = $user['full_name'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile</title>
<style>
.container {
    width: 60%;
    margin: 40px auto;
    background: #fff;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 0 12px #ddd;
}

.section {
    margin-bottom: 25px;
}

.section h3 {
    margin-bottom: 8px;
    color: #444;
}

a.button {
    display: inline-block;
    padding: 10px 16px;
    background: #ff6600;
    color: white;
    border-radius: 8px;
    text-decoration: none;
}
</style>
</head>
<body>
<a class="button" style="position: absolute; top: 20px; right: 20px;background:black;color:blue" href="homepage.php">Back to Home</a>

<div class="container">
    <h2>My Profile</h2>

    <div class="section">
        <h3>Name</h3>
        <p><?php echo $username; ?></p>
        <p><strong>Email:</strong> <?php echo $email; ?></p>
    </div>

    <div class="section">
        <h3>Change Password</h3>
        <a class="button" href="../view/forgot_password.php">Change Password</a>
    </div>

    <div class="section">
        <h3>Order History</h3>
        <a class="button" href="my_orders.php">View My Orders</a>
    </div>
    <div class="section">
    <h3>Delete Account</h3>
    <a class="button" style="background:#cc0000;" href="delete_account.php">Delete My Account</a>
</div>

</div>

</body>
</html>
