# Logging Configuration

This document describes the logging system used in the Check Later Bot.

## Overview

The Check Later Bot uses a PSR-3 compliant logging system based on Monolog. All PHP errors, warnings, and exceptions are captured by global handlers and recorded via the logger into a rotating log file defined in configuration.

## Configuration

Logging is configured through environment variables in the `.env` file:

```
# Logging Configuration
LOG_PATH=/full/path/to/logs/check_later_bot.log
LOG_LEVEL=warning  # Options: debug, info, notice, warning, error, critical, alert, emergency
```

### Environment Variables

- `LOG_PATH`: The full path to the log file. Make sure the directory exists and is writable by the web server.
- `LOG_LEVEL`: The minimum log level to record. Default is `warning`.

## Log Levels

The following log levels are available, in order of severity:

1. `debug`: Detailed debug information
2. `info`: Interesting events
3. `notice`: Normal but significant events
4. `warning`: Exceptional occurrences that are not errors
5. `error`: Runtime errors that do not require immediate action
6. `critical`: Critical conditions
7. `alert`: Action must be taken immediately
8. `emergency`: System is unusable

## Log File Rotation

Log files are automatically rotated to prevent them from growing too large. The system keeps 7 days of log files, with the current day's log file named according to the `LOG_PATH` setting, and previous days' logs appended with a date.

## Error Handling

The bot registers global error and exception handlers to capture all PHP errors, warnings, and exceptions:

- `set_error_handler`: Captures PHP errors and warnings
- `set_exception_handler`: Captures uncaught exceptions
- `register_shutdown_function`: Captures fatal errors

## Troubleshooting

If you're experiencing issues with logging:

1. **Check file permissions**: Make sure the logs directory and log file are writable by the web server.
2. **Verify log path**: Ensure the `LOG_PATH` in your `.env` file points to a valid location.
3. **Check log level**: If you're not seeing expected log entries, try setting `LOG_LEVEL` to a lower level like `debug`.
4. **Inspect error logs**: If the application is failing to start, check your web server's error logs for clues.

## Accessing Logs

Log files are stored in the location specified by `LOG_PATH`. You can view them using standard tools:

```bash
# View the entire log file
cat /path/to/logs/check_later_bot.log

# View the last 100 lines
tail -n 100 /path/to/logs/check_later_bot.log

# Follow the log in real-time
tail -f /path/to/logs/check_later_bot.log
```

## Development Guidelines

When adding new code to the bot, follow these logging guidelines:

1. Use appropriate log levels based on the severity of the event
2. Include relevant context in log messages (e.g., user IDs, entry IDs)
3. Don't log sensitive information like API tokens or passwords
4. Use structured logging with context arrays rather than concatenating values into messages