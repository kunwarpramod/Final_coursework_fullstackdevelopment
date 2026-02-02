<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: /online-voting-system 2/login.php");
  exit;
}


function require_role($role) {
  if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
    header("Location: /online-voting-system 2/index.php");
    exit;
  }
}
?>
