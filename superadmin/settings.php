<?php
require_once "auth.php";
require_roles(['super_admin']);

require_once __DIR__ . "/../config/db.php";

// super admin can see admin + employee
$stmt = $conn->prepare("SELECT id, login_id, role FROM users WHERE role IN ('admin','employee') ORDER BY role, id DESC");
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
<h2>Settings (Super Admin)</h2>
<a class="btn primary" href="dashboard.php" style="margin:10px 0; display:inline-flex;">← Back to Dashboard</a>
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