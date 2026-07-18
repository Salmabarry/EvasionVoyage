<?php
/**
 * Connexion PDO (MySQL 8 via MAMP, environnement local).
 */
function db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $dbname = 'evasionvoyage';
    $user = 'root';
    $socket = '/Applications/MAMP/tmp/mysql/mysql.sock';

    if (is_readable($socket)) {
      // macOS + MAMP (environnement d'origine du projet)
      $dsn = "mysql:unix_socket={$socket};dbname={$dbname};charset=utf8mb4";
      $pass = 'root';
    } else {
      // Windows + XAMPP (MySQL standard : port 3306, root sans mot de passe)
      $dsn = "mysql:host=127.0.0.1;port=3306;dbname={$dbname};charset=utf8mb4";
      $pass = '';
    }

    $pdo = new PDO($dsn, $user, $pass, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
  }
  return $pdo;
}
