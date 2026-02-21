<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../login.php"); exit();
}

$emp_id = $_SESSION['user_id'];
$task_id = (int)$_GET['task_id'];
$today = date('Y-m-d');

/* Fetch report */
$stmt = $conn->prepare("
SELECT r.id, t.title, r.submitted_at
FROM reports r
JOIN tasks t ON t.id = r.task_id
WHERE r.employee_id=? AND r.task_id=? AND r.report_date=?
LIMIT 1
");
$stmt->bind_param("iis",$emp_id,$task_id,$today);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if(!$report){
    echo "No submission found."; exit();
}

/* Fetch checklist answers */
$items = $conn->prepare("
SELECT question, answer
FROM report_items
WHERE report_id=?
");
$items->bind_param("i",$report['id']);
$items->execute();
$res = $items->get_result();
?>

<h2>Review Submission</h2>
<p><b><?php echo htmlspecialchars($report['title']); ?></b></p>
<p>Submitted at: <?php echo $report['submitted_at']; ?></p>

<hr>

<?php while($row = $res->fetch_assoc()): ?>
<p><b><?php echo htmlspecialchars($row['question']); ?></b></p>
<p><?php echo htmlspecialchars($row['answer']); ?></p>
<hr>
<?php endwhile; ?>
