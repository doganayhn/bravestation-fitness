<?php
require_once __DIR__ . '/inc/db.php';
require_once __DIR__ . '/inc/auth.php';
require_once __DIR__ . '/inc/helpers.php';

require_login();
$u = user();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$action = $_POST['action'] ?? ($_GET['action'] ?? 'list');

function json_ok($extra = []) {
  echo json_encode(array_merge(['ok' => true], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}
function json_err($msg, $extra = []) {
  echo json_encode(array_merge(['ok' => false, 'error' => $msg], $extra), JSON_UNESCAPED_UNICODE);
  exit;
}

function has_active_membership(int $userId): bool {
  $st = db()->prepare("SELECT id FROM orders WHERE user_id=? AND status='active' LIMIT 1");
  $st->bind_param("i", $userId);
  $st->execute();
  $row = $st->get_result()->fetch_assoc();
  return (bool)$row;
}

if ($action === 'list') {
  $items = [];
  $st = db()->prepare("SELECT item_key, item_name, price, billing_cycle, created_at
                       FROM cart_items
                       WHERE user_id=?
                       ORDER BY id DESC");
  $st->bind_param("i", $u['id']);
  $st->execute();
  $res = $st->get_result();
  while ($row = $res->fetch_assoc()) $items[] = $row;

  json_ok([
    'items' => $items,
    'has_active_membership' => has_active_membership((int)$u['id'])
  ]);
}

if ($action === 'add') {
  $itemKey  = trim($_POST['item_key'] ?? '');
  $itemName = trim($_POST['item_name'] ?? '');
  $price    = (int)($_POST['price'] ?? 0);

  $cycle = $_POST['billing_cycle'] ?? 'monthly';
  $cycle = ($cycle === 'yearly') ? 'yearly' : 'monthly';

  if ($itemKey === '' || $itemName === '' || $price <= 0) {
    json_err('Missing/invalid item data');
  }

  db()->begin_transaction();
  try {
    
    $st0 = db()->prepare("DELETE FROM cart_items WHERE user_id=?");
    $st0->bind_param("i", $u['id']);
    $st0->execute();

    $st = db()->prepare("INSERT INTO cart_items (user_id, item_key, item_name, price, billing_cycle)
                         VALUES (?,?,?,?,?)");
    $st->bind_param("issis", $u['id'], $itemKey, $itemName, $price, $cycle);
    $st->execute();

    db()->commit();
    json_ok(['billing_cycle' => $cycle]);
  } catch (Throwable $e) {
    db()->rollback();
    json_err('DB error while adding to cart');
  }
}

if ($action === 'remove') {
  $itemKey = trim($_POST['item_key'] ?? '');
  if ($itemKey === '') json_err('Missing item_key');

  $st = db()->prepare("DELETE FROM cart_items WHERE user_id=? AND item_key=?");
  $st->bind_param("is", $u['id'], $itemKey);
  $st->execute();

  json_ok();
}

if ($action === 'checkout') {
  if (has_active_membership((int)$u['id'])) {
    json_err("You already have an active membership. You can't purchase a second plan.", [
      'code' => 'ACTIVE_MEMBERSHIP'
    ]);
  }

  $st = db()->prepare("SELECT item_name, price, billing_cycle
                       FROM cart_items
                       WHERE user_id=?
                       ORDER BY id DESC
                       LIMIT 1");
  $st->bind_param("i", $u['id']);
  $st->execute();
  $item = $st->get_result()->fetch_assoc();

  if (!$item) json_err('Cart is empty');

  $membershipName = $item['item_name'];
  $price = (int)$item['price'];
  $cycle = ($item['billing_cycle'] === 'yearly') ? 'yearly' : 'monthly';

  $orderCode = 'ORD-' . strtoupper(bin2hex(random_bytes(4))) . '-' . time();
  $now = date('Y-m-d H:i:s');

  db()->begin_transaction();
  try {
    
    $st3 = db()->prepare("INSERT INTO orders (user_id, order_code, membership_name, price, billing_cycle, status, purchased_at)
                          VALUES (?,?,?,?,?, 'active', ?)");
    $st3->bind_param("ississ", $u['id'], $orderCode, $membershipName, $price, $cycle, $now);
    $st3->execute();

    $st4 = db()->prepare("DELETE FROM cart_items WHERE user_id=?");
    $st4->bind_param("i", $u['id']);
    $st4->execute();

    db()->commit();
    json_ok(['order_code' => $orderCode, 'billing_cycle' => $cycle]);
  } catch (Throwable $e) {
    db()->rollback();
    json_err('DB error while checkout');
  }
}

json_err('Unknown action');
