<?php

namespace CheckLaterBot;

use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;

class MessageHandler
{
    private Bot $bot;
    private array $categories;

    /**
     * MessageHandler constructor
     *
     * @param Bot $bot The bot instance
     */
    public function __construct(Bot $bot)
    {
        $this->bot = $bot;

        try {
            // Get available categories
            $this->categories = Database::getCategories();
        } catch (\PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error initializing MessageHandler: ' . $e->getMessage(), [
                'exception' => get_class($e)
            ]);
            $this->categories = [];
        }
    }

    /**
     * Process an incoming update
     *
     * @param Update $update The update to process
     * @return bool Success status
     */    public function processUpdate(Update $update): bool
    {
        try {
            // Handle callback queries (button presses)
            if ($update->getCallbackQuery()) {
                return $this->processCallbackQuery($update);
            }
            
            // Handle text messages
            if ($update->getMessage() && $update->getMessage()->getText()) {
                return $this->processMessage($update);
            }
            
            $logger = Logger::getInstance();
            $logger->info('Received update with no callback query or text message', [
                'update_id' => $update->getUpdateId()
            ]);
            return false;
        } catch (TelegramException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error processing update: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'update_id' => $update->getUpdateId()
            ]);
            return false;
        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error('Unexpected error processing update: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'update_id' => $update->getUpdateId()
            ]);
            return false;
        }
    }

    /**
     * Process a text message
     *
     * @param Update $update The update containing the message
     * @return bool Success status
     */
    private function processMessage(Update $update): bool
    {
        $message = $update->getMessage();
        $chatId = $message->getChat()->getId();
        $text = $message->getText();

        // Handle commands
        if ($text[0] === '/') {
            return $this->processCommand($chatId, $text);
        }

        // Check if the message is a category selection
        foreach ($this->categories as $category) {
            if (
                strtolower($text) === strtolower($category['name']) ||
                strtolower($text) === strtolower(ucfirst($category['name']))
            ) {
                return $this->bot->sendSuggestions($chatId, $category['name']);
            }
        }

        // Otherwise, treat as content to save
        return $this->bot->processMessage($chatId, $text);
    }

    /**
     * Process a command
     *
     * @param int $chatId The chat ID
     * @param string $command The command text
     * @return bool Success status
     */
    private function processCommand(int $chatId, string $command): bool
    {
        $command = strtolower(trim($command));

        switch ($command) {
            case '/start':
            case '/menu':
                return $this->bot->sendMainMenu($chatId);

            default:
                // Unknown command
                return false;
        }
    }

    /**
     * Process a callback query (button press)
     *
     * @param Update $update The update containing the callback query
     * @return bool Success status
     */
    private function processCallbackQuery(Update $update): bool
    {
        $callbackQuery = $update->getCallbackQuery();
        $chatId = $callbackQuery->getMessage()->getChat()->getId();
        $data = $callbackQuery->getData();

        try {
            // Handle category remapping
            if (strpos($data, 'remap_') === 0) {
                $parts = explode('_', $data);
                if (count($parts) === 3) {
                    $entryId = (int)$parts[1];
                    $newCategory = $parts[2];
                    return $this->bot->remapCategory($chatId, $entryId, $newCategory);
                }
            }

            // Handle marking as obsolete
            if (strpos($data, 'obsolete_') === 0) {
                $parts = explode('_', $data);
                if (count($parts) === 2) {
                    $entryId = (int)$parts[1];
                    return $this->bot->markObsolete($chatId, $entryId);
                }
            }

            return false;
        } catch (\Exception $e) {
            $logger = Logger::getInstance();
            $logger->error('Error processing callback query: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId,
                'callback_data' => $data
            ]);
            return false;
        }
    }
}
