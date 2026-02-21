<?php
session_start();
require_once "../config/db.php";

$title = $_POST['title'];
$desc = $_POST['description'];
$employee_id = $_POST['employee_id'];
$due_date = $_POST['due_date'];
$admin_id = $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO special_tasks(title,description,employee_id,assigned_by,due_date)
VALUES(?,?,?,?,?)");

$stmt->bind_param("ssiis",$title,$desc,$employee_id,$admin_id,$due_date);
$stmt->execute();

header("Location: special_tasks_list.php?msg=Task Assigned");
