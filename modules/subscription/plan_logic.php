<?php
// modules/subscription/plan_logic.php
require_once(__DIR__ . '/../../config/db.php');

class PlanLogic {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    // 1. Monthly Logins (audit_logs table) - FIXED
    public function get_monthly_logins($tenant_id) {
        $sql = "SELECT MONTHNAME(created_at) as month, COUNT(*) as total 
                FROM audit_logs 
                WHERE tenant_id = ? AND action LIKE '%Login%' 
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

    // 2. User Registration (users table) - FIXED: Using 'created_at'
    public function get_monthly_registrations($tenant_id) {
        $sql = "SELECT MONTHNAME(created_at) as month, COUNT(*) as total 
                FROM users 
                WHERE tenant_id = ? 
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

    // 3. Premium Sales (subscriptions table) - FIXED: Using 'start_date'
    public function get_premium_sales($tenant_id) {
        $sql = "SELECT MONTHNAME(start_date) as month, COUNT(*) as total 
                FROM subscriptions 
                WHERE tenant_id = ? AND plan_id = 2 
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

    // 4. Most Active Users - FIXED: Using 'u.name' instead of 'username'
    public function get_top_users($tenant_id) {
        $sql = "SELECT u.name as username, COUNT(a.id) as activity_count 
                FROM users u 
                JOIN audit_logs a ON u.id = a.user_id 
                WHERE a.tenant_id = ? AND a.action LIKE '%Login%' 
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

    // 🟢 NAYA FUNCTION: Subscription Billing & Expiry Details
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
            
            // Days remaining calculate karna
            $days_left = (int)$interval->format("%r%a");
            $row['days_remaining'] = $days_left;
            
            // Status color decide karna
            if ($days_left < 0) {
                $row['status_tag'] = 'Expired';
                $row['color'] = '#ef4444'; // Red
            } elseif ($days_left <= 7) {
                $row['status_tag'] = 'Expiring Soon';
                $row['color'] = '#f97316'; // Orange
            } else {
                $row['status_tag'] = 'Active';
                $row['color'] = '#22c55e'; // Green
            }
            
            $subscriptions[] = $row;
        }
        return $subscriptions;
    }

    // 🟢 NAYA FUNCTION: Recent Activity Logs fetch karne ke liye
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