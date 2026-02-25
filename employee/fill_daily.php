<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role']!='employee'){
  header("Location: ../index.php"); exit();
}
require_once "../config/db.php";
date_default_timezone_set('Asia/Kolkata');

$emp_id = $_SESSION['user_id'];
$task_id = (int)($_GET['task_id'] ?? 0);
if($task_id <= 0) die("Invalid task.");

$today = date("Y-m-d");
$day = date("N");
$timeNow = date("H:i");

if(!($day>=1 && $day<=6)) die("Locked: Mon–Sat only.");
if(!($timeNow>="09:00" && $timeNow<="18:00")) die("Locked: Open 9AM–6PM only.");

// check assigned daily
$chkA = $conn->prepare("SELECT id FROM daily_assignments WHERE employee_id=? AND task_id=? AND active='yes' LIMIT 1");
$chkA->bind_param("ii",$emp_id,$task_id);
$chkA->execute();
if($chkA->get_result()->num_rows==0) die("Not assigned.");

// already submitted today?
$chk = $conn->prepare("SELECT id FROM reports WHERE employee_id=? AND task_id=? AND report_date=? LIMIT 1");
$chk->bind_param("iis",$emp_id,$task_id,$today);
$chk->execute();
if($chk->get_result()->num_rows>0) die("Already submitted for today.");

// load task
$t = $conn->prepare("SELECT title,description FROM tasks WHERE id=? LIMIT 1");
$t->bind_param("i",$task_id);
$t->execute();
$task = $t->get_result()->fetch_assoc();
if(!$task) die("Task not found.");

$lines = preg_split("/\r\n|\n|\r/", trim($task['description']));
$items = [];
foreach($lines as $l){
  $l = trim($l);
  if($l!="") $items[] = $l;
}
if(count($items)==0) die("Checklist has no items.");
?>
<!DOCTYPE html>
<html>
<head>
  <title>Daily Checklist</title>
  <link rel="stylesheet" href="../assets/style.css">
  <style>
    .hidden{display:none;}
    .reviewBox{
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 16px;
      padding: 14px;
      margin-top: 14px;
    }
  </style>
</head>
<body>
<div class="container">
  <div class="page">
    <div class="pagehead">
      <div>
        <h2><?php echo htmlspecialchars($task['title']); ?> (Daily)</h2>
        <p>Step 1: Review → Step 2: Submit • Date: <?php echo $today; ?></p>
      </div>
      <div class="actions">
        <a class="btn" href="assignments.php">Back</a>
      </div>
    </div>

    <form id="dailyForm" method="POST" action="submit_daily.php">
      <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">

      <div class="tablewrap">
        <table>
          <tr><th>Item</th><th>Response</th><th>Comment</th></tr>

          <?php foreach($items as $idx => $text): ?>
            <tr>
              <td>
                <?php echo htmlspecialchars($text); ?>
                <input type="hidden" name="task_item[]" value="<?php echo htmlspecialchars($text); ?>">
              </td>
              <td>
                <select class="respSelect" name="response[]" required>
                  <option value="">--Select--</option>
                  <option value="yes">Yes</option>
                  <option value="no">No</option>
                  <option value="na">N/A</option>
                </select>
              </td>
              <td>
                <textarea class="cmtBox" name="comment[]" rows="2"></textarea>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>

      <!-- Step buttons -->
      <div class="actions" style="margin-top:14px;">
        <!-- FIRST button -->
        <button type="button" id="reviewBtn" class="btn success">Review Submission</button>

        <!-- SECOND button (hidden first) -->
        <button type="submit" id="submitBtn" class="btn primary hidden">Submit</button>

        <!-- Optional: allow edit after review 
        <button type="button" id="editBtn" class="btn hidden">Edit</button>-->
      </div>

      <!-- Review section -->
      <div id="reviewSection" class="reviewBox hidden">
        <h3 style="margin:0 0 10px 0;">Review your answers</h3>
        <div class="tablewrap">
          <table id="reviewTable">
            <tr><th>Item</th><th>Response</th><th>Comment</th></tr>
          </table>
        </div>
        <p style="color:var(--muted); margin-top:10px;">
          If everything looks correct, click <b>Submit</b>. After submitting you cannot edit.
        </p>
      </div>

    </form>
  </div>
</div>

<script>
  const reviewBtn = document.getElementById("reviewBtn");
  const submitBtn = document.getElementById("submitBtn");
  const editBtn   = document.getElementById("editBtn");
  const reviewSec = document.getElementById("reviewSection");
  const reviewTbl = document.getElementById("reviewTable");

  function escapeHtml(str){
    return String(str)
      .replaceAll("&","&amp;")
      .replaceAll("<","&lt;")
      .replaceAll(">","&gt;")
      .replaceAll('"',"&quot;")
      .replaceAll("'","&#039;");
  }

  reviewBtn.addEventListener("click", () => {
    const selects = document.querySelectorAll(".respSelect");
    const comments = document.querySelectorAll(".cmtBox");
    const items = document.querySelectorAll('input[name="task_item[]"]');

    // validation: every response must be selected
    for(let i=0;i<selects.length;i++){
      if(selects[i].value === ""){
        alert("Please select response for all items before reviewing.");
        selects[i].focus();
        return;
      }
    }

    // rebuild review table
    reviewTbl.innerHTML = `<tr><th>Item</th><th>Response</th><th>Comment</th></tr>`;
    for(let i=0;i<selects.length;i++){
      const itemText = items[i].value;
      const resp = selects[i].value.toUpperCase();
      const cmt = comments[i].value || "-";
      reviewTbl.innerHTML += `
        <tr>
          <td>${escapeHtml(itemText)}</td>
          <td>${escapeHtml(resp)}</td>
          <td>${escapeHtml(cmt)}</td>
        </tr>`;
    }

    // show review + submit
    reviewSec.classList.remove("hidden");
    submitBtn.classList.remove("hidden");
    editBtn.classList.remove("hidden");
    reviewBtn.classList.add("hidden");

    // (optional) lock inputs during review to force decision
   // selects.forEach(s => s.disabled = true);
    //comments.forEach(c => c.disabled = true);

    reviewSec.scrollIntoView({behavior:"smooth"});
  });

  
</script>

</body>
</html>