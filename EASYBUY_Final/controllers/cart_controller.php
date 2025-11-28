<?php
require_once __DIR__ . '/../classes/cart_class.php';

function add_to_cart_ctr($product_id, $quantity = 1, $target_user_id = null)
{
    $cart = new Cart();
    return $cart->addToCart($product_id, $quantity, $target_user_id);
}

function update_cart_ctr($product_id, $quantity)
{
    $cart = new Cart();
    return $cart->updateCart($product_id, $quantity);
}

function remove_from_cart_ctr($product_id, $user_id = null)
{
    $cart = new Cart();
    if ($user_id !== null) {
        // Remove from specific user's cart in database
        try {
            $cart->db_connect();
            if ($cart->db) {
                $sql = "DELETE FROM cart_items WHERE user_id = :user_id AND product_id = :product_id";
                $stmt = $cart->db->prepare($sql);
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':product_id' => $product_id
                ]);
                // Also remove from session if it's the current user
                if (isLoggedIn() && get_user_id() == $user_id) {
                    return $cart->removeFromCart($product_id);
                }
                return true;
            }
        } catch (PDOException $e) {
            error_log("Error removing cart item: " . $e->getMessage());
        }
        return false;
    }
    return $cart->removeFromCart($product_id);
}

function get_cart_items_ctr()
{
    $cart = new Cart();
    return $cart->getCartItems();
}

function get_cart_count_ctr()
{
    $cart = new Cart();
    return $cart->getCartItemCount(); // Return number of different products instead of total units
}
function get_cart_item_count_ctr()
{
    $cart = new Cart();
    return $cart->getCartItemCount();
}

function get_cart_total_ctr()
{
    $cart = new Cart();
    return $cart->getCartTotal();
}

function clear_cart_ctr()
{
    $cart = new Cart();
    return $cart->clearCart();
}

function get_cart_item_quantity_ctr($product_id)
{
    $cart = new Cart();
    return $cart->getItemQuantity($product_id);
}

function sync_cart_ctr($user_id)
{
    $cart = new Cart();
    return $cart->syncCartToDatabase($user_id);
}

