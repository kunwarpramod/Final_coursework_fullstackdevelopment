<?php
include '../db.php'; 

// Fetch elections that are either ongoing or completed
$elections = $conn->query("
    SELECT * FROM elections 
    WHERE status IN ('ongoing','completed')
    ORDER BY start_date DESC
");
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Election Results</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <h2>Election Results</h2>

  <?php if ($elections->num_rows === 0): ?>
    <p>No results available yet.</p>
  <?php endif; ?>

  <?php while ($e = $elections->fetch_assoc()): ?>
    <h3><?php echo htmlspecialchars($e['title']); ?> 
        (<?php echo htmlspecialchars($e['status']); ?>)</h3>

    <?php
    // Fetch candidates and their vote counts
    $stmt = $conn->prepare("
        SELECT c.name, c.organization, COUNT(v.vote_id) AS total_votes
        FROM candidates c
        LEFT JOIN votes v ON c.candidate_id = v.candidate_id
        WHERE c.election_id = ?
        GROUP BY c.candidate_id
        ORDER BY total_votes DESC
    ");
    $stmt->bind_param("i", $e['election_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    ?>

    <table border="1" cellpadding="8">
      <tr>
        <th>Candidate</th>
        <th>Organization</th>
        <th>Total Votes</th>
      </tr>
      <?php while ($row = $res->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($row['name']); ?></td>
          <td><?php echo htmlspecialchars($row['organization']); ?></td>
          <td><?php echo htmlspecialchars($row['total_votes']); ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php endwhile; ?>
</body>
</html>

