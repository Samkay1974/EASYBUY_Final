<?php
require_once __DIR__ . '/../classes/subscription_class.php';

function create_subscription_ctr($user_id, $plan_type, $amount, $payment_reference = null)
{
    $subscription = new Subscription();
    return $subscription->create_subscription($user_id, $plan_type, $amount, $payment_reference);
}

function get_active_subscription_ctr($user_id)
{
    $subscription = new Subscription();
    return $subscription->get_active_subscription($user_id);
}

function get_subscription_by_id_ctr($subscription_id)
{
    $subscription = new Subscription();
    return $subscription->get_subscription_by_id($subscription_id);
}

function get_subscription_by_reference_ctr($payment_reference)
{
    $subscription = new Subscription();
    return $subscription->get_subscription_by_reference($payment_reference);
}

function update_subscription_payment_status_ctr($subscription_id, $payment_status, $payment_reference = null)
{
    $subscription = new Subscription();
    return $subscription->update_payment_status($subscription_id, $payment_status, $payment_reference);
}

function record_subscription_payment_ctr($subscription_id, $user_id, $amount, $payment_reference, $payment_status = 'paid')
{
    $subscription = new Subscription();
    return $subscription->record_payment($subscription_id, $user_id, $amount, $payment_reference, $payment_status);
}

function get_user_subscriptions_ctr($user_id)
{
    $subscription = new Subscription();
    return $subscription->get_user_subscriptions($user_id);
}

function has_active_subscription_ctr($user_id)
{
    $subscription = new Subscription();
    return $subscription->has_active_subscription($user_id);
}

function can_create_product_ctr($user_id)
{
    $subscription = new Subscription();
    return $subscription->can_create_product($user_id);
}

function get_user_product_count_ctr($user_id)
{
    $subscription = new Subscription();
    return $subscription->get_user_product_count($user_id);
}

