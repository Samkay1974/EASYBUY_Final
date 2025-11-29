<?php
/**
 * Upload Configuration
 * Handles file uploads for both localhost and server environments
 * Uses the server's existing uploads directory structure
 */

// Upload directory configuration
define('UPLOADS_DIR', 'uploads/');
define('PRODUCTS_UPLOAD_DIR', 'uploads/products/');

// Optional: Override upload directory path for server-specific setups
// Uncomment and set the absolute path if the default detection doesn't work
// define('CUSTOM_UPLOAD_PATH', '/path/to/your/uploads/');


/**
 * Get the base upload directory path
 * Tries multiple possible locations including server's existing uploads folder
 * @return string|false Base upload directory path or false if none found
 */
function get_upload_base_dir() {
    // If custom path is defined, use it first
    if (defined('CUSTOM_UPLOAD_PATH') && !empty(CUSTOM_UPLOAD_PATH)) {
        $customPath = rtrim(CUSTOM_UPLOAD_PATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (is_dir($customPath) || @mkdir($customPath, 0775, true)) {
            return $customPath;
        }
    }
    
    // Try different possible base paths
    $possiblePaths = [
        // Relative to settings directory (most common)
        __DIR__ . '/../' . UPLOADS_DIR,
        // Absolute path from document root
        $_SERVER['DOCUMENT_ROOT'] . '/' . UPLOADS_DIR,
        // If DOCUMENT_ROOT contains the project folder
        $_SERVER['DOCUMENT_ROOT'] . '/EASYBUY_Final/' . UPLOADS_DIR,
        // Try using realpath
        realpath(__DIR__ . '/../') . '/' . UPLOADS_DIR,
    ];
    
    // Check if server has upload.php in common locations and use its directory
    $uploadPhpPaths = [
        __DIR__ . '/../upload.php',
        $_SERVER['DOCUMENT_ROOT'] . '/upload.php',
        dirname($_SERVER['SCRIPT_FILENAME']) . '/upload.php',
    ];
    
    foreach ($uploadPhpPaths as $uploadPhpPath) {
        if (file_exists($uploadPhpPath)) {
            // If upload.php exists, check for uploads folder in same directory
            $uploadPhpDir = dirname($uploadPhpPath);
            $uploadsInSameDir = $uploadPhpDir . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
            if (is_dir($uploadsInSameDir)) {
                array_unshift($possiblePaths, $uploadsInSameDir);
            }
        }
    }
    
    // Check for user home directory structure (common on shared hosting)
    if (isset($_SERVER['HOME'])) {
        $possiblePaths[] = $_SERVER['HOME'] . '/public_html/uploads/';
        $possiblePaths[] = $_SERVER['HOME'] . '/uploads/';
    }
    
    // Check for common shared hosting paths
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $docRoot = $_SERVER['DOCUMENT_ROOT'];
        // Check if we're in a user directory (e.g., ~username/public_html)
        if (preg_match('/([^\/]+)\/public_html/', $docRoot, $matches)) {
            $possiblePaths[] = dirname($docRoot) . '/uploads/';
        }
    }
    
    foreach ($possiblePaths as $path) {
        $normalizedPath = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        
        // Try to create if it doesn't exist
        if (!is_dir($normalizedPath)) {
            if (@mkdir($normalizedPath, 0775, true)) {
                return $normalizedPath;
            }
        } else {
            // Directory exists, check if it's writable
            if (is_writable($normalizedPath)) {
                return $normalizedPath;
            } else {
                // Try to make it writable
                @chmod($normalizedPath, 0775);
                if (is_writable($normalizedPath)) {
                    return $normalizedPath;
                }
            }
        }
    }
    
    return false;
}

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
    
    // Get base upload directory
    $baseDir = get_upload_base_dir();
    if ($baseDir === false) {
        // Log all attempted paths for debugging
        $debugPaths = [
            __DIR__ . '/../' . UPLOADS_DIR,
            $_SERVER['DOCUMENT_ROOT'] . '/' . UPLOADS_DIR,
            $_SERVER['DOCUMENT_ROOT'] . '/EASYBUY_Final/' . UPLOADS_DIR,
        ];
        $debugInfo = [];
        foreach ($debugPaths as $path) {
            $normalized = rtrim(str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $exists = is_dir($normalized) ? 'exists' : 'not found';
            $writable = is_dir($normalized) && is_writable($normalized) ? 'writable' : 'not writable';
            $perms = is_dir($normalized) ? substr(sprintf('%o', fileperms($normalized)), -4) : 'N/A';
            $debugInfo[] = "$normalized ($exists, $writable, perms: $perms)";
        }
        error_log("Could not find or create base upload directory. Tried: " . implode(' | ', $debugInfo));
        // Simplified error message without paths
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Could not access uploads directory. Please ensure the uploads folder exists at the project root and has write permissions (775 or 777). Check server error logs for detailed path information.'
        ];
    }
    
    // Build full upload directory path
    $uploadDir = $baseDir . $subdirectory . DIRECTORY_SEPARATOR;
    
    // Normalize path separators
    $uploadDir = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $uploadDir);
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0775, true)) {
            $error = error_get_last();
            error_log("Failed to create upload directory: $uploadDir. Error: " . ($error['message'] ?? 'Unknown'));
            
            // Try with 0777 permissions as fallback
            if (!@mkdir($uploadDir, 0777, true)) {
                // Don't include full path in user-facing error
                return [
                    'success' => false, 
                    'filename' => null, 
                    'error' => 'Failed to create upload directory. Please create the uploads/products directory manually via FTP or cPanel File Manager and set permissions to 775 or 777.'
                ];
            }
        }
    }
    
    // Check if directory exists
    if (!is_dir($uploadDir)) {
        // Don't include full path in user-facing error
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Upload directory does not exist. Please create the uploads/products directory via FTP or cPanel File Manager.'
        ];
    }
    
    // Try to make directory writable if it's not
    if (!is_writable($uploadDir)) {
        // Try to change permissions
        @chmod($uploadDir, 0775);
        
        // Check again
        if (!is_writable($uploadDir)) {
            // Try 0777 as last resort
            @chmod($uploadDir, 0777);
            
            if (!is_writable($uploadDir)) {
                $currentPerms = substr(sprintf('%o', fileperms($uploadDir)), -4);
                error_log("Upload directory is not writable: $uploadDir (current permissions: $currentPerms)");
                // Don't include full path in user-facing error to avoid URL interpretation issues
                return [
                    'success' => false, 
                    'filename' => null, 
                    'error' => 'Upload directory is not writable (current permissions: ' . $currentPerms . '). Please set the uploads/products directory permissions to 775 or 777 via FTP or cPanel File Manager.'
                ];
            }
        }
    }
    
    $target = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $target)) {
        // Verify file was actually written
        if (file_exists($target) && filesize($target) > 0) {
            // Set file permissions
            @chmod($target, 0644);
            return ['success' => true, 'filename' => $fileName, 'error' => null];
        } else {
            error_log("File was moved but doesn't exist or is empty: $target");
            return ['success' => false, 'filename' => null, 'error' => 'File upload verification failed'];
        }
    } else {
        $error = error_get_last();
        error_log("Failed to move uploaded file to $target. Error: " . ($error['message'] ?? 'Unknown'));
        error_log("Upload directory info - exists: " . (is_dir($uploadDir) ? 'yes' : 'no') . ", writable: " . (is_writable($uploadDir) ? 'yes' : 'no'));
        // Don't include full path in user-facing error
        return [
            'success' => false, 
            'filename' => null, 
            'error' => 'Failed to save uploaded file. Please check that the uploads/products directory exists and has write permissions (775 or 777).'
        ];
    }
}

