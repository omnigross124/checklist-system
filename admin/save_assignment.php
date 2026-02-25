<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$admin_id = $_SESSION['user_id'];
$employee_id = intval($_POST['employee_id']);
$task_id = intval($_POST['task_id']);
$due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;

// security: ensure employee belongs to admin
$checkEmp = $conn->prepare("SELECT id FROM users WHERE id=? AND role='employee' AND parent_id=? LIMIT 1");
$checkEmp->bind_param("ii", $employee_id, $admin_id);
$checkEmp->execute();
$empRes = $checkEmp->get_result();

if($empRes->num_rows == 0){
    header("Location: assign_task.php?error=Invalid employee selection");
    exit();
}

// security: ensure task belongs to admin
$checkTask = $conn->prepare("SELECT id FROM tasks WHERE id=? AND created_by=? LIMIT 1");
$checkTask->bind_param("ii", $task_id, $admin_id);
$checkTask->execute();
$taskRes = $checkTask->get_result();

if($taskRes->num_rows == 0){
    header("Location: assign_task.php?error=Invalid task selection");
    exit();
}

$stmt = $conn->prepare("INSERT INTO assignments (task_id, employee_id, assigned_by, due_date, status) VALUES (?,?,?,?, 'pending')");
$stmt->bind_param("iiis", $task_id, $employee_id, $admin_id, $due_date);

if($stmt->execute()){
    header("Location: assign_task.php?msg=Checklist assigned successfully");
} else {
    header("Location: assign_task.php?error=Failed to assign checklist");
}
exit();
$dup = $conn->prepare("SELECT id FROM assignments WHERE task_id=? AND employee_id=? AND status='pending' LIMIT 1");
$dup->bind_param("ii", $task_id, $employee_id);
$dup->execute();
$dupRes = $dup->get_result();

if($dupRes->num_rows > 0){
    header("Location: assign_task.php?error=This checklist is already pending for this employee");
    exit();
}
