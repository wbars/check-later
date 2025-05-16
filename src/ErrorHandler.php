<?php

namespace CheckLaterBot;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * Global error handler for the Check Later Bot
 * Captures all PHP errors, warnings, and exceptions
 */
class ErrorHandler
{
    private static ?ErrorHandler $instance = null;
    private LoggerInterface $logger;
    private bool $registered = false;

    /**
     * ErrorHandler constructor
     *
     * @param LoggerInterface $logger PSR-3 logger instance
     */
    private function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Get ErrorHandler instance (singleton)
     *
     * @param LoggerInterface $logger PSR-3 logger instance
     * @return ErrorHandler
     */
    public static function getInstance(LoggerInterface $logger): ErrorHandler
    {
        if (self::$instance === null) {
            self::$instance = new self($logger);
        }

        return self::$instance;
    }

    /**
     * Register all error handlers
     *
     * @return void
     */
    public function register(): void
    {
        if ($this->registered) {
            return;
        }

        // Set error handler for warnings, notices, etc.
        set_error_handler([$this, 'handleError']);

        // Set exception handler for uncaught exceptions
        set_exception_handler([$this, 'handleException']);

        // Register shutdown function for fatal errors
        register_shutdown_function([$this, 'handleShutdown']);

        $this->registered = true;
        $this->logger->info('Error handlers registered successfully');
    }

    /**
     * Handle PHP errors
     *
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile File where the error occurred
     * @param int $errline Line number where the error occurred
     * @return bool Whether the error was handled
     */
    public function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        // Don't handle errors if they're suppressed with @
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $context = [
            'file' => $errfile,
            'line' => $errline,
            'code' => $errno
        ];

        // Map PHP error levels to PSR-3 log levels
        $level = match ($errno) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR => LogLevel::ERROR,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => LogLevel::WARNING,
            E_NOTICE, E_USER_NOTICE => LogLevel::NOTICE,
            E_DEPRECATED, E_USER_DEPRECATED => LogLevel::INFO,
            default => LogLevel::WARNING,
        };

        // Log the error
        $this->logger->log($level, $errstr, $context);

        // Don't execute PHP's internal error handler
        return true;
    }

    /**
     * Handle uncaught exceptions
     *
     * @param Throwable $exception The uncaught exception
     * @return void
     */
    public function handleException(Throwable $exception): void
    {
        $context = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString()
        ];

        // Log the exception
        $this->logger->error($exception->getMessage(), $context);
    }

    /**
     * Handle fatal errors on shutdown
     *
     * @return void
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        // Only handle fatal errors
        if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
            $context = [
                'file' => $error['file'],
                'line' => $error['line'],
                'code' => $error['type']
            ];

            // Log the fatal error
            $this->logger->critical($error['message'], $context);
        }
    }
}
