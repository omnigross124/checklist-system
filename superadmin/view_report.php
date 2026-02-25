<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
    header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));

$report_id = isset($_GET['report_id']) ? intval($_GET['report_id']) : 0;
if($report_id <= 0){ die("Invalid report"); }

/*
  This works for BOTH:
  - daily submissions (reports.task_id)
  - any other submissions that are stored in reports with task_id
*/
$sql = "SELECT 
            r.id,
            r.report_date,
            r.submitted_at,
            emp.name AS emp_name,
            emp.email AS emp_email,
            adm.name AS admin_name,
            adm.email AS admin_email,
            t.title AS task_title
        FROM reports r
        JOIN users emp ON r.employee_id = emp.id
        LEFT JOIN users adm ON emp.parent_id = adm.id
        JOIN tasks t ON r.task_id = t.id
        WHERE r.id=? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $report_id);
$stmt->execute();
$res = $stmt->get_result();

if($res->num_rows == 0){ die("Invalid report"); }
$info = $res->fetch_assoc();

// report items
$itemStmt = $conn->prepare("SELECT task_item, response, comment FROM report_items WHERE report_id=? ORDER BY id ASC");
$itemStmt->bind_param("i", $report_id);
$itemStmt->execute();
$itemsRes = $itemStmt->get_result();

/* NEW: collect items + calculate YES/NA/NO counts */
$items = [];
$yesCount = 0;
$naCount  = 0;
$noCount  = 0;

while($row = $itemsRes->fetch_assoc()){
  $resp = strtolower(trim((string)$row['response']));

  if($resp === 'yes') $yesCount++;
  else if($resp === 'no') $noCount++;
  else if($resp === 'na' || $resp === 'n/a') $naCount++;
  else $naCount++; // treat unknown as NA

  $items[] = $row;
}

$totalItems = $yesCount + $naCount + $noCount;
if($totalItems > 0){
  $yesPct = round(($yesCount / $totalItems) * 100, 2);
  $naPct  = round(($naCount  / $totalItems) * 100, 2);
  $noPct  = max(0, 100 - $yesPct - $naPct); // fix rounding gap
} else {
  $yesPct = $naPct = $noPct = 0;
}

function badgeFor($v){
  $v = strtolower(trim((string)$v));
  if($v == 'yes') return 'ok';
  if($v == 'no') return 'off';
  return 'warn'; // NA
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

  <div class="page">
    <div class="pagehead">
      <div>
        <h2>Report #<?php echo $report_id; ?> — <?php echo htmlspecialchars($info['task_title']); ?></h2>
        <p>
          Employee: <?php echo htmlspecialchars($info['emp_name']); ?> (<?php echo htmlspecialchars($info['emp_email']); ?>)
          • Admin: <?php echo htmlspecialchars($info['admin_name'] ?? 'N/A'); ?> (<?php echo htmlspecialchars($info['admin_email'] ?? ''); ?>)
          • Submitted: <?php echo htmlspecialchars($info['submitted_at']); ?>
        </p>
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

        <?php if(count($items) == 0): ?>
          <tr><td colspan="3" style="color:var(--muted);">No report items found.</td></tr>
        <?php endif; ?>

        <?php foreach($items as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['task_item']); ?></td>
            <td>
              <span class="badge <?php echo badgeFor($row['response']); ?>">
                <?php echo htmlspecialchars(strtoupper($row['response'])); ?>
              </span>
            </td>
            <td style="color:var(--muted);"><?php echo nl2br(htmlspecialchars($row['comment'])); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <!-- NEW: Progress bar below report (uses your theme CSS in style.css) -->
    <div class="progressCard">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
        <b class="pcTitle">Progress</b>
        <span class="pcMeta">Total items: <?php echo $totalItems; ?></span>
      </div>

      <div style="margin-top:12px;">
        <div class="stackBar" title="YES: <?php echo $yesCount; ?> | NA: <?php echo $naCount; ?> | NO: <?php echo $noCount; ?>">

          <?php if($yesPct > 0): ?>
            <div class="seg yes" style="width:<?php echo $yesPct; ?>%"></div>
          <?php endif; ?>

          <?php if($naPct > 0): ?>
            <div class="seg na" style="width:<?php echo $naPct; ?>%"></div>
          <?php endif; ?>

          <?php if($noPct > 0): ?>
            <div class="seg no" style="width:<?php echo $noPct; ?>%"></div>
          <?php endif; ?>

        </div>

        <div class="legend">
          <span><span class="dot yes"></span> <b>YES</b>: <?php echo $yesCount; ?> (<?php echo round($yesPct); ?>%)</span>
          <span><span class="dot na"></span> <b>NA</b>: <?php echo $naCount; ?> (<?php echo round($naPct); ?>%)</span>
          <span><span class="dot no"></span> <b>NO</b>: <?php echo $noCount; ?> (<?php echo round($noPct); ?>%)</span>
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>