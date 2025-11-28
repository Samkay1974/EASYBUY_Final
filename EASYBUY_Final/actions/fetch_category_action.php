<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../controllers/category_controller.php';

if (!isLoggedIn()) {
    echo json_encode([]);
    exit;
}

$user_id = get_user_id();
$rows = get_categories_by_user_ctr($user_id);

$out = [];
foreach ((array)$rows as $r) {
    $item = $r;
    if (!isset($item['cat_id'])) {
        if (isset($item['cat'])) $item['cat_id'] = $item['cat'];
    }
    $out[] = $item;
}

echo json_encode($out);
exit;

?>
