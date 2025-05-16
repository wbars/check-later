<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

// Load configuration
$config = require __DIR__ . '/config.php';

function validateConfig(array $config): array {
    $errors = [];
    
    // Check bot token
    if (empty($config['telegram_bot_token']) || $config['telegram_bot_token'] === 'your_bot_token_here') {
        $errors[] = "Telegram bot token is not configured";
    }
    
    // Check SQLite database configuration
    if (empty($config['database']['path'])) {
        $errors[] = "Database path is not configured";
    }
    
    // Check webhook URL
    if (empty($config['webhook_url']) || $config['webhook_url'] === 'https://your-domain.com/webhook.php') {
        $errors[] = "Webhook URL is not configured";
    }
    
    // Validate webhook URL format
    if (!filter_var($config['webhook_url'], FILTER_VALIDATE_URL)) {
        $errors[] = "Webhook URL is not a valid URL";
    }
    
    // Check if webhook URL uses HTTPS
    if (parse_url($config['webhook_url'], PHP_URL_SCHEME) !== 'https') {
        $errors[] = "Webhook URL must use HTTPS";
    }
    
    return $errors;
}

function testDatabaseConnection(array $config): ?string {
    try {
        $dbDir = dirname($config['database']['path']);
        if (!is_dir($dbDir)) {
            if (!mkdir($dbDir, 0777, true)) {
                return "Could not create database directory at {$dbDir}";
            }
        }
        
        $pdo = new PDO("sqlite:{$config['database']['path']}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return null;
    } catch (PDOException $e) {
        return "Database connection failed: " . $e->getMessage();
    }
}

function setupWebhook(string $token, string $webhookUrl): array {
    $client = new Client(['base_uri' => "https://api.telegram.org/bot{$token}/"]);
    
    try {
        // First, delete any existing webhook
        $client->post('deleteWebhook');
        
        // Set new webhook
        $response = $client->post('setWebhook', [
            'form_params' => [
                'url' => $webhookUrl
            ]
        ]);
        
        $result = json_decode($response->getBody()->getContents(), true);
        
        if (!$result['ok']) {
            return [
                'success' => false,
                'message' => "Failed to set webhook: " . ($result['description'] ?? 'Unknown error')
            ];
        }
        
        // Verify webhook was set correctly
        $response = $client->get('getWebhookInfo');
        $webhookInfo = json_decode($response->getBody()->getContents(), true);
        
        if (!$webhookInfo['ok'] || $webhookInfo['result']['url'] !== $webhookUrl) {
            return [
                'success' => false,
                'message' => "Webhook verification failed"
            ];
        }
        
        return [
            'success' => true,
            'message' => "Webhook successfully set to: {$webhookUrl}"
        ];
    } catch (GuzzleException $e) {
        return [
            'success' => false,
            'message' => "Error setting webhook: " . $e->getMessage()
        ];
    }
}

// Main execution
echo "Starting Check Later Bot setup...\n\n";

// Validate configuration
echo "Validating configuration...\n";
$errors = validateConfig($config);
if (!empty($errors)) {
    echo "Configuration errors found:\n";
    foreach ($errors as $error) {
        echo "- {$error}\n";
    }
    exit(1);
}
echo "Configuration validation passed.\n\n";

// Test database connection
echo "Testing database connection...\n";
$dbError = testDatabaseConnection($config);
if ($dbError) {
    echo "Database error: {$dbError}\n";
    exit(1);
}
echo "Database connection successful.\n\n";

// Setup webhook
echo "Setting up webhook...\n";
$result = setupWebhook($config['telegram_bot_token'], $config['webhook_url']);
if (!$result['success']) {
    echo "Webhook setup failed: {$result['message']}\n";
    exit(1);
}
echo "{$result['message']}\n\n";

// Create log directory if it doesn't exist
$logDir = dirname($config['log_file']);
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0777, true)) {
        echo "Warning: Could not create log directory at {$logDir}\n";
    } else {
        echo "Created log directory at {$logDir}\n";
    }
}

echo "Setup completed successfully!\n";
echo "You can now start using your bot. Send /start to begin.\n"; 