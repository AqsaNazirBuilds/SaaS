<?php
function getTenantId() {
    return $_SESSION['tenant_id'] ?? null;
}

// Ye function har query mein use hoga
function applyTenantFilter($query) {
    $tenant_id = getTenantId();
    return $query . " AND tenant_id = $tenant_id";
}
?>