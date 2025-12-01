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
    
    // Detect base path from current file location
    $currentFile = str_replace('\\', '/', __FILE__); // settings/paystack_config.php
    $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']) : '';
    
    if (!empty($docRoot) && strpos($currentFile, $docRoot) !== false) {
        // File is within document root - extract relative path
        $relativePath = str_replace($docRoot, '', dirname($currentFile)); // /EASYBUY_Final/settings -> /EASYBUY_Final
        $relativePath = dirname($relativePath); // Go up one level
        $basePath = str_replace('\\', '/', $relativePath);
    } else {
        // Fallback: use SCRIPT_NAME if available
        $scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', $_SERVER['SCRIPT_NAME']) : '';
        if (strpos($scriptName, '/EASYBUY_Final') !== false) {
            $parts = explode('/EASYBUY_Final', $scriptName);
            $basePath = $parts[0] . '/EASYBUY_Final';
        } else {
            // Final fallback
            $basePath = '/EASYBUY_Final';
        }
    }
    
    // Normalize and ensure base path is correct
    $basePath = str_replace('\\', '/', $basePath);
    $basePath = rtrim($basePath, '/');
    if (empty($basePath) || $basePath === '/') {
        $basePath = '/EASYBUY_Final';
    }
    
    // Ensure it starts with /
    if (substr($basePath, 0, 1) !== '/') {
        $basePath = '/' . $basePath;
    }
    
    define('APP_BASE_URL', $protocol . $host . $basePath);
    
    // Log for debugging (remove in production)
    error_log("APP_BASE_URL detected: " . APP_BASE_URL);
}

define('PAYSTACK_CALLBACK_URL', APP_BASE_URL . '/view/paystack_callback.php'); // Callback after payment

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
