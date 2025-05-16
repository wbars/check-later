<?php

namespace CheckLaterBot;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Level;
use Psr\Log\LoggerInterface;

/**
 * Logger class for the Check Later Bot
 * Implements PSR-3 compliant logging using Monolog
 */
class Logger implements LoggerInterface
{
    private static ?LoggerInterface $instance = null;
    private LoggerInterface $logger;

    /**
     * Logger constructor
     *
     * @param string|null $logPath Path to the log file
     * @param string $logLevel Minimum log level to record
     */
    private function __construct(?string $logPath = null, string $logLevel = 'warning')
    {
        // Create Monolog instance
        $this->logger = new MonologLogger('check_later_bot');

        // Set log format
        $dateFormat = "Y-m-d H:i:s";
        $output = "[%datetime%] %level_name%: %message% %context% %extra%\n";
        $formatter = new LineFormatter($output, $dateFormat);

        // Convert string level to Monolog Level
        $level = $this->getMonologLevel($logLevel);

        // Add rotating file handler if log path is provided
        if ($logPath) {
            $fileHandler = new RotatingFileHandler($logPath, 7, $level);
            $fileHandler->setFormatter($formatter);
            $this->logger->pushHandler($fileHandler);
        } else {
            // Fallback to stderr if no log path
            $streamHandler = new StreamHandler('php://stderr', $level);
            $streamHandler->setFormatter($formatter);
            $this->logger->pushHandler($streamHandler);
        }
    }

    /**
     * Get logger instance (singleton)
     *
     * @param string|null $logPath Path to the log file
     * @param string $logLevel Minimum log level to record
     * @return LoggerInterface
     */
    public static function getInstance(?string $logPath = null, string $logLevel = 'warning'): LoggerInterface
    {
        if (self::$instance === null) {
            self::$instance = new self($logPath, $logLevel);
        }

        return self::$instance;
    }

    /**
     * Convert string log level to Monolog Level
     *
     * @param string $level Log level as string
     * @return Level Monolog Level
     */
    private function getMonologLevel(string $level): Level
    {
        return match (strtolower($level)) {
            'debug' => Level::Debug,
            'info' => Level::Info,
            'notice' => Level::Notice,
            'warning' => Level::Warning,
            'error' => Level::Error,
            'critical' => Level::Critical,
            'alert' => Level::Alert,
            'emergency' => Level::Emergency,
            default => Level::Warning,
        };
    }

    /**
     * System is unusable.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    /**
     * Critical conditions.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    /**
     * Runtime errors that do not require immediate action.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    /**
     * Interesting events.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|\Stringable $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }
}
