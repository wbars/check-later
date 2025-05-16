<?php

namespace CheckLaterBot;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Exception\TelegramException;
use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Bot
{
    private Telegram $telegram;
    private LoggerInterface $logger;

    /**
     * Initialize the bot
     *
     * @param string $apiToken Bot API token
     * @param string $botUsername Bot username
     * @param LoggerInterface|null $logger Optional logger instance
     * @throws TelegramException
     */
    public function __construct(string $apiToken, string $botUsername, ?LoggerInterface $logger = null)
    {
        $this->telegram = new Telegram($apiToken, $botUsername);
        $this->logger = $logger ?? Logger::getInstance();

        // Set up database connection for the Telegram Bot library
        $this->telegram->enableExternalMySql(Database::getInstance());
    }

    /**
     * Set webhook for the bot
     *
     * @param string $webhookUrl The URL for the webhook
     * @return bool Success status
     */
    public function setWebhook(string $webhookUrl): bool
    {
        try {
            $result = $this->telegram->setWebhook($webhookUrl);
            return $result->isOk();
        } catch (TelegramException $e) {
            $this->logger->error('Error setting webhook: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'webhook_url' => $webhookUrl
            ]);
            return false;
        }
    }

    /**
     * Handle incoming webhook request
     *
     * @return bool Success status
     */
    public function handleWebhook(): bool
    {
        try {
            $this->telegram->handle();
            return true;
        } catch (TelegramException $e) {
            $this->logger->error('Error handling webhook: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Process an incoming message
     *
     * @param int $chatId The chat ID
     * @param string $messageText The message text
     * @return bool Success status
     */
    public function processMessage(int $chatId, string $messageText): bool
    {
        try {
            // Classify the message
            $category = Classifier::classify($messageText);

            // Store in database
            $entryId = Database::addEntry($messageText, $category);

            // Send confirmation with category
            $this->sendConfirmation($chatId, $messageText, $category, $entryId);

            return true;
        } catch (PDOException $e) {
            $this->logger->error('Database error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId,
                'message' => substr($messageText, 0, 100) . (strlen($messageText) > 100 ? '...' : '')
            ]);
            $this->sendErrorMessage($chatId, 'Database error occurred. Please try again later.');
            return false;
        } catch (\Exception $e) {
            $this->logger->error('Error processing message: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId,
                'message' => substr($messageText, 0, 100) . (strlen($messageText) > 100 ? '...' : '')
            ]);
            $this->sendErrorMessage($chatId, 'An error occurred. Please try again.');
            return false;
        }
    }

    /**
     * Send confirmation message with category remap options
     *
     * @param int $chatId The chat ID
     * @param string $content The content that was saved
     * @param string $category The assigned category
     * @param int $entryId The database ID of the entry
     * @return bool Success status
     */
    private function sendConfirmation(int $chatId, string $content, string $category, int $entryId): bool
    {
        try {
            // Get all categories
            $categories = Database::getCategories();

            // Add a button for each category
            $buttons = [];
            $row = [];
            $count = 0;

            foreach ($categories as $cat) {
                // Skip the current category
                if ($cat['name'] === $category) {
                    continue;
                }

                $row[] = ['text' => ucfirst($cat['name']), 'callback_data' => "remap_{$entryId}_{$cat['name']}"];
                $count++;

                // Two buttons per row
                if ($count % 2 === 0) {
                    $buttons[] = $row;
                    $row = [];
                }
            }

            // Add any remaining buttons
            if (!empty($row)) {
                $buttons[] = $row;
            }

            // Create inline keyboard with buttons
            $inlineKeyboard = new InlineKeyboard($buttons);

            // Truncate content if too long
            $displayContent = (strlen($content) > 50) ? substr($content, 0, 47) . '...' : $content;

            // Send message with inline keyboard
            $result = Request::sendMessage([
                'chat_id' => $chatId,
                'text' => "✅ Saved to category: *" . ucfirst($category) . "*\n\n" .
                         "Content: `" . $displayContent . "`\n\n" .
                         "You can remap to a different category if needed:",
                'parse_mode' => 'Markdown',
                'reply_markup' => $inlineKeyboard,
            ]);

            if (!$result->isOk()) {
                $this->logger->warning('Failed to send confirmation message', [
                    'chat_id' => $chatId,
                    'error_code' => $result->getErrorCode(),
                    'error_description' => $result->getDescription()
                ]);
            }

            return $result->isOk();
        } catch (\Exception $e) {
            $this->logger->error('Error sending confirmation: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId,
                'entry_id' => $entryId
            ]);
            return false;
        }
    }

    /**
     * Handle category remapping
     *
     * @param int $chatId The chat ID
     * @param int $entryId The entry ID
     * @param string $newCategory The new category
     * @return bool Success status
     */
    public function remapCategory(int $chatId, int $entryId, string $newCategory): bool
    {
        try {
            // Update the category in the database
            if (Database::updateEntryCategory($entryId, $newCategory)) {
                // Send confirmation
                Request::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Entry remapped to *" . ucfirst($newCategory) . "*",
                    'parse_mode' => 'Markdown',
                ]);
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->error('Database error during remapping: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId,
                'entry_id' => $entryId,
                'new_category' => $newCategory
            ]);
            $this->sendErrorMessage($chatId, 'Database error occurred. Please try again later.');
            return false;
        }
    }

    /**
     * Send the main menu with category buttons
     *
     * @param int $chatId The chat ID
     * @return bool Success status
     */
    public function sendMainMenu(int $chatId): bool
    {
        try {
            // Get all categories
            $categories = Database::getCategories();

            // Create keyboard buttons
            $buttons = [];
            foreach ($categories as $category) {
                $buttons[] = [ucfirst($category['name'])];
            }

            $keyboard = new Keyboard(
                $buttons,
                true, // resize_keyboard
                true, // one_time_keyboard
                true  // selective
            );

            // Send message with keyboard
            $result = Request::sendMessage([
                'chat_id' => $chatId,
                'text' => "Welcome to Check Later Bot!\n\n" .
                         "Send me any link or text, and I'll save it for you.\n" .
                         "Select a category to get random suggestions:",
                'reply_markup' => $keyboard,
            ]);

            return $result->isOk();
        } catch (PDOException $e) {
            $this->logger->error('Database error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId
            ]);
            $this->sendErrorMessage($chatId, 'Database error occurred. Please try again later.');
            return false;
        }
    }

    /**
     * Send random suggestions from a category
     *
     * @param int $chatId The chat ID
     * @param string $category The category to get suggestions from
     * @return bool Success status
     */
    public function sendSuggestions(int $chatId, string $category): bool
    {
        try {
            // Get random entries
            $entries = Database::getRandomEntriesByCategory($category);

            if (empty($entries)) {
                Request::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "No entries found in category *" . ucfirst($category) . "*.\n" .
                             "Send me some content to save it!",
                    'parse_mode' => 'Markdown',
                ]);
                return true;
            }

            // Send each entry with an "Mark as obsolete" button
            foreach ($entries as $entry) {
                $inlineKeyboard = new InlineKeyboard([
                    [
                        ['text' => 'Mark as obsolete', 'callback_data' => "obsolete_{$entry['id']}"],
                    ],
                ]);

                Request::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "*Suggestion from " . ucfirst($category) . "*:\n\n" .
                             $entry['content'] . "\n\n" .
                             "Added: " . date('Y-m-d', strtotime($entry['date_added'])),
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $inlineKeyboard,
                ]);
            }

            return true;
        } catch (PDOException $e) {
            $this->logger->error('Database error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId,
                'category' => $category
            ]);
            $this->sendErrorMessage($chatId, 'Database error occurred. Please try again later.');
            return false;
        }
    }

    /**
     * Mark an entry as obsolete
     *
     * @param int $chatId The chat ID
     * @param int $entryId The entry ID
     * @return bool Success status
     */
    public function markObsolete(int $chatId, int $entryId): bool
    {
        try {
            // Update the entry in the database
            if (Database::markEntryObsolete($entryId)) {
                // Send confirmation
                Request::sendMessage([
                    'chat_id' => $chatId,
                    'text' => "✅ Entry marked as obsolete and won't be suggested again.",
                ]);
                return true;
            }

            return false;
        } catch (PDOException $e) {
            $this->logger->error('Database error marking obsolete: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId,
                'entry_id' => $entryId
            ]);
            $this->sendErrorMessage($chatId, 'Database error occurred. Please try again later.');
            return false;
        }
    }

    /**
     * Send an error message to the user
     *
     * @param int $chatId The chat ID
     * @param string $message The error message
     * @return bool Success status
     */
    private function sendErrorMessage(int $chatId, string $message): bool
    {
        try {
            $result = Request::sendMessage([
                'chat_id' => $chatId,
                'text' => "❌ " . $message,
            ]);

            return $result->isOk();
        } catch (\Exception $e) {
            $this->logger->error('Error sending error message: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'chat_id' => $chatId
            ]);
            return false;
        }
    }
}
