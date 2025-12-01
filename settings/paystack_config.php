<?php
/**
 * Paystack Configuration
 * Secure payment gateway settings
 */
// Include server credentials first (for server deployment)
if (file_exists(__DIR__ . '/server_cred.php')) {
    require_once 'server_cred.php';
} else {
    require_once 'db_cred.php';
}

// Temporary direct override for the Paystack callback URL (Option B)
// Replace the placeholder below with your real public URL for the deployed app.
// Example: 'https://yourdomain.com/EASYBUY_Final/view/paystack_callback.php'
if (!defined('PAYSTACK_CALLBACK_URL')) {
    define('PAYSTACK_CALLBACK_URL', 'http://169.239.251.102:442/~samuel.ninson/view/paystack_callback.php');
    error_log("PAYSTACK_CALLBACK_URL (override): " . PAYSTACK_CALLBACK_URL);
}

// Paystack API Keys
define('PAYSTACK_SECRET_KEY', 'sk_test_bb2ee86c84ac4da2d80ed76dba13c16e2dad28d8'); // My Test secret key
define('PAYSTACK_PUBLIC_KEY', 'pk_test_6d82a6db262a169aa0fb069f7b73c63cd8a471dd'); // My Test public key

// Paystack URLs
define('PAYSTACK_API_URL', 'https://api.paystack.co');
define('PAYSTACK_INIT_ENDPOINT', PAYSTACK_API_URL . '/transaction/initialize');
define('PAYSTACK_VERIFY_ENDPOINT', PAYSTACK_API_URL . '/transaction/verify/');

define('APP_ENVIRONMENT', 'test'); 

// Dynamically detect base URL from server environment
if (!defined('APP_BASE_URL')) {
    // Detect protocol (https or http)
    $protocol = 'http://';
    if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) {
        $protocol = 'https://';
    }
    
    // Get host
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 
            (defined('SERVER') ? SERVER : 'localhost');
    
    // Detect base path - prioritize REQUEST_URI as it's most accurate for current request
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']) : '';
    $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
    $requestUri = isset($_SERVER['REQUEST_URI']) ? str_replace('\\', '/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) : '';
    $currentFile = str_replace('\\', '/', __FILE__);
    
    $basePath = '';
    
    // Method 1: Extract from REQUEST_URI (most accurate for current page)
    if (!empty($requestUri)) {
        if (strpos($requestUri, '/EASYBUY_Final/') !== false) {
            $parts = explode('/EASYBUY_Final/', $requestUri);
            $basePath = $parts[0] . '/EASYBUY_Final';
        } elseif ($requestUri === '/EASYBUY_Final' || strpos($requestUri, '/EASYBUY_Final') === 0) {
            $basePath = '/EASYBUY_Final';
        }
    }
    
    // Method 2: Try to extract from SCRIPT_NAME
    if (empty($basePath) && !empty($scriptName)) {
        if (strpos($scriptName, '/EASYBUY_Final/') !== false) {
            $parts = explode('/EASYBUY_Final/', $scriptName);
            $basePath = $parts[0] . '/EASYBUY_Final';
        } elseif (strpos($scriptName, '/EASYBUY_Final') === 0) {
            $basePath = '/EASYBUY_Final';
        }
    }
    
    // Method 3: If SCRIPT_NAME didn't work, try DOCUMENT_ROOT method
    if (empty($basePath) && !empty($docRoot) && strpos($currentFile, $docRoot) !== false) {
        // File is within document root - extract relative path
        $relativePath = str_replace($docRoot, '', $currentFile);
        // $relativePath will be like: /EASYBUY_Final/settings/paystack_config.php
        if (strpos($relativePath, '/EASYBUY_Final/') === 0 || strpos($relativePath, 'EASYBUY_Final/') === 0) {
            // Extract just the /EASYBUY_Final part
            if (strpos($relativePath, '/') === 0) {
                $parts = explode('/', trim($relativePath, '/'));
            } else {
                $parts = explode('/', $relativePath);
            }
            if (!empty($parts[0]) && $parts[0] === 'EASYBUY_Final') {
                $basePath = '/EASYBUY_Final';
            } elseif (!empty($parts[0])) {
                $basePath = '/' . $parts[0];
            }
        }
    }
    
    // Final fallback
    if (empty($basePath)) {
        $basePath = '/EASYBUY_Final';
    }
    
    // Normalize the path
    $basePath = str_replace('\\', '/', $basePath);
    $basePath = rtrim($basePath, '/');
    
    // Ensure it starts with /
    if (substr($basePath, 0, 1) !== '/') {
        $basePath = '/' . $basePath;
    }
    
    // Prevent double paths (e.g., /EASYBUY_Final/EASYBUY_Final)
    if (strpos($basePath, '/EASYBUY_Final/EASYBUY_Final') !== false) {
        $basePath = '/EASYBUY_Final';
    }
    
    // Ensure it ends properly
    if (empty($basePath) || $basePath === '/') {
        $basePath = '/EASYBUY_Final';
    }
    
    define('APP_BASE_URL', $protocol . $host . $basePath);
    
    // Log for debugging
    error_log("=== BASE URL DETECTION ===");
    error_log("Protocol: $protocol");
    error_log("Host: $host");
    error_log("Document Root: $docRoot");
    error_log("Script Name: $scriptName");
    error_log("Request URI: $requestUri");
    error_log("Current File: $currentFile");
    error_log("Detected Base Path: $basePath");
    error_log("Final APP_BASE_URL: " . APP_BASE_URL);
}

