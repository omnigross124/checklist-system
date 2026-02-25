<?php
require_once "auth.php";
require_roles(['admin','super_admin']);

require_once __DIR__ . "/../config/db.php";

$user_id   = (int)($_POST['user_id'] ?? 0);
$login_id  = trim($_POST['login_id'] ?? '');
$password  = $_POST['password'] ?? '';

if ($user_id <= 0 || $login_id === '') {
  header("Location: settings.php?err=Invalid input");
  exit();
}

// fetch target role
$stmt = $conn->prepare("SELECT role FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$target = $res->fetch_assoc();

if (!$target) {
  header("Location: settings.php?err=User not found");
  exit();
}

$target_role = $target['role'];

// ADMIN can update only employees
if ($_SESSION['role'] === 'admin' && $target_role !== 'employee') {
  http_response_code(403);
  die("Access denied: admin can update employees only");
}

// SUPER ADMIN can update admin + employee (you can also allow super_admin updates if you want)
if ($_SESSION['role'] === 'super_admin' && !in_array($target_role, ['admin','employee'], true)) {
  http_response_code(403);
  die("Access denied");
}

// update login_id + password (password optional)
if ($password !== '') {
  // plain text as you requested
  $stmt = $conn->prepare("UPDATE users SET login_id=?, password=? WHERE id=?");
  $stmt->bind_param("ssi", $login_id, $password, $user_id);
} else {
  $stmt = $conn->prepare("UPDATE users SET login_id=? WHERE id=?");
  $stmt->bind_param("si", $login_id, $user_id);
}

if ($stmt->execute()) {
  header("Location: settings.php?ok=1");
  exit();
} else {
  header("Location: settings.php?err=Update failed");
  exit();
}