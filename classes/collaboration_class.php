<?php
require_once __DIR__ . '/../settings/db_class.php';

class Collaboration extends db_connection
{
    /**
     * Create a new collaboration group
     */
    public function create_collaboration($product_id, $creator_id, $min_contribution_percent = 30)
    {
        try {
            $this->db_connect();
            $sql = "INSERT INTO collaborations (product_id, creator_id, min_contribution_percent, status, created_at, expires_at)
                    VALUES (:product_id, :creator_id, :min_contribution, 'open', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH))";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':product_id' => $product_id,
                ':creator_id' => $creator_id,
                ':min_contribution' => $min_contribution_percent
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating collaboration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Join a collaboration group
     */
    public function join_collaboration($collaboration_id, $user_id, $contribution_percent)
    {
        try {
            $this->db_connect();
            
            // Check if user already joined
            $check = $this->db->prepare("SELECT * FROM collaboration_members WHERE collaboration_id = :cid AND user_id = :uid");
            $check->execute([':cid' => $collaboration_id, ':uid' => $user_id]);
            if ($check->fetch()) {
                return false; // Already a member
            }

            // Get collaboration details
            $collab = $this->get_collaboration_by_id($collaboration_id);
            if (!$collab || $collab['status'] != 'open') {
                return false;
            }

            // Check minimum contribution
            if ($contribution_percent < $collab['min_contribution_percent']) {
                return false;
            }

            // Get current total contribution
            $total = $this->get_total_contribution($collaboration_id);
            if ($total + $contribution_percent > 100) {
                return false; // Would exceed 100%
            }

            // Add member
            $sql = "INSERT INTO collaboration_members (collaboration_id, user_id, contribution_percent, joined_at)
                    VALUES (:cid, :uid, :percent, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cid' => $collaboration_id,
                ':uid' => $user_id,
                ':percent' => $contribution_percent
            ]);

            // Check if collaboration is complete
            $new_total = $this->get_total_contribution($collaboration_id);
            if ($new_total >= 100) {
                $this->complete_collaboration($collaboration_id);
            }

            return true;
        } catch (PDOException $e) {
            error_log("Error joining collaboration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get collaboration by ID
     */
    public function get_collaboration_by_id($collaboration_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT c.*, p.product_name, p.moq, p.wholesale_price, p.user_id AS product_wholesaler_id, u.full_name AS creator_name
                    FROM collaborations c
                    LEFT JOIN products p ON c.product_id = p.product_id
                    LEFT JOIN users u ON c.creator_id = u.id
                    WHERE c.collaboration_id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $collaboration_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting collaboration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all collaborations for a product
     */
    public function get_collaborations_by_product($product_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT c.*, u.full_name AS creator_name,
                    (SELECT COALESCE(SUM(contribution_percent), 0) FROM collaboration_members WHERE collaboration_id = c.collaboration_id) AS total_contribution
                    FROM collaborations c
                    LEFT JOIN users u ON c.creator_id = u.id
                    WHERE c.product_id = :pid AND c.status IN ('open', 'completed')
                    ORDER BY c.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':pid' => $product_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting collaborations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all open collaborations
     */
    public function get_all_open_collaborations()
    {
        try {
            $this->db_connect();
            $sql = "SELECT c.*, p.product_name, p.moq, p.wholesale_price, p.product_image,
                    u.full_name AS creator_name,
                    (SELECT COALESCE(SUM(contribution_percent), 0) FROM collaboration_members WHERE collaboration_id = c.collaboration_id) AS total_contribution
                    FROM collaborations c
                    LEFT JOIN products p ON c.product_id = p.product_id
                    LEFT JOIN users u ON c.creator_id = u.id
                    WHERE c.status = 'open'
                    ORDER BY c.created_at DESC";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting all collaborations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get collaboration members
     */
    public function get_collaboration_members($collaboration_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT cm.*, u.full_name, u.email, u.phone
                    FROM collaboration_members cm
                    LEFT JOIN users u ON cm.user_id = u.id
                    WHERE cm.collaboration_id = :cid
                    ORDER BY cm.joined_at ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cid' => $collaboration_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting members: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total contribution percentage
     */
    public function get_total_contribution($collaboration_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT COALESCE(SUM(contribution_percent), 0) AS total
                    FROM collaboration_members
                    WHERE collaboration_id = :cid";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cid' => $collaboration_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (float)$result['total'];
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Check if user is member of collaboration
     */
    public function is_member($collaboration_id, $user_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT * FROM collaboration_members WHERE collaboration_id = :cid AND user_id = :uid";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cid' => $collaboration_id, ':uid' => $user_id]);
            return $stmt->fetch() !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Complete a collaboration and automatically create order
     */
    public function complete_collaboration($collaboration_id)
    {
        try {
            $this->db_connect();
            
            // Check if order already exists
            $order_check = $this->db->prepare("SELECT * FROM orders WHERE collaboration_id = :collab_id AND status != 'cancelled' LIMIT 1");
            $order_check->execute([':collab_id' => $collaboration_id]);
            if ($order_check->fetch()) {
                // Order already exists, just update status
                $sql = "UPDATE collaborations SET status = 'completed', completed_at = NOW() WHERE collaboration_id = :id";
                $stmt = $this->db->prepare($sql);
                return $stmt->execute([':id' => $collaboration_id]);
            }
            
            // Get collaboration details
            $collab = $this->get_collaboration_by_id($collaboration_id);
            if (!$collab) {
                return false;
            }
            
            // Get all members
            $members = $this->get_collaboration_members($collaboration_id);
            if (empty($members)) {
                return false;
            }
            
            // Get product details - product_id is in collaboration
            $product_id = $collab['product_id'];
            require_once __DIR__ . '/../controllers/product_controller.php';
            $product = get_one_product_ctr($product_id);
            if (!$product) {
                error_log("Product not found for collaboration: " . $collaboration_id);
                return false;
            }
            
            // Calculate full order amount (1 MOQ unit)
            $total_amount = floatval($product['wholesale_price']); // Price for 1 MOQ unit
            $transaction_fee = $total_amount * 0.01;
            $final_amount = $total_amount + $transaction_fee;
            
            // Use creator as the contact person (customer_id)
            $contact_person_id = $collab['creator_id'];
            
            // Create the order
            require_once __DIR__ . '/order_class.php';
            $order = new Order();
            $order_id = $order->create_order($contact_person_id, $total_amount, $transaction_fee, $final_amount, $collaboration_id);
            
            if (!$order_id) {
                error_log("Failed to create order for collaboration: " . $collaboration_id);
                return false;
            }
            
            // Add order details with FULL quantity
            $wholesaler_id = $collab['product_wholesaler_id'] ?? $product['user_id'];
            $moq = (int)$product['moq'];
            $quantity = $moq; // Full MOQ quantity (1 MOQ unit = moq items)
            $unit_price = $total_amount; // Price per MOQ unit
            $subtotal = $total_amount; // Total for 1 MOQ unit
            
            $order->add_order_detail($order_id, $product_id, $wholesaler_id, $quantity, $unit_price, $subtotal);
            
            // Initialize payment records for all members and add to their carts
            require_once __DIR__ . '/../controllers/cart_controller.php';
            foreach ($members as $member) {
                $member_contribution = floatval($member['contribution_percent']);
                $member_subtotal = $total_amount * ($member_contribution / 100);
                $member_fee = $member_subtotal * 0.01;
                $member_amount = $member_subtotal + $member_fee;
                
                // Initialize payment record
                $init_sql = "INSERT INTO collaboration_order_payments 
                            (order_id, collaboration_id, user_id, contribution_percent, amount, payment_status, created_at)
                            VALUES (:order_id, :collab_id, :user_id, :percent, :amount, 'pending', NOW())
                            ON DUPLICATE KEY UPDATE created_at = NOW()";
                $init_stmt = $this->db->prepare($init_sql);
                $init_stmt->execute([
                    ':order_id' => $order_id,
                    ':collab_id' => $collaboration_id,
                    ':user_id' => $member['user_id'],
                    ':percent' => $member_contribution,
                    ':amount' => $member_amount
                ]);
                
                // Add product to each member's cart
                add_to_cart_ctr($product_id, 1, $member['user_id']); // Add 1 MOQ unit to each member's cart
            }
            
            // Update collaboration status to completed
            $sql = "UPDATE collaborations SET status = 'completed', completed_at = NOW() WHERE collaboration_id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $collaboration_id]);
        } catch (PDOException $e) {
            error_log("Error completing collaboration: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Expire old collaborations (should be called by cron or scheduled task)
     */
    public function expire_old_collaborations()
    {
        try {
            $this->db_connect();
            $sql = "UPDATE collaborations SET status = 'expired' WHERE status = 'open' AND expires_at < NOW()";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error expiring collaborations: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user's collaborations
     */
    public function get_user_collaborations($user_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT DISTINCT c.*, p.product_name, p.moq, p.wholesale_price, p.product_image,
                    (SELECT COALESCE(SUM(contribution_percent), 0) FROM collaboration_members WHERE collaboration_id = c.collaboration_id) AS total_contribution
                    FROM collaborations c
                    LEFT JOIN products p ON c.product_id = p.product_id
                    LEFT JOIN collaboration_members cm ON c.collaboration_id = cm.collaboration_id
                    WHERE (c.creator_id = :uid OR cm.user_id = :uid) AND c.status IN ('open', 'completed', 'expired')
                    ORDER BY c.created_at DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':uid' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user collaborations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Leave a collaboration group
     */
    public function leave_collaboration($collaboration_id, $user_id)
    {
        try {
            $this->db_connect();
            
            // Get collaboration details
            $collab = $this->get_collaboration_by_id($collaboration_id);
            if (!$collab) {
                return false;
            }
            
            // Check if user is a member
            if (!$this->is_member($collaboration_id, $user_id)) {
                return false; // Not a member
            }
            
            // Don't allow leaving if collaboration is already paid/ordered
            // Check if there are any orders for this collaboration
            $orderCheck = $this->db->prepare("SELECT COUNT(*) as order_count FROM orders WHERE collaboration_id = :cid AND payment_status = 'paid'");
            $orderCheck->execute([':cid' => $collaboration_id]);
            $orderResult = $orderCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($orderResult && $orderResult['order_count'] > 0) {
                return false; // Cannot leave after payment
            }
            
            // Remove member
            $sql = "DELETE FROM collaboration_members WHERE collaboration_id = :cid AND user_id = :uid";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':cid' => $collaboration_id,
                ':uid' => $user_id
            ]);
            
            if ($result) {
                // If collaboration was completed, check if it should be reopened
                $new_total = $this->get_total_contribution($collaboration_id);
                if ($collab['status'] == 'completed' && $new_total < 100) {
                    // Reopen the collaboration if it was completed
                    $updateSql = "UPDATE collaborations SET status = 'open', completed_at = NULL WHERE collaboration_id = :id";
                    $updateStmt = $this->db->prepare($updateSql);
                    $updateStmt->execute([':id' => $collaboration_id]);
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error leaving collaboration: " . $e->getMessage());
            return false;
        }
    }
}

