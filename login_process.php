<?php
session_start();
require_once "config/db.php";

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.php");
    exit();
}

// 🔹 Get Login ID instead of email
$login_id = trim($_POST['login_id']);
$password = trim($_POST['password']);

if($login_id == "" || $password == ""){
    header("Location: index.php?error=Enter Login ID and Password");
    exit();
}

// 🔹 Find user using login_id
$stmt = $conn->prepare("SELECT id,name,login_id,password,role,status FROM users WHERE login_id=? LIMIT 1");
$stmt->bind_param("s", $login_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: index.php?error=Invalid ID or Password");
    exit();
}

$user = $result->fetch_assoc();

// 🔹 Check active status
if ($user['status'] != 'active') {
    header("Location: index.php?error=Account inactive. Contact Admin.");
    exit();
}

// 🔹 Plain password check (as you requested)
if ($password != $user['password']) {
    header("Location: index.php?error=Invalid ID or Password");
    exit();
}

// 🔹 Set Session
$_SESSION['user_id'] = $user['id'];
$_SESSION['name']    = $user['name'];
$_SESSION['role']    = $user['role'];
$_SESSION['login_id']= $user['login_id']; // optional if you want to display ID

// 🔹 Redirect based on role
if ($user['role'] == 'super_admin') {
    header("Location: superadmin/dashboard.php");
} elseif ($user['role'] == 'admin') {
    header("Location: admin/dashboard.php");
} else {
    header("Location: employee/dashboard.php");
}
exit();