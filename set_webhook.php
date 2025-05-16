<?php

/**
 * Set webhook script for the Check Later Bot
 * This script sets the webhook URL for the bot
 */

// Load configuration
require_once __DIR__ . '/config.php';

use CheckLaterBot\Bot;
use CheckLaterBot\Logger;
use Longman\TelegramBot\Exception\TelegramException;

try {
    // Initialize the bot with logger
    $logger = Logger::getInstance();
    $bot = new Bot(BOT_API_TOKEN, BOT_USERNAME, $logger);
    
    // Set webhook
    $result = $bot->setWebhook(WEBHOOK_URL);
    
    if ($result) {
        echo "Webhook set successfully to: " . WEBHOOK_URL;
        $logger->info('Webhook set successfully', ['webhook_url' => WEBHOOK_URL]);
    } else {
        echo "Failed to set webhook.";
        $logger->error('Failed to set webhook', ['webhook_url' => WEBHOOK_URL]);
    }
} catch (TelegramException $e) {
    // Log telegram errors
    $logger = Logger::getInstance();
    $logger->error('Telegram Exception: ' . $e->getMessage(), [
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString()
    ]);
    echo "Error: " . $e->getMessage();
} catch (Exception $e) {
    // Log general errors
    $logger = Logger::getInstance();
    $logger->error('Error: ' . $e->getMessage(), [
        'exception' => get_class($e),
        'trace' => $e->getTraceAsString()
    ]);
    echo "Error: " . $e->getMessage();
}