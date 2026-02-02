<?php
function write_audit(mysqli $conn, int $user_id, string $action) {
  $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action) VALUES (?, ?)");
  $stmt->bind_param("is", $user_id, $action);
  $stmt->execute();
}
