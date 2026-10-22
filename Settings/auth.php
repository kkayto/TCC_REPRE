<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';

    if (!is_string($token) || empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(419);
        exit('Sessão expirada. Volte e tente novamente.');
    }
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_active_account(): void {
    require_login();
    $status = $_SESSION['user_status'] ?? '';
    
    if ($status !== 'ativo') {
        http_response_code(403);
        exit('Sua conta não está ativa.');
    }
}

function require_role(array $roles): void
{
    require_active_account();

    $tipo = $_SESSION['user_tipo'] ?? '';

    if (!in_array($tipo, $roles, true)) {
        http_response_code(403);
        exit('Você não possui permissão para acessar esta página.');
    }
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}
