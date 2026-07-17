<?php
require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function current_user(): ?array {
  return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool {
  return current_user() !== null;
}

function is_admin(): bool {
  $user = current_user();
  return $user !== null && !empty($user['is_admin']);
}

function require_login(string $redirectTo = 'connexion.php'): void {
  if (!is_logged_in()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? null;
    header('Location: ' . $redirectTo);
    exit;
  }
}

function require_admin(string $loginRedirect = 'connexion.php', string $deniedRedirect = 'index.php'): void {
  require_login($loginRedirect);
  if (!is_admin()) {
    header('Location: ' . $deniedRedirect);
    exit;
  }
}

function login_user(array $user): void {
  $_SESSION['user'] = [
    'id' => (int) $user['id'],
    'first_name' => $user['first_name'],
    'last_name' => $user['last_name'],
    'email' => $user['email'],
    'is_admin' => (bool) ($user['is_admin'] ?? false),
  ];
}

function logout_user(): void {
  $_SESSION = [];
  session_destroy();
}

function csrf_token(): string {
  if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  return $_SESSION['csrf_token'];
}

function csrf_field(): string {
  return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function csrf_check(?string $token): bool {
  return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
