<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];

// team employees
$emp = $conn->prepare("SELECT id,name,email FROM users WHERE role='employee' AND parent_id=? AND status='active' ORDER BY name");
$emp->bind_param("i", $admin_id);
$emp->execute();
$employees = $emp->get_result();

// tasks created by admin
$tsk = $conn->prepare("SELECT id,title FROM tasks WHERE created_by=? ORDER BY id DESC");
$tsk->bind_param("i", $admin_id);
$tsk->execute();
$tasks = $tsk->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Checklist</title>
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
        <h2>Assign Checklist</h2>
        <p>Select employee and checklist to assign.</p>
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

    <form class="form" action="save_assignment.php" method="POST">
      <div class="field">
        <label>Select Employee</label>
        <select name="employee_id" required>
          <option value="">-- Select --</option>
          <?php while($e = $employees->fetch_assoc()): ?>
            <option value="<?php echo $e['id']; ?>">
              <?php echo htmlspecialchars($e['name']." (".$e['email'].")"); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="field">
        <label>Select Checklist</label>
        <select name="task_id" required>
          <option value="">-- Select --</option>
          <?php while($t = $tasks->fetch_assoc()): ?>
            <option value="<?php echo $t['id']; ?>">
              <?php echo htmlspecialchars($t['title']); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="field">
        <label>Due Date (optional)</label>
        <input class="input" type="date" name="due_date">
      </div>

      <div class="actions">
        <button class="btn primary" type="submit">Assign</button>
        <a class="btn" href="assignments_list.php">View Assigned List</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
