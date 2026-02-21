<?php
session_start();
date_default_timezone_set("Asia/Kolkata");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php");
    exit();
}

require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];

$sql = "
SELECT 
  da.id,
  da.start_date,
  da.active,
  da.created_at,
  u.name AS employee_name,
  u.email AS employee_email,
  t.title AS checklist_title
FROM daily_assignments da
JOIN users u ON u.id = da.employee_id
JOIN tasks t ON t.id = da.task_id
WHERE u.parent_id = ?
ORDER BY da.id DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assigned List</title>
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
        <h2>Daily Assigned List</h2>
        <p>Track daily checklists assigned to employees.</p>
      </div>
      <div class="actions">
        <a class="btn primary" href="assign_daily.php">Assign Daily</a>
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>ID</th>
          <th>Employee</th>
          <th>Checklist</th>
          <th>Start Date</th>
          <th>Status</th>
          <th>Assigned At</th>
        </tr>

        <?php if($result->num_rows == 0): ?>
          <tr>
            <td colspan="6" style="color:var(--muted);">
              No daily assignments found.
            </td>
          </tr>
        <?php endif; ?>

        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
          <td>#<?php echo $row['id']; ?></td>

          <td>
            <b><?php echo htmlspecialchars($row['employee_name']); ?></b><br>
            <span style="color:var(--muted);font-size:12px;">
              <?php echo htmlspecialchars($row['employee_email']); ?>
            </span>
          </td>

          <td>
            <span class="badge warn">
              <?php echo htmlspecialchars($row['checklist_title']); ?>
            </span>
          </td>

          <td><?php echo htmlspecialchars($row['start_date']); ?></td>

          <td>
            <?php if($row['active'] == 'yes'): ?>
              <span class="badge ok">Active</span>
            <?php else: ?>
              <span class="badge warn">Inactive</span>
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
