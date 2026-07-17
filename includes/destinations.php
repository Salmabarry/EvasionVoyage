<?php
require_once __DIR__ . '/db.php';

$destinations = db()->query('SELECT * FROM destinations ORDER BY name ASC')->fetchAll();

function format_fcfa($amount) {
  return number_format((float) $amount, 0, ',', ' ') . ' FCFA';
}
