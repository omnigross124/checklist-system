<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT id, login_id, name, email, status, created_at
    FROM users
    WHERE parent_id=? AND role='employee'
    ORDER BY id DESC
");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Team</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
  <div class="topbar">
    <div class="brand">
      <div class="logo"></div>
      <div>
        <h1>Checklist System</h1>
        <p>Admin Panel</p>
      </div>
    </div>

    <div class="userchip">
      <div class="avatar"><?php echo $initial; ?></div>
      <div class="meta">
        <b><?php echo htmlspecialchars($name); ?></b>
        <span>Role: Admin</span>
      </div>
      <a class="btn danger" href="../logout.php">Logout</a>
    </div>
  </div>

  <div class="page">
    <div class="pagehead">
      <div>
        <h2>My Team</h2>
        <p>Employees under your admin account.</p>
      </div>
      <div class="actions">
        <a class="btn primary" href="add_employee.php">Add Employee</a>
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>ID</th><th>Employee ID</th><th>Name</th><th>Email</th><th>Status</th><th>Created</th>
        </tr>

        <?php if($result->num_rows == 0): ?>
          <tr><td colspan="5" style="color:var(--muted);">No employees in your team yet.</td></tr>
        <?php endif; ?>

        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $row['id']; ?></td>
            <td><?php echo htmlspecialchars($row['login_id']); ?></td>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td>
              <?php if($row['status']=='active'): ?>
                <span class="badge ok">active</span>
              <?php else: ?>
                <span class="badge off">inactive</span>
              <?php endif; ?> 
            </td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </div>
</div>

</body>
</html>
