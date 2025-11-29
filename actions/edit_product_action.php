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
    $upload_result = upload_file($_FILES['product_image'], 'products');
    
    if ($upload_result['success']) {
        $product_image = $upload_result['filename'];
    } else {
        // For edit, if upload fails, we can continue without updating the image
        // But log the error
        error_log("Image upload failed during product edit: " . ($upload_result['error'] ?? 'Unknown error'));
    }
}

$ok = update_product_ctr($product_id, $user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image);

if ($ok) echo json_encode(['status'=>'success','message'=>'Product updated']);
else echo json_encode(['status'=>'error','message'=>'Could not update product']);
