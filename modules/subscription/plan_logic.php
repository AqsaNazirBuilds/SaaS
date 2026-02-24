
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

    // 1. Monthly Logins
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

    // 2. User Registration
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


    // 2. Total Revenue (Ab reports.php mein error nahi aayega)
public function get_total_revenue($tenant_id) {
    $sql = "SELECT SUM(amount) as total FROM payments WHERE tenant_id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    // Agar koi payment nahi mili toh 0 return karega
    return $result['total'] ?? 0;
}

    // 3. Premium Sales
    public function get_premium_sales($tenant_id, $filter = 'month') {
        $date_cond = $this->get_date_condition($filter, 'start_date');
        $sql = "SELECT MONTHNAME(start_date) as month, COUNT(*) as total 
                FROM subscriptions 
                WHERE tenant_id = ? AND plan_id = 3 $date_cond
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

    // 4. Most Active Users
    public function get_top_users($tenant_id, $filter = 'month') {
        $date_cond = $this->get_date_condition($filter, 'a.created_at');
        $sql = "SELECT u.id as user_id, u.name as username, COUNT(a.id) as activity_count 
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

    // 5. User Usage Summary (FIXED: Dynamic Limit from Database)
    public function get_user_usage($tenant_id) {
        // ERROR REMOVED: Ab hum direct join se plans table se limit utha rahe hain
        $sql = "SELECT p.user_limit, p.plan_name, s.plan_id 
                FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.tenant_id = ? AND s.status = 'active' LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $plan_data = $stmt->get_result()->fetch_assoc();
        
        // Agar plan data na mile toh default (Trial) values set karein
        $limit = $plan_data['user_limit'] ?? 5; 
        $plan_name = $plan_data['plan_name'] ?? 'Free Trial';
        $plan_id = $plan_data['plan_id'] ?? 1;

        // Current User Count
        $sql_count = "SELECT COUNT(id) as total FROM users WHERE tenant_id = ?";
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_count->bind_param("i", $tenant_id);
        $stmt_count->execute();
        $current_users = $stmt_count->get_result()->fetch_assoc()['total'];

        // Total Logins
        $sql_logins = "SELECT COUNT(*) as total_logins FROM audit_logs WHERE tenant_id = ? AND action LIKE '%Login%'";
        $stmt_l = $this->db->prepare($sql_logins);
        $stmt_l->bind_param("i", $tenant_id);
        $stmt_l->execute();
        $total_logins = $stmt_l->get_result()->fetch_assoc()['total_logins'] ?? 0;

        $percentage = ($limit > 0) ? ($current_users / $limit) * 100 : 0;

        return [
            'limit' => $limit, // Ab ye Basic par 10 aur Premium par 999 sahi dega
            'current' => $current_users,
            'logins_total' => $total_logins, 
            'plan_id' => $plan_id,
            'plan_name' => $plan_name,
            'percentage' => round($percentage, 1)
        ];
    }

    // 6. Subscription Billing & Expiry Details
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

    // 7. Recent Activity Logs
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

    // 8. Check if Subscription is Blocked
    public function is_subscription_blocked($tenant_id) {
        $sql = "SELECT status, expiry_date FROM subscriptions WHERE tenant_id = ? AND status = 'active' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $sub = $stmt->get_result()->fetch_assoc();

        if (!$sub) return true;

        $today = date('Y-m-d');
        if ($today > $sub['expiry_date']) {
            return true; 
        }
        return false;
    }

    // 9. Check Feature Access
    public function can_access_feature($tenant_id, $feature_name) {
        $sql = "SELECT p.features_json FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.tenant_id = ? AND s.status = 'active' LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if (!$data) return false;

        $features = json_decode($data['features_json'], true);
        return isset($features[$feature_name]) && $features[$feature_name] === true;
    }
}


?>