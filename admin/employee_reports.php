<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$admin_id = $_SESSION['user_id'];
$emp_id = intval($_GET['emp_id']);

// security: employee must belong to this admin
$chk = $conn->prepare("SELECT id,name,email FROM users WHERE id=? AND role='employee' AND parent_id=? LIMIT 1");
$chk->bind_param("ii", $emp_id, $admin_id);
$chk->execute();
$empRes = $chk->get_result();
if($empRes->num_rows==0){ die("Invalid employee"); }
$emp = $empRes->fetch_assoc();

$sql = "
SELECT 
    r.id AS report_id,
    r.report_date,
    r.submitted_at,
    t.title AS task_title
FROM reports r
JOIN tasks t ON t.id = r.task_id
JOIN daily_assignments da ON da.task_id = r.task_id AND da.employee_id = r.employee_id
WHERE da.assigned_by=? AND r.employee_id=?
ORDER BY r.report_date DESC
";


$stmt = $conn->prepare($sql);
$stmt->bind_param("ii",$admin_id,$emp_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Employee Reports</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
  <div class="page">
    <div class="pagehead">
      <div>
        <h2>Reports — <?php echo htmlspecialchars($emp['name']); ?></h2>
        <p><?php echo htmlspecialchars($emp['email']); ?></p>
      </div>
      <div class="actions">
        <a class="btn" href="reports_list.php">Back</a>
      </div>
    </div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>Report ID</th>
          <th>Checklist</th>
          <th>Submitted At</th>
          <th>Action</th>
        </tr>

        <?php if($res->num_rows==0): ?>
          <tr><td colspan="4" style="color:var(--muted);">No reports submitted yet.</td></tr>
        <?php endif; ?>

        <?php while($r=$res->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $r['report_id']; ?></td>
            <td><span class="badge warn"><?php echo htmlspecialchars($r['task_title']); ?></span></td>
            <td><?php echo htmlspecialchars($r['submitted_at']); ?></td>
            <td>
              <a class="btn success" href="view_report.php?report_id=<?php echo $r['report_id']; ?>">View</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>

  </div>
</div>
</body>
</html>
