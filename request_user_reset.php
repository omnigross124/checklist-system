<?php
session_start();
require_once "../config/db.php"; // if you place this inside admin/ or superadmin/ folder
require_once "../mail_helper.php";

if (!isset($_SESSION['role'], $_SESSION['user_id'])) {
  header("Location: ../login.php"); exit();
}

$target_id = (int)($_GET['id'] ?? 0);
if ($target_id <= 0) {
  header("Location: dashboard.php?error=Invalid user"); exit();
}

// Fetch target
$q = $conn->prepare("SELECT id,email,role,status FROM users WHERE id=? LIMIT 1");
$q->bind_param("i", $target_id);
$q->execute();
$target = $q->get_result()->fetch_assoc();

if (!$target || $target['status'] !== 'active') {
  header("Location: dashboard.php?error=User not found/inactive"); exit();
}

$meRole = $_SESSION['role'];

// SECURITY RULES
$allowed = false;
if ($meRole === 'admin' && $target['role'] === 'employee') $allowed = true;
if ($meRole === 'super_admin' && $target['role'] === 'admin') $allowed = true;

// NOTE: super_admin resetting himself is done from forgot_password.php (public self reset)
if (!$allowed) {
  header("Location: dashboard.php?error=Not authorized"); exit();
}

// Create reset token
$token = bin2hex(random_bytes(32));
$token_hash = password_hash($token, PASSWORD_DEFAULT);
$expires_at = date('Y-m-d H:i:s', time() + (30 * 60));
$ip = $_SERVER['REMOTE_ADDR'] ?? null;

$ins = $conn->prepare("INSERT INTO password_resets(user_id, token_hash, expires_at, requested_by, requested_ip) VALUES(?,?,?,?,?)");
$requested_by = (int)$_SESSION['user_id'];
$ins->bind_param("issis", $target['id'], $token_hash, $expires_at, $requested_by, $ip);
$ins->execute();

// Build reset link
$reset_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST']
            . "/reset_password.php?token=" . urlencode($token);

// Send email
send_reset_email($target['email'], $reset_link);

header("Location: users_list.php?success=Reset email sent"); exit();