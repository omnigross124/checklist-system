<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee'){
    header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$emp_id = $_SESSION['user_id'];
$assignment_id = intval($_POST['assignment_id']);

$task_items = $_POST['task_item'];
$responses  = $_POST['response'];
$comments   = $_POST['comment'];

// verify assignment belongs to employee and not completed
$chk = $conn->prepare("SELECT status FROM assignments WHERE id=? AND employee_id=? LIMIT 1");
$chk->bind_param("ii", $assignment_id, $emp_id);
$chk->execute();
$chkRes = $chk->get_result();

if($chkRes->num_rows == 0){
    die("Invalid assignment");
}

$row = $chkRes->fetch_assoc();
if($row['status'] == 'completed'){
    die("Already submitted.");
}

$conn->begin_transaction();

try {
    // insert report
    $stmt = $conn->prepare("INSERT INTO reports (assignment_id, employee_id) VALUES (?,?)");
    $stmt->bind_param("ii", $assignment_id, $emp_id);
    $stmt->execute();
    $report_id = $conn->insert_id;

    // insert report items
    $itemStmt = $conn->prepare("INSERT INTO report_items (report_id, task_item, response, comment) VALUES (?,?,?,?)");

    for($i=0; $i<count($task_items); $i++){
        $ti = trim($task_items[$i]);
        $rp = trim($responses[$i]);
        $cm = trim($comments[$i]);

        $itemStmt->bind_param("isss", $report_id, $ti, $rp, $cm);
        $itemStmt->execute();
    }

    // mark assignment completed
    $up = $conn->prepare("UPDATE assignments SET status='completed' WHERE id=?");
    $up->bind_param("i", $assignment_id);
    $up->execute();

    $conn->commit();
    header("Location: assignments.php");
    exit();

} catch(Exception $e){
    $conn->rollback();
    die("Error submitting report.");
}
