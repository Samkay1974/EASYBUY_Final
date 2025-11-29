<?php
/**
 * Upload Configuration
 * Simple direct upload to server's uploads directory
 * Uses structure: uploads/u{user_id}/p{product_id}/
 */

/**
 * Upload file - simple direct upload
 * @param array $file $_FILES array element (e.g., $_FILES['product_image'])
 * @param int $user_id User ID
 * @param int $product_id Product ID (0 for new products)
 * @return array ['success' => bool, 'filename' => string|null, 'error' => string|null, 'path' => string|null]
 */
function upload_file($file, $user_id, $product_id = 0) {
    if (empty($file['name']) || !isset($file['tmp_name'])) {
        return ['success' => false, 'filename' => null, 'error' => 'No file provided', 'path' => null];
    }
    
    // Validate file was uploaded successfully
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'filename' => null, 'error' => 'Invalid file upload', 'path' => null];
    }
    
    // Validate file extension
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_extensions)) {
        return ['success' => false, 'filename' => null, 'error' => 'Invalid file type. Allowed: ' . implode(', ', $allowed_extensions), 'path' => null];
    }
    
    // Generate unique filename
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    
    // Build directory structure: uploads/u{user_id}/p{product_id}/
    $baseUploads = __DIR__ . '/../uploads';
    $userDir = $baseUploads . "/u{$user_id}";
    
    // For new products (product_id = 0), upload to user directory
    // For existing products, upload to product directory
    if ($product_id > 0) {
        $productDir = $userDir . "/p{$product_id}";
        $uploadDir = $productDir;
        $relativePath = "u{$user_id}/p{$product_id}/{$fileName}";
    } else {
        $uploadDir = $userDir;
        $relativePath = "u{$user_id}/{$fileName}";
    }
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0775, true)) {
            // Try with 0777 permissions as fallback
            if (!@mkdir($uploadDir, 0777, true)) {
                error_log("Failed to create upload directory: $uploadDir");
                return [
                    'success' => false, 
                    'filename' => null, 
                    'error' => 'Failed to create upload directory. Please check directory permissions.',
                    'path' => null
                ];
            }
        }
    }
    
    // Check if directory is writable
    if (!is_writable($uploadDir)) {
        // Try to change permissions
        @chmod($uploadDir, 0775);
        if (!is_writable($uploadDir)) {
            @chmod($uploadDir, 0777);
            if (!is_writable($uploadDir)) {
                $currentPerms = substr(sprintf('%o', fileperms($uploadDir)), -4);
                error_log("Upload directory is not writable: $uploadDir (current permissions: $currentPerms)");
                return [
                    'success' => false, 
                    'filename' => null, 
                    'error' => 'Upload directory is not writable (current permissions: ' . $currentPerms . '). Please set directory permissions to 775 or 777.',
                    'path' => null
                ];
            }
        }
    }
    
    $target = $uploadDir . '/' . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Verify file was actually written
        if (file_exists($target) && filesize($target) > 0) {
            // Set file permissions
            @chmod($target, 0644);
            return [
                'success' => true, 
                'filename' => $fileName, 
                'error' => null,
                'path' => $relativePath
            ];
        } else {
            error_log("File was moved but doesn't exist or is empty: $target");
            return ['success' => false, 'filename' => null, 'error' => 'File upload verification failed', 'path' => null];
        }
    } else {
        $error = error_get_last();
        error_log("Failed to move uploaded file to $target. Error: " . ($error['message'] ?? 'Unknown'));
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Failed to save uploaded file. Please check directory permissions.',
            'path' => null
        ];
    }
}

/**
 * Move uploaded file from user directory to product directory
 * Used when a new product is created and we now have the product_id
 * @param string $oldPath Relative path from uploads (e.g., "u1/filename.jpg")
 * @param int $user_id User ID
 * @param int $product_id Product ID
 * @return string|false New relative path on success, false on failure
 */
function move_file_to_product_dir($oldPath, $user_id, $product_id) {
    if (empty($oldPath) || $product_id <= 0) {
        return false;
    }
    
    $baseUploads = __DIR__ . '/../uploads';
    $oldFullPath = $baseUploads . '/' . $oldPath;
    
    // Extract filename from old path
    $fileName = basename($oldPath);
    
    // Build new path
    $userDir = $baseUploads . "/u{$user_id}";
    $productDir = $userDir . "/p{$product_id}";
    $newPath = $productDir . '/' . $fileName;
    $newRelativePath = "u{$user_id}/p{$product_id}/{$fileName}";
    
    // Create product directory if it doesn't exist
    if (!is_dir($productDir)) {
        if (!@mkdir($productDir, 0775, true)) {
            error_log("Failed to create product directory: $productDir");
            return false;
        }
    }
    
    // Move file if it exists
    if (file_exists($oldFullPath)) {
        if (@rename($oldFullPath, $newPath)) {
            return $newRelativePath;
        } else {
            error_log("Failed to move file from $oldFullPath to $newPath");
            return false;
        }
    }
    
    return false;
}
