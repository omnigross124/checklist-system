<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$name = trim($_POST['name']);
$email = trim($_POST['email']);
$password = trim($_POST['password']);
$admin_id = $_SESSION['user_id'];

// check existing email
$check = $conn->prepare("SELECT id FROM users WHERE email=? LIMIT 1");
$check->bind_param("s", $email);
$check->execute();
$res = $check->get_result();

if($res->num_rows > 0){
    header("Location: add_employee.php?error=Email already exists");
    exit();
}

// role fixed as employee + parent_id is admin_id
$login_id = generateLoginId($conn, "EMP");

$stmt = $conn->prepare("INSERT INTO users (login_id,name,email,password,role,parent_id,status) VALUES (?,?,?,?,?,?,'active')");
$role = "employee";
$stmt->bind_param("sssssi", $login_id, $name, $email, $password, $role, $admin_id);

if($stmt->execute()){
    header("Location: add_employee.php?msg=Employee added successfully");
} else {
    header("Location: add_employee.php?error=Something went wrong");
}
exit();
