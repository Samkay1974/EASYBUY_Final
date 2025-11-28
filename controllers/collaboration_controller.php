<?php
require_once __DIR__ . '/../classes/collaboration_class.php';

function create_collaboration_ctr($product_id, $creator_id, $min_contribution_percent = 30)
{
    $collab = new Collaboration();
    return $collab->create_collaboration($product_id, $creator_id, $min_contribution_percent);
}

function join_collaboration_ctr($collaboration_id, $user_id, $contribution_percent)
{
    $collab = new Collaboration();
    return $collab->join_collaboration($collaboration_id, $user_id, $contribution_percent);
}

function get_collaboration_by_id_ctr($collaboration_id)
{
    $collab = new Collaboration();
    return $collab->get_collaboration_by_id($collaboration_id);
}

function get_collaborations_by_product_ctr($product_id)
{
    $collab = new Collaboration();
    return $collab->get_collaborations_by_product($product_id);
}

function get_all_open_collaborations_ctr()
{
    $collab = new Collaboration();
    return $collab->get_all_open_collaborations();
}

function get_collaboration_members_ctr($collaboration_id)
{
    $collab = new Collaboration();
    return $collab->get_collaboration_members($collaboration_id);
}

function get_total_contribution_ctr($collaboration_id)
{
    $collab = new Collaboration();
    return $collab->get_total_contribution($collaboration_id);
}

function is_member_ctr($collaboration_id, $user_id)
{
    $collab = new Collaboration();
    return $collab->is_member($collaboration_id, $user_id);
}

function get_user_collaborations_ctr($user_id)
{
    $collab = new Collaboration();
    return $collab->get_user_collaborations($user_id);
}

function expire_old_collaborations_ctr()
{
    $collab = new Collaboration();
    return $collab->expire_old_collaborations();
}

function leave_collaboration_ctr($collaboration_id, $user_id)
{
    $collab = new Collaboration();
    return $collab->leave_collaboration($collaboration_id, $user_id);
}

