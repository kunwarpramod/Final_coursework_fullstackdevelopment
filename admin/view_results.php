<?php
session_start();
include '../db.php';
include '../auth_check.php';


$elections = $conn->query("
    SELECT * FROM elections 
    WHERE status IN ('ongoing','completed')
    ORDER BY start_date DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Election Results</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>
  <h1>Election Results Dashboard</h1>

  <p style="text-align: center;">
    <a href="admin_dashboard.php">Back to Admin Dashboard</a>
  </p>

  <?php if ($elections->num_rows > 0): ?>
    <?php while ($e = $elections->fetch_assoc()): ?>
      
      <h3>
        <?php echo htmlspecialchars($e['title']); ?> 
        <span style="font-size: 0.8em; opacity: 0.7;">
            (<?php echo strtoupper(htmlspecialchars($e['status'])); ?>)
        </span>
      </h3>

      <?php
  
      $stmt = $conn->prepare("
          SELECT c.name, c.organization, c.image, COUNT(v.vote_id) AS total_votes
          FROM candidates c
          LEFT JOIN votes v ON c.candidate_id = v.candidate_id
          WHERE c.election_id = ?
          GROUP BY c.candidate_id
          ORDER BY total_votes DESC
      ");
      $stmt->bind_param("i", $e['election_id']);
      $stmt->execute();
      $res = $stmt->get_result();


      $count_stmt = $conn->prepare("SELECT COUNT(*) as sum FROM votes WHERE election_id = ?");
      $count_stmt->bind_param("i", $e['election_id']);
      $count_stmt->execute();
      $total_sum = $count_stmt->get_result()->fetch_assoc()['sum'];
      ?>

      <table>
        <thead>
          <tr>
            <th style="width: 80px;">Candidate</th>
            <th>Name & Party</th>
            <th>Votes</th>
            <th>Percentage</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $res->fetch_assoc()): 
            $percent = ($total_sum > 0) ? round(($row['total_votes'] / $total_sum) * 100, 1) : 0;
          ?>
            <tr>
              <td>
                <?php if ($row['image']): ?>
                  <img src="../<?php echo htmlspecialchars($row['image']); ?>" width="50" style="border-radius: 4px;">
                <?php else: ?>
                  <div style="width:50px; height:50px; background:#eee; border-radius:4px;"></div>
                <?php endif; ?>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($row['name']); ?></strong><br>
                <small><?php echo htmlspecialchars($row['organization']); ?></small>
              </td>
              <td>
                <span style="font-size: 1.2rem; font-weight: 700; color: #1877f2;">
                    <?php echo $row['total_votes']; ?>
                </span>
              </td>
              <td>
                <div style="background: #f1f5f9; border-radius: 4px; height: 10px; width: 100px; display: inline-block; margin-right: 10px;">
                    <div style="background: #1877f2; height: 100%; border-radius: 4px; width: <?php echo $percent; ?>%;"></div>
                </div>
                <strong><?php echo $percent; ?>%</strong>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

      <p style="margin-top: -21px; border-top: none; font-size: 0.9rem; text-align: right; color: #64748b;">
        Total Participation: <strong><?php echo $total_sum; ?> Votes</strong>
      </p>

    <?php endwhile; ?>
  <?php else: ?>
    <p style="text-align: center;">No active or completed elections found.</p>
  <?php endif; ?>

</body>
</html>