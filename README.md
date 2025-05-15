# Check Later Bot

A Telegram bot for managing "check later" links and content. The bot automatically classifies entries (YouTube videos, books, movies, etc.), allows category remapping, and provides random suggestions from each category.

## Features

- Receive and classify any message (text or link)
- Automatically categorize entries (YouTube, book, movie, other)
- Store entries in a structured database
- Allow remapping entries to different categories
- Provide random suggestions from each category
- Mark entries as obsolete to exclude from future suggestions

## Requirements

- PHP 8.0 or higher
- SQLite (php-sqlite3 extension)
- Composer
- Telegram Bot API token

## Installation

### Local Development

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/check_later_my_bot.git
   cd check_later_my_bot
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Create a `.env` file from the example:
   ```
   cp .env.example .env
   ```

4. Edit the `.env` file with your Telegram Bot token and set the SQLite database path:
   ```
   BOT_API_TOKEN=your_telegram_bot_token_here
   BOT_USERNAME=your_bot_username_here
   DB_DRIVER=sqlite
   DB_SQLITE_PATH=/full/path/to/database/check_later_bot.sqlite
   ```

5. For local testing, start ngrok to expose your local server:
   ```
   ngrok http 8080
   ```

6. Set the webhook (the script will automatically detect the ngrok URL):
   ```
   php set_webhook.php
   ```

   The bot now automatically detects your ngrok URL - no need to manually configure the webhook URL!

## Database

### SQLite Configuration

The bot uses SQLite as the primary database storage for simplicity and ease of setup. Here's how to configure and use it:

1. Ensure the SQLite PHP extension is installed:
   ```
   php -m | grep sqlite
   ```
   If it's not listed, install it:
   ```
   # On Ubuntu/Debian
   sudo apt install php8.0-sqlite3
   
   # On macOS with Homebrew
   brew install php
   ```

2. Set the SQLite configuration in your `.env` file:
   ```
   DB_DRIVER=sqlite
   DB_SQLITE_PATH=/full/path/to/database/check_later_bot.sqlite
   ```
   Note: Use an absolute path to avoid any issues with relative paths.

3. Initialize the SQLite database and create tables:
   ```
   php database/init_sqlite_db.php
   ```
   
   Alternatively, you can manually create and initialize the database:
   ```
   touch database/check_later_bot.sqlite
   sqlite3 database/check_later_bot.sqlite < database/migrations_sqlite.sql
   ```

4. The database schema is defined in [database/migrations_sqlite.sql](database/migrations_sqlite.sql) and includes:
   - `entries` table: Stores all user-submitted content with categories
   - `categories` table: Contains predefined content categories

5. To view or modify the database directly:
   ```
   sqlite3 database/check_later_bot.sqlite
   ```
   
   Some useful SQLite commands:
   ```
   .tables                  # List all tables
   .schema entries          # Show schema for entries table
   SELECT * FROM categories; # View all categories
   .quit                    # Exit SQLite console
   ```

### Legacy MySQL Support (Optional)

The bot also supports MySQL for legacy deployments, but SQLite is recommended for most use cases. If you need to use MySQL, refer to the commented section in `.env.example` for configuration details.

## DigitalOcean Deployment

Follow these steps to deploy the bot on a DigitalOcean droplet:

1. **Create a DigitalOcean Droplet**

   - Log in to your DigitalOcean account
   - Click "Create" and select "Droplet"
   - Choose an image: Ubuntu 20.04 (LTS) x64
   - Select a plan: Basic ($5/mo is sufficient for this bot)
   - Choose a datacenter region close to your users
   - Add your SSH key or create a password
   - Click "Create Droplet"

2. **Connect to Your Droplet**

   ```
   ssh root@your_droplet_ip
   ```

3. **Update System and Install Required Software**

   ```
   apt update && apt upgrade -y
   apt install -y nginx php8.0-fpm php8.0-sqlite3 php8.0-curl php8.0-mbstring php8.0-xml php8.0-zip unzip git
   ```

4. **Clone the Repository**

   ```
   cd /var/www
   git clone https://github.com/yourusername/check_later_my_bot.git
   cd check_later_my_bot
   ```

5. **Install Composer and Dependencies**

   ```
   curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
   composer install --no-dev
   ```

6. **Configure Environment**

   ```
   cp .env.example .env
   nano .env
   ```

   Update the following variables:
   - `BOT_API_TOKEN`: Your Telegram bot token
   - `BOT_USERNAME`: Your bot's username
   - `DB_DRIVER`: sqlite
   - `DB_SQLITE_PATH`: /var/www/check_later_my_bot/database/check_later_bot.sqlite
   
   Note: You no longer need to set the WEBHOOK_URL manually. For production deployment, you'll need to modify the NgrokService or create a custom service to use your domain.

7. **Set Up Database**

   ```
   php database/init_sqlite_db.php
   ```

8. **Configure Nginx**

   ```
   nano /etc/nginx/sites-available/check_later_bot
   ```

   Add the following configuration:

   ```
   server {
       listen 80;
       server_name your-domain.com; # Or your droplet IP if you don't have a domain
       root /var/www/check_later_my_bot;

       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
       }

       location ~ /\.ht {
           deny all;
       }
   }
   ```

   Enable the site:

   ```
   ln -s /etc/nginx/sites-available/check_later_bot /etc/nginx/sites-enabled/
   nginx -t
   systemctl restart nginx
   ```

9. **Set Permissions**

    ```
    chown -R www-data:www-data /var/www/check_later_my_bot
    chmod -R 755 /var/www/check_later_my_bot
    chmod -R 777 /var/www/check_later_my_bot/database  # Ensure SQLite database is writable
    ```

10. **Set Up SSL with Let's Encrypt** (recommended for production)

    ```
    apt install -y certbot python3-certbot-nginx
    certbot --nginx -d your-domain.com
    ```

    Follow the prompts to complete the SSL setup.

11. **Set the Webhook**

    ```
    cd /var/www/check_later_my_bot
    php set_webhook.php
    ```

12. **Configure Supervisor to Keep the Bot Running**

    ```
    apt install -y supervisor
    nano /etc/supervisor/conf.d/check_later_bot.conf
    ```

    Add the following configuration:

    ```
    [program:check_later_bot]
    command=php /var/www/check_later_my_bot/webhook.php
    autostart=true
    autorestart=true
    stderr_logfile=/var/log/check_later_bot.err.log
    stdout_logfile=/var/log/check_later_bot.out.log
    user=www-data
    ```

    Update supervisor:

    ```
    supervisorctl reread
    supervisorctl update
    supervisorctl start check_later_bot
    ```

## Usage

1. Start a conversation with your bot on Telegram.
2. Send `/start` to see the main menu.
3. Send any link or text to save it.
4. The bot will automatically classify it and allow you to remap if needed.
5. Use the main menu to get random suggestions from each category.
6. Mark entries as obsolete when you're done with them.

## Error Handling

The bot includes comprehensive error handling:
- Database connection errors are logged and reported
- Invalid inputs receive appropriate error messages
- Webhook errors are logged for troubleshooting

## License

This project is licensed under the MIT License - see the LICENSE file for details.