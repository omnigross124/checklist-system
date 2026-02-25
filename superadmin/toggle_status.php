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

$get = $conn->prepare("SELECT status FROM users WHERE id=? LIMIT 1");
$get->bind_param("i", $id);
$get->execute();
$res = $get->get_result();

if($res->num_rows == 0){
    header("Location: users_list.php");
    exit();
}

$user = $res->fetch_assoc();
$newStatus = ($user['status'] == 'active') ? 'inactive' : 'active';

$upd = $conn->prepare("UPDATE users SET status=? WHERE id=?");
$upd->bind_param("si", $newStatus, $id);
$upd->execute();

header("Location: users_list.php");
exit();
