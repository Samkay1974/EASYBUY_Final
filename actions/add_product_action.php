<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/upload_config.php';
require_once __DIR__ . '/../controllers/product_controller.php';
require_once __DIR__ . '/../controllers/subscription_controller.php';

// ensure logged in and wholesaler (role 1)
if (!isLoggedIn() || !check_user_role(1)) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$user_id = get_user_id();

// Check if user can create more products (subscription check)
if (!can_create_product_ctr($user_id)) {
    $product_count = get_user_product_count_ctr($user_id);
    echo json_encode([
        'status' => 'error',
        'message' => 'You have reached the limit of 3 free products. Please subscribe to create more products.',
        'requires_subscription' => true,
        'product_count' => $product_count
    ]);
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

// sanitize
$product_name = trim($_POST['product_name']);
$product_brand = intval($_POST['product_brand']);
$product_cat = intval($_POST['product_cat']);
$moq = intval($_POST['moq']);
$wholesale_price = floatval($_POST['wholesale_price']);

// handle image upload
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
    
    $upload_result = upload_file($_FILES['product_image'], 'products');
    
    if ($upload_result['success']) {
        $product_image = $upload_result['filename'];
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => $upload_result['error'] ?? 'Image upload failed'
        ]);
        exit;
    }
}

$insertId = add_product_ctr($user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image);
if ($insertId) {
    echo json_encode(['status'=>'success','message'=>'Product added','id'=>$insertId]);
} else {
    echo json_encode(['status'=>'error','message'=>'Could not add product']);
}
