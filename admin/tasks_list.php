<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));
$admin_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT id,title,description,created_at FROM tasks WHERE created_by=? ORDER BY id DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Checklists</title>
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
        <h2>My Checklists</h2>
        <p>Templates created by you.</p>
      </div>
      <div class="actions">
        <a class="btn primary" href="create_task.php">Create Checklist</a>
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <div class="tablewrap">
      <table>
        <tr>
          <th>ID</th><th>Title</th><th>Description</th><th>Created</th>
        </tr>

        <?php if($result->num_rows == 0): ?>
          <tr><td colspan="4" style="color:var(--muted);">No checklists created yet.</td></tr>
        <?php endif; ?>

        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $row['id']; ?></td>
            <td><span class="badge warn"><?php echo htmlspecialchars($row['title']); ?></span></td>
            <td style="color:var(--muted);"><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>
  </div>
</div>

</body>
</html>
