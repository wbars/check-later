<?php

namespace CheckLater;

use PDO;
use PDOException;
use GuzzleHttp\Client;

class TelegramBot {
    private PDO $db;
    private Client $client;
    private array $config;
    private string $token;
    
    public function __construct(array $config) {
        $this->config = $config;
        $this->token = $config['telegram_bot_token'];
        $this->client = new Client(['base_uri' => "https://api.telegram.org/bot{$this->token}/"]);
        $this->initDatabase();
    }
    
    private function initDatabase(): void {
        try {
            $this->db = new PDO(
                "mysql:host={$this->config['database']['host']};dbname={$this->config['database']['name']};charset=utf8mb4",
                $this->config['database']['user'],
                $this->config['database']['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $this->logError("Database connection failed: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function handleUpdate(array $update): void {
        try {
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
            }
        } catch (\Exception $e) {
            $this->logError("Error handling update: " . $e->getMessage());
        }
    }
    
    private function handleMessage(array $message): void {
        $chatId = $message['chat']['id'];
        $userId = $message['from']['id'];
        $text = $message['text'] ?? '';
        
        // Handle /start command
        if ($text === '/start') {
            $this->sendWelcomeMessage($chatId);
            return;
        }
        
        // Handle /menu command
        if ($text === '/menu') {
            $this->sendMenu($chatId);
            return;
        }
        
        // Handle URL
        if (filter_var($text, FILTER_VALIDATE_URL)) {
            $category = $this->classifyUrl($text);
            $this->saveLink($text, $category, $userId);
            $this->sendMessage($chatId, "✅ Link saved to category: {$this->config['categories'][$category]['name']}");
            return;
        }
        
        $this->sendMessage($chatId, "Please send me a valid URL to save, or use /menu to view your saved links.");
    }
    
    private function handleCallbackQuery(array $callbackQuery): void {
        $chatId = $callbackQuery['message']['chat']['id'];
        $data = $callbackQuery['data'];
        
        if (strpos($data, 'category_') === 0) {
            $category = substr($data, 9);
            $this->sendRandomLinks($chatId, $category);
        } elseif (strpos($data, 'obsolete_') === 0) {
            $linkId = (int)substr($data, 9);
            $this->markLinkAsObsolete($linkId);
            $this->sendMessage($chatId, "✅ Link marked as obsolete and won't be suggested anymore.");
        }
        
        // Answer callback query to remove loading state
        $this->client->post('answerCallbackQuery', [
            'json' => ['callback_query_id' => $callbackQuery['id']]
        ]);
    }
    
    private function classifyUrl(string $url): string {
        $url = strtolower($url);
        foreach ($this->config['categories'] as $category => $data) {
            foreach ($data['patterns'] as $pattern) {
                if (strpos($url, $pattern) !== false) {
                    return $category;
                }
            }
        }
        return 'other';
    }
    
    private function saveLink(string $url, string $category, int $userId): void {
        $stmt = $this->db->prepare("INSERT INTO links (url, category, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$url, $category, $userId]);
    }
    
    private function sendRandomLinks(int $chatId, string $category): void {
        $stmt = $this->db->prepare("
            SELECT id, url FROM links 
            WHERE category = ? AND is_obsolete = FALSE 
            ORDER BY RAND() LIMIT 2
        ");
        $stmt->execute([$category]);
        $links = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($links)) {
            $this->sendMessage($chatId, "No links found in this category.");
            return;
        }
        
        $message = "Here are 2 random links from {$this->config['categories'][$category]['name']}:\n\n";
        foreach ($links as $link) {
            $message .= "🔗 {$link['url']}\n";
            $this->sendMessage($chatId, $message, [
                'reply_markup' => [
                    'inline_keyboard' => [[
                        ['text' => 'Mark as obsolete', 'callback_data' => "obsolete_{$link['id']}"]
                    ]]
                ]
            ]);
            $message = "";
        }
    }
    
    private function markLinkAsObsolete(int $linkId): void {
        $stmt = $this->db->prepare("UPDATE links SET is_obsolete = TRUE WHERE id = ?");
        $stmt->execute([$linkId]);
    }
    
    private function sendWelcomeMessage(int $chatId): void {
        $message = "👋 Welcome to Check Later Bot!\n\n";
        $message .= "Send me any URL and I'll save it in an appropriate category.\n";
        $message .= "Use /menu to view your saved links by category.";
        $this->sendMessage($chatId, $message);
    }
    
    private function sendMenu(int $chatId): void {
        $keyboard = [];
        foreach ($this->config['categories'] as $category => $data) {
            $keyboard[] = [['text' => $data['name'], 'callback_data' => "category_{$category}"]];
        }
        
        $this->sendMessage($chatId, "Choose a category to view random links:", [
            'reply_markup' => ['inline_keyboard' => $keyboard]
        ]);
    }
    
    private function sendMessage(int $chatId, string $text, array $options = []): void {
        try {
            $this->client->post('sendMessage', [
                'json' => array_merge([
                    'chat_id' => $chatId,
                    'text' => $text,
                    'parse_mode' => 'HTML'
                ], $options)
            ]);
        } catch (\Exception $e) {
            $this->logError("Error sending message: " . $e->getMessage());
        }
    }
    
    private function logError(string $message): void {
        $logFile = $this->config['log_file'];
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, $logFile);
    }
} 