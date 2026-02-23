<?php
// modules/subscription/subscription.php
require_once(__DIR__ . '/../../config/db.php'); 

class Subscription {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    // STEP 2: Active Subscription Fetch Logic
    public function get_active_subscription($tenant_id) {
        $sql = "SELECT s.*, p.plan_name, p.user_limit, p.features_json 
                FROM subscriptions s 
                JOIN plans p ON s.plan_id = p.id 
                WHERE s.tenant_id = ? AND s.status = 'active' 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $tenant_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
    }

    // STEP 3: Subscription Status Check (Expiry Logic)
    public function is_valid($subscription) {
        if (!$subscription) return false;
        
        $today = new DateTime();
        $expiry = new DateTime($subscription['expiry_date']);
        
        return ($expiry >= $today);
    }
}
?>