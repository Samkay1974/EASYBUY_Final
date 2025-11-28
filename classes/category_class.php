<?php
// classes/category_class.php
require_once __DIR__ . '/../settings/db_class.php';

class Category extends db_connection
{
    public function add_category($user_id, $category_name)
    {
        try {
            $db = $this->db; // PDO instance from db_connection

            // Check for duplicate
            $stmt = $db->prepare("SELECT COUNT(*) FROM categories WHERE cat_name = :name AND user_id = :uid");
            $stmt->execute([':name' => $category_name, ':uid' => $user_id]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                return false;
            }

            $insert = $db->prepare("INSERT INTO categories (cat_name, user_id) VALUES (:name, :uid)");
            return $insert->execute([':name' => $category_name, ':uid' => $user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Fetch all categories created by a user
     */
    public function get_categories_by_user($user_id)
    {
        try {
            $db = $this->db;
            $stmt = $db->prepare("SELECT * FROM categories WHERE user_id = :uid ORDER BY cat_id DESC");
            $stmt->execute([':uid' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_categories()
    {
        try {
            $db = $this->db;
            $stmt = $db->query("SELECT * FROM categories ORDER BY cat_name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }


    /**
     * Update a category name
     */
    public function update_category($user_id, $category_id, $category_name)
    {
        try {
            $db = $this->db;
            $stmt = $db->prepare("UPDATE categories SET cat_name = :name WHERE cat_id = :cid AND user_id = :uid");
            return $stmt->execute([':name' => $category_name, ':cid' => $category_id, ':uid' => $user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Delete a category
     */
    public function delete_category($user_id, $category_id)
    {
        try {
            $db = $this->db;
            $stmt = $db->prepare("DELETE FROM categories WHERE cat_id = :cid AND user_id = :uid");
            return $stmt->execute([':cid' => $category_id, ':uid' => $user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }


    public function get_category_by_name($name)
    {
        try {
            $db = $this->db;
            $stmt = $db->prepare("SELECT * FROM categories WHERE cat_name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}
