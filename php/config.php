<?php
declare(strict_types=1);

/**
 * DigitalPay database bootstrap.
 * Configure with environment variables; never commit production credentials.
 */

function db(): PDO
{
    static $connection = null;
    if ($connection instanceof PDO) {
        return $connection;
    }

    $host = getenv('DIGITALPAY_DB_HOST') ?: '127.0.0.1';
    $port = getenv('DIGITALPAY_DB_PORT') ?: '3306';
    $name = getenv('DIGITALPAY_DB_NAME') ?: 'digitalpay';
    $user = getenv('DIGITALPAY_DB_USER') ?: 'root';
    $password = getenv('DIGITALPAY_DB_PASSWORD') ?: '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $connection = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $connection;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_string(string $key): string
{
    return trim((string)($_POST[$key] ?? ''));
}

function wants_json(): bool
{
    return str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') ||
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $submitted = (string)($_POST['csrf_token'] ?? '');
    if (!$submitted || !hash_equals((string)($_SESSION['csrf_token'] ?? ''), $submitted)) {
        throw new RuntimeException('The form session expired. Refresh the page and try again.');
    }
}

function render_message_page(string $title, string $message, bool $success = false, string $back = '../index.html'): never
{
    http_response_code($success ? 200 : 422);
    $safeTitle = e($title);
    $safeMessage = e($message);
    $safeBack = e($back);
    $statusClass = $success ? 'alert-success' : 'alert-error';
    echo <<<HTML
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{$safeTitle} — DigitalPay</title><link rel="stylesheet" href="../css/style.css"><link rel="stylesheet" href="../css/responsive.css"></head><body><main class="section"><div class="container"><div class="form-card"><a class="brand" href="../index.html"><img src="../images/logo-mark.svg" alt=""><span>Digital<span>Pay</span></span></a><div class="alert {$statusClass}" style="margin-top:1.5rem"><strong>{$safeTitle}</strong><span>{$safeMessage}</span></div><a class="btn btn-primary" href="{$safeBack}" style="margin-top:1rem">Return to DigitalPay</a></div></div></main></body></html>
HTML;
    exit;
}
