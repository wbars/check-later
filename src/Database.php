<?php

namespace CheckLaterBot;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;
    
    /**
     * Get database connection instance (singleton pattern)
     *
     * @return PDO
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8mb4';
                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];
                
                self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], $options);
            } catch (PDOException $e) {
                // Log error and rethrow
                error_log('Database connection error: ' . $e->getMessage());
                throw $e;
            }
        }
        
        return self::$instance;
    }
    
    /**
     * Add a new entry to the database
     *
     * @param string $content The content of the entry
     * @param string $category The category of the entry
     * @return int The ID of the inserted entry
     */
    public static function addEntry(string $content, string $category): int
    {
        $db = self::getInstance();
        $stmt = $db->prepare('INSERT INTO entries (content, category) VALUES (?, ?)');
        $stmt->execute([$content, $category]);
        
        return (int) $db->lastInsertId();
    }
    
    /**
     * Update the category of an entry
     *
     * @param int $id The ID of the entry
     * @param string $category The new category
     * @return bool Success status
     */
    public static function updateEntryCategory(int $id, string $category): bool
    {
        $db = self::getInstance();
        $stmt = $db->prepare('UPDATE entries SET category = ? WHERE id = ?');
        
        return $stmt->execute([$category, $id]);
    }
    
    /**
     * Mark an entry as obsolete
     *
     * @param int $id The ID of the entry
     * @return bool Success status
     */
    public static function markEntryObsolete(int $id): bool
    {
        $db = self::getInstance();
        $stmt = $db->prepare('UPDATE entries SET obsolete = TRUE WHERE id = ?');
        
        return $stmt->execute([$id]);
    }
    
    /**
     * Get random entries from a specific category
     *
     * @param string $category The category to fetch from
     * @param int $limit The number of entries to fetch
     * @return array The random entries
     */
    public static function getRandomEntriesByCategory(string $category, int $limit = 2): array
    {
        $db = self::getInstance();
        $stmt = $db->prepare('
            SELECT id, content, category, date_added 
            FROM entries 
            WHERE category = ? AND obsolete = FALSE
            ORDER BY RAND() 
            LIMIT ?
        ');
        $stmt->execute([$category, $limit]);
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get all available categories
     *
     * @return array List of categories
     */
    public static function getCategories(): array
    {
        $db = self::getInstance();
        $stmt = $db->query('SELECT name, description FROM categories');
        
        return $stmt->fetchAll();
    }
    
    /**
     * Check if a category exists
     *
     * @param string $category The category name to check
     * @return bool Whether the category exists
     */
    public static function categoryExists(string $category): bool
    {
        $db = self::getInstance();
        $stmt = $db->prepare('SELECT COUNT(*) FROM categories WHERE name = ?');
        $stmt->execute([$category]);
        
        return (int) $stmt->fetchColumn() > 0;
    }
}