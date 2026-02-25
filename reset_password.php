<?php
session_start();
require_once "config/db.php";

$token = $_GET['token'] ?? '';
$token = is_string($token) ? trim($token) : '';

$err = "";
$msg = "";
$showForm = false;
$user_id = null;
$reset_id = null;

if ($token === "") {
  $err = "Invalid reset link.";
} else {
  // Find valid reset request (not used, not expired)
  $now = date('Y-m-d H:i:s');
  $q = $conn->prepare("SELECT id,user_id,token_hash,expires_at,used_at FROM password_resets WHERE used_at IS NULL AND expires_at >= ? ORDER BY id DESC LIMIT 50");
  $q->bind_param("s", $now);
  $q->execute();
  $res = $q->get_result();

  while ($row = $res->fetch_assoc()) {
    if (password_verify($token, $row['token_hash'])) {
      $reset_id = (int)$row['id'];
      $user_id = (int)$row['user_id'];
      $showForm = true;
      break;
    }
  }

  if (!$showForm) {
    $err = "Reset link expired or invalid.";
  }
}

// Handle submit new password
if ($showForm && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $p1 = $_POST['password'] ?? '';
  $p2 = $_POST['confirm_password'] ?? '';

  if ($p1 === "" || strlen($p1) < 6) {
    $err = "Password must be at least 6 characters.";
  } elseif ($p1 !== $p2) {
    $err = "Passwords do not match.";
  } else {
    // Update user password (your system currently uses plain text, so saving plain text)
    // If you want hashing later, we can upgrade safely.
    $up = $conn->prepare("UPDATE users SET password=? WHERE id=? LIMIT 1");
    $up->bind_param("si", $p1, $user_id);
    $up->execute();

    // Mark token used
    $u2 = $conn->prepare("UPDATE password_resets SET used_at=? WHERE id=? LIMIT 1");
    $used_at = date('Y-m-d H:i:s');
    $u2->bind_param("si", $used_at, $reset_id);
    $u2->execute();

    $msg = "Password updated successfully. You can login now.";
    $showForm = false;
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Reset Password</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="auth">
  <div class="authcard">
    <div class="authright" style="width:100%;">
      <h2>Reset Password</h2>

      <?php if($err): ?><div class="alert err"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>
      <?php if($msg): ?><div class="alert"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>

      <?php if($showForm): ?>
        <form class="form" method="POST">
          <div class="field">
            <label>New Password</label>
            <input class="input" type="password" name="password" required>
          </div>
          <div class="field">
            <label>Confirm Password</label>
            <input class="input" type="password" name="confirm_password" required>
          </div>
          <div class="actions">
            <button class="btn primary" type="submit">Update Password</button>
          </div>
        </form>
      <?php else: ?>
        <div class="actions">
          <a class="btn primary" href="index.php">Back to Login</a>
        </div>
      <?php endif; ?>

    </div>
  </div>
</div>
</body>
</html>