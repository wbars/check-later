<?php

require_once __DIR__ . '/vendor/autoload.php';

use CheckLater\TelegramBot;

// Load configuration
$config = require __DIR__ . '/config.php';

// Get the raw POST data
$update = json_decode(file_get_contents('php://input'), true);

if (!$update) {
    http_response_code(400);
    exit('Invalid request');
}

try {
    $bot = new TelegramBot($config);
    $bot->handleUpdate($update);
    http_response_code(200);
    echo 'OK';
} catch (\Exception $e) {
    error_log("Error in webhook: " . $e->getMessage(), 3, $config['log_file']);
    http_response_code(500);
    echo 'Internal Server Error';
} 