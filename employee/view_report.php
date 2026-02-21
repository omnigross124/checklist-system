<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!='employee'){
  header("Location: ../login.php"); exit();
}
require_once "../config/db.php";
date_default_timezone_set("Asia/Kolkata");

$emp_id = $_SESSION['user_id'];
$report_id = (int)($_GET['report_id'] ?? 0);
if($report_id <= 0) die("Invalid report.");

/* detect if reports table has task_id column */
$hasTaskId = false;
$col = $conn->query("SHOW COLUMNS FROM reports LIKE 'task_id'");
if($col && $col->num_rows > 0) $hasTaskId = true;

if($hasTaskId){
  $sql = "SELECT r.id, r.submitted_at, r.report_date, t.title AS task_title
          FROM reports r
          LEFT JOIN tasks t ON t.id = r.task_id
          WHERE r.id=? AND r.employee_id=? LIMIT 1";
} else {
  // fallback if old schema uses assignment_id
  $sql = "SELECT r.id, r.submitted_at, r.report_date, t.title AS task_title
          FROM reports r
          JOIN assignments a ON a.id = r.assignment_id
          JOIN tasks t ON t.id = a.task_id
          WHERE r.id=? AND r.employee_id=? LIMIT 1";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $report_id, $emp_id);
$stmt->execute();
$infoRes = $stmt->get_result();
if($infoRes->num_rows == 0) die("Report not found.");
$info = $infoRes->fetch_assoc();

/* items */
$itemStmt = $conn->prepare("SELECT task_item, response, comment FROM report_items WHERE report_id=? ORDER BY id ASC");
$itemStmt->bind_param("i", $report_id);
$itemStmt->execute();
$items = $itemStmt->get_result();

function badgeFor($v){
  $v = strtolower(trim($v));
  if($v == 'yes') return 'ok';
  if($v == 'no') return 'off';
  return 'warn';
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Review Submission</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
  <div class="page">
    <div class="pagehead">
      <div>
        <h2>Review — <?php echo htmlspecialchars($info['task_title'] ?? 'Checklist'); ?></h2>
        <p>Date: <?php echo htmlspecialchars($info['report_date']); ?> • Submitted: <?php echo htmlspecialchars($info['submitted_at']); ?></p>
      </div>
      <div class="actions">
        <a class="btn" href="assignments.php">Back</a>
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