<?php
include '../auth_check.php';
require_role('admin');
include '../db.php';
include '../csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) { die('Invalid CSRF token.'); }
    $title = trim($_POST['title']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    
    $current_date = date('Y-m-d');
    $status = 'upcoming';
    if ($current_date >= $start_date && $current_date <= $end_date) {
        $status = 'ongoing';
    } elseif ($current_date > $end_date) {
        $status = 'completed';
    }

    $stmt = $conn->prepare("INSERT INTO elections (title, start_date, end_date, status) VALUES (?,?,?,?)");
    $stmt->bind_param("ssss", $title, $start_date, $end_date, $status);
    $stmt->execute();
    header("Location: manage_elections.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $csrf = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) { die('Invalid CSRF token.'); }
    $election_id = (int)$_POST['election_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE elections SET status=? WHERE election_id=?");
    $stmt->bind_param("si", $status, $election_id);
    $stmt->execute();
    header("Location: manage_elections.php");
    exit;
}


if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $csrf = $_GET['csrf'] ?? '';
    if (!verify_csrf_token($csrf)) { die('Invalid CSRF token.'); }
    $stmt = $conn->prepare("DELETE FROM elections WHERE election_id=?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    header("Location: manage_elections.php");
    exit;
}

$elections = $conn->query("SELECT * FROM elections ORDER BY election_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Elections</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>

  <h1>Election Management</h1>

  <h3>Create New Election</h3>
  <form method="POST">
    <?php echo csrf_input_field(); ?>
    <div style="display: flex; gap: 15px; flex-wrap: wrap; align-items: flex-end;">
      <div style="flex: 2; min-width: 200px;">
        <label style="font-size: 12px; font-weight: 600;">Election Title</label>
        <input type="text" name="title" placeholder="e.g. Local Election 2026" required>
      </div>
      <div style="flex: 1; min-width: 150px;">
        <label style="font-size: 12px; font-weight: 600;">Start Date</label>
        <input type="date" name="start_date" required>
      </div>
      <div style="flex: 1; min-width: 150px;">
        <label style="font-size: 12px; font-weight: 600;">End Date</label>
        <input type="date" name="end_date" required>
      </div>
      <button type="submit" name="create">Create</button>
    </div>
  </form>

  <h2>Existing Elections</h2>
  <table>
    <thead>
      <tr>
        <th>Title</th>
        <th>Schedule</th>
        <th>Status</th>
        <th>Update Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($e = $elections->fetch_assoc()): ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($e['title']); ?></strong></td>
          <td>
            <small><?php echo $e['start_date']; ?> to <?php echo $e['end_date']; ?></small>
          </td>
          <td>
            <span style="font-weight: bold; color: <?php 
              echo ($e['status'] == 'ongoing' ? '#15803d' : ($e['status'] == 'upcoming' ? '#4338ca' : '#4b5563')); 
            ?>;">
              <?php echo strtoupper($e['status']); ?>
            </span>
          </td>
          <td>
            <form method="POST" style="margin: 0; padding: 0; border: none; box-shadow: none; display: flex; gap: 5px;"><?php echo csrf_input_field(); ?>
              <input type="hidden" name="election_id" value="<?php echo $e['election_id']; ?>">
              <select name="status" style="margin: 0; padding: 5px;">
                <option value="upcoming" <?php if($e['status']=='upcoming') echo 'selected'; ?>>Upcoming</option>
                <option value="ongoing" <?php if($e['status']=='ongoing') echo 'selected'; ?>>Ongoing</option>
                <option value="completed" <?php if($e['status']=='completed') echo 'selected'; ?>>Completed</option>
              </select>
              <button type="submit" name="update_status" style="padding: 5px 10px; margin: 0; font-size: 11px;">Set</button>
            </form>
          </td>
          <td>
            <a href="?delete=<?php echo $e['election_id']; ?>&csrf=<?php echo urlencode(generate_csrf_token()); ?>" 
               class="glass-btn" 
               onclick="return confirm('Delete this election and all related data?')">
               Delete
            </a>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <p style="text-align: center;">
    <a href="admin_dashboard.php" style="background: #64748b;">Back to Dashboard</a>
  </p>

</body>
</html>