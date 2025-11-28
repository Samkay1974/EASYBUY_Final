<?php
require_once __DIR__ . '/../classes/product_class.php';

function add_product_ctr($user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image)
{
    $p = new Product();
    return $p->add_product($user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image);
}

function get_all_products_ctr()
{
    $p = new Product();
    return $p->get_all_products();
}

function get_products_by_user_ctr($user_id)
{
    $p = new Product();
    return $p->get_products_by_user($user_id);
}

function get_one_product_ctr($product_id)
{
    $p = new Product();
    return $p->get_one_product($product_id);
}

function update_product_ctr($product_id, $user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image = null)
{
    $p = new Product();
    return $p->update_product($product_id, $user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image);
}

function delete_product_ctr($product_id, $user_id)
{
    $p = new Product();
    return $p->delete_product($product_id, $user_id);
}
