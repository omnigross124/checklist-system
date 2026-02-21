<?php
session_start();
if(!isset($_SESSION['role']) || !in_array($_SESSION['role'],['admin','employee'])){
    header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];
$report_id = intval($_GET['report_id']);

// verify report belongs to this admin
$sql = "SELECT r.id, r.submitted_at,
               u.name AS emp_name, u.email AS emp_email,
               t.title AS task_title
        FROM reports r
        LEFT JOIN assignments a ON r.assignment_id = a.id
        JOIN users u ON r.employee_id = u.id
        JOIN tasks t ON r.task_id = t.id
        WHERE r.id=? 
        AND (
              a.assigned_by=? 
              OR r.employee_id=?
            )
        LIMIT 1";


$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $report_id, $admin_id, $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){ die("Invalid report"); }
$info = $res->fetch_assoc();

// items
$itemStmt = $conn->prepare("SELECT task_item,response,comment FROM report_items WHERE report_id=?");
$itemStmt->bind_param("i", $report_id);
$itemStmt->execute();
$items = $itemStmt->get_result();

function badgeFor($v){
  $v = strtolower($v);
  if($v == 'yes') return 'ok';
  if($v == 'no') return 'off';
  return 'warn';
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>View Report</title>
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
        <h2>Report #<?php echo $report_id; ?> — <?php echo htmlspecialchars($info['task_title']); ?></h2>
        <p>Employee: <?php echo htmlspecialchars($info['emp_name']); ?> (<?php echo htmlspecialchars($info['emp_email']); ?>) • Submitted: <?php echo htmlspecialchars($info['submitted_at']); ?></p>
      </div>
      <div class="actions">
        <a class="btn" href="reports_list.php">Back</a>
      </div>
    </div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>Checklist Item</th>
          <th>Response</th>
          <th>Comment</th>
        </tr>
        <?php while($row = $items->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['task_item']); ?></td>
            <td>
              <span class="badge <?php echo badgeFor($row['response']); ?>">
                <?php echo htmlspecialchars(strtoupper($row['response'])); ?>
              </span>
            </td>
            <td style="color:var(--muted);"><?php echo nl2br(htmlspecialchars($row['comment'])); ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>

  </div>
</div>

</body>
</html>
