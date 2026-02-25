<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee'){
  header("Location: ../index.php"); exit();
}
$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
require_once "../config/db.php";
$emp_id = $_SESSION['user_id'];

$pendingAssignments = 0;
$pendingSpecial = 0;

$q1 = $conn->prepare("SELECT COUNT(*) c FROM assignments WHERE employee_id=? AND status='pending'");
$q1->bind_param("i",$emp_id);
$q1->execute();
$pendingAssignments = (int)$q1->get_result()->fetch_assoc()['c'];

$q2 = $conn->prepare("SELECT COUNT(*) c FROM special_tasks WHERE employee_id=? AND status='pending'");
$q2->bind_param("i",$emp_id);
$q2->execute();
$pendingSpecial = (int)$q2->get_result()->fetch_assoc()['c'];

?>

<!DOCTYPE html>
<html>
<head>
  <title>Employee Dashboard</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
  <div class="topbar">
    <div class="brand">
      <div class="logo"></div>
      <div>
        <h1>Checklist System</h1>
        <p>Employee Panel</p>
      </div>
    </div>

    <div class="userchip">
      <div class="avatar"><?php echo $initial; ?></div>
      <div class="meta">
        <b><?php echo htmlspecialchars($name); ?></b>
        <span>Role: Employee</span>
      </div>
      <a class="btn danger" href="../logout.php">Logout</a>
    </div>
  </div>

  <div class="grid">
    <div class="card" style="grid-column:span 12;">
      <h3>My Assignments</h3>
      <p>Open your assigned checklists, fill them, and submit.</p>
      <div class="actions">
  <span class="btnwrap">
    <a class="btn primary" href="assignments.php">Open Assignments</a>
    <?php if($pendingAssignments > 0): ?>
      <span class="badge-dot"><?php echo $pendingAssignments; ?></span>
    <?php endif; ?>
  </span>

  <span class="btnwrap">
    <a class="btn" href="special_tasks.php">Special Tasks</a>
    <?php if($pendingSpecial > 0): ?>
      <span class="badge-dot"><?php echo $pendingSpecial; ?></span>
    <?php endif; ?>
  </span>
</div>

    </div>
  </div>
</div>

</body>
</html>
