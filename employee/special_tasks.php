<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$emp_id = $_SESSION['user_id'];
// Progress calculation (Special Tasks)
$qS = $conn->prepare("SELECT COUNT(*) total,
                      SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) done
                      FROM special_tasks
                      WHERE employee_id=?");
$qS->bind_param("i", $emp_id);
$qS->execute();
$s = $qS->get_result()->fetch_assoc();

$sp_total = (int)$s['total'];
$sp_done  = (int)$s['done'];
$sp_percent = ($sp_total > 0) ? round(($sp_done/$sp_total)*100) : 0;

if($sp_total == 0) $sp_class="gray";
else if($sp_percent < 50) $sp_class="red";
else if($sp_percent < 80) $sp_class="yellow";
else $sp_class="green";

$stmt = $conn->prepare("SELECT id,title,description,due_date,status,created_at,employee_remark,completed_at
                        FROM special_tasks
                        WHERE employee_id=?
                        ORDER BY id DESC");
$stmt->bind_param("i",$emp_id);
$stmt->execute();
$res = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Special Tasks</title>
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
        <h2>My Special Tasks</h2>
        <p>Urgent tasks assigned to you by admin.</p>
      </div>
      <div class="actions">
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
      <div class="alert ok"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
      <div class="alert err"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>
<div class="progressMini">
  <div class="row">
    <b>Special Tasks Progress</b>
    <span><?php echo $sp_done; ?> completed / <?php echo $sp_total; ?> assigned (<?php echo $sp_percent; ?>%)</span>
  </div>
  <div class="progressTrack">
    <div class="progressFill <?php echo $sp_class; ?>" style="width:<?php echo $sp_percent; ?>%;"></div>
  </div>
</div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>ID</th>
          <th>Task</th>
          <th>Due</th>
          <th>Status</th>
          <th>Progress</th>
          <th>Action</th>
        </tr>

        <?php if($res->num_rows == 0): ?>
          <tr><td colspan="5" style="color:var(--muted);">No special tasks assigned to you.</td></tr>
        <?php endif; ?>

        <?php while($r=$res->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $r['id']; ?></td>
            <td>
              <b><?php echo htmlspecialchars($r['title']); ?></b><br>
              <span style="color:var(--muted);font-size:12px;"><?php echo nl2br(htmlspecialchars($r['description'])); ?></span>
            </td>
            <td><?php echo htmlspecialchars($r['due_date']); ?></td>
            <td>
              <?php if($r['status']=='completed'): ?>
                <span class="badge ok">completed</span>
              <?php else: ?>
                <span class="badge warn">pending</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if($r['status']=='completed'): ?>
                <span class="badge ok">Done</span>
              <?php else: ?>
                <a class="btn primary" href="complete_special_task.php?id=<?php echo $r['id']; ?>">Complete</a>
              <?php endif; ?>
            </td>
            <td>
  <div class="miniTrack">
  <?php if(strtolower(trim($r['status'])) == 'completed'): ?>
    <div class="miniFill completed"></div>
  <?php else: ?>
    <div class="miniFill pending"></div>
  <?php endif; ?>
</div>


          </tr>
        <?php endwhile; ?>
      </table>
    </div>

  </div>
</div>

</body>
</html>
