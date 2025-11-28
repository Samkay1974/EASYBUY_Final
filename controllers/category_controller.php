<?php
// controllers/category_controller.php
require_once __DIR__ . '/../classes/category_class.php';

/**
 * Add a category (controller)
 */
function add_category_ctr($user_id, $category_name)
{
    $category = new Category();
    return $category->add_category($user_id, $category_name);
}

/**
 * Fetch categories by user (controller)
 */
function get_categories_by_user_ctr($user_id)
{
    $category = new Category();
    return $category->get_categories_by_user($user_id);
}
function get_all_categories_ctr() {
    $category = new Category();
    return $category->get_all_categories();
}


/**
 * Update category (controller)
 */
function update_category_ctr($user_id, $category_id, $category_name)
{
    $category = new Category();
    return $category->update_category($user_id, $category_id, $category_name);
}

/**
 * Delete category (controller)
 */
function delete_category_ctr($user_id, $category_id)
{
    $category = new Category();
    return $category->delete_category($user_id, $category_id);
}

function get_category_by_name_ctr($name) {
    $cat = new Category();
    return $cat->get_category_by_name($name);
}

