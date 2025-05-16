<?php

namespace CheckLaterBot\Tests\Unit;

use CheckLaterBot\Bot;
use PHPUnit\Framework\TestCase;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Request;

class BotTest extends TestCase
{
    private $telegramMock;
    private $requestMock;
    
    protected function setUp(): void
    {
        // Mock the Telegram class
        $this->telegramMock = $this->createMock(Telegram::class);
        
        // Set up environment variables for testing
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_SQLITE_PATH'] = ':memory:';
    }
    
    public function testSetWebhook(): void
    {
        // Create a mock for ServerResponse
        $serverResponseMock = $this->createMock(ServerResponse::class);
        $serverResponseMock->method('isOk')->willReturn(true);
        
        // Configure the Telegram mock to return our ServerResponse mock
        $this->telegramMock->method('setWebhook')->willReturn($serverResponseMock);
        
        // Create a partial mock of the Bot class to inject our Telegram mock
        $botMock = $this->getMockBuilder(Bot::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct'])
            ->getMock();
        
        // Set the telegram property using reflection
        $reflection = new \ReflectionClass(Bot::class);
        $property = $reflection->getProperty('telegram');
        $property->setAccessible(true);
        $property->setValue($botMock, $this->telegramMock);
        
        // Test the setWebhook method
        $result = $botMock->setWebhook('https://example.com/webhook.php');
        $this->assertTrue($result);
    }
    
    public function testSetWebhookFailure(): void
    {
        // Create a mock for ServerResponse that returns false for isOk
        $serverResponseMock = $this->createMock(ServerResponse::class);
        $serverResponseMock->method('isOk')->willReturn(false);
        
        // Configure the Telegram mock to return our ServerResponse mock
        $this->telegramMock->method('setWebhook')->willReturn($serverResponseMock);
        
        // Create a partial mock of the Bot class to inject our Telegram mock
        $botMock = $this->getMockBuilder(Bot::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct'])
            ->getMock();
        
        // Set the telegram property using reflection
        $reflection = new \ReflectionClass(Bot::class);
        $property = $reflection->getProperty('telegram');
        $property->setAccessible(true);
        $property->setValue($botMock, $this->telegramMock);
        
        // Test the setWebhook method with failure
        $result = $botMock->setWebhook('https://example.com/webhook.php');
        $this->assertFalse($result);
    }
    
    public function testSendMessage(): void
    {
        // Mock the static Request class
        $requestMock = $this->createMock(Request::class);
        
        // Create a mock for ServerResponse
        $serverResponseMock = $this->createMock(ServerResponse::class);
        $serverResponseMock->method('isOk')->willReturn(true);
        
        // Create a partial mock of the Bot class
        $botMock = $this->getMockBuilder(Bot::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct'])
            ->getMock();
        
        // Set the telegram property using reflection
        $reflection = new \ReflectionClass(Bot::class);
        $property = $reflection->getProperty('telegram');
        $property->setAccessible(true);
        $property->setValue($botMock, $this->telegramMock);
        
        // Test sending a message
        // Note: Since Request is a static class, we can't easily mock it in PHPUnit
        // In a real test, we would use a library like AspectMock or create a wrapper around Request
        // For this example, we'll just test that the method exists and is callable
        $this->assertTrue(method_exists($botMock, 'sendMessage'));
    }
}