// Define callback URL - ensure proper path construction
if (!defined('PAYSTACK_CALLBACK_URL')) {
    $callbackPath = rtrim(APP_BASE_URL, '/') . '/view/paystack_callback.php';
    define('PAYSTACK_CALLBACK_URL', $callbackPath);
    error_log("PAYSTACK_CALLBACK_URL: " . PAYSTACK_CALLBACK_URL);
}

/**
 * Initialize a Paystack transaction
 * 
 * @param float $amount Amount in GHS 
 * @param string $email Customer email
 * @param string $reference Optional reference
 * @return array Response with 'status' and 'data' containing authorization_url
 */
function paystack_initialize_transaction($amount, $email, $reference = null) {
    $reference = $reference ?? 'ref_' . uniqid();
    
    // Convert GHS to pesewas (1 GHS = 100 pesewas)
    $amount_in_pesewas = round($amount * 100);
    
    $data = [
        'amount' => $amount_in_pesewas,
        'email' => $email,
        'reference' => $reference,
        'callback_url' => PAYSTACK_CALLBACK_URL,
        'metadata' => [
            'currency' => 'GHS',
            'app' => 'Aya Crafts',
            'environment' => APP_ENVIRONMENT
        ]
    ];
    
    $response = paystack_api_request('POST', PAYSTACK_INIT_ENDPOINT, $data);
    
    return $response;
}

/**
 * Verify a Paystack transaction
 * 
 * @param string $reference Transaction reference
 * @return array Response with transaction details
 */
function paystack_verify_transaction($reference) {
    $response = paystack_api_request('GET', PAYSTACK_VERIFY_ENDPOINT . $reference);
    
    return $response;
}

/**
 * Make a request to Paystack API
 * 
 * @param string $method HTTP method (GET, POST, etc)
 * @param string $url Full API endpoint URL
 * @param array $data Optional data to send
 * @return array API response decoded as array
 */
function paystack_api_request($method, $url, $data = null) {
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Set headers
    $headers = [
        'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
        'Content-Type: application/json'
    ];
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    // Send data for POST/PUT requests
    if ($method !== 'GET' && $data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    // Execute request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    curl_close($ch);
    
    // Handle curl errors
    if ($curl_error) {
        error_log("Paystack API CURL Error: $curl_error");
        return [
            'status' => false,
            'message' => 'Connection error: ' . $curl_error
        ];
    }
    
    // Decode response
    $result = json_decode($response, true);
    
    // Log for debugging
    error_log("Paystack API Response (HTTP $http_code): " . json_encode($result));
    
    return $result;
}

/**
 * Get currency symbol for display
 */
function get_currency_symbol($currency = 'GHS') {
    $symbols = [
        'GHS' => '₵',
        'USD' => '$',
        'EUR' => '€',
        'NGN' => '₦'
    ];
    
    return $symbols[$currency] ?? $currency;
}
?>
