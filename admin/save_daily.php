<?php
session_start();
if($_SESSION['role']!='admin'){ header("Location: ../index.php"); exit(); }
require_once "../config/db.php";

$admin_id = $_SESSION['user_id'];
$employee_id = (int)$_POST['employee_id'];
$task_id = (int)$_POST['task_id'];
$start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : NULL;

// prevent duplicates
$chk = $conn->prepare("SELECT id FROM daily_assignments WHERE employee_id=? AND task_id=? AND assigned_by=? AND active='yes' LIMIT 1");
$chk->bind_param("iii",$employee_id,$task_id,$admin_id);
$chk->execute();
if($chk->get_result()->num_rows>0){
  header("Location: assign_daily.php?error=Already assigned"); exit();
}

$stmt = $conn->prepare("INSERT INTO daily_assignments(task_id,employee_id,assigned_by,start_date,active)
                        VALUES(?,?,?,?, 'yes')");
$stmt->bind_param("iiis",$task_id,$employee_id,$admin_id,$start_date);
$stmt->execute();

header("Location: assign_daily.php?msg=Daily checklist assigned");
