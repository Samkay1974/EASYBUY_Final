<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';

// ensure logged in and wholesaler (role 1)
if (!isLoggedIn() || !check_user_role(1)) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']);
    exit;
}

$user_id = get_user_id();

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
    $uploadDir = __DIR__ . '/../uploads/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
    $fileName = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $target = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target)) {
        $product_image = $fileName;
    } else {
        echo json_encode(['status'=>'error','message'=>'Image upload failed']);
        exit;
    }
}

$insertId = add_product_ctr($user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image);
if ($insertId) {
    echo json_encode(['status'=>'success','message'=>'Product added','id'=>$insertId]);
} else {
    echo json_encode(['status'=>'error','message'=>'Could not add product']);
}
