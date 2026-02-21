<?php
session_start();
require_once "config/db.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email']);
$password = trim($_POST['password']);

$stmt = $conn->prepare("SELECT id,name,email,password,role,status FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: login.php?error=Invalid email or password");
    exit();
}

$user = $result->fetch_assoc();

if ($user['status'] != 'active') {
    header("Location: login.php?error=Account inactive. Contact Admin.");
    exit();
}

// ✅ Simple password check (plain text)
if ($password != $user['password']) {
    header("Location: login.php?error=Invalid email or password");
    exit();
}

// ✅ Set Session
$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['role']    = $user['role'];

// ✅ Redirect
if ($user['role'] == 'super_admin') {
    header("Location: superadmin/dashboard.php");
} elseif ($user['role'] == 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: employee/dashboard.php");
}
exit();