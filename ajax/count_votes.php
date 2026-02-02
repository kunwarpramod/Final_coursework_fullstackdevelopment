<?php
include '../db.php';

$election_id = (int)($_GET['election_id'] ?? 0);
if (!$election_id) { die("Select an election."); }

$stmt = $conn->prepare("
  SELECT c.name, c.organization, c.district, COUNT(v.vote_id) AS total_votes
  FROM candidates c
  LEFT JOIN votes v ON c.candidate_id = v.candidate_id AND v.election_id = c.election_id
  WHERE c.election_id = ?
  GROUP BY c.candidate_id
  ORDER BY total_votes DESC, c.name ASC
");
$stmt->bind_param("i", $election_id);
$stmt->execute();
$res = $stmt->get_result();

$out = "<table border='1' cellpadding='6'><tr><th>Candidate</th><th>Organization</th><th>District</th><th>Votes</th></tr>";
while ($r = $res->fetch_assoc()) {
  $out .= "<tr>
    <td>".htmlspecialchars($r['name'])."</td>
    <td>".htmlspecialchars($r['organization'])."</td>
    <td>".htmlspecialchars($r['district'])."</td>
    <td>".(int)$r['total_votes']."</td>
  </tr>";
}
$out .= "</table>";

echo $out;
