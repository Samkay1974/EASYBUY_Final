<?php
session_start();
require_once __DIR__ . '/../controllers/user_controller.php';

// Accept either 'email' or 'customer_email' for compatibility
$email = isset($_POST['email']) ? trim($_POST['email']) : (isset($_POST['customer_email']) ? trim($_POST['customer_email']) : '');
$password = isset($_POST['password']) ? $_POST['password'] : '';

// Detect AJAX requests
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (empty($email) || empty($password)) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit;
    }
    $_SESSION['login_error'] = 'Email and password are required.';
    header('Location: ../login.php');
    exit;
}

$customer = login_customer_ctr($email, $password);

if ($customer) {
    // normalize session keys to match DB fields: 'id' and 'role'
    $_SESSION['id'] = $customer['id'];
    $_SESSION['full_name'] = $customer['full_name'];
    $_SESSION['role'] = $customer['role'];

    // Load cart from database after login
    require_once __DIR__ . '/../controllers/cart_controller.php';
    $cart = new Cart();
    $cart->syncCartToDatabase($customer['id']);

    // ROLE-BASED REDIRECTION LOGIC
    // Roles: 0 = Retailer, 1 = Wholesaler, 2 = Superadmin
    // Default: retailers and wholesalers go to homepage, superadmin to admin dashboard
    $redirect = '../view/homepage.php';
    if (isset($customer['role']) && $customer['role'] == 2) {
        $redirect = '../admin/dashboard.php';
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => $redirect]);
        exit;
    }

    header('Location: ' . $redirect);
    exit;

} else {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        exit;
    }
    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: ../login.php");
    exit;
}
