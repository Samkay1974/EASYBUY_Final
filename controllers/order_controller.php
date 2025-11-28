<?php
require_once __DIR__ . '/../classes/order_class.php';

function create_order_ctr($customer_id, $total_amount, $transaction_fee, $final_amount, $collaboration_id = null)
{
    $order = new Order();
    return $order->create_order($customer_id, $total_amount, $transaction_fee, $final_amount, $collaboration_id);
}

function add_order_details_ctr($order_id, $product_id, $wholesaler_id, $quantity, $unit_price, $subtotal)
{
    $order = new Order();
    return $order->add_order_detail($order_id, $product_id, $wholesaler_id, $quantity, $unit_price, $subtotal);
}

function get_order_by_id_ctr($order_id)
{
    $order = new Order();
    return $order->get_order_by_id($order_id);
}

function get_last_order_ctr($customer_id)
{
    $order = new Order();
    return $order->get_last_order($customer_id);
}

function get_order_details_ctr($order_id)
{
    $order = new Order();
    return $order->get_order_details($order_id);
}

function get_orders_for_wholesaler_ctr($wholesaler_id)
{
    $order = new Order();
    return $order->get_orders_for_wholesaler($wholesaler_id);
}

function get_orders_for_customer_ctr($customer_id)
{
    $order = new Order();
    return $order->get_orders_for_customer($customer_id);
}

function cancel_order_ctr($order_id, $customer_id = null)
{
    $order = new Order();
    return $order->cancel_order($order_id, $customer_id);
}

function update_payment_status_ctr($order_id, $payment_status)
{
    $order = new Order();
    return $order->update_payment_status($order_id, $payment_status);
}

function update_order_status_ctr($order_id, $status)
{
    $order = new Order();
    return $order->update_order_status($order_id, $status);
}

function get_collaboration_orders_ctr($collaboration_id)
{
    $order = new Order();
    return $order->get_collaboration_orders($collaboration_id);
}

function get_unpaid_orders_count_for_wholesaler_ctr($wholesaler_id)
{
    $order = new Order();
    return $order->get_unpaid_orders_count_for_wholesaler($wholesaler_id);
}

function get_monthly_paid_orders_ctr($year = null)
{
    $order = new Order();
    return $order->get_monthly_paid_orders($year);
}

function get_pending_orders_for_customer_ctr($customer_id)
{
    $order = new Order();
    return $order->get_pending_orders_for_customer($customer_id);
}

function get_pending_order_for_product_ctr($customer_id, $product_id)
{
    $order = new Order();
    return $order->get_pending_order_for_product($customer_id, $product_id);
}

function get_collaboration_order_ctr($collaboration_id)
{
    $order = new Order();
    return $order->get_collaboration_order($collaboration_id);
}

function record_collaboration_payment_ctr($order_id, $collaboration_id, $user_id, $contribution_percent, $amount)
{
    $order = new Order();
    return $order->record_collaboration_payment($order_id, $collaboration_id, $user_id, $contribution_percent, $amount);
}

function get_member_payment_status_ctr($order_id, $user_id)
{
    $order = new Order();
    return $order->get_member_payment_status($order_id, $user_id);
}

function get_collaboration_payments_ctr($order_id)
{
    $order = new Order();
    return $order->get_collaboration_payments($order_id);
}

