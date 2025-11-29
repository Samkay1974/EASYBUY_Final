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
        error_log("Upload via server: Invalid file or not uploaded file");
        return false;
    }
    
    // Check if file exists and is readable
    if (!file_exists($file['tmp_name']) || !is_readable($file['tmp_name'])) {
        error_log("Upload via server: File not readable: " . $file['tmp_name']);
        return false;
    }
    
    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    
    // Read file content
    $fileContent = file_get_contents($file['tmp_name']);
    if ($fileContent === false) {
        error_log("Upload via server: Failed to read file content");
        return false;
    }
    
    $boundary = null;
    $headers = [];
    
    // Check if CURLFile class exists (PHP 5.5+)
    if (class_exists('CURLFile')) {
        // Use CURLFile for better compatibility
        $cfile = new CURLFile($file['tmp_name'], $file['type'], $fileName);
        
        // Try different possible field names that upload.php might expect
        $data = [
            'file' => $cfile,
            'upload' => $cfile,
            'image' => $cfile,
            'product_image' => $cfile,
            'subdirectory' => $subdirectory
        ];
    } else {
        // Fallback: send as multipart/form-data manually
        $boundary = uniqid();
        $delimiter = '-------------' . $boundary;
        
        $postData = '';
        $postData .= "--" . $delimiter . "\r\n";
        $postData .= 'Content-Disposition: form-data; name="file"; filename="' . $fileName . '"' . "\r\n";
        $postData .= 'Content-Type: ' . $file['type'] . "\r\n\r\n";
        $postData .= $fileContent . "\r\n";
        $postData .= "--" . $delimiter . "\r\n";
        $postData .= 'Content-Disposition: form-data; name="subdirectory"' . "\r\n\r\n";
        $postData .= $subdirectory . "\r\n";
        $postData .= "--" . $delimiter . "--\r\n";
        
        $data = $postData;
        $headers[] = 'Content-Type: multipart/form-data; boundary=-------------' . $boundary;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, SERVER_UPLOAD_URL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // Set headers if using manual multipart
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $curlInfo = curl_getinfo($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("Upload via server failed (cURL error): " . $error);
        return false;
    }
    
    // Log response for debugging
    error_log("Upload via server response (HTTP $httpCode): " . substr($response, 0, 500));
    
    // Try to parse response - upload.php might return JSON or HTML
    $responseData = json_decode($response, true);
    
    // If upload was successful (200 status), return the filename
    if ($httpCode == 200) {
        // If response contains filename, use it; otherwise use generated name
        if (is_array($responseData)) {
            if (isset($responseData['filename'])) {
                return $responseData['filename'];
            } elseif (isset($responseData['file'])) {
                return basename($responseData['file']);
            } elseif (isset($responseData['success']) && $responseData['success']) {
                // If success is true, use generated filename
                return $fileName;
            }
        } else {
            // Response might be plain text with filename or HTML
            // Try to extract filename from response
            if (preg_match('/filename["\']?\s*[:=]\s*["\']?([^"\'\s]+)/i', $response, $matches)) {
                return basename($matches[1]);
            } elseif (preg_match('/([a-f0-9_]+\.(jpg|jpeg|png|gif|webp))/i', $response, $matches)) {
                return $matches[1];
            }
        }
        
        // If we can't parse the response but got 200, assume success with generated filename
        return $fileName;
    }
    
    error_log("Upload via server returned HTTP code: $httpCode, Response: " . substr($response, 0, 200));
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
    
    // Validate file was uploaded successfully
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'filename' => null, 'error' => 'Invalid file upload'];
    }
    
    // Validate file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        return ['success' => false, 'filename' => null, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions)];
    }
    
    // Generate unique filename
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    
    // On server, try upload.php endpoint first (if available)
    if (is_server_environment() && function_exists('curl_init')) {
        $result = upload_via_server($file, $subdirectory);
        if ($result !== false && !empty($result)) {
            return ['success' => true, 'filename' => $result, 'error' => null];
        }
        // If upload.php fails, fall through to direct upload attempt
        error_log("Server upload endpoint failed, trying direct upload");
    }
    
    // Try direct upload method (works on localhost and if server allows direct writes)
    $uploadDir = __DIR__ . '/../' . UPLOADS_DIR . $subdirectory . '/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: $uploadDir");
        }
    }
    
    // Check if directory exists and is writable
    if (is_dir($uploadDir) && is_writable($uploadDir)) {
        $target = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $target)) {
            // Verify file was actually written
            if (file_exists($target) && filesize($target) > 0) {
                return ['success' => true, 'filename' => $fileName, 'error' => null];
            } else {
                error_log("File was moved but doesn't exist or is empty: $target");
                return ['success' => false, 'filename' => null, 'error' => 'File upload verification failed'];
            }
        } else {
            $error = error_get_last();
            error_log("Failed to move uploaded file to $target. Error: " . ($error['message'] ?? 'Unknown'));
            return ['success' => false, 'filename' => null, 'error' => 'Failed to move uploaded file. Check directory permissions.'];
        }
    }
    
    // If both methods failed, return error
    if (is_server_environment()) {
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Upload failed. Server upload endpoint returned error and direct upload is not available. Please check: 1) Upload endpoint at ' . SERVER_UPLOAD_URL . ' is accessible, 2) Uploads directory has write permissions.'
        ];
    } else {
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Upload failed. Please check directory permissions for: ' . $uploadDir
        ];
    }
}

