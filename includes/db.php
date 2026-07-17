<?php
/**
 * Connexion PDO (MySQL 8 via MAMP, environnement local).
 */
function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $dbname = 'evasionvoyage';
    $user = 'root';
    $pass = 'root';
    $socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

    $dsn = is_readable($socket)
      ? "mysql:unix_socket={$socket};dbname={$dbname};charset=utf8mb4"
      : "mysql:host=127.0.0.1;port=8889;dbname={$dbname};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
  }
  return $pdo;
}
