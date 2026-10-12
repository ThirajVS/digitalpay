<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(['ok' => false, 'error' => 'Method not allowed'], 405);
}

try {
    $userId = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
    if ($userId) {
        $statement = db()->prepare('SELECT reference_number AS id, transaction_type AS type, recipient, amount, status, DATE(transaction_date) AS date FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC LIMIT 100');
        $statement->execute(['user_id' => $userId]);
    } else {
        $statement = db()->prepare('SELECT reference_number AS id, transaction_type AS type, recipient, amount, status, DATE(transaction_date) AS date FROM transactions ORDER BY transaction_date DESC LIMIT 100');
        $statement->execute();
    }
    json_response(['ok' => true, 'transactions' => $statement->fetchAll()]);
} catch (PDOException $exception) {
    error_log('DigitalPay transactions error: ' . $exception->getMessage());
    json_response(['ok' => false, 'error' => 'Transaction data is temporarily unavailable.'], 503);
}
