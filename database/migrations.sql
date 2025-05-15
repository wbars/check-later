-- Database schema for Check Later Bot

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS check_later_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE check_later_bot;

-- Create entries table
CREATE TABLE IF NOT EXISTS entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content TEXT NOT NULL,
    category VARCHAR(50) NOT NULL,
    date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    obsolete BOOLEAN DEFAULT FALSE
);

-- Create categories table for predefined categories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255)
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES 
('youtube', 'YouTube videos'),
('book', 'Books and reading materials'),
('movie', 'Movies and TV shows'),
('other', 'Other content');