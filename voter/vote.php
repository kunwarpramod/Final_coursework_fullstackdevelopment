<?php
include '../auth_check.php';
include '../db.php';
include '../csrf.php';
if ($_SESSION['role'] !== 'voter') { header("Location: ../index.php"); exit; }

$election_id = (int)($_GET['election_id'] ?? 0);
if (!$election_id) { die("Invalid election."); }

$candidates = $conn->prepare("SELECT candidate_id, name, organization, district, image FROM candidates WHERE election_id=?");
$candidates->bind_param("i", $election_id);
$candidates->execute();
$res = $candidates->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cast Vote</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <h1>Election Ballot</h1>

    <h2>Select Your Candidate</h2>
    <form method="POST" action="vote_process.php">
        <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
        <?php echo csrf_input_field(); ?>

        <?php while ($c = $res->fetch_assoc()): ?>
            <ul style="list-style:none; display:flex; align-items:center; gap:20px; margin-bottom:10px;">
                <li>
                    <?php if ($c['image']): ?>
                        <img src="../<?php echo htmlspecialchars($c['image']); ?>" width="80" style="border-radius:8px; border:1px solid #d6dee8;">
                    <?php endif; ?>
                </li>
                <li style="flex-grow:1;">
                    <strong style="font-size:1.1rem;"><?php echo htmlspecialchars($c['name']); ?></strong><br>
                    <span><?php echo htmlspecialchars($c['organization']); ?></span> | 
                    <small><?php echo htmlspecialchars($c['district']); ?></small>
                </li>
                <li>
                    <input type="radio" name="candidate_id" value="<?php echo $c['candidate_id']; ?>" required style="width:25px; height:25px; cursor:pointer;">
                </li>
            </ul>
        <?php endwhile; ?>

        <div style="text-align:center; margin-top:20px;">
            <button type="submit">Submit My Vote</button>
        </div>
    </form>

    <p style="text-align:center;"><a href="voter_dashboard.php" style="background:#64748b;">Return to Dashboard</a></p>
</body>
</html>


