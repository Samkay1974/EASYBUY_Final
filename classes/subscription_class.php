<?php
require_once __DIR__ . '/../settings/db_class.php';

class Subscription extends db_connection
{
    /**
     * Create a new subscription
     */
    public function create_subscription($user_id, $plan_type, $amount, $payment_reference = null)
    {
        try {
            $this->db_connect();
            $sql = "INSERT INTO subscriptions (user_id, plan_type, amount, payment_status, payment_reference, starts_at, created_at)
                    VALUES (:user_id, :plan_type, :amount, 'pending', :payment_reference, NOW(), NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':plan_type' => $plan_type,
                ':amount' => $amount,
                ':payment_reference' => $payment_reference
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating subscription: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active subscription for a user
     */
    public function get_active_subscription($user_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM subscriptions 
                    WHERE user_id = :user_id 
                    AND status = 'active' 
                    AND payment_status = 'paid'
                    AND (expires_at IS NULL OR expires_at > NOW())
                    ORDER BY created_at DESC 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting active subscription: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subscription by ID
     */
    public function get_subscription_by_id($subscription_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM subscriptions WHERE subscription_id = :subscription_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':subscription_id' => $subscription_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting subscription by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subscription by payment reference
     */
    public function get_subscription_by_reference($payment_reference)
    {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM subscriptions WHERE payment_reference = :reference LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':reference' => $payment_reference]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting subscription by reference: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update subscription payment status
     */
    public function update_payment_status($subscription_id, $payment_status, $payment_reference = null)
    {
        try {
            $this->db_connect();
            $sql = "UPDATE subscriptions 
                    SET payment_status = :payment_status, updated_at = NOW()";
            
            if ($payment_reference) {
                $sql .= ", payment_reference = :payment_reference";
            }
            
            if ($payment_status == 'paid') {
                $sql .= ", status = 'active', expires_at = DATE_ADD(NOW(), INTERVAL 6 MONTH)";
            }
            
            $sql .= " WHERE subscription_id = :subscription_id";
            
            $stmt = $this->db->prepare($sql);
            $params = [
                ':subscription_id' => $subscription_id,
                ':payment_status' => $payment_status
            ];
            
            if ($payment_reference) {
                $params[':payment_reference'] = $payment_reference;
            }
            
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating subscription payment status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record subscription payment
     */
    public function record_payment($subscription_id, $user_id, $amount, $payment_reference, $payment_status = 'paid')
    {
        try {
            $this->db_connect();
            $sql = "INSERT INTO subscription_payments 
                    (subscription_id, user_id, amount, payment_reference, payment_status, payment_method, created_at, paid_at)
                    VALUES (:subscription_id, :user_id, :amount, :payment_reference, :payment_status, 'paystack', NOW(), 
                    " . ($payment_status == 'paid' ? 'NOW()' : 'NULL') . ")";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':subscription_id' => $subscription_id,
                ':user_id' => $user_id,
                ':amount' => $amount,
                ':payment_reference' => $payment_reference,
                ':payment_status' => $payment_status
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error recording subscription payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all subscriptions for a user
     */
    public function get_user_subscriptions($user_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM subscriptions 
                    WHERE user_id = :user_id 
                    ORDER BY created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user subscriptions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Check if user has active subscription
     */
    public function has_active_subscription($user_id)
    {
        $subscription = $this->get_active_subscription($user_id);
        return $subscription !== false && !empty($subscription);
    }

    /**
     * Get product count for user
     */
    public function get_user_product_count($user_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT COUNT(*) as count FROM products WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['count'] : 0;
        } catch (PDOException $e) {
            error_log("Error getting user product count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Check if user can create more products
     */
    public function can_create_product($user_id)
    {
        $product_count = $this->get_user_product_count($user_id);
        
        // Free tier: 3 products
        if ($product_count < 3) {
            return true;
        }
        
        // After 3 products, need active subscription
        return $this->has_active_subscription($user_id);
    }
}

