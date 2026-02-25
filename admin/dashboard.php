<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../index.php"); exit();
}

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));

require_once "../config/db.php";

$admin_id = $_SESSION['user_id'];

// total assignments assigned by this admin
$stmt1 = $conn->prepare("SELECT COUNT(*) c FROM assignments WHERE assigned_by=?");
$stmt1->bind_param("i",$admin_id);
$stmt1->execute();
$totalAssigned = (int)$stmt1->get_result()->fetch_assoc()['c'];

// completed assignments
$stmt2 = $conn->prepare("SELECT COUNT(*) c FROM assignments WHERE assigned_by=? AND status='completed'");
$stmt2->bind_param("i",$admin_id);
$stmt2->execute();
$totalCompleted = (int)$stmt2->get_result()->fetch_assoc()['c'];

$percent = 0;
if($totalAssigned > 0){
  $percent = round(($totalCompleted / $totalAssigned) * 100);
}

// your rule: if completed < 5 => red else green
$barClass = ($totalCompleted < 5) ? "red" : "green";
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
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

      <!-- Settings Button -->
      <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin'): ?>
  <a class="btn settings" href="settings.php">⚙ Settings</a>
<?php endif; ?>

      <a class="btn danger" href="../logout.php">Logout</a>
    </div>
  </div>

  <div class="grid">
    <div class="card">
      <h3>Team</h3>
      <p>Add employees to your team and manage members.</p>
      <div class="actions">
        <a class="btn primary" href="add_employee.php">Add Employee</a>
        <a class="btn" href="team_list.php">Team List</a>
      </div>
    </div>

    <div class="card">
      <h3>Checklists</h3>
      <p>Create checklist templates (one question per line in description).</p>
      <div class="actions">
        <a class="btn primary" href="create_task.php">Create Checklist</a>
        <a class="btn" href="tasks_list.php">My Checklists</a>
      </div>
    </div>

    <div class="card">
      <h3>Assignments</h3>
      <p>Assign checklists to employees and track completion. Use Special Task for urgent one-time work.</p>

      <div class="actions grid2" style="margin-top:12px;">
        <a class="btn success" href="assign_daily.php">Assign Checklist</a>
        <a class="btn" href="assignments_list.php">Daily Assigned List</a>
        <a class="btn primary" href="special_task.php">Special Task</a>
        <a class="btn" href="special_tasks_list.php">Special Tasks List</a>
      </div>
    </div>

    <div class="card">
      <h3>Reports</h3>
      <p>View submitted employee reports.</p>
      <div class="actions">
        <a class="btn success" href="reports_list.php">Employee Reports</a>
      </div>
    </div>

  </div>
</div>

</body>
</html>