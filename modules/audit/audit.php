<?php
// modules/audit/audit.php

class AuditLog {
    private $db;

    public function __construct($db_conn) {
        $this->db = $db_conn;
    }

    // Function jo har activity ko record karega
    public function log($tenant_id, $user_id, $action, $module) {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $sql = "INSERT INTO audit_logs (tenant_id, user_id, action, module, ip_address) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("iisss", $tenant_id, $user_id, $action, $module, $ip);
        
        return $stmt->execute();
    }
}
?>