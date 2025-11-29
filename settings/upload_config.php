<?php
/**
 * Upload Configuration
 * Handles file uploads for both localhost and server environments
 * Uses the server's existing uploads directory structure
 */

// Upload directory configuration
define('UPLOADS_DIR', 'uploads/');
define('PRODUCTS_UPLOAD_DIR', 'uploads/products/');


/**
 * Upload file - uses direct upload to server's existing uploads directory
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
    
    // Use server's existing uploads directory
    $uploadDir = __DIR__ . '/../' . UPLOADS_DIR . $subdirectory . '/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0755, true)) {
            error_log("Failed to create upload directory: $uploadDir");
            return [
                'success' => false, 
                'filename' => null, 
                'error' => 'Failed to create upload directory. Please check directory permissions.'
            ];
        }
    }
    
    // Check if directory exists and is writable
    if (!is_dir($uploadDir)) {
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Upload directory does not exist: ' . $uploadDir
        ];
    }
    
    if (!is_writable($uploadDir)) {
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Upload directory is not writable. Please check directory permissions for: ' . $uploadDir
        ];
    }
    
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
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Failed to move uploaded file. Check directory permissions for: ' . $uploadDir
        ];
    }
}

