<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];

$emp = $conn->prepare("SELECT id,name,email FROM users WHERE role='employee' AND parent_id=? AND status='active' ORDER BY name");
$emp->bind_param("i",$admin_id);
$emp->execute();
$employees = $emp->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Assign Special Task</title>
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
        <h2>Assign Special Task</h2>
        <p>Quick one-time urgent task with due date.</p>
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

    <form class="form" action="save_special_task.php" method="POST">
      <div class="field">
        <label>Task Title</label>
        <input class="input" type="text" name="title" placeholder="e.g., Submit inventory report" required>
      </div>

      <div class="field">
        <label>Description (optional)</label>
        <textarea name="description" placeholder="Write task details..."></textarea>
      </div>

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
        <label>Due Date</label>
        <input class="input" type="date" name="due_date" required>
      </div>

      <div class="actions">
        <button class="btn primary" type="submit">Assign Task</button>
        <a class="btn" href="special_tasks_list.php">Special Tasks List</a>
      </div>
    </form>

  </div>
</div>

</body>
</html>
