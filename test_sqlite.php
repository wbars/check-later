<?php

// Load environment variables
require_once __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Test SQLite connection and CRUD operations
try {
    echo "Testing SQLite connection and CRUD operations...\n";
    
    // Initialize database if it doesn't exist
    if (!file_exists($_ENV['DB_SQLITE_PATH'])) {
        echo "Initializing SQLite database...\n";
        require_once __DIR__ . '/database/init_sqlite_db.php';
    }
    
    // Get database instance
    $db = CheckLaterBot\Database::getInstance();
    echo "Database connection successful.\n";
    
    // Test category retrieval
    echo "\nTesting category retrieval:\n";
    $categories = CheckLaterBot\Database::getCategories();
    foreach ($categories as $category) {
        echo "- {$category['name']}: {$category['description']}\n";
    }
    
    // Test adding an entry
    echo "\nTesting entry creation:\n";
    $content = "Test entry created at " . date('Y-m-d H:i:s');
    $category = "other";
    $id = CheckLaterBot\Database::addEntry($content, $category);
    echo "Created entry with ID: $id\n";
    
    // Test updating an entry category
    echo "\nTesting entry category update:\n";
    $newCategory = "book";
    $result = CheckLaterBot\Database::updateEntryCategory($id, $newCategory);
    echo "Updated entry category: " . ($result ? "Success" : "Failed") . "\n";
    
    // Test getting random entries
    echo "\nTesting random entry retrieval:\n";
    $entries = CheckLaterBot\Database::getRandomEntriesByCategory($newCategory, 1);
    if (count($entries) > 0) {
        foreach ($entries as $entry) {
            echo "- ID: {$entry['id']}, Content: {$entry['content']}, Category: {$entry['category']}\n";
        }
    } else {
        echo "No entries found in category: $newCategory\n";
    }
    
    // Test marking entry as obsolete
    echo "\nTesting marking entry as obsolete:\n";
    $result = CheckLaterBot\Database::markEntryObsolete($id);
    echo "Marked entry as obsolete: " . ($result ? "Success" : "Failed") . "\n";
    
    echo "\nAll tests completed successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}