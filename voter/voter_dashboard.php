<?php
include '../auth_check.php';
include '../db.php';
if ($_SESSION['role'] !== 'voter') { header("Location: ../index.php"); exit; }

$elections = $conn->query("SELECT * FROM elections WHERE status IN ('upcoming','ongoing') ORDER BY start_date DESC");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Voter Dashboard</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <h2>Voter Dashboard</h2>

  <h3>Available Elections</h3>
  <ul>
    <?php while ($e = $elections->fetch_assoc()): ?>
      <li>
        <?php echo htmlspecialchars($e['title']); ?> (<?php echo htmlspecialchars($e['status']); ?>)
        <?php if ($e['status'] === 'ongoing'): ?>
          - <a class="glass-btn" href="vote.php?election_id=<?php echo $e['election_id']; ?>">Vote Now</a>
          - <a class="glass-btn" href="results.php?election_id=<?php echo $e['election_id']; ?>">View Results</a>
        <?php endif; ?>
      </li>
    <?php endwhile; ?>
  </ul>

  <p><a href="../logout.php">Logout</a></p>
</body>
</html>
