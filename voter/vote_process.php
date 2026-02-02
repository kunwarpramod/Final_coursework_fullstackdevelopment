<?php
include '../auth_check.php';
include '../db.php';
include '../csrf.php';

// CSRF protection
$csrf = $_POST['csrf_token'] ?? '';
if (!verify_csrf_token($csrf)) {
    echo "<h1>Invalid request</h1>";
    echo "<div class='error'>Invalid or missing CSRF token. Please try again.</div>";
    echo "<p style='text-align:center;'><a href='voter_dashboard.php'>Back to Dashboard</a></p>";
    exit;
}



if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'voter') { 
    header("Location: ../index.php"); 
    exit; 
}

$voter_id = (int)$_SESSION['user_id'];
$candidate_id = (int)($_POST['candidate_id'] ?? 0);
$election_id = (int)($_POST['election_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Processing Vote...</title>
  <link rel="stylesheet" href="../styles.css">
</head>
<body>

<?php
if (!$candidate_id || !$election_id) { 
    echo "<h1>Error</h1>";
    echo "<div class='error'>Invalid vote submission. Please select a candidate.</div>";
    echo "<p style='text-align:center;'><a href='voter_dashboard.php'>Back to Dashboard</a></p>";
    exit;
}

// Prevent duplicate voting
$check = $conn->prepare("SELECT vote_id FROM votes WHERE voter_id=? AND election_id=?");
$check->bind_param("ii", $voter_id, $election_id);
$check->execute();
$existing = $check->get_result();

if ($existing->num_rows > 0) { 
    echo "<h1>Notice</h1>";
    echo "<div class='error'>You have already voted in this election. Duplicates are not allowed.</div>";
} else {

    $ins = $conn->prepare("INSERT INTO votes (voter_id, candidate_id, election_id) VALUES (?,?,?)");
    $ins->bind_param("iii", $voter_id, $candidate_id, $election_id);

    if ($ins->execute()) {
       
        if (file_exists("../logs/audit_log.php")) {
            include "../logs/audit_log.php";
            write_audit($conn, $voter_id, "VOTE_CAST election_id=$election_id candidate_id=$candidate_id");
        }
        
        echo "<h1>Success</h1>";
        echo "<div class='success'>Thank you! Your vote has been cast successfully.</div>";
    } else {
        echo "<h1>System Error</h1>";
        echo "<div class='error'>Something went wrong while processing your vote. Please try again later.</div>";
    }
}

echo "<p style='text-align: center;'>";
echo "<a href='voter_dashboard.php'>Return to Dashboard</a>";
echo "</p>";
?>

</body>
</html>