<?php

require_once(__DIR__ . '/../controllers/user_controller.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic required fields (all fields required per specification)
    $required = ['full_name', 'customer_email', 'password', 'confirm_password', 'user_role', 'city', 'country', 'phone_number'];
    foreach ($required as $f) {
        if (!isset($_POST[$f]) || $_POST[$f] === '') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
            exit;
        }
    }

    $full_name      = trim($_POST['full_name']);
    $customer_email = trim($_POST['customer_email']);
    $password       = $_POST['password'];
    $confirm_pass   = $_POST['confirm_password'];
    $city           = isset($_POST['city']) ? trim($_POST['city']) : null;
    $country        = isset($_POST['country']) ? trim($_POST['country']) : null;
    $phone_number   = isset($_POST['phone_number']) ? trim($_POST['phone_number']) : null;
    $user_role      = intval($_POST['user_role']);

    if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        exit;
    }
    if ($password !== $confirm_pass) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        exit;
    }

    $result = register_customer_controller(
        $full_name,
        $customer_email,
        $password,
        $city,
        $country,
        $phone_number,
        $user_role
    );

    header('Content-Type: application/json');
    if ($result) {
        // on success, include redirect target
        echo json_encode([
            'success' => true,
            'message' => 'Congratulations! Registration successful. You can login now...',
            'redirect' => '../login/login.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed. Email may already exist or input is invalid.']);
    }
    exit;
}
?>
