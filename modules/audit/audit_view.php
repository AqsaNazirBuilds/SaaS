<?php
// modules/audit/audit_view.php
require_once(__DIR__ . '/../../config/db.php');
require_once(__DIR__ . '/audit.php');

$audit_obj = new AuditLog($db);

// Logic for Search and Filters
$search = $_GET['search'] ?? '';
$module = $_GET['module'] ?? '';

$query = "SELECT * FROM audit_logs WHERE 1=1";
if ($search) $query .= " AND action_performed LIKE '%$search%'";
if ($module) $query .= " AND module_name = '$module'";
$query .= " ORDER BY created_at DESC";

$logs = $db->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Audit Logs | Saas Project</title>
    <link rel="stylesheet" href="../../css/laiba/audit_view.css"> <link rel="stylesheet" href="../../css/laiba/audit.css">  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="main-wrapper" style="max-width: 1200px;"> <div class="status-card">
        
        <div class="card-header">
            <div class="header-left">
                <h2><i class="fas fa-history"></i> System Activity Logs</h2>
                <p>Track all changes and actions performed</p>
            </div>
        </div>

        <div class="filter-bar">
            <form method="GET" style="display: flex; gap: 10px; width: 100%;">
                <div class="search-input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="search-field" placeholder="Search logs..." value="<?php echo $search; ?>">
                </div>
                
                <select name="module" class="filter-select">
                    <option value="">All Modules</option>
                    <option value="Subscription">Subscription</option>
                    <option value="Users">Users</option>
                </select>

                <button type="submit" class="btn-filter">Filter</button>
            </form>
        </div>

        <div style="padding: 20px;">
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
   <div class="table-container">
    <table class="audit-table">
        <tbody>
            <?php while($row = $logs->fetch_assoc()): ?>
            <tr>
                <td class="badge-id">#<?php echo $row['id']; ?></td>
                <td style="font-weight: 500;">User ID: <?php echo $row['user_id']; ?></td>
                <td><?php echo $row['action_performed'] ?? $row['action']; ?></td>
                <td><span class="badge-module"><?php echo $row['module_name'] ?? $row['module']; ?></span></td>
                <td class="timestamp"><?php echo $row['created_at']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>         </table>
        </div>
    </div>
</div>

</body>
</html>