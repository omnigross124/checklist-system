<?php
session_start();

if (!isset($_SESSION['role'])) {
  header("Location: ../index.php");
  exit();
}

function require_roles(array $roles) {
  if (!in_array($_SESSION['role'], $roles, true)) {
    http_response_code(403);
    die("Access denied");
  }
}