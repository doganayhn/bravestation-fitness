<?php
require_once __DIR__ . '/inc/auth.php';
session_destroy();
header('Location: index.php');
exit;
