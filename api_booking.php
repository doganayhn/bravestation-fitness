<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/helpers.php';
require_login();
$uid = (int)user()['id'];

$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

if ($action === 'create') {
  $trainer_id = (int)($_POST['trainer_id'] ?? 0);
  $class_type = trim($_POST['class_type'] ?? '');
  $date = $_POST['date'] ?? '';
  $time = trim($_POST['time_slot'] ?? '');
  if ($trainer_id<=0 || $class_type==='' || $date==='' || $time==='') json_out(['ok'=>false], 400);

  $st = db()->prepare("INSERT INTO bookings (user_id,trainer_id,class_type,date,time_slot,status) VALUES (?,?,?,?,?,'Confirmed')");
  $st->bind_param("iisss", $uid, $trainer_id, $class_type, $date, $time);
  $st->execute();
  json_out(['ok'=>true]);
}

if ($action === 'list') {
  $rows = [];
  $st = db()->prepare("
    SELECT b.class_type, b.date, b.time_slot, b.status, t.name AS trainer_name
    FROM bookings b
    JOIN trainers t ON t.id=b.trainer_id
    WHERE b.user_id=?
    ORDER BY b.date DESC, b.id DESC
  ");
  $st->bind_param("i", $uid);
  $st->execute();
  $res = $st->get_result();
  while ($r = $res->fetch_assoc()) $rows[] = $r;
  json_out(['ok'=>true,'bookings'=>$rows]);
}
json_out(['ok'=>false], 400);

