<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'employee'])) {
    header("Location: ../index.php");
    exit();
}

require_once "../config/db.php";

$name     = $_SESSION['name'] ?? '';
$initial  = strtoupper(substr($name, 0, 1));
$role     = $_SESSION['role'];
$user_id  = (int)($_SESSION['user_id'] ?? 0);

$report_id = isset($_GET['report_id']) ? (int)$_GET['report_id'] : 0;
if ($report_id <= 0) {
    die("Invalid report");
}

/**
 * CHANGE THIS IF YOU WANT STRICT ADMIN PERMISSIONS:
 * - true  => admin can view ANY report
 * - false => admin can view only reports from assignments created by them
 */
$ADMIN_CAN_VIEW_ALL = true;

/**
 * Fetch report header info with role-based access control
 */
if ($role === 'admin') {

    if ($ADMIN_CAN_VIEW_ALL) {
        $sql = "SELECT r.id, r.submitted_at,
                       u.name AS emp_name, u.email AS emp_email,
                       t.title AS task_title
                FROM reports r
                JOIN users u ON r.employee_id = u.id
                JOIN tasks t ON r.task_id = t.id
                WHERE r.id=?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $report_id);
    } else {
        $sql = "SELECT r.id, r.submitted_at,
                       u.name AS emp_name, u.email AS emp_email,
                       t.title AS task_title
                FROM reports r
                LEFT JOIN assignments a ON r.assignment_id = a.id
                JOIN users u ON r.employee_id = u.id
                JOIN tasks t ON r.task_id = t.id
                WHERE r.id=? AND a.assigned_by=?
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $report_id, $user_id);
    }

} else {
    $sql = "SELECT r.id, r.submitted_at,
                   u.name AS emp_name, u.email AS emp_email,
                   t.title AS task_title
            FROM reports r
            JOIN users u ON r.employee_id = u.id
            JOIN tasks t ON r.task_id = t.id
            WHERE r.id=? AND r.employee_id=?
            LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $report_id, $user_id);
}

$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    die("Invalid report");
}
$info = $res->fetch_assoc();

/**
 * Fetch checklist items
 */
$itemStmt = $conn->prepare("SELECT task_item, response, comment FROM report_items WHERE report_id=?");
$itemStmt->bind_param("i", $report_id);
$itemStmt->execute();
$itemsRes = $itemStmt->get_result();

/**
 * Read items into array so we can:
 * 1) calculate progress
 * 2) still display rows
 */
$items = [];
$yesCount = 0;
$noCount  = 0;
$naCount  = 0;

while ($row = $itemsRes->fetch_assoc()) {
    $resp = strtolower(trim((string)$row['response']));

    if ($resp === 'yes') $yesCount++;
    else if ($resp === 'no') $noCount++;
    else if ($resp === 'na' || $resp === 'n/a') $naCount++;
    else {
        // if your system stores something else, treat as NA by default
        $naCount++;
        $row['response'] = 'NA';
    }

    $items[] = $row;
}

$total = count($items);
$yesPct = $total > 0 ? round(($yesCount / $total) * 100, 1) : 0;
$naPct  = $total > 0 ? round(($naCount  / $total) * 100, 1) : 0;
$noPct  = $total > 0 ? round(($noCount  / $total) * 100, 1) : 0;

function badgeFor($v) {
    $v = strtolower(trim((string)$v));
    if ($v === 'yes') return 'ok';
    if ($v === 'no')  return 'off';
    if ($v === 'na' || $v === 'n/a') return 'warn';
    return 'warn';
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>View Report</title>
  <link rel="stylesheet" href="../assets/style.css">

  <!-- Add this CSS (or move it into your style.css) -->
  <style>
    .progressCard{
      background:#fff;
      border:1px solid rgba(0,0,0,.08);
      border-radius:14px;
      padding:14px;
      margin:14px 0;
      box-shadow: 0 6px 18px rgba(0,0,0,.04);
    }
    .stackBar{
      height:14px;
      border-radius:999px;
      overflow:hidden;
      background: rgba(0,0,0,.08);
      display:flex;
    }
    .seg{ height:100%; }
    .seg.yes{ background:#22c55e; }  /* green */
    .seg.na{  background:#facc15; }  /* yellow */
    .seg.no{  background:#ef4444; }  /* red */

    .legend{
      display:flex;
      gap:14px;
      flex-wrap:wrap;
      margin-top:10px;
      font-size:13px;
      color: rgba(0,0,0,.65);
    }
    .dot{
      width:10px;
      height:10px;
      border-radius:50%;
      display:inline-block;
      margin-right:6px;
      vertical-align:middle;
    }
    .dot.yes{ background:#22c55e; }
    .dot.na{  background:#facc15; }
    .dot.no{  background:#ef4444; }
    .legend b{ color:#111; font-weight:700; }
  </style>
</head>
<body>

<div class="container">
  <div class="topbar">
    <div class="brand">
      <div class="logo"></div>
      <div>
        <h1>Checklist System</h1>
        <p><?php echo ($role === 'admin') ? 'Admin Panel' : 'Employee Panel'; ?></p>
      </div>
    </div>
    <div class="userchip">
      <div class="avatar"><?php echo htmlspecialchars($initial); ?></div>
      <div class="meta">
        <b><?php echo htmlspecialchars($name); ?></b>
        <span>Role: <?php echo htmlspecialchars(ucfirst($role)); ?></span>
      </div>
      <a class="btn danger" href="../logout.php">Logout</a>
    </div>
  </div>

  <div class="page">
    <div class="pagehead">
      <div>
        <h2>Report #<?php echo (int)$report_id; ?> — <?php echo htmlspecialchars($info['task_title']); ?></h2>
        <p>
          Employee: <?php echo htmlspecialchars($info['emp_name']); ?>
          (<?php echo htmlspecialchars($info['emp_email']); ?>)
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
<!-- ✅ STACKED PROGRESS BAR -->
    <div class="progressCard">
      <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
        <b>Progress</b>
        <span style="color:rgba(247, 249, 251, 0.97);font-size:13px;">
          Total items: <?php echo $total; ?>
        </span>
      </div>

      <div style="margin-top:10px;">
        <div class="stackBar" title="YES: <?php echo $yesCount; ?> | NA: <?php echo $naCount; ?> | NO: <?php echo $noCount; ?>">
          <div class="seg yes" style="width: <?php echo $yesPct; ?>%"></div>
          <div class="seg na"  style="width: <?php echo $naPct; ?>%"></div>
          <div class="seg no"  style="width: <?php echo $noPct; ?>%"></div>
        </div>

        <div class="legend">
          <span><span class="dot yes"></span><b>YES</b>: <?php echo $yesCount; ?> (<?php echo $yesPct; ?>%)</span>
          <span><span class="dot na"></span><b>NA</b>: <?php echo $naCount; ?> (<?php echo $naPct; ?>%)</span>
          <span><span class="dot no"></span><b>NO</b>: <?php echo $noCount; ?> (<?php echo $noPct; ?>%)</span>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>