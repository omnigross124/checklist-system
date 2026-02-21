<?php
session_start();
if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] == 'super_admin') header("Location: superadmin/dashboard.php");
  if ($_SESSION['role'] == 'admin') header("Location: admin/dashboard.php");
  if ($_SESSION['role'] == 'employee') header("Location: employee/dashboard.php");
  exit();
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Login - Checklist System</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="auth">
  <div class="authcard">
    <div class="authleft">
      <div class="brand" style="margin-bottom:18px;">
        <div class="logo"></div>
        <div>
          <h1 style="margin:0;">Checklist System</h1>
          <p>Login to manage tasks, assign checklists, and track reports.</p>
        </div>
      </div>

      <div class="alert" style="margin-top:16px;">
        <b>Tip:</b> Use the same login page for Super Admin, Admin & Employee.
      </div>
    </div>

    <div class="authright">
      <h2>Sign in</h2>

      <?php if(isset($_GET['error'])): ?>
        <div class="alert err"><?php echo htmlspecialchars($_GET['error']); ?></div>
      <?php endif; ?>

      <form class="form" action="login_process.php" method="POST">
        <div class="field">
          <label>Email</label>
          <input class="input" type="email" name="email" placeholder="you@example.com" required>
        </div>

        <div class="field">
          <label>Password</label>
          <input class="input" type="password" name="password" placeholder="Enter password" required>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Login</button>
        </div>
      </form>

      <p style="color:var(--muted);font-size:12px;margin-top:14px;">
        For now passwords are simple (plain text) because you asked for it.
      </p>
    </div>
  </div>
</div>

</body>
</html>
