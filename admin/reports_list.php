<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../index.php"); exit();
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

  <!-- ONLY ADDED: stacked progress bar style (matches your dark UI) -->
  <style>
    .progressTrack{
      width: 100%;
      height: 14px;
      border-radius: 999px;
      overflow: hidden;
      background: rgba(255,255,255,.08);
      border: 1px solid rgba(255,255,255,.10);
      box-shadow: inset 0 1px 2px rgba(0,0,0,.35);
      display:flex; /* IMPORTANT for stacked segments */
    }
    .progressSeg{ height:100%; }
    .progressSeg.green{ background:#22c55e; }  /* YES */
    .progressSeg.yellow{ background:#facc15; } /* NA */
    .progressSeg.red{ background:#ef4444; }    /* NO */
    .progressSeg.gray{ background: rgba(255,255,255,.18); } /* no data */
  </style>
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

/* Calculate % (KEEPING YOUR SAME LOGIC/TEXT) */
$percent = ($assigned>0) ? round(($done/$assigned)*100) : 0;

/* NEW: Count YES / NA / NO from today's report items */
$yesCount = 0; $naCount = 0; $noCount = 0;

$qItems = $conn->prepare("
  SELECT LOWER(TRIM(ri.response)) AS resp, COUNT(*) AS c
  FROM report_items ri
  JOIN reports r ON r.id = ri.report_id
  WHERE r.employee_id=? AND r.report_date=?
  GROUP BY LOWER(TRIM(ri.response))
");
$qItems->bind_param("is", $emp_id, $today);
$qItems->execute();
$itemRes = $qItems->get_result();

while($rr = $itemRes->fetch_assoc()){
  $resp = $rr['resp'];
  $c    = (int)$rr['c'];

  if($resp === 'yes') $yesCount += $c;
  else if($resp === 'no') $noCount += $c;
  else if($resp === 'na' || $resp === 'n/a') $naCount += $c;
  else {
    // if something unexpected stored, treat it as NA
    $naCount += $c;
  }
}

$totalItems = $yesCount + $naCount + $noCount;

if($totalItems > 0){
  $yesPct = round(($yesCount / $totalItems) * 100, 2);
  $naPct  = round(($naCount  / $totalItems) * 100, 2);
  // avoid rounding issues so total becomes 100%
  $noPct  = max(0, 100 - $yesPct - $naPct);
} else {
  $yesPct = 0; $naPct = 0; $noPct = 0;
}
?>

<tr>
<td>
  <b><?php echo htmlspecialchars($emp['name']); ?></b><br>
  <span style="font-size:12px;color:var(--muted)">
    <?php echo htmlspecialchars($emp['email']); ?>
  </span>
</td>

<td style="min-width:300px;">
  <!-- UPDATED: stacked bar (green + yellow + red) -->
  <div class="progressTrack" title="YES: <?php echo $yesCount; ?> | NA: <?php echo $naCount; ?> | NO: <?php echo $noCount; ?>">
    <?php if($assigned==0 || $totalItems==0): ?>
      <div class="progressSeg gray" style="width:100%;"></div>
    <?php else: ?>
      <?php if($yesPct > 0): ?>
        <div class="progressSeg green" style="width:<?php echo $yesPct; ?>%;"></div>
      <?php endif; ?>
      <?php if($naPct > 0): ?>
        <div class="progressSeg yellow" style="width:<?php echo $naPct; ?>%;"></div>
      <?php endif; ?>
      <?php if($noPct > 0): ?>
        <div class="progressSeg red" style="width:<?php echo $noPct; ?>%;"></div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- KEEPING YOUR SAME TEXT -->
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