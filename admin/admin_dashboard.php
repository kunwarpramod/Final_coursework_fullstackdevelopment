<?php
include '../auth_check.php';
require_role('admin');
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <h2>Admin Dashboard</h2>
  <ul>
    <li><a class="glass-btn" href="manage_elections.php">Manage Elections</a></li>
    <li><a class="glass-btn" href="manage_candidates.php">Manage Candidates</a></li>
    <li><a class="glass-btn" href="manage_voters.php">Manage Voters</a></li>
    <li><a class="glass-btn" href="view_results.php">View Results</a></li>
  </ul>
  <p><a href="../logout.php">Logout</a></p>
</body>
</html>
