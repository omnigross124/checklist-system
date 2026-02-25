<?php
session_start();
if($_SESSION['role']!='admin'){ header("Location: ../index.php"); exit(); }
require_once "../config/db.php";

$admin_id = $_SESSION['user_id'];

$emps = $conn->prepare("SELECT id,name,email FROM users WHERE role='employee' AND parent_id=? AND status='active' ORDER BY name");
$emps->bind_param("i",$admin_id);
$emps->execute();
$employees = $emps->get_result();

$tasks = $conn->prepare("SELECT id,title FROM tasks WHERE created_by=? ORDER BY id DESC");
$tasks->bind_param("i",$admin_id);
$tasks->execute();
$taskRes = $tasks->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Daily Checklist</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="container">
  <div class="page">
    <div class="pagehead">
      <div>
        <h2>Assign Daily Checklist</h2>
        <p>Same checklist repeats Mon–Sat (9AM to 6PM).</p>
      </div>
      <div class="actions">
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <form class="form" method="POST" action="save_daily.php">
      <div class="field">
        <label>Select Employee</label>
        <select name="employee_id" required>
          <option value="">--Select--</option>
          <?php while($e=$employees->fetch_assoc()): ?>
            <option value="<?php echo $e['id']; ?>">
              <?php echo htmlspecialchars($e['name']." (".$e['email'].")"); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="field">
        <label>Select Checklist Template</label>
        <select name="task_id" required>
          <option value="">--Select--</option>
          <?php while($t=$taskRes->fetch_assoc()): ?>
            <option value="<?php echo $t['id']; ?>">
              <?php echo htmlspecialchars($t['title']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="field">
        <label>Start Date (optional)</label>
        <input class="input" type="date" name="start_date">
      </div>

      <div class="actions">
        <button class="btn primary" type="submit">Assign Daily</button>
        <a class="btn" href="dashboard.php">Cancel</a>
      </div>
    </form>
  </div>
</div>
</body>
</html>
