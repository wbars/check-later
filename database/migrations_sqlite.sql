-- Database schema for Check Later Bot (SQLite version)

-- Create entries table
CREATE TABLE IF NOT EXISTS entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    content TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    obsolete BOOLEAN DEFAULT 0
);

-- Create categories table for predefined categories
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
);

-- Insert default categories
INSERT OR IGNORE INTO categories (name, description) VALUES 
('youtube', 'YouTube videos'),
('book', 'Books and reading materials'),
('movie', 'Movies and TV shows'),
('other', 'Other content');