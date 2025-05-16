<?php

namespace CheckLaterBot\Tests\Unit;

use CheckLaterBot\Database;
use PHPUnit\Framework\TestCase;
use PDO;

class DatabaseTest extends TestCase
{
    private PDO $db;
    
    protected function setUp(): void
    {
        // Set environment variables for in-memory SQLite database
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_SQLITE_PATH'] = ':memory:';
        
        // Get database instance
        $this->db = Database::getInstance();
        
        // Initialize test database schema
        $this->initTestDatabase();
    }
    
    protected function tearDown(): void
    {
        // Reset the singleton instance for the next test
        $this->setDatabaseInstanceToNull();
    }
    
    public function testGetInstance(): void
    {
        $instance = Database::getInstance();
        $this->assertInstanceOf(PDO::class, $instance);
        
        // Test singleton pattern
        $instance2 = Database::getInstance();
        $this->assertSame($instance, $instance2);
    }
    
    public function testSaveEntry(): void
    {
        $userId = 123456;
        $content = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $category = 'youtube';
        
        $result = Database::saveEntry($userId, $content, $category);
        $this->assertTrue($result);
        
        // Verify entry was saved
        $stmt = $this->db->prepare('SELECT * FROM entries WHERE user_id = ? AND content = ?');
        $stmt->execute([$userId, $content]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($entry);
        $this->assertEquals($userId, $entry['user_id']);
        $this->assertEquals($content, $entry['content']);
        $this->assertEquals($category, $entry['category']);
        $this->assertEquals(0, $entry['obsolete']);
    }
    
    public function testGetRandomEntry(): void
    {
        // Add test entries
        $userId = 123456;
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=video1', 'youtube');
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=video2', 'youtube');
        
        // Get random entry
        $entry = Database::getRandomEntry($userId, 'youtube');
        
        $this->assertIsArray($entry);
        $this->assertEquals($userId, $entry['user_id']);
        $this->assertEquals('youtube', $entry['category']);
        $this->assertEquals(0, $entry['obsolete']);
    }
    
    public function testMarkEntryAsObsolete(): void
    {
        // Add test entry
        $userId = 123456;
        Database::saveEntry($userId, 'https://www.youtube.com/watch?v=obsoleteVideo', 'youtube');
        
        // Get the entry ID
        $stmt = $this->db->prepare('SELECT id FROM entries WHERE user_id = ? AND content = ?');
        $stmt->execute([$userId, 'https://www.youtube.com/watch?v=obsoleteVideo']);
        $entryId = $stmt->fetchColumn();
        
        // Mark as obsolete
        $result = Database::markEntryAsObsolete($entryId);
        $this->assertTrue($result);
        
        // Verify entry was marked as obsolete
        $stmt = $this->db->prepare('SELECT obsolete FROM entries WHERE id = ?');
        $stmt->execute([$entryId]);
        $obsolete = $stmt->fetchColumn();
        
        $this->assertEquals(1, $obsolete);
    }
    
    public function testUpdateEntryCategory(): void
    {
        // Add test entry
        $userId = 123456;
        Database::saveEntry($userId, 'Content to recategorize', 'other');
        
        // Get the entry ID
        $stmt = $this->db->prepare('SELECT id FROM entries WHERE user_id = ? AND content = ?');
        $stmt->execute([$userId, 'Content to recategorize']);
        $entryId = $stmt->fetchColumn();
        
        // Update category
        $result = Database::updateEntryCategory($entryId, 'book');
        $this->assertTrue($result);
        
        // Verify category was updated
        $stmt = $this->db->prepare('SELECT category FROM entries WHERE id = ?');
        $stmt->execute([$entryId]);
        $category = $stmt->fetchColumn();
        
        $this->assertEquals('book', $category);
    }
    
    private function initTestDatabase(): void
    {
        // Create tables for testing
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS categories (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL UNIQUE
            )
        ');
        
        $this->db->exec('
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
        $stmt = $this->db->prepare('INSERT OR IGNORE INTO categories (name) VALUES (?)');
        
        foreach ($categories as $category) {
            $stmt->execute([$category]);
        }
    }
    
    /**
     * Helper method to reset the Database singleton instance
     * Uses reflection to access and modify the private static property
     */
    private function setDatabaseInstanceToNull(): void
    {
        $reflection = new \ReflectionClass(Database::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null, null);
    }
}