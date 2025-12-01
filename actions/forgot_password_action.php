<?php
session_start();
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/user_controller.php';

$email = isset($_POST["email"]) ? trim($_POST["email"]) : '';

if(empty($email)){
    $_SESSION['error'] = "Email is required.";
    header("Location: ../view/forgot_password.php");
    exit;
}

$user = get_user_by_email_ctr($email);

if(!$user){
    $_SESSION['error'] = "Email not found in our system.";
    header("Location: ../view/forgot_password.php");
    exit;
}

// Generate secure token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Save token
$result = save_reset_token_ctr($email, $token, $expires);

if(!$result){
    $_SESSION['error'] = "Failed to generate reset token. Please try again.";
    header("Location: ../view/forgot_password.php");
    exit;
}

// Dynamically generate reset link based on server environment
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Get base path from script location
$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']) : '';

// Extract base path
if (strpos($scriptName, '/EASYBUY_Final') !== false) {
    $parts = explode('/EASYBUY_Final', $scriptName);
    $basePath = $parts[0] . '/EASYBUY_Final';
} else {
    // Try to detect from current file location
    $currentFile = str_replace('\\', '/', __DIR__);
    if (!empty($docRoot) && strpos($currentFile, $docRoot) !== false) {
        $relativePath = str_replace($docRoot, '', $currentFile);
        $basePath = dirname(dirname($relativePath));
        if (strpos($basePath, '/EASYBUY_Final') === false) {
            $basePath = '/EASYBUY_Final';
        }
    } else {
        $basePath = '/EASYBUY_Final';
    }
}

$basePath = rtrim($basePath, '/');
$resetLink = $protocol . $host . $basePath . "/view/reset_password.php?token=$token";

// In production, use proper email sending
// For now, we'll just show the link (for development)
// mail($email, "Password Reset - EasyBuy", 
//     "Click the link to reset your password: $resetLink\n\nThis link expires in 1 hour.");

$_SESSION['success'] = "A reset link has been sent to your email. For development, use this link: " . $resetLink;
header("Location: ../view/forgot_password.php");
exit;
