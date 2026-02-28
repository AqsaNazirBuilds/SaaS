<?php
// modules/subscription/subscription.php
require_once(__DIR__ . '/../../config/db.php'); 

class Subscription {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    /**
     * NEW Helper: Get Upgrade URL
     * Taake pure project mein kahin bhi upgrade link chahiye ho to yahi se mil jaye
     */
    public function get_upgrade_url() {
        return BASE_URL . "modules/subscription/checkout.php";
    }

    /**
     * Active Subscription Fetch Logic
     */
    public function get_active_subscription($tenant_id) {
        $sql = "SELECT s.*, p.plan_name, p.user_limit, p.features_json 
                FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.tenant_id = ? AND s.status = 'active' 
                ORDER BY s.id DESC LIMIT 1"; // Added order to get latest
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    /**
     * Subscription Status Check (Expiry Logic)
     */
    public function is_valid($subscription) {
        if (!$subscription) return false;
        
        $today = new DateTime();
        $expiry = new DateTime($subscription['expiry_date']);
        
        return ($expiry >= $today);
    }

    /**
     * Feature Permission Logic (Phase 3)
     */
    public function has_feature($tenant_id, $feature_key) {
        $sub = $this->get_active_subscription($tenant_id);
        if (!$sub || !$this->is_valid($sub)) return false;

        $features = json_decode($sub['features_json'], true);
        return isset($features[$feature_key]) && $features[$feature_key] === true;
    }

    /**
     * Plan Limit Checker (Phase 2)
     */
    public function check_limit($tenant_id) {
        $sub = $this->get_active_subscription($tenant_id);
        if (!$sub) return ['can_add' => false, 'message' => 'No active plan'];

        // Current users count
        $sql_count = "SELECT COUNT(id) as total FROM users WHERE tenant_id = ?";
        $stmt_count = $this->db->prepare($sql_count);
        $stmt_count->bind_param("i", $tenant_id);
        $stmt_count->execute();
        $current_users = $stmt_count->get_result()->fetch_assoc()['total'];

        $limit = $sub['user_limit'];
        
        return [
            'can_add' => ($limit == 999 || $current_users < $limit),
            'current' => $current_users,
            'limit' => $limit,
            'percentage' => ($limit > 0 && $limit != 999) ? round(($current_users / $limit) * 100) : 0,
            'upgrade_link' => $this->get_upgrade_url() // BASE_URL integrated here
        ];
    }
}
?>