CREATE DATABASE IF NOT EXISTS check_later;
USE check_later;

CREATE TABLE IF NOT EXISTS links (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    url TEXT NOT NULL,
    category TEXT NOT NULL,
    user_id INTEGER NOT NULL,
    is_obsolete INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_category ON links(category);
CREATE INDEX IF NOT EXISTS idx_user_category ON links(user_id, category);
CREATE INDEX IF NOT EXISTS idx_obsolete ON links(is_obsolete);

-- Trigger to update the updated_at timestamp
CREATE TRIGGER IF NOT EXISTS update_links_timestamp 
AFTER UPDATE ON links
BEGIN
    UPDATE links SET updated_at = CURRENT_TIMESTAMP WHERE id = NEW.id;
END; 