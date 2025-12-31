<?php
if (session_status() === PHP_SESSION_NONE) session_start();

function user() { return $_SESSION['user'] ?? null; }
function is_logged_in(): bool { return isset($_SESSION['user']); }
function is_admin(): bool { return is_logged_in() && (($_SESSION['user']['role'] ?? '') === 'admin'); }

function require_login(): void {
  if (!is_logged_in()) { header('Location: index.php'); exit; }
}
function require_admin(): void {
  if (!is_admin()) { header('Location: dashboard.php'); exit; }
}
?>