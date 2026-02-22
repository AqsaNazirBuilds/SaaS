<?php
include('../../core/tenant_middleware.php'); 
include('../../config/db.php');

$tenant_id = $_SESSION['tenant_id'];

// Aqsa's Security: Sirf apni company ke users fetch karna
$query = "SELECT * FROM users WHERE tenant_id = $tenant_id";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Users - Aqsa Module</title>
    <link rel="stylesheet" href="../../css/aqsa_core_style.css">
</head>
<body>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Company Users</h2>
        <a href="add_user.php" class="btn btn-primary">+ Add New User</a>
    </div>

    <?php if(isset($_GET['msg'])): ?>
        <p class="badge active" style="display:block; margin-bottom:15px;"><?php echo $_GET['msg']; ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($user = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $user['id']; ?></td>
                <td><?php echo $user['name']; ?></td>
                <td><?php echo $user['email']; ?></td>
                <td>
                    <span class="badge <?php echo ($user['status'] == 'active') ? 'active' : 'inactive'; ?>">
                        <?php echo strtoupper($user['status']); ?>
                    </span>
                </td>
                <td>
                    <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline">Edit</a>
                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-outline" style="border-color:red; color:red;" onclick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>