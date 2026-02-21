<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

date_default_timezone_set('Asia/Kolkata');

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];

/* Get Employees of this Admin */
$empQuery = $conn->prepare("
  SELECT id, name, email
  FROM users
  WHERE parent_id=? AND role='employee' AND status='active'
  ORDER BY id DESC
");
$empQuery->bind_param("i",$admin_id);
$empQuery->execute();
$employees = $empQuery->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Employee Progress</title>
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
        <h2>Employee Daily Work Progress</h2>
        <p>Shows today's completion of daily checklist.</p>
      </div>
      <div class="actions">
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>Employee</th>
          <th>Today's Progress</th>
          <th>Counts</th>
          <th>Action</th>
        </tr>

<?php
if($employees->num_rows == 0){
  echo "<tr><td colspan='4'>No employees found.</td></tr>";
}

$today = date('Y-m-d');

while($emp = $employees->fetch_assoc()):

$emp_id = $emp['id'];

/* Count Daily Assigned Templates */
$qAssigned = $conn->prepare("
  SELECT COUNT(*) total
  FROM daily_assignments
  WHERE employee_id=? AND active='yes'
");
$qAssigned->bind_param("i",$emp_id);
$qAssigned->execute();
$assigned = (int)$qAssigned->get_result()->fetch_assoc()['total'];

/* Count Today's Completed */
$qDone = $conn->prepare("
  SELECT COUNT(*) done
  FROM reports
  WHERE employee_id=? AND report_date=?
");
$qDone->bind_param("is",$emp_id,$today);
$qDone->execute();
$done = (int)$qDone->get_result()->fetch_assoc()['done'];

/* Calculate % */
$percent = ($assigned>0) ? round(($done/$assigned)*100) : 0;

/* Color Logic */
if($assigned==0) $barClass="gray";
else if($percent < 50) $barClass="red";
else if($percent < 80) $barClass="yellow";
else $barClass="green";
?>

<tr>
<td>
  <b><?php echo htmlspecialchars($emp['name']); ?></b><br>
  <span style="font-size:12px;color:var(--muted)">
    <?php echo htmlspecialchars($emp['email']); ?>
  </span>
</td>

<td style="min-width:300px;">
  <div class="progressTrack">
    <div class="progressFill <?php echo $barClass; ?>" style="width:<?php echo $percent; ?>%;"></div>
  </div>
  <small><?php echo $done; ?> / <?php echo $assigned; ?> done today (<?php echo $percent; ?>%)</small>
</td>

<td>
  <span class="badge"><?php echo $done; ?> completed</span>
  <span class="badge"><?php echo $assigned; ?> assigned</span>
</td>

<td>
  <a class="btn success" href="employee_reports.php?emp_id=<?php echo $emp_id; ?>">
    View Daily Reports
  </a>
</td>
</tr>

<?php endwhile; ?>
      </table>
    </div>
  </div>
</div>

</body>
</html>
