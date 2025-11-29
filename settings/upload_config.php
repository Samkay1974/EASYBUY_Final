<?php
/**
 * Upload Configuration
 * Handles file uploads for both localhost and server environments
 */

// Server upload configuration
define('SERVER_UPLOAD_URL', 'http://169.239.251.102:442/~samuel.ninson/upload.php');
define('UPLOADS_DIR', 'uploads/');
define('PRODUCTS_UPLOAD_DIR', 'uploads/products/');

/**
 * Check if we're on the server (not localhost)
 * @return bool
 */
function is_server_environment() {
    // Check if we're on localhost
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $server_name = $_SERVER['SERVER_NAME'] ?? '';
    
    // If host contains localhost, 127.0.0.1, or is empty, we're on localhost
    if (empty($host) || 
        strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false ||
        $host === 'localhost' ||
        $server_name === 'localhost' ||
        $server_name === '127.0.0.1') {
        return false;
    }
    
    return true;
}

/**
 * Check if we can write directly to uploads directory
 * @return bool
 */
function can_write_directly() {
    $uploadDir = __DIR__ . '/../' . PRODUCTS_UPLOAD_DIR;
    
    // Try to create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0755, true);
    }
    
    // Check if directory exists and is writable
    if (is_dir($uploadDir) && is_writable($uploadDir)) {
        // Try to write a test file
        $testFile = $uploadDir . '.test_write_' . time();
        $test = @file_put_contents($testFile, 'test');
        if ($test !== false) {
            @unlink($testFile);
            return true;
        }
    }
    
    return false;
}

/**
 * Upload file using server's upload.php endpoint
 * @param array $file $_FILES array element
 * @param string $subdirectory Subdirectory in uploads (e.g., 'products')
 * @return string|false Filename on success, false on failure
 */
function upload_via_server($file, $subdirectory = 'products') {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return false;
    }
    
    // Check if CURLFile class exists (PHP 5.5+)
    if (!class_exists('CURLFile')) {
        error_log("CURLFile not available - cannot upload via server endpoint");
        return false;
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    
    // Prepare file for upload
    $cfile = new CURLFile($file['tmp_name'], $file['type'], $fileName);
    
    // Try different possible field names that upload.php might expect
    $data = [
        'file' => $cfile,
        'upload' => $cfile,
        'image' => $cfile,
        'subdirectory' => $subdirectory
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SERVER_UPLOAD_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Upload via server failed: " . $error);
        return false;
    }
    
    // Try to parse response - upload.php might return JSON or HTML
    $responseData = json_decode($response, true);
    
    // If upload was successful (200 status), return the filename
    // The actual filename might be in the response, or we use our generated one
    if ($httpCode == 200) {
        // If response contains filename, use it; otherwise use generated name
        if (isset($responseData['filename'])) {
            return $responseData['filename'];
        } elseif (isset($responseData['file'])) {
            return basename($responseData['file']);
        } else {
            // Use our generated filename
            return $fileName;
        }
    }
    
    error_log("Upload via server returned HTTP code: $httpCode");
    return false;
}

/**
 * Upload file - handles both localhost and server environments
 * @param array $file $_FILES array element (e.g., $_FILES['product_image'])
 * @param string $subdirectory Subdirectory in uploads (e.g., 'products')
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null]
 */
function upload_file($file, $subdirectory = 'products') {
    if (empty($file['name']) || !isset($file['tmp_name'])) {
        return ['success' => false, 'filename' => null, 'error' => 'No file provided'];
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    
    // On server, try upload.php endpoint first (if available)
    if (is_server_environment() && function_exists('curl_init') && class_exists('CURLFile')) {
        $result = upload_via_server($file, $subdirectory);
        if ($result !== false) {
            return ['success' => true, 'filename' => $result, 'error' => null];
        }
        // If upload.php fails, fall through to direct upload attempt
    }
    
    // Try direct upload method (works on localhost and if server allows direct writes)
    if (can_write_directly()) {
        $uploadDir = __DIR__ . '/../' . UPLOADS_DIR . $subdirectory . '/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
        }
        
        $target = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            return ['success' => true, 'filename' => $fileName, 'error' => null];
        } else {
            return ['success' => false, 'filename' => null, 'error' => 'Failed to move uploaded file'];
        }
    }
    
    // If both methods failed, return error
    if (is_server_environment()) {
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Upload failed. Please ensure the uploads directory has write permissions or the upload endpoint is accessible at ' . SERVER_UPLOAD_URL
        ];
    } else {
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Upload failed. Please check directory permissions.'
        ];
    }
}

