<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];

$sql = "SELECT s.id, s.title, s.description, s.due_date, s.status, s.created_at,
               u.name AS emp_name, u.email AS emp_email
        FROM special_tasks s
        JOIN users u ON s.employee_id = u.id
        WHERE s.assigned_by=?
        ORDER BY s.id DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i",$admin_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Special Tasks</title>
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
        <h2>Special Tasks List</h2>
        <p>Urgent tasks assigned by you.</p>
      </div>
      <div class="actions">
        <a class="btn primary" href="special_task.php">Assign Special Task</a>
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
      <div class="alert ok"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>

    <div class="tablewrap">
      <table>
        <tr>
          <th>ID</th>
          <th>Title</th>
          <th>Employee</th>
          <th>Due</th>
          <th>Status</th>
          <th>Created</th>
        </tr>

        <?php if($res->num_rows == 0): ?>
          <tr><td colspan="6" style="color:var(--muted);">No special tasks yet.</td></tr>
        <?php endif; ?>

        <?php while($r = $res->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $r['id']; ?></td>
            <td>
              <b><?php echo htmlspecialchars($r['title']); ?></b><br>
              <span style="color:var(--muted);font-size:12px;">
                <?php echo htmlspecialchars($r['description']); ?>
              </span>
            </td>
            <td>
              <b><?php echo htmlspecialchars($r['emp_name']); ?></b><br>
              <span style="color:var(--muted);font-size:12px;"><?php echo htmlspecialchars($r['emp_email']); ?></span>
            </td>
            <td><?php echo htmlspecialchars($r['due_date']); ?></td>
            <td>
              <?php if($r['status']=='completed'): ?>
                <span class="badge ok">completed</span>
              <?php else: ?>
                <span class="badge warn">pending</span>
              <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($r['created_at']); ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>

  </div>
</div>

</body>
</html>
