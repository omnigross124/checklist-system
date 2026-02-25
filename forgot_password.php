<?php
session_start();
require_once "config/db.php";
require_once "config/app.php"; 
require_once "mail_helper.php";

$msg = "";
$err = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $login_id = trim($_POST['login_id'] ?? '');

  // Always show generic msg (no user enumeration)
  $generic = "If the account exists, an email has been sent to the authorized person.";

  if ($login_id === "") {
    $err = "Please enter Login ID.";
  } else {

    // Find user by login_id
    $q = $conn->prepare("SELECT id, login_id, name, email, role, status FROM users WHERE login_id=? LIMIT 1");
    $q->bind_param("s", $login_id);
    $q->execute();
    $user = $q->get_result()->fetch_assoc();

    if (!$user || $user['status'] !== 'active') {
      $msg = $generic;
    } else {

      /* =========================================
         🔐 HARDCODED AUTHORITY EMAILS
         ========================================= */

      $ADMIN_EMAIL      = "admin@gmail.com";       // 🔁 CHANGE THIS
      $SUPERADMIN_EMAIL = "superadmin@gmail.com";  // 🔁 CHANGE THIS

      $toEmail = null;

      if ($user['role'] === 'employee') {
          // Employee forgot → goes to Admin
          $toEmail = $ADMIN_EMAIL;

      } elseif ($user['role'] === 'admin') {
          // Admin forgot → goes to Super Admin
          $toEmail = $SUPERADMIN_EMAIL;

      } elseif ($user['role'] === 'super_admin') {
          // Super Admin forgot → goes to himself
          $toEmail = $SUPERADMIN_EMAIL;
      }

      if (empty($toEmail)) {
          $msg = $generic;
      } else {

          // Create reset token
          $token = bin2hex(random_bytes(32));
          $token_hash = password_hash($token, PASSWORD_DEFAULT);
          $expires_at = date('Y-m-d H:i:s', time() + (30 * 60)); // 30 minutes
          $ip = $_SERVER['REMOTE_ADDR'] ?? null;

          $ins = $conn->prepare("INSERT INTO password_resets(user_id, token_hash, expires_at, requested_by, requested_ip)
                                 VALUES(?,?,?,?,?)");
          $requested_by = null;
          $ins->bind_param("issis", $user['id'], $token_hash, $expires_at, $requested_by, $ip);
          $ins->execute();

          // Build reset link


$reset_link = rtrim(APP_URL, '/') . "/reset_password.php?token=" . urlencode($token);

          $subject = "Password Reset Request - Checklist System";

          $body =
            "Hello,\n\n".
            "A password reset was requested for:\n".
            "Login ID: {$user['login_id']}\n".
            "Name: {$user['name']}\n".
            "Role: {$user['role']}\n\n".
            "Authorized person can reset password using this link (valid 30 minutes):\n".
            "{$reset_link}\n\n".
            "If you did not request this, ignore this email.\n";

          // Send email
          send_mail_simple($toEmail, $subject, $body);

          $msg = $generic;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Forgot Password</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="auth">
  <div class="authcard">
    <div class="authright" style="width:100%;">
      <h2>Forgot Password</h2>

      <?php if($err): ?>
        <div class="alert err"><?php echo htmlspecialchars($err); ?></div>
      <?php endif; ?>

      <?php if($msg): ?>
        <div class="alert"><?php echo htmlspecialchars($msg); ?></div>
      <?php endif; ?>

      <form class="form" method="POST">
        <div class="field">
          <label>Login ID</label>
          <input class="input" type="text" name="login_id"
                 placeholder="EMP101 / ADM101 / SUPER1" required>
        </div>

        <div class="actions">
          <button class="btn primary" type="submit">Continue</button>
          <a class="btn" href="index.php">Back to Login</a>
        </div>
      </form>

      <p style="color:var(--muted);font-size:12px;margin-top:14px;">
        Note: Reset email goes to the authorized person (Admin/Super Admin).
      </p>

    </div>
  </div>
</div>

</body>
</html>