<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$mode  = $_POST['mode'] ?? 'signin';
$email = trim($_POST['email'] ?? '');
$pass  = $_POST['password'] ?? '';
$name  = trim($_POST['full_name'] ?? '');

if ($email === '' || $pass === '') {
  $_SESSION['login_error'] = 'Please enter email and password.';
  header('Location: index.php?login=1');
  exit;
}

if ($mode === 'signup') {
  if ($name === '') $name = 'User';
  $hash = password_hash($pass, PASSWORD_DEFAULT);

  try {
    $st = db()->prepare("INSERT INTO users (full_name,email,password_hash,role) VALUES (?,?,?,'user')");
    $st->bind_param("sss", $name, $email, $hash);
    $st->execute();
  } catch (mysqli_sql_exception $e) {
    $_SESSION['login_error'] = 'Email already registered. Please sign in.';
    header('Location: index.php?login=1');
    exit;
  }
}

$st = db()->prepare("SELECT id, full_name, email, password_hash, role FROM users WHERE email=? LIMIT 1");
$st->bind_param("s", $email);
$st->execute();
$u = $st->get_result()->fetch_assoc();

if (!$u || !password_verify($pass, $u['password_hash'])) {
  $_SESSION['login_error'] = 'Invalid email or password.';
  header('Location: index.php?login=1');
  exit;
}

$_SESSION['user'] = [
  'id' => (int)$u['id'],
  'full_name' => $u['full_name'],
  'email' => $u['email'],
  'role' => $u['role'],
];

header('Location: ' . ($u['role'] === 'admin' ? 'admin.php' : 'dashboard.php'));
exit;
