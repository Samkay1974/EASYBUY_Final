<?php
//Database credentials
// Settings/db_cred.php

// define('DB_HOST', 'localhost');
// define('DB_USER', 'root');
// define('DB_PASS', '');
// define('DB_NAME', 'dbforlab');


if (!defined("SERVER")) {
    define("SERVER", "localhost");
}

if (!defined("USERNAME")) {
    define("USERNAME", "samuel.ninson");
}

if (!defined("PASSWD")) {
    define("PASSWD", "Sam@Ashesi2021");
}

if (!defined("DATABASE")) {
    // Use the database name from the provided SQL dump
    define("DATABASE", "ecommerce_2025A_samuel_ninson");
}

// Optional: Manually set the base URL if auto-detection fails
// Uncomment and set your server's full base URL (including protocol and domain)
// Example: define('APP_BASE_URL', 'https://yourdomain.com/EASYBUY_Final');
// if (!defined('APP_BASE_URL')) {
//     define('APP_BASE_URL', 'https://yourdomain.com/EASYBUY_Final');
// }

// Define the application's public base URL for production/deployment.
// Set to your hosted URL so automatic detection is not required.
if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', 'http://169.239.251.102:442/~samuel.ninson');
}
?>