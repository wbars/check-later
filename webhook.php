<?php

/**
 * Webhook entry point for the Check Later Bot
 * This file handles incoming webhook requests from Telegram
 */

// Load configuration
require_once __DIR__ . '/config.php';

use CheckLaterBot\Bot;
use CheckLaterBot\MessageHandler;
use Longman\TelegramBot\Exception\TelegramException;

try {
    // Initialize the bot
    $bot = new Bot(BOT_API_TOKEN, BOT_USERNAME);
    
    // Handle the webhook request
    $bot->handleWebhook();
} catch (TelegramException $e) {
    // Log telegram errors
    error_log('Telegram Exception: ' . $e->getMessage());
} catch (Exception $e) {
    // Log general errors
    error_log('Error: ' . $e->getMessage());
}