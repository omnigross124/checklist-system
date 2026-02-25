<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
    header("Location: ../index.php");
    exit();
}

require_once "../config/db.php";

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = trim($_POST['password']); // simple password
$role = trim($_POST['role']);

if($role != 'admin' && $role != 'employee'){
    header("Location: add_user.php?error=Invalid role selected");
    exit();
}

// check if email already exists
$check = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$check->bind_param("s", $email);
$check->execute();
$checkRes = $check->get_result();

if($checkRes->num_rows > 0){
    header("Location: add_user.php?error=Email already exists");
    exit();
}

if($role == "admin"){
    $login_id = generateLoginId($conn, "ADM");
}
else if($role == "employee"){
    $login_id = generateLoginId($conn, "EMP");
}
else if($role == "super_admin"){
    $login_id = generateLoginId($conn, "SUPER"); // optional
}

$stmt = $conn->prepare("INSERT INTO users (login_id,name,email,password,role,parent_id,status) VALUES (?,?,?,?,?,?,'active')");
$stmt->bind_param("sssssi", $login_id, $name, $email, $password, $role, $parent_id);

if($stmt->execute()){
    header("Location: add_user.php?msg=User added successfully");
} else {
    header("Location: add_user.php?error=Something went wrong");
}
exit();
