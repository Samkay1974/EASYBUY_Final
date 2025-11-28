<?php
require_once __DIR__ . '/../settings/db_class.php';

class Report extends db_connection
{
    public function create_report($user_id, $report_type, $subject, $description)
    {
        try {
            $this->db_connect();
            $sql = "INSERT INTO user_reports (user_id, report_type, subject, description, status, created_at)
                    VALUES (:user_id, :report_type, :subject, :description, 'pending', NOW())";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':user_id' => $user_id,
                ':report_type' => $report_type,
                ':subject' => $subject,
                ':description' => $description
            ]);
        } catch (PDOException $e) {
            error_log("Error creating report: " . $e->getMessage());
            return false;
        }
    }

    public function get_all_reports($status = null)
    {
        try {
            $this->db_connect();
            if ($status) {
                $sql = "SELECT r.*, u.full_name, u.email, u.phone
                        FROM user_reports r
                        LEFT JOIN users u ON r.user_id = u.id
                        WHERE r.status = :status
                        ORDER BY r.created_at DESC";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':status' => $status]);
            } else {
                $sql = "SELECT r.*, u.full_name, u.email, u.phone
                        FROM user_reports r
                        LEFT JOIN users u ON r.user_id = u.id
                        ORDER BY r.created_at DESC";
                $stmt = $this->db->query($sql);
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting reports: " . $e->getMessage());
            return [];
        }
    }

    public function get_pending_reports_count()
    {
        try {
            $this->db_connect();
            $sql = "SELECT COUNT(*) FROM user_reports WHERE status = 'pending'";
            $stmt = $this->db->query($sql);
            return intval($stmt->fetchColumn());
        } catch (PDOException $e) {
            error_log("Error getting pending reports count: " . $e->getMessage());
            return 0;
        }
    }

    public function get_report_by_id($report_id)
    {
        try {
            $this->db_connect();
            $sql = "SELECT r.*, u.full_name, u.email, u.phone
                    FROM user_reports r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.report_id = :report_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':report_id' => $report_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting report by ID: " . $e->getMessage());
            return false;
        }
    }

    public function update_report_status($report_id, $status, $admin_id = null, $admin_notes = null)
    {
        try {
            $this->db_connect();
            $sql = "UPDATE user_reports 
                    SET status = :status, 
                        reviewed_by = :admin_id,
                        admin_notes = :admin_notes,
                        reviewed_at = NOW(),
                        updated_at = NOW()
                    WHERE report_id = :report_id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':status' => $status,
                ':admin_id' => $admin_id,
                ':admin_notes' => $admin_notes,
                ':report_id' => $report_id
            ]);
        } catch (PDOException $e) {
            error_log("Error updating report status: " . $e->getMessage());
            return false;
        }
    }
}

