<?php

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Required environment variables
$dotenv->required(['BOT_API_TOKEN', 'BOT_USERNAME', 'WEBHOOK_URL']);
$dotenv->required(['DB_DRIVER', 'DB_SQLITE_PATH']);

// Legacy MySQL variables are only required if using MySQL
if ($_ENV['DB_DRIVER'] === 'mysql') {
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);
}

// Set error reporting
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('UTC');

// Define constants
define('BOT_API_TOKEN', $_ENV['BOT_API_TOKEN']);
define('BOT_USERNAME', $_ENV['BOT_USERNAME']);
define('WEBHOOK_URL', $_ENV['WEBHOOK_URL']);