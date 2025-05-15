<?php

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Check if SQLite driver is set
if ($_ENV['DB_DRIVER'] !== 'sqlite') {
    echo "Error: DB_DRIVER is not set to 'sqlite' in .env file.\n";
    exit(1);
}

$sqlitePath = $_ENV['DB_SQLITE_PATH'];
$migrationsFile = __DIR__ . '/migrations_sqlite.sql';

// Ensure directory exists
$directory = dirname($sqlitePath);
if (!is_dir($directory)) {
    mkdir($directory, 0755, true);
}

try {
    // Create/connect to SQLite database
    $pdo = new PDO("sqlite:{$sqlitePath}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON;');
    
    // Read and execute migrations
    $sql = file_get_contents($migrationsFile);
    $pdo->exec($sql);
    
    echo "SQLite database initialized successfully at {$sqlitePath}\n";
} catch (PDOException $e) {
    echo "Error initializing SQLite database: " . $e->getMessage() . "\n";
    exit(1);
}