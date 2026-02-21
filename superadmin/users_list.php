<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
  header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));

$result = $conn->query("SELECT id,login_id,name,email,role,status,created_at FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Users</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
  <div class="topbar">
    <div class="brand">
      <div class="logo"></div>
      <div>
        <h1>Checklist System</h1>
        <p>Super Admin Panel</p>
      </div>
    </div>
    <div class="userchip">
      <div class="avatar"><?php echo $initial; ?></div>
      <div class="meta">
        <b><?php echo htmlspecialchars($name); ?></b>
        <span>Role: Super Admin</span>
      </div>
      <a class="btn danger" href="../logout.php">Logout</a>
    </div>
  </div>

  <div class="page">
    <div class="pagehead">
      <div>
        <h2>All Users</h2>
        <p>Manage admins and employees.</p>
      </div>
      <div class="actions">
        <a class="btn primary" href="add_user.php">Add User</a>
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>ID</th><th>Login ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th>
        </tr>

        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['login_id'] ?? '-'); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><span class="badge warn"><?php echo htmlspecialchars($row['role']); ?></span></td>
            <td>
              <?php if($row['status']=='active'): ?>
                <span class="badge ok">active</span>
              <?php else: ?>
                <span class="badge off">inactive</span>
              <?php endif; ?>
            </td>
            <td><?php echo $row['created_at']; ?></td>
            <td>
              <?php if($row['id'] != $_SESSION['user_id']): ?>
                <div class="actions-inline">
                  <a class="link" href="toggle_status.php?id=<?php echo $row['id']; ?>">
                    <?php echo ($row['status']=='active') ? "Deactivate" : "Activate"; ?>
                  </a>
                  <a class="link" href="delete_user.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Delete user?')">Delete</a>
                </div>
              <?php else: ?>
                <span class="badge">You</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </div>
</div>

</body>
</html>
