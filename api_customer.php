<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/helpers.php';
require_login();
require_admin();

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

if ($action === 'list') {
  $q = trim($_GET['q'] ?? '');
  if ($q !== '') {
    $like = "%$q%";
    $st = db()->prepare("SELECT * FROM customers WHERE full_name LIKE ? OR email LIKE ? ORDER BY id DESC");
    $st->bind_param("ss", $like, $like);
  } else {
    $st = db()->prepare("SELECT * FROM customers ORDER BY id DESC");
  }
  $st->execute();
  $res = $st->get_result();
  $rows = [];
  while ($r = $res->fetch_assoc()) $rows[] = $r;
  json_out(['ok'=>true,'customers'=>$rows]);
}

if ($action === 'create') {
  $name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $membership = trim($_POST['membership'] ?? 'Basic');
  $join_date = $_POST['join_date'] ?? date('Y-m-d');
  $status = $_POST['status'] ?? 'active';
  if ($name==='' || $email==='' || $phone==='') json_out(['ok'=>false], 400);

  $st = db()->prepare("INSERT INTO customers (full_name,email,phone,membership,join_date,status) VALUES (?,?,?,?,?,?)");
  $st->bind_param("ssssss", $name, $email, $phone, $membership, $join_date, $status);
  $st->execute();
  json_out(['ok'=>true]);
}

if ($action === 'update') {
  $id = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['full_name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $membership = trim($_POST['membership'] ?? 'Basic');
  $join_date = $_POST['join_date'] ?? date('Y-m-d');
  $status = $_POST['status'] ?? 'active';
  if ($id<=0) json_out(['ok'=>false], 400);

  $st = db()->prepare("UPDATE customers SET full_name=?,email=?,phone=?,membership=?,join_date=?,status=? WHERE id=?");
  $st->bind_param("ssssssi", $name, $email, $phone, $membership, $join_date, $status, $id);
  $st->execute();
  json_out(['ok'=>true]);
}

if ($action === 'delete') {
  $id = (int)($_POST['id'] ?? 0);
  $st = db()->prepare("DELETE FROM customers WHERE id=?");
  $st->bind_param("i", $id);
  $st->execute();
  json_out(['ok'=>true]);
}

json_out(['ok'=>false], 400);
