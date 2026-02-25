<?php
session_start();
if($_SESSION['role']!='employee'){ header("Location: ../index.php"); exit(); }
date_default_timezone_set("Asia/Kolkata"); 
require_once "../config/db.php";

$emp_id = $_SESSION['user_id'];
$task_id = (int)$_POST['task_id'];
$tz = new DateTimeZone('Asia/Kolkata');
$now = new DateTime('now', $tz);

$today = $now->format('Y-m-d');
$day   = (int)$now->format('N');    // 1=Mon ... 7=Sun
$time  = $now->format('H:i');

$open  = new DateTime($today.' 09:00', $tz);
$close = new DateTime($today.' 18:00', $tz);

if(!($day >= 1 && $day <= 6)) die("Locked: Mon–Sat only. Server time: ".$now->format('Y-m-d H:i:s'));
if(!($now >= $open && $now <= $close)) die("Locked: Open 9AM–6PM only. Server time: ".$now->format('Y-m-d H:i:s'));

// already submitted?
$chk = $conn->prepare("SELECT id FROM reports WHERE employee_id=? AND task_id=? AND report_date=? LIMIT 1");
$chk->bind_param("iis",$emp_id,$task_id,$today);
$chk->execute();
if($chk->get_result()->num_rows>0){ die("Already submitted today"); }

// find admin (assigned_by) from daily_assignments
$getA = $conn->prepare("SELECT assigned_by FROM daily_assignments WHERE employee_id=? AND task_id=? AND active='yes' LIMIT 1");
$getA->bind_param("ii",$emp_id,$task_id);
$getA->execute();
$a = $getA->get_result()->fetch_assoc();
$admin_id = (int)$a['assigned_by'];

// insert report
$ins = $conn->prepare("
  INSERT INTO reports (employee_id, task_id, submitted_at, report_date)
  VALUES (?, ?, NOW(), ?)
");
$ins->bind_param("iis", $emp_id, $task_id, $today);
$ins->execute();
$report_id = $conn->insert_id;

// insert items
$task_item = $_POST['task_item'];
$response = $_POST['response'];
$comment = $_POST['comment'];

for($i=0;$i<count($task_item);$i++){
  $it = $task_item[$i];
  $rs = $response[$i];
  $cm = $comment[$i];

  $ri = $conn->prepare("INSERT INTO report_items(report_id, task_item, response, comment)
                        VALUES(?,?,?,?)");
  $ri->bind_param("isss",$report_id,$it,$rs,$cm);
  $ri->execute();
}

header("Location: assignments.php?msg=Daily checklist submitted for today");

