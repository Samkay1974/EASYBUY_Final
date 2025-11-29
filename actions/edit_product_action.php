<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/upload_config.php';
require_once __DIR__ . '/../controllers/product_controller.php';

if (!isLoggedIn() || !check_user_role(1)) { 
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); 
    exit; 
}

$user_id = get_user_id();
$product_id = intval($_POST['product_id'] ?? 0);

if ($product_id <= 0) { 
    echo json_encode(['status'=>'error','message'=>'Invalid product']); 
    exit; 
}

// Validate required fields
$req = ['product_name','product_brand','product_cat','moq','wholesale_price'];
foreach ($req as $f) {
    if (empty($_POST[$f])) {
        echo json_encode(['status'=>'error','message'=>"Missing field: $f"]);
        exit;
    }
}

$product_name = trim($_POST['product_name'] ?? '');
$product_brand = intval($_POST['product_brand'] ?? 0);
$product_cat = intval($_POST['product_cat'] ?? 0);
$moq = intval($_POST['moq'] ?? 0);
$wholesale_price = floatval($_POST['wholesale_price'] ?? 0);

// image optional
$product_image = null;
if (!empty($_FILES['product_image']['name'])) {
    // Check for upload errors
    if ($_FILES['product_image']['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        $error_msg = $upload_errors[$_FILES['product_image']['error']] ?? 'Unknown upload error';
        echo json_encode(['status'=>'error','message'=>'Image upload error: ' . $error_msg]);
        exit;
    }
    
    // Upload directly to product directory (we have product_id)
    $upload_result = upload_file($_FILES['product_image'], $user_id, $product_id);
    
    if ($upload_result['success']) {
        $product_image = $upload_result['path']; // Store the relative path
    } else {
        // For edit, if upload fails, return error so user knows
        $error_msg = $upload_result['error'] ?? 'Image upload failed';
        error_log("Image upload failed during product edit: " . $error_msg);
        echo json_encode(['status'=>'error','message'=>'Failed to upload image: ' . $error_msg]);
        exit;
    }
}

$ok = update_product_ctr($product_id, $user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image);

if ($ok) echo json_encode(['status'=>'success','message'=>'Product updated']);
else echo json_encode(['status'=>'error','message'=>'Could not update product']);
