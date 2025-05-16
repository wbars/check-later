<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables for testing
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../', '.env.testing');
$dotenv->load();

// Initialize database for testing
require_once __DIR__ . '/../database/init_sqlite_db.php';