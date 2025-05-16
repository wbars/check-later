<?php

// Load configuration
$config = require __DIR__ . '/config.php';

$logDir = dirname($config['log_file']);
$logFile = $config['log_file'];

echo "Setting up logs directory...\n";

// Create logs directory if it doesn't exist
if (!is_dir($logDir)) {
    if (!mkdir($logDir, 0777, true)) {
        echo "Error: Could not create logs directory at {$logDir}\n";
        exit(1);
    }
    echo "Created logs directory at {$logDir}\n";
} else {
    echo "Logs directory already exists at {$logDir}\n";
}

// Create log file if it doesn't exist
if (!file_exists($logFile)) {
    if (!touch($logFile)) {
        echo "Error: Could not create log file at {$logFile}\n";
        exit(1);
    }
    echo "Created log file at {$logFile}\n";
} else {
    echo "Log file already exists at {$logFile}\n";
}

// Set permissions
if (!chmod($logDir, 0777)) {
    echo "Warning: Could not set permissions on logs directory\n";
} else {
    echo "Set permissions on logs directory\n";
}

if (!chmod($logFile, 0666)) {
    echo "Warning: Could not set permissions on log file\n";
} else {
    echo "Set permissions on log file\n";
}

// Test if we can write to the log file
if (!is_writable($logFile)) {
    echo "Error: Log file is not writable. Please check permissions.\n";
    exit(1);
}

echo "\nLogs setup completed successfully!\n";
echo "You can now check the logs at: {$logFile}\n"; 