<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_message_page('Method not allowed', 'User deletion requires a confirmed POST request.', false, 'users.php');
}

try {
    verify_csrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        throw new InvalidArgumentException('A valid user ID is required.');
    }
    $statement = db()->prepare('DELETE FROM users WHERE id = :id');
    $statement->execute(['id' => $id]);
    header('Location: users.php?deleted=1');
    exit;
} catch (Throwable $exception) {
    error_log('DigitalPay delete error: ' . $exception->getMessage());
    render_message_page('Delete failed', 'The user could not be deleted safely.', false, 'users.php');
}
