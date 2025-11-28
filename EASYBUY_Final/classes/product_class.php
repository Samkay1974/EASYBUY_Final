<?php
require_once __DIR__ . '/../settings/db_class.php';

class Product extends db_connection
{
    public function add_product($user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image)
    {
        try {
            $this->db_connect();
            $sql = "INSERT INTO products (user_id, product_name, product_brand, product_cat, moq, wholesale_price, product_image)
                    VALUES (:uid, :name, :brand, :cat, :moq, :price, :img)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':uid'   => $user_id,
                ':name'  => $product_name,
                ':brand' => $product_brand,
                ':cat'   => $product_cat,
                ':moq'   => $moq,
                ':price' => $wholesale_price,
                ':img'   => $product_image
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            // log $e->getMessage();
            return false;
        }
    }

    public function get_all_products()
    {
        try {
            $this->db_connect();
            $sql = "SELECT p.*, b.brand_name, c.cat_name, u.full_name AS wholesaler_name
                    FROM products p
                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                    LEFT JOIN users u ON p.user_id = u.id
                    ORDER BY p.product_id DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log error for debugging
            error_log("Error in get_all_products: " . $e->getMessage());
            return [];
        }
    }

    public function get_products_by_user($user_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT p.*, b.brand_name, c.cat_name
                    FROM products p
                    LEFT JOIN brands b ON p.product_brand = b.brand_id
                    LEFT JOIN categories c ON p.product_cat = c.cat_id
                    WHERE p.user_id = :uid
                    ORDER BY p.product_id DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':uid' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function get_one_product($product_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM products WHERE product_id = :pid LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':pid' => $product_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function update_product($product_id, $user_id, $product_name, $product_brand, $product_cat, $moq, $wholesale_price, $product_image = null)
    {
        try {
            $this->db_connect();
            if ($product_image) {
                $sql = "UPDATE products SET product_name = :name, product_brand = :brand, product_cat = :cat, moq = :moq, wholesale_price = :price, product_image = :img
                        WHERE product_id = :pid AND user_id = :uid";
                $params = [
                    ':name'  => $product_name,
                    ':brand' => $product_brand,
                    ':cat'   => $product_cat,
                    ':moq'   => $moq,
                    ':price' => $wholesale_price,
                    ':img'   => $product_image,
                    ':pid'   => $product_id,
                    ':uid'   => $user_id
                ];
            } else {
                $sql = "UPDATE products SET product_name = :name, product_brand = :brand, product_cat = :cat, moq = :moq, wholesale_price = :price
                        WHERE product_id = :pid AND user_id = :uid";
                $params = [
                    ':name'  => $product_name,
                    ':brand' => $product_brand,
                    ':cat'   => $product_cat,
                    ':moq'   => $moq,
                    ':price' => $wholesale_price,
                    ':pid'   => $product_id,
                    ':uid'   => $user_id
                ];
            }
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_product($product_id, $user_id)
    {
        try {
            $this->db_connect();
            $sql = "DELETE FROM products WHERE product_id = :pid AND user_id = :uid";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':pid' => $product_id, ':uid' => $user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
