<?php
require_once __DIR__ . '/../settings/db_class.php';

class Order extends db_connection
{
    /**
     * Create a new order
     */
    public function create_order($customer_id, $total_amount, $transaction_fee, $final_amount, $collaboration_id = null)
    {
        try {
            $this->db_connect();
            $sql = "INSERT INTO orders (customer_id, collaboration_id, total_amount, transaction_fee, final_amount, status, payment_status, created_at)
                    VALUES (:customer_id, :collaboration_id, :total_amount, :transaction_fee, :final_amount, 'pending', 'pending', NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':collaboration_id' => $collaboration_id,
                ':total_amount' => $total_amount,
                ':transaction_fee' => $transaction_fee,
                ':final_amount' => $final_amount
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add order detail (product in order)
     */
    public function add_order_detail($order_id, $product_id, $wholesaler_id, $quantity, $unit_price, $subtotal)
    {
        try {
            $this->db_connect();
            $sql = "INSERT INTO order_details (order_id, product_id, wholesaler_id, quantity, unit_price, subtotal, created_at)
                    VALUES (:order_id, :product_id, :wholesaler_id, :quantity, :unit_price, :subtotal, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':wholesaler_id' => $wholesaler_id,
                ':quantity' => $quantity,
                ':unit_price' => $unit_price,
                ':subtotal' => $subtotal
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error adding order detail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order by ID
     */
    public function get_order_by_id($order_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT o.*, u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
                    FROM orders o
                    LEFT JOIN users u ON o.customer_id = u.id
                    WHERE o.order_id = :order_id
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':order_id' => $order_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get last order for a customer
     */
    public function get_last_order($customer_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM orders 
                    WHERE customer_id = :customer_id 
                    ORDER BY order_id DESC 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':customer_id' => $customer_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting last order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get order details (products in order)
     */
    public function get_order_details($order_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT od.*, p.product_name, p.product_image, u.full_name AS wholesaler_name
                    FROM order_details od
                    LEFT JOIN products p ON od.product_id = p.product_id
                    LEFT JOIN users u ON od.wholesaler_id = u.id
                    WHERE od.order_id = :order_id
                    ORDER BY od.order_detail_id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':order_id' => $order_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting order details: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all orders for a wholesaler (orders for products they created)
     */
    public function get_orders_for_wholesaler($wholesaler_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT od.*, o.order_id, o.customer_id, o.collaboration_id, o.status, o.payment_status, o.created_at AS order_date,
                    o.total_amount, o.final_amount, o.transaction_fee,
                    p.product_name, p.product_image,
                    u.full_name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
                    FROM order_details od
                    INNER JOIN orders o ON od.order_id = o.order_id
                    LEFT JOIN products p ON od.product_id = p.product_id
                    LEFT JOIN users u ON o.customer_id = u.id
                    WHERE od.wholesaler_id = :wholesaler_id
                    ORDER BY o.created_at DESC, od.order_detail_id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':wholesaler_id' => $wholesaler_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting orders for wholesaler: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all orders for a customer
     */
    public function get_orders_for_customer($customer_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT o.*, 
                    (SELECT COUNT(*) FROM order_details WHERE order_id = o.order_id) AS item_count
                    FROM orders o
                    WHERE o.customer_id = :customer_id
                    ORDER BY o.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':customer_id' => $customer_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting orders for customer: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cancel an order
     */
    public function cancel_order($order_id, $customer_id = null)
    {
        try {
            $this->db_connect();
            $sql = "UPDATE orders 
                    SET status = 'cancelled', cancelled_at = NOW(), updated_at = NOW()
                    WHERE order_id = :order_id";
            
            if ($customer_id !== null) {
                $sql .= " AND customer_id = :customer_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $params = [':order_id' => $order_id];
            if ($customer_id !== null) {
                $params[':customer_id'] = $customer_id;
            }
            
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error cancelling order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order payment status
     */
    public function update_payment_status($order_id, $payment_status)
    {
        try {
            $this->db_connect();
            $sql = "UPDATE orders 
                    SET payment_status = :payment_status, updated_at = NOW()
                    WHERE order_id = :order_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_id' => $order_id,
                ':payment_status' => $payment_status
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update order status
     */
    public function update_order_status($order_id, $status)
    {
        try {
            $this->db_connect();
            $sql = "UPDATE orders 
                    SET status = :status, updated_at = NOW()
                    WHERE order_id = :order_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':order_id' => $order_id,
                ':status' => $status
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating order status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get orders for a collaboration
     */
    public function get_collaboration_orders($collaboration_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT o.*, u.full_name AS customer_name
                    FROM orders o
                    LEFT JOIN users u ON o.customer_id = u.id
                    WHERE o.collaboration_id = :collaboration_id
                    ORDER BY o.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':collaboration_id' => $collaboration_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting collaboration orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get monthly paid orders statistics
     */
    public function get_monthly_paid_orders($year = null)
    {
        try {
            $this->db_connect();
            if ($year) {
                $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') AS month,
                        COUNT(*) AS order_count,
                        SUM(final_amount) AS total_revenue
                        FROM orders 
                        WHERE payment_status = 'paid'
                        AND YEAR(created_at) = :year
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month ASC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':year' => $year]);
            } else {
                $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') AS month,
                        COUNT(*) AS order_count,
                        SUM(final_amount) AS total_revenue
                        FROM orders 
                        WHERE payment_status = 'paid'
                        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                        ORDER BY month DESC
                        LIMIT 12";
                $stmt = $this->db->query($sql);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting monthly paid orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get count of unpaid orders for a wholesaler
     */
    public function get_unpaid_orders_count_for_wholesaler($wholesaler_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                return 0;
            }
            $sql = "SELECT COUNT(DISTINCT o.order_id) AS unpaid_count
                    FROM orders o
                    INNER JOIN order_details od ON o.order_id = od.order_id
                    WHERE od.wholesaler_id = :wholesaler_id
                    AND o.payment_status = 'pending'
                    AND o.status != 'cancelled'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':wholesaler_id' => $wholesaler_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['unpaid_count'] : 0;
        } catch (PDOException $e) {
            error_log("Error getting unpaid orders count: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get pending unpaid orders for a customer
     */
    public function get_pending_orders_for_customer($customer_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                return [];
            }
            $sql = "SELECT o.*
                    FROM orders o
                    WHERE o.customer_id = :customer_id
                    AND o.payment_status = 'pending'
                    AND o.status != 'cancelled'
                    ORDER BY o.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':customer_id' => $customer_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting pending orders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get pending order for a specific product and customer
     * Returns the order if found, false otherwise
     */
    public function get_pending_order_for_product($customer_id, $product_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                return false;
            }
            $sql = "SELECT o.*
                    FROM orders o
                    INNER JOIN order_details od ON o.order_id = od.order_id
                    WHERE o.customer_id = :customer_id
                    AND od.product_id = :product_id
                    AND o.payment_status = 'pending'
                    AND o.status != 'cancelled'
                    AND o.collaboration_id IS NULL
                    ORDER BY o.created_at DESC
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':product_id' => $product_id
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : false;
        } catch (PDOException $e) {
            error_log("Error getting pending order for product: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if collaboration order already exists
     */
    public function get_collaboration_order($collaboration_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                return false;
            }
            $sql = "SELECT * FROM orders WHERE collaboration_id = :collab_id AND status != 'cancelled' LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':collab_id' => $collaboration_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting collaboration order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Record member payment for collaboration order
     */
    public function record_collaboration_payment($order_id, $collaboration_id, $user_id, $contribution_percent, $amount)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                error_log("Database connection failed in record_collaboration_payment");
                return false;
            }
            
            // Use VALUES() function for ON DUPLICATE KEY UPDATE to avoid parameter name conflicts
            $sql = "INSERT INTO collaboration_order_payments 
                    (order_id, collaboration_id, user_id, contribution_percent, amount, payment_status, created_at)
                    VALUES (:order_id, :collab_id, :user_id, :percent, :amount, 'paid', NOW())
                    ON DUPLICATE KEY UPDATE 
                    payment_status = 'paid', 
                    amount = VALUES(amount), 
                    paid_at = NOW()";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                $error = $this->db->errorInfo();
                error_log("SQL prepare error in record_collaboration_payment: " . print_r($error, true));
                return false;
            }
            
            $result = $stmt->execute([
                ':order_id' => $order_id,
                ':collab_id' => $collaboration_id,
                ':user_id' => $user_id,
                ':percent' => $contribution_percent,
                ':amount' => $amount
            ]);
            
            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("SQL execute error in record_collaboration_payment: " . print_r($error, true));
                error_log("Parameters: order_id=$order_id, collab_id=$collaboration_id, user_id=$user_id, percent=$contribution_percent, amount=$amount");
                return false;
            }
            
            if ($result) {
                // Check if all members have paid
                $this->check_collaboration_payment_complete($order_id, $collaboration_id);
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("PDO Exception in record_collaboration_payment: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        } catch (Exception $e) {
            error_log("General Exception in record_collaboration_payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if all members have paid for collaboration order
     */
    public function check_collaboration_payment_complete($order_id, $collaboration_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                return false;
            }
            
            // Get all members
            $members_sql = "SELECT user_id, contribution_percent FROM collaboration_members WHERE collaboration_id = :collab_id";
            $members_stmt = $this->db->prepare($members_sql);
            $members_stmt->execute([':collab_id' => $collaboration_id]);
            $all_members = $members_stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get paid members
            $paid_sql = "SELECT user_id FROM collaboration_order_payments 
                        WHERE order_id = :order_id AND payment_status = 'paid'";
            $paid_stmt = $this->db->prepare($paid_sql);
            $paid_stmt->execute([':order_id' => $order_id]);
            $paid_members = $paid_stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Check if all members have paid
            if (count($paid_members) >= count($all_members) && count($all_members) > 0) {
                // All members have paid, update order payment status and status to completed
                $update_sql = "UPDATE orders SET payment_status = 'paid', status = 'completed', updated_at = NOW() WHERE order_id = :order_id";
                $update_stmt = $this->db->prepare($update_sql);
                $update_result = $update_stmt->execute([':order_id' => $order_id]);
                
                if ($update_result) {
                    // Get product_id from order
                    $order_sql = "SELECT product_id FROM order_details WHERE order_id = :order_id LIMIT 1";
                    $order_stmt = $this->db->prepare($order_sql);
                    $order_stmt->execute([':order_id' => $order_id]);
                    $order_detail = $order_stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($order_detail) {
                        $product_id = $order_detail['product_id'];
                        
                        // Remove product from all members' carts
                        require_once __DIR__ . '/../controllers/cart_controller.php';
                        foreach ($all_members as $member) {
                            remove_from_cart_ctr($product_id, $member['user_id']);
                        }
                    }
                    
                    // Dissolve the collaboration group by updating its status to 'expired'
                    $dissolve_sql = "UPDATE collaborations SET status = 'expired' WHERE collaboration_id = :collab_id";
                    $dissolve_stmt = $this->db->prepare($dissolve_sql);
                    $dissolve_stmt->execute([':collab_id' => $collaboration_id]);
                }
                
                return $update_result;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error checking collaboration payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get member payment status for collaboration order
     */
    public function get_member_payment_status($order_id, $user_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                return null;
            }
            $sql = "SELECT * FROM collaboration_order_payments 
                    WHERE order_id = :order_id AND user_id = :user_id LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting member payment status: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all member payments for collaboration order
     */
    public function get_collaboration_payments($order_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                return [];
            }
            $sql = "SELECT cop.*, u.full_name, u.email 
                    FROM collaboration_order_payments cop
                    LEFT JOIN users u ON cop.user_id = u.id
                    WHERE cop.order_id = :order_id
                    ORDER BY cop.created_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':order_id' => $order_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting collaboration payments: " . $e->getMessage());
            return [];
        }
    }
}

