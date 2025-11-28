<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
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
    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target = $uploadDir . $fileName;
    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) $product_image = $fileName;
}

$ok = update_product_ctr($product_id, $user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image);

if ($ok) echo json_encode(['status'=>'success','message'=>'Product updated']);
else echo json_encode(['status'=>'error','message'=>'Could not update product']);
