<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/product_controller.php';

if (!isLoggedIn()) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$user_id = get_user_id();
$product_id = intval($_POST['product_id'] ?? 0);

if ($product_id <= 0) { echo json_encode(['status'=>'error','message'=>'Invalid product']); exit; }

$ok = delete_product_ctr($product_id, $user_id);
if ($ok) echo json_encode(['status'=>'success','message'=>'Product deleted']);
else echo json_encode(['status'=>'error','message'=>'Could not delete product']);
