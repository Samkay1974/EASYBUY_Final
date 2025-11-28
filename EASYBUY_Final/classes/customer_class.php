<?php

require_once __DIR__ . '/../settings/db_class.php';

class Customer extends db_connection {

    public function __construct()
    {
        parent::__construct();
    }

    // Register a customer using PDO
    public function register_customer($full_name, $customer_email, $password, $city = null, $country = null, $phone_number = null, $user_role = 0)
    {
        $pdo = $this->db;
        $sql = 'INSERT INTO users (full_name, email, password, city, country, phone, role) VALUES (:full_name, :email, :password, :city, :country, :phone, :role)';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':full_name' => $full_name,
            ':email' => $customer_email,
            ':password' => $password,
            ':city' => $city,
            ':country' => $country,
            ':phone' => $phone_number,
            ':role' => $user_role
        ]);
    }

    // Login customer: return associative array of user row or false
    public function login_customer($email)
    {
        $pdo = $this->db;
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }

    public function delete_user($user_id){
        try {
            $this->db_connect();
            $sql = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':user_id' => $user_id]);
        } catch (PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }

    public function get_user_by_email($email) {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by email: " . $e->getMessage());
            return false;
        }
    }

    public function get_user_by_id($user_id) {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM users WHERE id = :user_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return false;
        }
    }

    public function save_reset_token($email, $token, $expires_at) {
        try {
            $this->db_connect();
            // Delete any existing tokens for this email
            $deleteSql = "DELETE FROM password_reset_tokens WHERE email = :email";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute([':email' => $email]);
            
            // Insert new token
            $sql = "INSERT INTO password_reset_tokens (email, token, expires_at) VALUES (:email, :token, :expires_at)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':email' => $email,
                ':token' => $token,
                ':expires_at' => $expires_at
            ]);
        } catch (PDOException $e) {
            error_log("Error saving reset token: " . $e->getMessage());
            return false;
        }
    }

    public function get_token_details($token) {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM password_reset_tokens WHERE token = :token LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting token details: " . $e->getMessage());
            return false;
        }
    }

    public function delete_token($token) {
        try {
            $this->db_connect();
            $sql = "DELETE FROM password_reset_tokens WHERE token = :token";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':token' => $token]);
        } catch (PDOException $e) {
            error_log("Error deleting token: " . $e->getMessage());
            return false;
        }
    }

    public function update_password_by_email($email, $password_hash) {
        try {
            $this->db_connect();
            $sql = "UPDATE users SET password = :password WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':password' => $password_hash,
                ':email' => $email
            ]);
        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }
    function get_all_customers($search = null){
        try {
            $this->db_connect();
            if ($search) {
                $sql = "SELECT * FROM users 
                        WHERE full_name LIKE :search 
                        OR email LIKE :search 
                        OR phone LIKE :search
                        ORDER BY id DESC";
                $stmt = $this->db->prepare($sql);
                $searchParam = '%' . $search . '%';
                $stmt->execute([':search' => $searchParam]);
            } else {
                $sql = "SELECT * FROM users ORDER BY id DESC";
                $stmt = $this->db->query($sql);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $e) {
            error_log("Error getting all customers: " . $e->getMessage());
            return false;
        }
    }
}
?>
