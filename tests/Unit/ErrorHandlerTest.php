<?php

namespace CheckLaterBot\Tests\Unit;

use CheckLaterBot\ErrorHandler;
use CheckLaterBot\Logger;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\TestHandler;
use Monolog\Logger as MonologLogger;

class ErrorHandlerTest extends TestCase
{
    private $testHandler;
    private $logger;
    private $errorHandler;
    
    protected function setUp(): void
    {
        // Create a test handler to capture log records
        $this->testHandler = new TestHandler();
        
        // Create a custom logger with the test handler
        $monologLogger = new MonologLogger('test');
        $monologLogger->pushHandler($this->testHandler);
        
        // Mock the Logger class to return our test logger
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        
        // Create the error handler with our mocked logger
        $this->errorHandler = ErrorHandler::getInstance($this->logger);
        $this->errorHandler->register();
    }
    
    public function testHandleError(): void
    {
        // Set up logger expectations
        $this->logger->expects($this->once())
            ->method('log')
            ->with(
                $this->equalTo('warning'),
                $this->stringContains('Test warning'),
                $this->callback(function ($context) {
                    return isset($context['file']) && isset($context['line']) && isset($context['code']);
                })
            );
        
        // Trigger a warning
        $this->errorHandler->handleError(E_WARNING, 'Test warning', __FILE__, __LINE__);
    }
    
    public function testHandleException(): void
    {
        // Create a test exception
        $exception = new \Exception('Test exception');
        
        // Set up logger expectations
        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->equalTo('Test exception'),
                $this->callback(function ($context) {
                    return isset($context['file']) && isset($context['line']) && isset($context['code']) && isset($context['trace']);
                })
            );
        
        // Handle the exception
        $this->errorHandler->handleException($exception);
    }
    
    public function testErrorHandlerIntegration(): void
    {
        // Create a real logger with test handler for integration testing
        $testHandler = new TestHandler();
        $monologLogger = new MonologLogger('test');
        $monologLogger->pushHandler($testHandler);
        
        // Create a new error handler with the real logger
        $realErrorHandler = ErrorHandler::getInstance($monologLogger);
        $realErrorHandler->register();
        
        // Trigger a user warning
        trigger_error('Test user warning', E_USER_WARNING);
        
        // Check if the warning was logged
        $this->assertTrue($testHandler->hasWarningRecords());
        $this->assertTrue($testHandler->hasWarningThatContains('Test user warning'));
    }
}