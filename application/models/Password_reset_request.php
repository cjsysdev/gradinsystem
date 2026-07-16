<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Password_reset_request extends MY_Model
{
    public $table = 'password_reset_requests';
    public $primary_key = 'request_id';
    public $protected = ['request_id'];
    public $fillable = ['student_id', 'student_no', 'status', 'default_username', 'default_password', 'admin_notes'];

    public function __construct()
    {
        $this->timestamps = true;
        parent::__construct();
    }

    // Idempotently creates the password_reset_requests table and adds the
    // accounts.must_change_password flag column. Mirrors Project_log_model::install().
    public function install()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `password_reset_requests` (
            `request_id`       INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `student_id`       INT NOT NULL,
            `student_no`       VARCHAR(50) NULL,
            `status`           ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
            `default_username` VARCHAR(50) NULL,
            `default_password` VARCHAR(255) NULL,
            `admin_notes`      VARCHAR(255) NULL,
            `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at`       DATETIME NULL,
            KEY `idx_student` (`student_id`),
            KEY `idx_status`  (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        if (!$this->db->field_exists('must_change_password', 'accounts')) {
            $this->db->query("ALTER TABLE `accounts` ADD `must_change_password` TINYINT(1) NOT NULL DEFAULT 0");
        }
    }

    // Number of pending reset requests — drives the admin nav notification badge.
    public function count_pending()
    {
        return $this->db->where('status', 'pending')->count_all_results('password_reset_requests');
    }

    // Whether a student already has an open (pending) request — blocks duplicates.
    public function has_pending($student_id)
    {
        return $this->db
            ->where(['student_id' => $student_id, 'status' => 'pending'])
            ->count_all_results('password_reset_requests') > 0;
    }

    // Reset requests joined to student_master for display, newest first.
    public function get_all($status = null)
    {
        $sql = "
            SELECT r.*, sm.lastname, sm.firstname, sm.student_no AS sm_student_no
            FROM password_reset_requests r
            JOIN student_master sm ON r.student_id = sm.trans_no
        ";
        $params = [];
        if ($status) {
            $sql .= " WHERE r.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY r.created_at DESC, r.request_id DESC";
        return $this->db->query($sql, $params)->result_array();
    }
}
