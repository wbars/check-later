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
- MySQL/MariaDB database
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

4. Edit the `.env` file with your Telegram Bot token and database credentials.

5. Create the database and tables:
   ```
   mysql -u your_username -p < database/migrations.sql
   ```

6. For local testing, you can use a tool like ngrok to expose your local server:
   ```
   ngrok http 8080
   ```

7. Update the `WEBHOOK_URL` in your `.env` file with the ngrok URL.

8. Set the webhook:
   ```
   php set_webhook.php
   ```

### DigitalOcean Deployment

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
   apt install -y nginx mysql-server php8.0-fpm php8.0-mysql php8.0-curl php8.0-mbstring php8.0-xml php8.0-zip unzip git
   ```

4. **Secure MySQL and Create Database**

   ```
   mysql_secure_installation
   ```

   Follow the prompts to set a root password and secure your MySQL installation.

   Then create the database and user:

   ```
   mysql -u root -p
   ```

   ```sql
   CREATE DATABASE check_later_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'botuser'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON check_later_bot.* TO 'botuser'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

5. **Clone the Repository**

   ```
   cd /var/www
   git clone https://github.com/yourusername/check_later_my_bot.git
   cd check_later_my_bot
   ```

6. **Install Composer and Dependencies**

   ```
   curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
   composer install --no-dev
   ```

7. **Configure Environment**

   ```
   cp .env.example .env
   nano .env
   ```

   Update the following variables:
   - `BOT_API_TOKEN`: Your Telegram bot token
   - `BOT_USERNAME`: Your bot's username
   - `WEBHOOK_URL`: https://your-domain.com/webhook.php (or your droplet IP if you don't have a domain)
   - `DB_HOST`: localhost
   - `DB_NAME`: check_later_bot
   - `DB_USER`: botuser
   - `DB_PASS`: your_secure_password

8. **Set Up Database Tables**

   ```
   mysql -u botuser -p check_later_bot < database/migrations.sql
   ```

9. **Configure Nginx**

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

10. **Set Permissions**

    ```
    chown -R www-data:www-data /var/www/check_later_my_bot
    chmod -R 755 /var/www/check_later_my_bot
    ```

11. **Set Up SSL with Let's Encrypt** (recommended for production)

    ```
    apt install -y certbot python3-certbot-nginx
    certbot --nginx -d your-domain.com
    ```

    Follow the prompts to complete the SSL setup.

12. **Set the Webhook**

    ```
    cd /var/www/check_later_my_bot
    php set_webhook.php
    ```

13. **Configure Supervisor to Keep the Bot Running**

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