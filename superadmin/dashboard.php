<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
  header("Location: ../login.php"); exit();
}
$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
?>
<!DOCTYPE html>
<html>
<head>
  <title>Super Admin Dashboard</title>
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

  <div class="grid">
    <div class="card">
      <h3>Users Management</h3>
      <p>Create Admins and Employees. Control active/inactive status and keep your org clean.</p>
      <div class="actions">
        <a class="btn primary" href="add_user.php">Add Admin / Employee</a>
        <a class="btn" href="users_list.php">View Users</a>
      </div>
    </div>

    <div class="card">
      <h3>Reports Overview</h3>
      <p>Monitor all checklist submissions across admins and employees with filters.</p>
      <div class="actions">
        <a class="btn success" href="reports_list.php">All Reports</a>
      </div>
    </div>

    <div class="card">
      <h3>Quick Tips</h3>
      <p>For best checklist experience: Admin should enter one checklist question per line in the description.</p>
      <div class="actions">
        
        <a class="btn" href="reports_list.php">Check Submissions</a>
      </div>
    </div>

    <div class="card">
      <h3>System Health</h3>
      <p>Everything running locally on your machine. Later you can deploy to hosting (optional).</p>
      <div class="actions">
        <a class="btn" href="#">Localhost</a>
        <a class="btn danger" href="../logout.php">Logout</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>
