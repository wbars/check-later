<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load configuration
$config = require __DIR__ . '/config.php';

function createDatabase(array $config): array {
    try {
        // Create data directory if it doesn't exist
        $dbDir = dirname($config['database']['path']);
        if (!is_dir($dbDir)) {
            if (!mkdir($dbDir, 0777, true)) {
                return [
                    'success' => false,
                    'message' => "Could not create database directory at {$dbDir}"
                ];
            }
        }
        
        // Connect to SQLite database
        $pdo = new PDO("sqlite:{$config['database']['path']}");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Enable foreign keys
        $pdo->exec('PRAGMA foreign_keys = ON');
        
        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/schema.sql');
        $pdo->exec($schema);
        
        return [
            'success' => true,
            'message' => "Database created successfully at {$config['database']['path']}"
        ];
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => "Database error: " . $e->getMessage()
        ];
    }
}

// Main execution
echo "Starting SQLite database setup...\n\n";

// Create database and tables
$result = createDatabase($config);
if (!$result['success']) {
    echo "Database setup failed: {$result['message']}\n";
    exit(1);
}

echo "{$result['message']}\n";
echo "Database setup completed successfully!\n"; 