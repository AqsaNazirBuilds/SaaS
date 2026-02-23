<?php
// modules/subscription/plan_logic.php
require_once(__DIR__ . '/../../config/db.php');

class PlanLogic {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    /**
     * Professional Logic: Get Detailed Usage Analytics
     * Ye function bars aur graphs ke liye data taiyar karta hai
     */
    public function get_user_usage($tenant_id) {
        // 1. Plan ki detail aur limit nikalna
        $sql = "SELECT p.user_limit, p.plan_name FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.tenant_id = ? AND s.status = 'active' LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $plan_data = $stmt->get_result()->fetch_assoc();
        
        $limit = $plan_data['user_limit'] ?? 0;
        $plan_name = $plan_data['plan_name'] ?? 'No Active Plan';

        // 2. Count current users (Database se real-time count)
        $sql_count = "SELECT COUNT(id) as total FROM users WHERE tenant_id = ?";
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_count->bind_param("i", $tenant_id);
        $stmt_count->execute();
        $current_users = $stmt_count->get_result()->fetch_assoc()['total'];

        // 3. Percentage Calculation (Graph bar ki width ke liye)
        // Agar limit 999 (Unlimited) hai toh percentage 0 rakhenge
        $percentage = ($limit > 0 && $limit != 999) ? ($current_users / $limit) * 100 : 0;

        return [
            'limit' => $limit,
            'current' => $current_users,
            'remaining' => ($limit == 999) ? 'Unlimited' : ($limit - $current_users),
            'percentage' => round($percentage, 0),
            'can_add' => ($limit == 999 || $current_users < $limit),
            'plan_name' => $plan_name,
            'status_color' => ($percentage > 80) ? '#f97316' : '#1f3b57' // Orange if near limit
        ];
    }
}
?>