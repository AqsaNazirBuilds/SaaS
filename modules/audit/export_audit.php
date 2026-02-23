<?php
// modules/audit/export_audit.php
require_once(__DIR__ . '/../../config/db.php');

// 1. Kisi bhi kism ki purani output ya space ko khatam karna
if (ob_get_length()) ob_end_clean();

// 2. Database se data nikalna (Columns aapke T.jpg ke mutabiq hain)
$query = "SELECT id, user_id, action, module, created_at FROM audit_logs ORDER BY created_at DESC";
$result = $db->query($query);

// 3. Browser ko Headers bhejna
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=audit_report_' . date('Y-m-d') . '.csv');
header('Pragma: no-cache');
header('Expires: 0');

// 4. File pointer open karna
$output = fopen('php://output', 'w');

// 5. Excel ki Headings likhna
fputcsv($output, array('Log ID', 'User ID', 'Action Performed', 'Module Name', 'Date Time'));

// 6. Data ko rows mein convert karna
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, array(
            $row['id'],
            $row['user_id'],
            $row['action'],
            $row['module'],
            $row['created_at']
        ));
    }
}

// 7. Kaam khatam
fclose($output);
exit();
?>