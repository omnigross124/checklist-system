<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
  header("Location: ../login.php"); exit();
}
$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add User</title>
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
        <h2>Add Admin / Employee</h2>
        <p>Create accounts and manage access.</p>
      </div>
      <div class="breadcrumb">
        <a href="dashboard.php">Dashboard</a> › <span>Add User</span>
      </div>
    </div>

    <?php if(isset($_GET['msg'])): ?>
      <div class="alert ok"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <?php if(isset($_GET['error'])): ?>
      <div class="alert err"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <form class="form" action="save_user.php" method="POST">
      <div class="field">
        <label>Name</label>
        <input class="input" type="text" name="name" placeholder="Full name" required>
      </div>

      <div class="field">
        <label>Email</label>
        <input class="input" type="email" name="email" placeholder="user@gmail.com" required>
      </div>

      <div class="row">
        <div class="field">
          <label>Password</label>
          <input class="input" type="text" name="password" placeholder="simple password" required>
        </div>

        <div class="field">
          <label>Role</label>
          <select name="role" required>
            <option value="admin">Admin</option>
            <option value="employee">Employee</option>
          </select>
        </div>
      </div>

      <div class="actions">
        <button class="btn primary" type="submit">Save</button>
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </form>
  </div>
</div>

</body>
</html>
