<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));

$result = $conn->query("SELECT id,login_id,name,email,role,status,created_at FROM users ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Users</title>
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
        <h2>All Users</h2>
        <p>Manage admins and employees.</p>
      </div>
      <div class="actions">
        <a class="btn primary" href="add_user.php">Add User</a>
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

<div class="tablewrap">
  <table>
    <thead>
      <tr>
        <th>ID</th>
        <th>Login ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Status</th>
        <th>Created</th>
        <th>Actions</th>
      </tr>
    </thead>

    <tbody>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?php echo (int)$row['id']; ?></td>
        <td><?php echo htmlspecialchars($row['login_id']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['role']); ?></td>
        <td>
          <span class="badge <?php echo ($row['status']=='active') ? 'ok' : 'off'; ?>">
            <?php echo htmlspecialchars($row['status']); ?>
          </span>
        </td>
        <td><?php echo htmlspecialchars($row['created_at']); ?></td>

        <td>
          <?php if($row['id'] != $_SESSION['user_id']): ?>
            <div class="actions-inline">

              <a class="link" href="toggle_status.php?id=<?php echo (int)$row['id']; ?>">
                <?php echo ($row['status']=='active') ? "Deactivate" : "Activate"; ?>
              </a>

              <a class="link"
                 href="delete_user.php?id=<?php echo (int)$row['id']; ?>"
                 onclick="return confirm('Delete user?')">
                 Delete
              </a>

              <?php
                $allowed = false;

                // Super Admin can reset Admin only
                if ($_SESSION['role'] == 'super_admin' && $row['role'] == 'admin') $allowed = true;

                // Admin can reset Employee only
                if ($_SESSION['role'] == 'admin' && $row['role'] == 'employee') $allowed = true;

                if ($allowed):
              ?>
                <a class="link"
                   href="request_user_reset.php?id=<?php echo (int)$row['id']; ?>"
                   onclick="return confirm('Send password reset email?')">
                   Reset Password
                </a>
              <?php endif; ?>

            </div>
          <?php else: ?>
            <span class="badge">You</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
  </div>
</div>

</body>
</html>
