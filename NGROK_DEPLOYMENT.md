# Ngrok Deployment Guide

This guide explains how to use ngrok for local development and testing of the Check Later Bot.

## What is ngrok?

Ngrok is a tool that creates secure tunnels to expose your local web server to the internet. This is particularly useful for testing webhook-based applications like Telegram bots without deploying to a production server.

## Setup and Usage

### 1. Install ngrok

If you haven't already installed ngrok, you can download it from [ngrok.com](https://ngrok.com/download) or install it using package managers:

```bash
# macOS with Homebrew
brew install ngrok

# Ubuntu/Debian
sudo apt install ngrok-client

# Or download and install manually from ngrok.com
```

### 2. Start your local web server

Make sure your PHP server is running on your local machine. For example:

```bash
# Using PHP's built-in server
php -S localhost:8080
```

### 3. Start ngrok

Open a new terminal window and start ngrok, pointing to your local server port:

```bash
ngrok http 8080
```

This will display a dashboard showing the ngrok tunnel information, including the public URL that can be used to access your local server.

### 4. Set the webhook

Run the webhook setup script:

```bash
php set_webhook.php
```

The script will automatically:
1. Connect to the ngrok API at http://localhost:4040/api/tunnels
2. Retrieve the HTTPS URL from the running ngrok tunnel
3. Set the Telegram webhook to this URL + "/webhook.php"

### 5. Test your bot

Your bot should now be accessible through Telegram. Send a message to your bot to test if it's working correctly.

## How it Works

The Check Later Bot now includes an `NgrokService` class that:

1. Connects to the ngrok API running on localhost
2. Retrieves the list of active tunnels
3. Finds the HTTPS tunnel URL
4. Appends the webhook path to create the complete webhook URL

This eliminates the need to manually update the webhook URL in your `.env` file every time you restart ngrok.

## Troubleshooting

If you encounter issues with the ngrok integration:

1. **Ngrok not running**: Make sure ngrok is running and the dashboard is accessible at http://localhost:4040

2. **No HTTPS tunnel**: Ensure ngrok is creating an HTTPS tunnel. The output should show both HTTP and HTTPS URLs.

3. **Connection errors**: If the bot can't connect to the ngrok API, check if ngrok is running on the default port (4040) or if you need to modify the `NGROK_API_URL` constant in the `NgrokService` class.

4. **Webhook errors**: If Telegram can't reach your webhook, ensure your local server is running and accessible through the ngrok URL.

## Production Deployment

For production deployment, you should:

1. Either modify the `NgrokService` to use your production domain
2. Or create a custom service that returns your production URL
3. Or directly specify the webhook URL in the `set_webhook.php` script

Remember that ngrok is primarily a development tool and not recommended for production use unless you have a paid plan with stable URLs.