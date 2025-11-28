<?php
require_once __DIR__ . '/../settings/core.php';
require_once __DIR__ . '/../settings/db_class.php';

class Cart extends db_connection
{
    /**
     * Initialize cart in session
     */
    private function initCart()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    /**
     * Load cart from database into session (for logged-in users)
     */
    public function loadCartFromDatabase($user_id)
    {
        try {
            if (!$this->db_connect() || !$this->db) {
                error_log("Error: Database connection failed in loadCartFromDatabase");
                return false;
            }
            $sql = "SELECT product_id, quantity FROM cart_items WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_id' => $user_id]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->initCart();
            foreach ($items as $item) {
                $_SESSION['cart'][$item['product_id']] = (int)$item['quantity'];
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error loading cart from database: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error in loadCartFromDatabase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Save cart to database (for logged-in users)
     */
    private function saveCartToDatabase($user_id)
    {
        if (!isLoggedIn() || !$user_id) {
            return false;
        }
        
        try {
            if (!$this->db_connect() || !$this->db) {
                error_log("Error: Database connection failed in saveCartToDatabase");
                return false;
            }
            $this->initCart();
            
            // Delete existing cart items for this user
            $deleteSql = "DELETE FROM cart_items WHERE user_id = :user_id";
            $deleteStmt = $this->db->prepare($deleteSql);
            $deleteStmt->execute([':user_id' => $user_id]);
            
            // Insert current cart items
            if (!empty($_SESSION['cart'])) {
                $insertSql = "INSERT INTO cart_items (user_id, product_id, quantity, created_at, updated_at) 
                             VALUES (:user_id, :product_id, :quantity, NOW(), NOW())";
                $insertStmt = $this->db->prepare($insertSql);
                
                foreach ($_SESSION['cart'] as $product_id => $quantity) {
                    $insertStmt->execute([
                        ':user_id' => $user_id,
                        ':product_id' => $product_id,
                        ':quantity' => $quantity
                    ]);
                }
            }
            
            return true;
        } catch (PDOException $e) {
            error_log("Error saving cart to database: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            error_log("Error in saveCartToDatabase: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add product to cart
     */
    public function addToCart($product_id, $quantity = 1, $target_user_id = null)
    {
        $this->initCart();
        
        // If target_user_id is provided, add directly to database for that user
        if ($target_user_id) {
            try {
                if (!$this->db_connect() || !$this->db) {
                    return false;
                }
                
                // Check if item already exists
                $check_sql = "SELECT quantity FROM cart_items WHERE user_id = :user_id AND product_id = :product_id";
                $check_stmt = $this->db->prepare($check_sql);
                $check_stmt->execute([':user_id' => $target_user_id, ':product_id' => $product_id]);
                $existing = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($existing) {
                    // Update quantity
                    $update_sql = "UPDATE cart_items SET quantity = quantity + :qty, updated_at = NOW() WHERE user_id = :user_id AND product_id = :product_id";
                    $update_stmt = $this->db->prepare($update_sql);
                    $update_stmt->execute([':user_id' => $target_user_id, ':product_id' => $product_id, ':qty' => $quantity]);
                } else {
                    // Insert new item
                    $insert_sql = "INSERT INTO cart_items (user_id, product_id, quantity, created_at, updated_at) 
                                 VALUES (:user_id, :product_id, :quantity, NOW(), NOW())";
                    $insert_stmt = $this->db->prepare($insert_sql);
                    $insert_stmt->execute([
                        ':user_id' => $target_user_id,
                        ':product_id' => $product_id,
                        ':quantity' => $quantity
                    ]);
                }
                
                return true;
            } catch (PDOException $e) {
                error_log("Error adding to cart for user: " . $e->getMessage());
                return false;
            }
        }
        
        // Default behavior: add to current session user's cart
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $quantity;
        }
        
        // Save to database if user is logged in
        if (isLoggedIn()) {
            $user_id = get_user_id();
            $this->saveCartToDatabase($user_id);
        }
        
        return true;
    }

    /**
     * Update cart item quantity
     */
    public function updateCart($product_id, $quantity)
    {
        $this->initCart();
        
        if ($quantity <= 0) {
            return $this->removeFromCart($product_id);
        }
        
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id] = $quantity;
            
            // Save to database if user is logged in
            if (isLoggedIn()) {
                $user_id = get_user_id();
                $this->saveCartToDatabase($user_id);
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Remove product from cart
     */
    public function removeFromCart($product_id)
    {
        $this->initCart();
        
        if (isset($_SESSION['cart'][$product_id])) {
            unset($_SESSION['cart'][$product_id]);
            
            // Remove from database if user is logged in
            if (isLoggedIn()) {
                $user_id = get_user_id();
                try {
                    if ($this->db_connect() && $this->db) {
                        $sql = "DELETE FROM cart_items WHERE user_id = :user_id AND product_id = :product_id";
                        $stmt = $this->db->prepare($sql);
                        $stmt->execute([
                            ':user_id' => $user_id,
                            ':product_id' => $product_id
                        ]);
                    }
                } catch (PDOException $e) {
                    error_log("Error removing cart item from database: " . $e->getMessage());
                } catch (Exception $e) {
                    error_log("Error in removeFromCart database operation: " . $e->getMessage());
                }
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * Get cart items with product details
     * Note: quantity in cart represents MOQ multipliers (1 = 1×MOQ, 2 = 2×MOQ, etc.)
     */
    public function getCartItems()
    {
        // Load from database if user is logged in and session cart is empty
        if (isLoggedIn() && (empty($_SESSION['cart']) || !isset($_SESSION['cart']))) {
            $user_id = get_user_id();
            $this->loadCartFromDatabase($user_id);
        }
        
        $this->initCart();
        
        if (empty($_SESSION['cart'])) {
            return [];
        }
        
        require_once __DIR__ . '/../controllers/product_controller.php';
        
        $cartItems = [];
        foreach ($_SESSION['cart'] as $product_id => $moq_quantity) {
            $product = get_one_product_ctr($product_id);
            if ($product) {
                // Get full product details with brand and category
                $allProducts = get_all_products_ctr();
                $productDetails = null;
                foreach ($allProducts as $p) {
                    if ($p['product_id'] == $product_id) {
                        $productDetails = $p;
                        break;
                    }
                }
                
                if ($productDetails) {
                    $moq = (int)$productDetails['moq'];
                    $actual_units = $moq_quantity * $moq; // Total units = MOQ quantity × MOQ
                    $moq_price = $productDetails['wholesale_price']; // Price for 1 MOQ unit
                    $subtotal = $moq_price * $moq_quantity; // Total price = price of MOQ × number of MOQ units
                    
                    $cartItems[] = [
                        'product_id' => $product_id,
                        'product_name' => $productDetails['product_name'],
                        'product_image' => $productDetails['product_image'],
                        'brand_name' => $productDetails['brand_name'] ?? 'N/A',
                        'cat_name' => $productDetails['cat_name'] ?? 'N/A',
                        'wholesale_price' => $moq_price,
                        'moq' => $moq,
                        'moq_quantity' => $moq_quantity, // Number of MOQ units
                        'actual_units' => $actual_units, // Total actual units
                        'quantity' => $moq_quantity, // For backward compatibility
                        'subtotal' => $subtotal
                    ];
                }
            }
        }
        
        return $cartItems;
    }

    /**
     * Get cart total count (total actual units)
     */
    public function getCartCount()
    {
        // Load from database if user is logged in and session cart is empty
        if (isLoggedIn() && (empty($_SESSION['cart']) || !isset($_SESSION['cart']))) {
            $user_id = get_user_id();
            $this->loadCartFromDatabase($user_id);
        }
        
        $this->initCart();
        
        if (empty($_SESSION['cart'])) {
            return 0;
        }
        
        require_once __DIR__ . '/../controllers/product_controller.php';
        
        $total_units = 0;
        foreach ($_SESSION['cart'] as $product_id => $moq_quantity) {
            $product = get_one_product_ctr($product_id);
            if ($product) {
                $moq = (int)$product['moq'];
                $total_units += $moq_quantity * $moq; // MOQ quantity × MOQ = actual units
            }
        }
        
        return $total_units;
    }
    
    /**
     * Get cart item count (number of different products)
     */
    public function getCartItemCount()
    {
        $this->initCart();
        return count($_SESSION['cart']);
    }

    /**
     * Get cart total price
     */
    public function getCartTotal()
    {
        $items = $this->getCartItems();
        $total = 0;
        
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        return $total;
    }

    /**
     * Clear entire cart (both session and database)
     */
    public function clearCart()
    {
        $this->initCart();
        
        // Clear from database if user is logged in
        if (isLoggedIn()) {
            $user_id = get_user_id();
            try {
                if ($this->db_connect() && $this->db) {
                    $sql = "DELETE FROM cart_items WHERE user_id = :user_id";
                    $stmt = $this->db->prepare($sql);
                    $stmt->execute([':user_id' => $user_id]);
                }
            } catch (PDOException $e) {
                error_log("Error clearing cart from database: " . $e->getMessage());
            } catch (Exception $e) {
                error_log("Error in clearCart database operation: " . $e->getMessage());
            }
        }
        
        $_SESSION['cart'] = [];
        return true;
    }

    /**
     * Get cart item quantity
     */
    public function getItemQuantity($product_id)
    {
        // Load from database if user is logged in and session cart is empty
        if (isLoggedIn() && (empty($_SESSION['cart']) || !isset($_SESSION['cart']))) {
            $user_id = get_user_id();
            $this->loadCartFromDatabase($user_id);
        }
        
        $this->initCart();
        return isset($_SESSION['cart'][$product_id]) ? $_SESSION['cart'][$product_id] : 0;
    }
    
    /**
     * Sync session cart to database (useful after login)
     */
    public function syncCartToDatabase($user_id)
    {
        $this->initCart();
        
        // If session has items, save them to database
        if (!empty($_SESSION['cart'])) {
            $this->saveCartToDatabase($user_id);
        } else {
            // If session is empty, load from database
            $this->loadCartFromDatabase($user_id);
        }
        
        return true;
    }
}

