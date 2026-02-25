<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'super_admin'){
    header("Location: ../index.php"); exit();
}
require_once "../config/db.php";

$name = $_SESSION['name'];
$initial = strtoupper(substr($name,0,1));

$admin_id = isset($_GET['admin_id']) ? intval($_GET['admin_id']) : 0;
$employee_id = isset($_GET['employee_id']) ? intval($_GET['employee_id']) : 0;
$from = isset($_GET['from']) ? $_GET['from'] : '';
$to   = isset($_GET['to']) ? $_GET['to'] : '';

$admins = $conn->query("SELECT id,name,email FROM users WHERE role='admin' ORDER BY name");
$employees = $conn->query("SELECT id,name,email FROM users WHERE role='employee' ORDER BY name");

// Build WHERE
$where = " WHERE 1=1 ";
$params = [];
$types = "";

if($admin_id > 0){
    $where .= " AND emp.parent_id=? ";
    $types .= "i";
    $params[] = $admin_id;
}

if($employee_id > 0){
    $where .= " AND r.employee_id=? ";
    $types .= "i";
    $params[] = $employee_id;
}

if($from != ''){
    $where .= " AND DATE(r.submitted_at) >= ? ";
    $types .= "s";
    $params[] = $from;
}

if($to != ''){
    $where .= " AND DATE(r.submitted_at) <= ? ";
    $types .= "s";
    $params[] = $to;
}

$sql = "SELECT 
            r.id AS report_id,
            r.submitted_at,
            emp.name AS emp_name,
            emp.email AS emp_email,
            adm.name AS admin_name,
            adm.email AS admin_email,
            t.title AS task_title
        FROM reports r
        JOIN users emp ON r.employee_id = emp.id
        LEFT JOIN users adm ON emp.parent_id = adm.id
        JOIN tasks t ON r.task_id = t.id
        $where
        ORDER BY r.id DESC";

$stmt = $conn->prepare($sql);

if(count($params) > 0){
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// KPI counts (small dashboard vibe)
$countTotal = $conn->query("SELECT COUNT(*) c FROM reports")->fetch_assoc()['c'];
$countFiltered = $result->num_rows;
?>
<!DOCTYPE html>
<html>
<head>
  <title>All Reports</title>
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<div class="container">
  <div class="topbar">
    <div class="brand">
      <div class="logo"></div>
      <div>
        <h1>Checklist System</h1>
        <p>Super Admin Panel</p>
      </div>
    </div>

    <div class="userchip">
      <div class="avatar"><?php echo $initial; ?></div>
      <div class="meta">
        <b><?php echo htmlspecialchars($name); ?></b>
        <span>Role: Super Admin</span>
      </div>
      <a class="btn danger" href="../logout.php">Logout</a>
    </div>
  </div>

  <div class="page">
    <div class="pagehead">
      <div>
        <h2>All Reports</h2>
        <p>Monitor all checklist submissions across admins and employees.</p>
      </div>
      <div class="actions">
        <a class="btn" href="dashboard.php">Back</a>
      </div>
    </div>

    <div class="kpi">
      <div class="mini">
        <span style="color:var(--muted);font-size:12px;">Total Reports</span>
        <b><?php echo intval($countTotal); ?></b>
      </div>
      <div class="mini">
        <span style="color:var(--muted);font-size:12px;">Showing</span>
        <b><?php echo intval($countFiltered); ?></b>
      </div>
      <div class="mini">
        <span style="color:var(--muted);font-size:12px;">Filter</span>
        <b><?php echo ($admin_id||$employee_id||$from||$to) ? "ON" : "OFF"; ?></b>
      </div>
      <div class="mini">
        <span style="color:var(--muted);font-size:12px;">Status</span>
        <b>Live</b>
      </div>
    </div>

    <form class="toolbar" method="GET">
      <div class="field">
        <label>Admin</label>
        <select name="admin_id">
          <option value="0">All</option>
          <?php while($a = $admins->fetch_assoc()): ?>
            <option value="<?php echo $a['id']; ?>" <?php if($admin_id==$a['id']) echo "selected"; ?>>
              <?php echo htmlspecialchars($a['name']." (".$a['email'].")"); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="field">
        <label>Employee</label>
        <select name="employee_id">
          <option value="0">All</option>
          <?php while($e = $employees->fetch_assoc()): ?>
            <option value="<?php echo $e['id']; ?>" <?php if($employee_id==$e['id']) echo "selected"; ?>>
              <?php echo htmlspecialchars($e['name']." (".$e['email'].")"); ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="field">
        <label>From</label>
        <input class="input" type="date" name="from" value="<?php echo htmlspecialchars($from); ?>">
      </div>

      <div class="field">
        <label>To</label>
        <input class="input" type="date" name="to" value="<?php echo htmlspecialchars($to); ?>">
      </div>

      <div class="actions">
        <button class="btn primary" type="submit">Filter</button>
        <a class="btn" href="reports_list.php">Reset</a>
      </div>
    </form>

    <div class="tablewrap">
      <table>
        <tr>
          <th>Report ID</th>
          <th>Employee</th>
          <th>Admin</th>
          <th>Checklist</th>
          <th>Submitted At</th>
          <th>Action</th>
        </tr>

        <?php if($result->num_rows == 0): ?>
          <tr>
            <td colspan="6" style="color:var(--muted);">No reports found for selected filters.</td>
          </tr>
        <?php endif; ?>

        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td>#<?php echo $row['report_id']; ?></td>
            <td>
              <b><?php echo htmlspecialchars($row['emp_name']); ?></b><br>
              <span style="color:var(--muted);font-size:12px;"><?php echo htmlspecialchars($row['emp_email']); ?></span>
            </td>
            <td>
              <b><?php echo htmlspecialchars($row['admin_name']); ?></b><br>
              <span style="color:var(--muted);font-size:12px;"><?php echo htmlspecialchars($row['admin_email']); ?></span>
            </td>
            <td><span class="badge warn"><?php echo htmlspecialchars($row['task_title']); ?></span></td>
            <td><?php echo htmlspecialchars($row['submitted_at']); ?></td>
            <td>
              <a class="btn success" href="view_report.php?report_id=<?php echo $row['report_id']; ?>">View</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </table>
    </div>

  </div>
</div>

</body>
</html>
