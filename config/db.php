<?php
date_default_timezone_set("Asia/Kolkata");
$host = "localhost";
$user = "root";
$pass = "";
$db   = "checklist_system";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
function generateLoginId($conn, $prefix){
    // get last used login_id for this prefix
    $stmt = $conn->prepare("SELECT login_id FROM users WHERE login_id LIKE CONCAT(?, '%') ORDER BY id DESC LIMIT 1");
    $likePrefix = $prefix;
    $stmt->bind_param("s", $likePrefix);
    $stmt->execute();
    $res = $stmt->get_result();

    $nextNumber = 1;

    if($row = $res->fetch_assoc()){
        // extract number part
        $last = $row['login_id']; // example EMP012
        $num = intval(substr($last, strlen($prefix)));
        $nextNumber = $num + 1;
    }

    // format EMP001 / ADM001
    return $prefix . str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
}
?>
