## Check Later Bot

A Telegram bot that helps you manage content you want to check later. The bot automatically classifies and organizes links and text messages into categories (YouTube videos, books, movies, etc.), allowing you to retrieve random suggestions from your saved content when you have time to check them.

### Features

- Automatically categorizes content (YouTube videos, books, movies, other)
- Allows users to remap categories if needed
- Provides random suggestions from each category
- Marks entries as obsolete to exclude them from future suggestions
- Simple and intuitive interface
- Comprehensive error logging and handling

### Requirements

- PHP 8.0 or higher
- SQLite (recommended) or MySQL database
- Composer for dependency management
- Telegram Bot API token

### Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/check_later_my_bot.git
   cd check_later_my_bot
   ```

2. Install dependencies:
   ```
   composer install
   ```

3. Create a `.env` file based on the example:
   ```
   cp .env.example .env
   ```

4. Edit the `.env` file with your bot token, username, database settings, and logging configuration:
   ```
   BOT_API_TOKEN=your_telegram_bot_token_here
   BOT_USERNAME=your_bot_username_here
   WEBHOOK_URL=https://your-domain.com/webhook.php
   
   # SQLite (recommended)
   DB_DRIVER=sqlite
   DB_SQLITE_PATH=/full/path/to/database/check_later_bot.sqlite
   
   # MySQL (legacy, not recommended)
   # DB_DRIVER=mysql
   # DB_HOST=localhost
   # DB_NAME=check_later_bot
   # DB_USER=your_database_user
   # DB_PASS=your_database_password
   
   # Logging Configuration
   LOG_PATH=/full/path/to/logs/check_later_bot.log
   LOG_LEVEL=warning
   ```

5. Create the database and logs directories and ensure they're writable:
   ```
   mkdir -p database logs
   chmod 755 database logs
   ```

6. Set the webhook:
   ```
   php set_webhook.php
   ```

### Deployment

#### DigitalOcean Droplet (Recommended)

1. Create a new Ubuntu droplet on DigitalOcean
2. Install required packages:
   ```
   apt update
   apt install -y php8.0-cli php8.0-fpm php8.0-sqlite3 php8.0-curl php8.0-mbstring nginx git unzip
   ```

3. Install Composer:
   ```
   curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
   ```

4. Clone the repository to `/var/www/check_later_my_bot`
5. Set up Nginx:
   ```
   server {
       listen 80;
       server_name your-domain.com;
       root /var/www/check_later_my_bot;
       
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

6. Set up SSL with Let's Encrypt:
   ```
   apt install -y certbot python3-certbot-nginx
   certbot --nginx -d your-domain.com
   ```

7. Follow the installation steps above to complete the setup

### Logging

The bot uses a PSR-3 compliant logging system based on Monolog. All PHP errors, warnings, and exceptions are captured and logged to a rotating log file.

Configure logging in your `.env` file:
```
LOG_PATH=/path/to/logs/check_later_bot.log
LOG_LEVEL=warning  # Options: debug, info, notice, warning, error, critical, alert, emergency
```

For more details on logging, see [docs/logging.md](docs/logging.md).

### Development

#### Code Style

The project follows PSR-12 coding standards. You can check your code with:

```
composer phpcs
```

And automatically fix some issues with:

```
composer phpcbf
```

#### Static Analysis

The project uses PHPStan for static analysis:

```
composer phpstan
```

#### Testing

Run the test suite with:

```
composer test
```

### License

This project is licensed under the MIT License - see the LICENSE file for details.