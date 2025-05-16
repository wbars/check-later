<?php

namespace CheckLaterBot\Tests\Unit;

use CheckLaterBot\Logger;
use PHPUnit\Framework\TestCase;
use Monolog\Handler\TestHandler;
use ReflectionClass;

class LoggerTest extends TestCase
{
    public function testLoggerSingleton(): void
    {
        // Get two instances of the logger
        $logger1 = Logger::getInstance();
        $logger2 = Logger::getInstance();
        
        // They should be the same instance
        $this->assertSame($logger1, $logger2);
    }
    
    public function testLoggerLevels(): void
    {
        // Create a logger instance
        $logger = Logger::getInstance();
        
        // Use reflection to access the private logger property
        $reflection = new ReflectionClass($logger);
        $loggerProperty = $reflection->getProperty('logger');
        $loggerProperty->setAccessible(true);
        
        // Get the internal Monolog logger
        $monologLogger = $loggerProperty->getValue($logger);
        
        // Add a test handler to capture log records
        $testHandler = new TestHandler();
        $monologLogger->pushHandler($testHandler);
        
        // Test different log levels
        $logger->emergency('Emergency message');
        $logger->alert('Alert message');
        $logger->critical('Critical message');
        $logger->error('Error message');
        $logger->warning('Warning message');
        $logger->notice('Notice message');
        $logger->info('Info message');
        $logger->debug('Debug message');
        
        // Verify that all messages were logged at the correct levels
        $this->assertTrue($testHandler->hasEmergencyRecords());
        $this->assertTrue($testHandler->hasAlertRecords());
        $this->assertTrue($testHandler->hasCriticalRecords());
        $this->assertTrue($testHandler->hasErrorRecords());
        $this->assertTrue($testHandler->hasWarningRecords());
        $this->assertTrue($testHandler->hasNoticeRecords());
        $this->assertTrue($testHandler->hasInfoRecords());
        $this->assertTrue($testHandler->hasDebugRecords());
        
        // Check specific messages
        $this->assertTrue($testHandler->hasEmergencyThatContains('Emergency message'));
        $this->assertTrue($testHandler->hasAlertThatContains('Alert message'));
        $this->assertTrue($testHandler->hasCriticalThatContains('Critical message'));
        $this->assertTrue($testHandler->hasErrorThatContains('Error message'));
        $this->assertTrue($testHandler->hasWarningThatContains('Warning message'));
        $this->assertTrue($testHandler->hasNoticeThatContains('Notice message'));
        $this->assertTrue($testHandler->hasInfoThatContains('Info message'));
        $this->assertTrue($testHandler->hasDebugThatContains('Debug message'));
    }
    
    public function testLoggerContext(): void
    {
        // Create a logger instance
        $logger = Logger::getInstance();
        
        // Use reflection to access the private logger property
        $reflection = new ReflectionClass($logger);
        $loggerProperty = $reflection->getProperty('logger');
        $loggerProperty->setAccessible(true);
        
        // Get the internal Monolog logger
        $monologLogger = $loggerProperty->getValue($logger);
        
        // Add a test handler to capture log records
        $testHandler = new TestHandler();
        $monologLogger->pushHandler($testHandler);
        
        // Log a message with context
        $context = ['user_id' => 123, 'action' => 'test'];
        $logger->error('Error with context', $context);
        
        // Get the last record
        $records = $testHandler->getRecords();
        $lastRecord = end($records);
        
        // Verify the context was included
        $this->assertArrayHasKey('user_id', $lastRecord['context']);
        $this->assertArrayHasKey('action', $lastRecord['context']);
        $this->assertEquals(123, $lastRecord['context']['user_id']);
        $this->assertEquals('test', $lastRecord['context']['action']);
    }
}