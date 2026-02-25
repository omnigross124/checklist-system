<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
    header("Location: ../index.php");
    exit();
}
require_once "../config/db.php";

$id = intval($_GET['id']);

if($id == $_SESSION['user_id']){
    header("Location: users_list.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM users WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();

header("Location: users_list.php");
exit();
