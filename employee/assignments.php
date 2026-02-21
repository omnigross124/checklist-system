<?php
session_start();
require_once "../config/db.php";
function getTodayReportId($conn,$emp,$task,$date){
    $q = $conn->prepare("SELECT id FROM reports WHERE employee_id=? AND task_id=? AND report_date=? LIMIT 1");
    $q->bind_param("iis",$emp,$task,$date);
    $q->execute();
    $r = $q->get_result()->fetch_assoc();
    return $r ? $r['id'] : 0;
}

date_default_timezone_set('Asia/Kolkata');

if(!isset($_SESSION['role']) || $_SESSION['role']!='employee'){
  header("Location: ../login.php"); exit();
}

$emp_id = $_SESSION['user_id'];
$name   = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));

$today = date('Y-m-d');
$dayOfWeek = date('N'); // 1=Mon ... 7=Sun
$nowTime = date('H:i:s');
// DEBUG (remove later)
$debugNow = date("Y-m-d H:i:s");
$isWorkingDay = ($dayOfWeek <= 6); // Mon–Sat
$isInWindow   = ($nowTime >= '09:00:00' && $nowTime <= '18:00:00');

// Fetch daily templates assigned to this employee
$daily = $conn->prepare("
  SELECT da.task_id, t.title
  FROM daily_assignments da
  JOIN tasks t ON t.id = da.task_id
  WHERE da.employee_id=? AND da.active='yes'
  ORDER BY da.id DESC
");
$daily->bind_param("i",$emp_id);
$daily->execute();
$dailyRes = $daily->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Daily Checklist</title>
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

  <div class="page">
    <div class="pagehead">
      <div>
        <h2>Daily Checklist</h2>
       <p>Daily checklist repeats Mon–Sat. Opens 9AM and locks 6PM.</p>
<p style="color:#9aa4b2;font-size:13px;">
  Server time: <?php echo $debugNow; ?> | TZ: <?php echo date_default_timezone_get(); ?>
</p>
      </div>
      <div class="actions">
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <?php if($dailyRes->num_rows == 0): ?>
      <div class="card">
        <p style="color:var(--muted);">No daily checklist assigned yet.</p>
      </div>
    <?php endif; ?>

    <?php while($d = $dailyRes->fetch_assoc()): 

      // check if submitted today
      $chk = $conn->prepare("SELECT id FROM reports
                             WHERE employee_id=? AND task_id=? AND report_date=? LIMIT 1");
      $chk->bind_param("iis",$emp_id,$d['task_id'],$today);
      $chk->execute();
      $already = ($chk->get_result()->num_rows > 0);

      $lockedReason = "";
      if(!$isWorkingDay) $lockedReason = "Available Mon–Sat only.";
      else if(!$isInWindow) $lockedReason = "Opens at 9 AM and locks at 6 PM.";
      else if($already) $lockedReason = "Submitted for today.";
    ?>

    <div class="card" style="margin-bottom:14px;">
      <h3><?php echo htmlspecialchars($d['title']); ?> <span class="badge">Daily</span></h3>

<?php if($already): ?>

  <p style="color:var(--muted)">You already submitted today’s checklist.</p>

  <a class="btn success"
   href="view_report.php?report_id=<?php echo getTodayReportId($conn,$emp_id,$d['task_id'],$today); ?>">
   Review Submission
</a>

<?php elseif($lockedReason!=""): ?>

  <p style="color:var(--muted)"><?php echo $lockedReason; ?></p>
  <span class="badge warn">Locked</span>

<?php else: ?>

        <p style="color:var(--muted);">Today’s checklist is open (till 6 PM).</p>
        <?php if($already): ?>
   <a class="btn" href="view_submission.php?task_id=<?=$d['task_id']?>">Review</a>
<?php else: ?>
   <a class="btn primary" href="fill_daily.php?task_id=<?php echo (int)$d['task_id']; ?>">Fill</a>
<?php endif; ?>


      <?php endif; ?>
    </div>

    <?php endwhile; ?>

  </div>
</div>

</body>
</html>
