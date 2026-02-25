<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$title = trim($_POST['title']);
$description = trim($_POST['description']);
$created_by = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO tasks (title, description, created_by) VALUES (?,?,?)");
$stmt->bind_param("ssi", $title, $description, $created_by);

if($stmt->execute()){
    header("Location: create_task.php?msg=Checklist created successfully");
} else {
    header("Location: create_task.php?error=Failed to create checklist");
}
exit();
