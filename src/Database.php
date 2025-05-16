<?php

namespace CheckLaterBot;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Get database instance (singleton)
     *
     * @return PDO Database connection
     * @throws PDOException If connection fails
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            try {
                if ($_ENV['DB_DRIVER'] === 'sqlite') {
                    // SQLite connection
                    $dsn = 'sqlite:' . $_ENV['DB_SQLITE_PATH'];
                    self::$instance = new PDO($dsn);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    // Create tables if they don't exist
                    self::createTables();
                } else {
                    // MySQL connection (legacy support)
                    $dsn = 'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_NAME'] . ';charset=utf8mb4';
                    self::$instance = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
                    self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
            } catch (PDOException $e) {
                $logger = Logger::getInstance();
                $logger->error('Database connection error: ' . $e->getMessage(), [
                    'exception' => get_class($e),
                    'driver' => $_ENV['DB_DRIVER']
                ]);
                throw $e;
            }
        }

        return self::$instance;
    }

    /**
     * Create database tables if they don't exist
     *
     * @return void
     * @throws PDOException If query fails
     */
    private static function createTables(): void
    {
        try {
            $db = self::$instance;

            // Create categories table
            $db->exec('
                CREATE TABLE IF NOT EXISTS categories (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT NOT NULL UNIQUE
                )
            ');

            // Create entries table
            $db->exec('
                CREATE TABLE IF NOT EXISTS entries (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    content TEXT NOT NULL,
                    category TEXT NOT NULL,
                    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    is_obsolete INTEGER DEFAULT 0
                )
            ');

            // Insert default categories if they don't exist
            $categories = ['youtube', 'book', 'movie', 'other'];
            $stmt = $db->prepare('INSERT OR IGNORE INTO categories (name) VALUES (?)');

            foreach ($categories as $category) {
                $stmt->execute([$category]);
            }
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error creating database tables: ' . $e->getMessage(), [
                'exception' => get_class($e)
            ]);
            throw $e;
        }
    }

    /**
     * Add a new entry to the database
     *
     * @param string $content The content to save
     * @param string $category The category
     * @return int The ID of the new entry
     * @throws PDOException If query fails
     */
    public static function addEntry(string $content, string $category): int
    {
        try {
            $db = self::getInstance();

            $stmt = $db->prepare('INSERT INTO entries (content, category) VALUES (?, ?)');
            $stmt->execute([$content, $category]);

            return (int)$db->lastInsertId();
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error adding entry: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'category' => $category
            ]);
            throw $e;
        }
    }

    /**
     * Update the category of an entry
     *
     * @param int $entryId The entry ID
     * @param string $newCategory The new category
     * @return bool Success status
     * @throws PDOException If query fails
     */
    public static function updateEntryCategory(int $entryId, string $newCategory): bool
    {
        try {
            $db = self::getInstance();

            $stmt = $db->prepare('UPDATE entries SET category = ? WHERE id = ?');
            $stmt->execute([$newCategory, $entryId]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error updating entry category: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'entry_id' => $entryId,
                'new_category' => $newCategory
            ]);
            throw $e;
        }
    }

    /**
     * Mark an entry as obsolete
     *
     * @param int $entryId The entry ID
     * @return bool Success status
     * @throws PDOException If query fails
     */
    public static function markEntryObsolete(int $entryId): bool
    {
        try {
            $db = self::getInstance();

            $stmt = $db->prepare('UPDATE entries SET is_obsolete = 1 WHERE id = ?');
            $stmt->execute([$entryId]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error marking entry as obsolete: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'entry_id' => $entryId
            ]);
            throw $e;
        }
    }

    /**
     * Get all categories
     *
     * @return array List of categories
     * @throws PDOException If query fails
     */    public static function getCategories(): array
    {
        try {
            $db = self::getInstance();
            
            $stmt = $db->query('SELECT * FROM categories ORDER BY name');
            if ($stmt === false) {
                $logger = Logger::getInstance();
                $logger->error('Failed to execute categories query');
                return [];
            }
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error getting categories: ' . $e->getMessage(), [
                'exception' => get_class($e)
            ]);
            throw $e;
        }
    }

    /**
     * Get random entries from a category
     *
     * @param string $category The category
     * @param int $limit Maximum number of entries to return
     * @return array List of entries
     * @throws PDOException If query fails
     */
    public static function getRandomEntriesByCategory(string $category, int $limit = 3): array
    {
        try {
            $db = self::getInstance();

            $stmt = $db->prepare('
                SELECT * FROM entries 
                WHERE category = ? AND is_obsolete = 0
                ORDER BY RANDOM() 
                LIMIT ?
            ');
            $stmt->execute([$category, $limit]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $logger = Logger::getInstance();
            $logger->error('Error getting random entries: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'category' => $category,
                'limit' => $limit
            ]);
            throw $e;
        }
    }
}
