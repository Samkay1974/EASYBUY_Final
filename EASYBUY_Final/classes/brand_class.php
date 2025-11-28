<?php
require_once __DIR__ . '/../settings/db_class.php';

class Brand extends db_connection
{
    public function add_brand($user_id, $brand_name)
    {
        try {
            $db = $this->db;

            // Check duplicate
            $stmt = $db->prepare("SELECT COUNT(*) FROM brands WHERE brand_name = :name AND user_id = :uid");
            $stmt->execute([':name' => $brand_name, ':uid' => $user_id]);

            if ($stmt->fetchColumn() > 0) {
                return false;
            }

            // Insert brand
            $insert = $db->prepare("
                INSERT INTO brands (brand_name, user_id)
                VALUES (:name, :uid)
            ");

            if ($insert->execute([':name' => $brand_name, ':uid' => $user_id])) {
                return $db->lastInsertId(); // Always returns correct ID
            }

            return false;

        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_brands_by_user($user_id)
    {
        try {
            $db = $this->db;
            $stmt = $db->prepare("SELECT * FROM brands WHERE user_id = :uid ORDER BY brand_id DESC");
            $stmt->execute([':uid' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_all_brands()
    {
        try {
            $db = $this->db;
            $stmt = $db->query("SELECT * FROM brands ORDER BY brand_name ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function update_brand($user_id, $brand_id, $brand_name)
    {
        try {
            $db = $this->db;

            // Check duplicate
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM brands 
                WHERE brand_name = :name 
                AND user_id = :uid
                AND brand_id != :bid
            ");
            $stmt->execute([':name' => $brand_name, ':uid' => $user_id, ':bid' => $brand_id]);

            if ($stmt->fetchColumn() > 0) return false;

            // Update
            $update = $db->prepare("
                UPDATE brands 
                SET brand_name = :name 
                WHERE brand_id = :bid AND user_id = :uid
            ");

            return $update->execute([
                ':name' => $brand_name,
                ':bid' => $brand_id,
                ':uid' => $user_id
            ]);

        } catch (PDOException $e) {
            return false;
        }
    }

    public function delete_brand($user_id, $brand_id)
    {
        try {
            $db = $this->db;
            $stmt = $db->prepare("
                DELETE FROM brands 
                WHERE brand_id = :bid AND user_id = :uid
            ");
            return $stmt->execute([':bid' => $brand_id, ':uid' => $user_id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    public function get_brand_by_name($name)
    {
        try {
            $db = $this->db;
            $stmt = $db->prepare("SELECT * FROM brands WHERE brand_name = :name LIMIT 1");
            $stmt->execute([':name' => $name]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }
}
