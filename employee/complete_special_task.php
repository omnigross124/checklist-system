<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$emp_id = $_SESSION['user_id'];
$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id,title,description,due_date,status
                        FROM special_tasks
                        WHERE id=? AND employee_id=? LIMIT 1");
$stmt->bind_param("ii",$id,$emp_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){ die("Invalid task"); }
$task = $res->fetch_assoc();
if($task['status']=='completed'){ header("Location: special_tasks.php?msg=Already completed"); exit(); }
?>
<!DOCTYPE html>
<html>
<head>
  <title>Complete Special Task</title>
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
        <h2>Complete Task — <?php echo htmlspecialchars($task['title']); ?></h2>
        <p><?php if(!empty($task['due_date'])) echo "Due: ".htmlspecialchars($task['due_date']); ?></p>
      </div>
      <div class="actions">
        <a class="btn" href="special_tasks.php">Back</a>
      </div>
    </div>

    <div class="alert">
      <b>Description:</b><br>
      <span style="color:var(--muted);"><?php echo nl2br(htmlspecialchars($task['description'])); ?></span>
    </div>

    <form class="form" action="save_complete_special_task.php" method="POST" style="margin-top:14px;">
      <input type="hidden" name="id" value="<?php echo $task['id']; ?>">

      <div class="field">
        <label>Remark (optional)</label>
        <textarea name="employee_remark" placeholder="What did you do / any notes?"></textarea>
      </div>

      <div class="actions">
        <button class="btn success" type="submit">Mark as Completed</button>
        <a class="btn" href="special_tasks.php">Cancel</a>
      </div>
    </form>

  </div>
</div>

</body>
</html>
