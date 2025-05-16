<?php

namespace CheckLaterBot\Tests\Integration;

use CheckLaterBot\Database;
use PHPUnit\Framework\TestCase;
use PDO;

class DatabaseIntegrationTest extends TestCase
{
    private static PDO $db;
    
    public static function setUpBeforeClass(): void
    {
        // Set up SQLite in-memory database for testing
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_SQLITE_PATH'] = ':memory:';
        
        // Get database instance
        self::$db = Database::getInstance();
        
        // Initialize database schema
        self::initDatabase();
    }
    
    protected function setUp(): void
    {
        // Clear existing data before each test
        self::$db->exec('DELETE FROM entries');
        
        // Reset auto-increment
        self::$db->exec('DELETE FROM sqlite_sequence WHERE name = "entries"');
    }
    
    public function testSaveAndRetrieveEntry(): void
    {
        $userId = 123456;
        $content = 'https://www.youtube.com/watch?v=testVideo';
        $category = 'youtube';
        
        // Save entry
        $result = Database::saveEntry($userId, $content, $category);
        $this->assertTrue($result);
        
        // Retrieve entry
        $stmt = self::$db->prepare('SELECT * FROM entries WHERE user_id = ? AND content = ?');
        $stmt->execute([$userId, $content]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($entry);
        $this->assertEquals($userId, $entry['user_id']);
        $this->assertEquals($content, $entry['content']);
        $this->assertEquals($category, $entry['category']);
        $this->assertEquals(0, $entry['obsolete']);
    }
    
    public function testGetRandomEntryWithMultipleEntries(): void
    {
        $userId = 123456;
        
        // Add multiple entries
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=video1', 'youtube');
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=video2', 'youtube');
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=video3', 'youtube');
        Database::saveEntry($userId, 'Book: The Great Gatsby', 'book');
        
        // Get random YouTube entry
        $youtubeEntry = Database::getRandomEntry($userId, 'youtube');
        
        $this->assertIsArray($youtubeEntry);
        $this->assertEquals($userId, $youtubeEntry['user_id']);
        $this->assertEquals('youtube', $youtubeEntry['category']);
        
        // Get random book entry
        $bookEntry = Database::getRandomEntry($userId, 'book');
        
        $this->assertIsArray($bookEntry);
        $this->assertEquals($userId, $bookEntry['user_id']);
        $this->assertEquals('book', $bookEntry['category']);
        $this->assertEquals('Book: The Great Gatsby', $bookEntry['content']);
    }
    
    public function testMarkEntryAsObsoleteAndExcludeFromRandom(): void
    {
        $userId = 123456;
        
        // Add entries
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=obsoleteVideo', 'youtube');
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=activeVideo', 'youtube');
        
        // Get the entry ID for the obsolete video
        $stmt = self::$db->prepare('SELECT id FROM entries WHERE content = ?');
        $stmt->execute(['https://www.youtube.com/watch?v=obsoleteVideo']);
        $entryId = $stmt->fetchColumn();
        
        // Mark as obsolete
        $result = Database::markEntryAsObsolete($entryId);
        $this->assertTrue($result);
        
        // Get random entry - should only return the active video
        $randomEntry = Database::getRandomEntry($userId, 'youtube');
        
        $this->assertIsArray($randomEntry);
        $this->assertEquals('https://www.youtube.com/watch?v=activeVideo', $randomEntry['content']);
    }
    
    public function testUpdateEntryCategory(): void
    {
        $userId = 123456;
        
        // Add entry
        Database::saveEntry($userId, 'Content to recategorize', 'other');
        
        // Get the entry ID
        $stmt = self::$db->prepare('SELECT id FROM entries WHERE content = ?');
        $stmt->execute(['Content to recategorize']);
        $entryId = $stmt->fetchColumn();
        
        // Update category
        $result = Database::updateEntryCategory($entryId, 'book');
        $this->assertTrue($result);
        
        // Verify category was updated
        $stmt = self::$db->prepare('SELECT category FROM entries WHERE id = ?');
        $stmt->execute([$entryId]);
        $category = $stmt->fetchColumn();
        
        $this->assertEquals('book', $category);
        
        // Get random entry from book category - should return our recategorized entry
        $randomEntry = Database::getRandomEntry($userId, 'book');
        
        $this->assertIsArray($randomEntry);
        $this->assertEquals('Content to recategorize', $randomEntry['content']);
        $this->assertEquals('book', $randomEntry['category']);
    }
    
    private static function initDatabase(): void
    {
        // Create tables for testing
        self::$db->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL UNIQUE
            )
        ');
        
        self::$db->exec('
            CREATE TABLE IF NOT EXISTS entries (
                id INTEGER PRIMARY KEY,
                user_id INTEGER NOT NULL,
                content TEXT NOT NULL,
                category TEXT NOT NULL,
                obsolete INTEGER DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ');
        
        // Insert default categories
        $categories = ['youtube', 'book', 'movie', 'other'];
        $stmt = self::$db->prepare('INSERT OR IGNORE INTO categories (name) VALUES (?)');
        
        foreach ($categories as $category) {
            $stmt->execute([$category]);
        }
    }
}