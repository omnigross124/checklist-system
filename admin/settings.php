<?php
require_once __DIR__ . "/auth.php";
require_roles(['admin','super_admin']);


require_once __DIR__ . "/../config/db.php"; // your connection file

// admin can see only employees
$stmt = $conn->prepare("SELECT id, login_id, role FROM users WHERE role='employee' ORDER BY id DESC");
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Settings</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<h2>Settings (Admin)</h2>
<a class="btn" href="dashboard.php" style="display:inline-block;margin:10px 0;">← Back to Dashboard</a>
<?php if(isset($_GET['ok'])): ?>
  <div class="alert">Updated successfully</div>
<?php endif; ?>
<?php if(isset($_GET['err'])): ?>
  <div class="alert err"><?php echo htmlspecialchars($_GET['err']); ?></div>
<?php endif; ?>

<table border="1" cellpadding="10">
  <tr>
    <th>ID</th>
    <th>Login ID</th>
    <th>Role</th>
    <th>Change Login ID / Password</th>
  </tr>

  <?php while($u = $users->fetch_assoc()): ?>
    <tr>
      <td><?php echo (int)$u['id']; ?></td>
      <td><?php echo htmlspecialchars($u['login_id']); ?></td>
      <td><?php echo htmlspecialchars($u['role']); ?></td>
      <td>
        <form action="update_credentials.php" method="POST">
          <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">

          <input type="text" name="login_id" value="<?php echo htmlspecialchars($u['login_id']); ?>" required>
          <input type="password" name="password" placeholder="New password (optional)">
          <button type="submit">Update</button>
        </form>
      </td>
    </tr>
  <?php endwhile; ?>
</table>
</body>
</html>