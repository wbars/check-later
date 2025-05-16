CREATE DATABASE IF NOT EXISTS check_later;
USE check_later;

CREATE TABLE IF NOT EXISTS links (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(2048) NOT NULL,
    category VARCHAR(50) NOT NULL,
    user_id BIGINT NOT NULL,
    is_obsolete BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_user_category (user_id, category),
    INDEX idx_obsolete (is_obsolete)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 