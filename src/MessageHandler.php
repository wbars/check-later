<?php

namespace CheckLaterBot;

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;

class MessageHandler
{
    private Bot $bot;
    
    /**
     * Initialize the message handler
     *
     * @param Bot $bot The bot instance
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;
    }
    
    /**
     * Process an update from Telegram
     *
     * @param Update $update The update object
     * @return bool Success status
     */
    public function processUpdate(Update $update): bool
    {
        try {
            // Handle text messages
            if ($update->getMessage() && $update->getMessage()->getText()) {
                return $this->handleTextMessage($update);
            }
            
            // Handle callback queries (inline keyboard buttons)
            if ($update->getCallbackQuery()) {
                return $this->handleCallbackQuery($update);
            }
            
            return false;
        } catch (TelegramException $e) {
            error_log('Error processing update: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Handle text messages
     *
     * @param Update $update The update object
     * @return bool Success status
     */
    private function handleTextMessage(Update $update): bool
    {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();
        
        // Handle commands
        if ($text[0] === '/') {
            return $this->handleCommand($chatId, $text);
        }
        
        // Handle category selection from main menu
        $categories = array_map(function($category) {
            return strtolower($category['name']);
        }, Database::getCategories());
        
        $lowercaseText = strtolower($text);
        if (in_array($lowercaseText, $categories)) {
            return $this->bot->sendSuggestions($chatId, $lowercaseText);
        }
        
        // Handle regular messages (links, text)
        return $this->bot->processMessage($chatId, $text);
    }
    
    /**
     * Handle commands
     *
     * @param int $chatId The chat ID
     * @param string $command The command text
     * @return bool Success status
     */
    private function handleCommand(int $chatId, string $command): bool
    {
        $command = strtolower(trim($command));
        
        switch ($command) {
            case '/start':
                return $this->bot->sendMainMenu($chatId);
                
            case '/help':
                return $this->sendHelpMessage($chatId);
                
            default:
                return $this->sendUnknownCommandMessage($chatId);
        }
    }
    
    /**
     * Handle callback queries (inline keyboard buttons)
     *
     * @param Update $update The update object
     * @return bool Success status
     */
    private function handleCallbackQuery(Update $update): bool
    {
        $callbackQuery = $update->getCallbackQuery();
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $data = $callbackQuery->getData();
        
        // Handle category remapping
        if (strpos($data, 'remap_') === 0) {
            list(, $entryId, $newCategory) = explode('_', $data);
            return $this->bot->remapCategory($chatId, (int) $entryId, $newCategory);
        }
        
        // Handle marking as obsolete
        if (strpos($data, 'obsolete_') === 0) {
            list(, $entryId) = explode('_', $data);
            return $this->bot->markObsolete($chatId, (int) $entryId);
        }
        
        return false;
    }
    
    /**
     * Send help message
     *
     * @param int $chatId The chat ID
     * @return bool Success status
     */
    private function sendHelpMessage(int $chatId): bool
    {
        $message = "🤖 *Check Later Bot Help*\n\n" .
                  "This bot helps you save content to check later.\n\n" .
                  "*Commands:*\n" .
                  "/start - Show main menu\n" .
                  "/help - Show this help message\n\n" .
                  "*How to use:*\n" .
                  "1. Send any link or text to save it\n" .
                  "2. The bot will automatically classify it\n" .
                  "3. You can remap to a different category if needed\n" .
                  "4. Use the main menu to get random suggestions\n" .
                  "5. Mark entries as obsolete when you're done with them";
        
        $result = \Longman\TelegramBot\Request::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
        ]);
        
        return $result->isOk();
    }
    
    /**
     * Send unknown command message
     *
     * @param int $chatId The chat ID
     * @return bool Success status
     */
    private function sendUnknownCommandMessage(int $chatId): bool
    {
        $message = "Unknown command. Use /help to see available commands.";
        
        $result = \Longman\TelegramBot\Request::sendMessage([
            'chat_id' => $chatId,
            'text' => $message,
        ]);
        
        return $result->isOk();
    }
}