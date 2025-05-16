# Check Later Telegram Bot

A Telegram bot for managing and categorizing "check later" links. The bot automatically classifies links into categories (YouTube, books, movies, etc.) and provides a menu interface to view random links from each category.

## Features

- Automatic link categorization
- Menu-based interface for viewing links
- Random link suggestions from each category
- Ability to mark links as obsolete
- Persistent storage in SQLite database
- Error logging

## Requirements

- PHP 8.1 or higher
- SQLite3 PHP extension
- Nginx with PHP-FPM
- Composer
- SSL certificate (for webhook)

## Installation

1. Clone the repository:
   ```bash
   git clone git@your-server:check-later.git
   cd check-later
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Configure the bot:
   - Copy `config.php` and update the following settings:
     - Telegram Bot Token (get it from @BotFather)
     - Webhook URL (must be HTTPS)
     - Log file path
   - The SQLite database path is configured by default to `data/check_later.db`

4. Configure Nginx:
   - Copy `nginx.conf` to your Nginx sites directory
   - Update the server_name and root path
   - Enable the site: `sudo ln -s /etc/nginx/sites-available/check-later /etc/nginx/sites-enabled/`
   - Test and reload Nginx: `sudo nginx -t && sudo systemctl reload nginx`

5. Run the database setup script:
   ```bash
   php setup_database.php
   ```
   This script will:
   - Create the data directory
   - Initialize the SQLite database
   - Create the required tables and indexes

6. Run the bot setup script:
   ```bash
   php setup.php
   ```
   This script will:
   - Validate your configuration
   - Test database connection
   - Set up the webhook
   - Create necessary directories
   - Verify the webhook is working

7. Create log directory and set permissions (if not done by setup script):
   ```bash
   mkdir -p logs
   chmod 777 logs
   ```

## Usage

1. Start a chat with your bot on Telegram
2. Send `/start` to get the welcome message
3. Send any URL to save it
4. Use `/menu` to view your saved links by category
5. Click on category buttons to get random links
6. Use "Mark as obsolete" buttons to mark links you've already checked

## Error Logging

Errors are logged to the file specified in `config.php` (default: `logs/error.log`). Make sure the log directory is writable by the web server user.

## Security Notes

- The webhook endpoint is protected by Telegram's secret token
- Sensitive files (config.php, composer.json, schema.sql) are protected by Nginx
- The SQLite database file is stored in the data directory
- All user input is properly sanitized
- HTTPS is required for the webhook

## Maintenance

To update the bot:

1. Pull the latest changes:
   ```bash
   git pull
   ```

2. Update dependencies:
   ```bash
   composer update
   ```

3. Check the logs for any errors:
   ```bash
   tail -f logs/error.log
   ```

4. Backup the database:
   ```bash
   cp data/check_later.db data/check_later.db.backup
   ``` 