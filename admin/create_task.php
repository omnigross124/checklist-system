<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../login.php"); exit();
}
$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
?>
<!DOCTYPE html>
<html>
<head>
  <title>Create Checklist</title>
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
        <h2>Create Checklist</h2>
        <p>Add one checklist item per line in description.</p>
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

    <form class="form" action="save_task.php" method="POST">
      <div class="field">
        <label>Checklist Title</label>
        <input class="input" type="text" name="title" placeholder="e.g., Daily Safety Checklist" required>
      </div>

      <div class="field">
        <label>Description (one item per line)</label>
        <textarea name="description" placeholder="Engine oil checked?&#10;Lights working?&#10;Safety equipment available?"></textarea>
      </div>

      <div class="actions">
        <button class="btn primary" type="submit">Save Checklist</button>
        <a class="btn" href="tasks_list.php">My Checklists</a>
      </div>
    </form>

  </div>
</div>

</body>
</html>
