<?php
require_once __DIR__ . '/config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function db(): mysqli {
  static $c = null;
  if ($c === null) {
    $c = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $c->set_charset('utf8mb4');
  }
  return $c;
}
