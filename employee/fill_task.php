<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'employee'){
    header("Location: ../login.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));

$emp_id = $_SESSION['user_id'];
$assignment_id = intval($_GET['assignment_id']);

$sql = "SELECT a.id, a.status, a.due_date, t.title, t.description
        FROM assignments a
        JOIN tasks t ON a.task_id = t.id
        WHERE a.id=? AND a.employee_id=? LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $assignment_id, $emp_id);
$stmt->execute();
$res = $stmt->get_result();
if($res->num_rows == 0){ die("Invalid assignment"); }

$data = $res->fetch_assoc();
if($data['status'] == 'completed'){ die("This checklist already submitted."); }

// description → items (one per line)
$lines = preg_split("/\r\n|\n|\r/", trim($data['description']));
$items = [];
foreach($lines as $l){
  $l = trim($l);
  if($l != "") $items[] = $l;
}
if(count($items) == 0){
  die("Checklist has no items. Admin must add one question per line in description.");
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Fill Checklist</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
  <div class="topbar">
    <div class="brand">
      <div class="logo"></div>
      <div>
        <h1>Checklist System</h1>
        <p>Employee Panel</p>
      </div>
    </div>

    <div class="userchip">
      <div class="avatar"><?php echo $initial; ?></div>
      <div class="meta">
        <b><?php echo htmlspecialchars($name); ?></b>
        <span>Role: Employee</span>
      </div>
      <a class="btn danger" href="../logout.php">Logout</a>
    </div>
  </div>

  <div class="page">
    <div class="pagehead">
      <div>
        <h2>Fill Checklist — <?php echo htmlspecialchars($data['title']); ?></h2>
        <p>Assignment #<?php echo $assignment_id; ?> <?php if(!empty($data['due_date'])) echo "• Due: ".htmlspecialchars($data['due_date']); ?></p>
      </div>
      <div class="actions">
        <a class="btn" href="assignments.php">Back</a>
      </div>
    </div>

    <form action="submit_report.php" method="POST">
      <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">

      <div class="tablewrap">
        <table>
          <tr>
            <th>Checklist Item</th>
            <th>Response</th>
            <th>Comment</th>
          </tr>

          <?php foreach($items as $text): ?>
            <tr>
              <td>
                <?php echo htmlspecialchars($text); ?>
                <input type="hidden" name="task_item[]" value="<?php echo htmlspecialchars($text); ?>">
              </td>
              <td style="min-width:160px;">
                <select name="response[]" required>
                  <option value="">--Select--</option>
                  <option value="yes">Yes</option>
                  <option value="no">No</option>
                  <option value="na">N/A</option>
                </select>
              </td>
              <td style="min-width:260px;">
                <textarea name="comment[]" rows="2" placeholder="Optional comment"></textarea>
              </td>
            </tr>
          <?php endforeach; ?>

        </table>
      </div>

      <div class="actions" style="margin-top:14px;">
        <button class="btn primary" type="submit">Submit Report</button>
        <a class="btn" href="assignments.php">Cancel</a>
      </div>
    </form>

  </div>
</div>

</body>
</html>
