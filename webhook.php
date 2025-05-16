<?php

/**
 * Webhook entry point for the Check Later Bot
 * This file handles incoming webhook requests from Telegram
 */

// Load configuration
require_once __DIR__ . '/config.php';

use CheckLaterBot\Bot;
use CheckLaterBot\Logger;
use CheckLaterBot\MessageHandler;
use Longman\TelegramBot\Exception\TelegramException;

try {
    // Initialize the bot with logger
    $logger = Logger::getInstance();
    $bot = new Bot(BOT_API_TOKEN, BOT_USERNAME, $logger);
    
    // Handle the webhook request
    $bot->handleWebhook();
} catch (TelegramException $e) {
    // Log telegram errors
    $logger = Logger::getInstance();
    $logger->error('Telegram Exception: ' . $e->getMessage(), [
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Exception $e) {
    // Log general errors
    $logger = Logger::getInstance();
    $logger->error('Error: ' . $e->getMessage(), [
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString()
    ]);
}