<?php
require_once __DIR__ . '/../classes/report_class.php';

function create_report_ctr($user_id, $report_type, $subject, $description)
{
    $report = new Report();
    return $report->create_report($user_id, $report_type, $subject, $description);
}

function get_all_reports_ctr($status = null)
{
    $report = new Report();
    return $report->get_all_reports($status);
}

function get_pending_reports_count_ctr()
{
    $report = new Report();
    return $report->get_pending_reports_count();
}

function get_report_by_id_ctr($report_id)
{
    $report = new Report();
    return $report->get_report_by_id($report_id);
}

function update_report_status_ctr($report_id, $status, $admin_id = null, $admin_notes = null)
{
    $report = new Report();
    return $report->update_report_status($report_id, $status, $admin_id, $admin_notes);
}

