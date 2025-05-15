<?php

/**
 * Script to set the webhook for the Check Later Bot
 * Run this script once after deploying to register the webhook with Telegram
 */

// Load configuration
require_once __DIR__ . '/config.php';

use CheckLaterBot\Bot;
use Longman\TelegramBot\Exception\TelegramException;

try {
    // Initialize the bot
    $bot = new Bot(BOT_API_TOKEN, BOT_USERNAME);
    
    // Set the webhook
    $result = $bot->setWebhook(WEBHOOK_URL);
    
    if ($result) {
        echo "✅ Webhook set successfully to: " . WEBHOOK_URL . PHP_EOL;
    } else {
        echo "❌ Failed to set webhook." . PHP_EOL;
    }
} catch (TelegramException $e) {
    // Log telegram errors
    echo "❌ Telegram Exception: " . $e->getMessage() . PHP_EOL;
} catch (Exception $e) {
    // Log general errors
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
}