<?php

/**
 * Script to set the webhook for the Check Later Bot
 * Run this script once after deploying to register the webhook with Telegram
 * 
 * This script automatically detects and uses the ngrok tunnel URL if ngrok is running.
 * No need to manually set WEBHOOK_URL in the .env file.
 */

// Load configuration
require_once __DIR__ . '/config.php';

use CheckLaterBot\Bot;
use CheckLaterBot\NgrokService;
use Longman\TelegramBot\Exception\TelegramException;

try {
    // Initialize the bot
    $bot = new Bot(BOT_API_TOKEN, BOT_USERNAME);
    
    // Get the webhook URL from ngrok
    $ngrokService = new NgrokService();
    $webhookUrl = $ngrokService->getPublicUrl('/webhook.php');
    
    // Set the webhook
    $result = $bot->setWebhook($webhookUrl);
    
    if ($result) {
        echo "✅ Webhook set successfully to: " . $webhookUrl . PHP_EOL;
    } else {
        echo "❌ Failed to set webhook." . PHP_EOL;
    }
} catch (TelegramException $e) {
    // Log telegram errors
    echo "❌ Telegram Exception: " . $e->getMessage() . PHP_EOL;
} catch (Exception $e) {
    // Log general errors
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Make sure ngrok is running with 'ngrok http 8080' (or your preferred port)" . PHP_EOL;
}