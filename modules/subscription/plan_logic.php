<?php
// modules/subscription/plan_logic.php
require_once(__DIR__ . '/../../config/db.php');

class PlanLogic {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    // Helper function to get Date Condition based on filter
    private function get_date_condition($filter, $column = 'created_at') {
        switch ($filter) {
            case '7days':
                return " AND $column >= NOW() - INTERVAL 7 DAY ";
            case '6months':
                return " AND $column >= NOW() - INTERVAL 6 MONTH ";
            case 'year':
                return " AND $column >= NOW() - INTERVAL 1 YEAR ";
            case 'month':
            default:
                return " AND MONTH($column) = MONTH(NOW()) AND YEAR($column) = YEAR(NOW()) ";
        }
    }

    // 1. Monthly Logins (audit_logs table) - UPDATED with Filter
    public function get_monthly_logins($tenant_id, $filter = 'month') {
        $date_cond = $this->get_date_condition($filter, 'created_at');
        $sql = "SELECT MONTHNAME(created_at) as month, COUNT(*) as total 
                FROM audit_logs 
                WHERE tenant_id = ? AND action LIKE '%Login%' $date_cond
                GROUP BY MONTH(created_at) 
                ORDER BY created_at ASC LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $months = []; $counts = [];
        while ($row = $result->fetch_assoc()) {
            $months[] = $row['month'];
            $counts[] = $row['total'];
        }
        return ['labels' => $months, 'data' => $counts];
    }

    // 2. User Registration (users table) - UPDATED with Filter
    public function get_monthly_registrations($tenant_id, $filter = 'month') {
        $date_cond = $this->get_date_condition($filter, 'created_at');
        $sql = "SELECT MONTHNAME(created_at) as month, COUNT(*) as total 
                FROM users 
                WHERE tenant_id = ? $date_cond
                GROUP BY MONTH(created_at) 
                ORDER BY created_at ASC LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $months = []; $counts = [];
        while ($row = $result->fetch_assoc()) {
            $months[] = $row['month'];
            $counts[] = $row['total'];
        }
        return ['labels' => $months, 'data' => $counts];
    }

    // 3. Premium Sales (subscriptions table) - UPDATED with Filter
    public function get_premium_sales($tenant_id, $filter = 'month') {
        $date_cond = $this->get_date_condition($filter, 'start_date');
        $sql = "SELECT MONTHNAME(start_date) as month, COUNT(*) as total 
                FROM subscriptions 
                WHERE tenant_id = ? AND plan_id = 2 $date_cond
                GROUP BY MONTH(start_date) 
                ORDER BY start_date ASC LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $months = []; $counts = [];
        while ($row = $result->fetch_assoc()) {
            $months[] = $row['month'];
            $counts[] = $row['total'];
        }
        return ['labels' => $months, 'data' => $counts];
    }

    // 4. Most Active Users - UPDATED with Filter
    public function get_top_users($tenant_id, $filter = 'month') {
        $date_cond = $this->get_date_condition($filter, 'a.created_at');
        $sql = "SELECT u.name as username, COUNT(a.id) as activity_count 
                FROM users u 
                JOIN audit_logs a ON u.id = a.user_id 
                WHERE a.tenant_id = ? AND a.action LIKE '%Login%' $date_cond
                GROUP BY u.id 
                ORDER BY activity_count DESC LIMIT 3";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 5. User Usage Summary
    public function get_user_usage($tenant_id) {
        $sql = "SELECT p.user_limit as default_limit, p.plan_name, s.plan_id 
                FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.tenant_id = ? AND s.status = 'active' LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $plan_data = $stmt->get_result()->fetch_assoc();
        
        $plan_id = $plan_data['plan_id'] ?? 1; 
        $limit = ($plan_id == 2) ? 100 : ($plan_data['default_limit'] ?? 10);

        // User Count
        $sql_count = "SELECT COUNT(id) as total FROM users WHERE tenant_id = ?";
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_count->bind_param("i", $tenant_id);
        $stmt_count->execute();
        $current_users = $stmt_count->get_result()->fetch_assoc()['total'];

        // Login Count
        $sql_logins = "SELECT COUNT(*) as total_logins FROM audit_logs WHERE tenant_id = ? AND action LIKE '%Login%'";
        $stmt_l = $this->db->prepare($sql_logins);
        $stmt_l->bind_param("i", $tenant_id);
        $stmt_l->execute();
        $total_logins = $stmt_l->get_result()->fetch_assoc()['total_logins'] ?? 0;

        $percentage = ($limit > 0) ? ($current_users / $limit) * 100 : 0;

        return [
            'limit' => $limit,
            'current' => $current_users,
            'logins_total' => $total_logins, 
            'plan_id' => $plan_id,
            'percentage' => round($percentage, 0)
        ];
    }

    // 🟢 Subscription Billing & Expiry Details
    public function get_subscription_details($tenant_id)
    {
        $sql = "SELECT s.id, p.plan_name, s.start_date, s.expiry_date, s.status 
                FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.tenant_id = ? 
                ORDER BY s.expiry_date ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $subscriptions = [];
        $today = new DateTime();

        while ($row = $result->fetch_assoc()) {
            $expiry = new DateTime($row['expiry_date']);
            $interval = $today->diff($expiry);
            
            $days_left = (int)$interval->format("%r%a");
            $row['days_remaining'] = $days_left;
            
            if ($days_left < 0) {
                $row['status_tag'] = 'Expired';
                $row['color'] = '#ef4444';
            } elseif ($days_left <= 7) {
                $row['status_tag'] = 'Expiring Soon';
                $row['color'] = '#f97316';
            } else {
                $row['status_tag'] = 'Active';
                $row['color'] = '#22c55e';
            }
            
            $subscriptions[] = $row;
        }
        return $subscriptions;
    }

    // 🟢 Recent Activity Logs
    public function get_recent_activity($tenant_id) {
        $sql = "SELECT action, created_at 
                FROM audit_logs 
                WHERE tenant_id = ? 
                ORDER BY created_at DESC LIMIT 5";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>