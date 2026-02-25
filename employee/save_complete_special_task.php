<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$emp_id = $_SESSION['user_id'];
$id = intval($_POST['id']);
$remark = isset($_POST['employee_remark']) ? $_POST['employee_remark'] : '';

$stmt = $conn->prepare("UPDATE special_tasks
                        SET status='completed', employee_remark=?, completed_at=NOW()
                        WHERE id=? AND employee_id=?");
$stmt->bind_param("sii",$remark,$id,$emp_id);
$stmt->execute();

header("Location: special_tasks.php?msg=Task marked completed");
