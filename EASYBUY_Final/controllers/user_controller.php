<?php
require_once __DIR__ . '/../settings/db_class.php';

require_once __DIR__ . '/../classes/customer_class.php';

function register_customer_controller($full_name, $customer_email, $password, $city = null, $country = null, $phone_number = null, $user_role = 0)
{
    $full_name = trim($full_name);
    $customer_email = trim(strtolower($customer_email));
    $user_role = intval($user_role);

    if (empty($full_name) || empty($customer_email) || empty($password)) {
        return false;
    }

    $db = new db_connection();
    $pdo = $db->db;

    try {
        // Check for existing email
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $customer_email]);
        if ($stmt->fetch()) {
            return false;
        }

        // Hash and insert
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = $pdo->prepare('INSERT INTO users (full_name, email, password, city, country, phone, role) VALUES (:full_name, :email, :password, :city, :country, :phone, :role)');
        $insert->execute([
            ':full_name' => $full_name,
            ':email' => $customer_email,
            ':password' => $hash,
            ':city' => $city ?: null,
            ':country' => $country ?: null,
            ':phone' => $phone_number ?: null,
            ':role' => $user_role
        ]);

        return true;
    } catch (PDOException $e) {
        error_log('User register error: ' . $e->getMessage());
        return false;
    }
}
function login_customer_ctr($email, $password) {
    $customer = new Customer();

    $result = $customer->login_customer($email);

    if ($result) {
        if (password_verify($password, $result['password'])) {
            return $result; // SUCCESS
        }
    }
    return false; // FAILED
}
function delete_user_ctr($user_id){
    $customer = new Customer();
    return $customer->delete_user($user_id);
}

function get_user_by_email_ctr($email) {
    $customer = new Customer();
    return $customer->get_user_by_email($email);
}

function get_user_by_id_ctr($user_id) {
    $customer = new Customer();
    return $customer->get_user_by_id($user_id);
}

function save_reset_token_ctr($email, $token, $expires_at) {
    $customer = new Customer();
    return $customer->save_reset_token($email, $token, $expires_at);
}

function get_token_details_ctr($token) {
    $customer = new Customer();
    return $customer->get_token_details($token);
}

function delete_token_ctr($token) {
    $customer = new Customer();
    return $customer->delete_token($token);
}

function update_password_by_email_ctr($email, $password_hash) {
    $customer = new Customer();
    return $customer->update_password_by_email($email, $password_hash);
}

function get_orders_by_user_ctr($user_id) {
    require_once __DIR__ . '/order_controller.php';
    return get_orders_for_customer_ctr($user_id);
}

function get_all_users_ctr($search = null) {
    $customer = new Customer();
    return $customer->get_all_customers($search);
}

?>
