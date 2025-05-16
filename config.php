<?php

return [
    // Telegram Bot Token (get it from @BotFather)
    'telegram_bot_token' => 'your_bot_token_here',
    
    // Database Configuration (SQLite)
    'database' => [
        'path' => __DIR__ . '/data/check_later.db'
    ],
    
    // Webhook URL (your domain where the bot will be hosted)
    'webhook_url' => 'https://your-domain.com/webhook.php',
    
    // Log file path
    'log_file' => __DIR__ . '/logs/error.log',
    
    // Categories for link classification
    'categories' => [
        'youtube' => [
            'patterns' => ['youtube.com', 'youtu.be'],
            'name' => 'YouTube Videos'
        ],
        'books' => [
            'patterns' => ['goodreads.com', 'amazon.com/books', 'book'],
            'name' => 'Books'
        ],
        'movies' => [
            'patterns' => ['imdb.com', 'netflix.com', 'movie'],
            'name' => 'Movies & TV Shows'
        ],
        'articles' => [
            'patterns' => ['medium.com', 'blog', 'article'],
            'name' => 'Articles & Blogs'
        ],
        'other' => [
            'patterns' => [],
            'name' => 'Other Links'
        ]
    ]
]; 