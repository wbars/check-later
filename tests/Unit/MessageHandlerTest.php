<?php

namespace CheckLaterBot\Tests\Unit;

use CheckLaterBot\MessageHandler;
use CheckLaterBot\Bot;
use CheckLaterBot\Classifier;
use PHPUnit\Framework\TestCase;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\Message;

class MessageHandlerTest extends TestCase
{
    private $botMock;
    private $classifierMock;
    private $messageHandler;
    
    protected function setUp(): void
    {
        // Create mocks
        $this->botMock = $this->createMock(Bot::class);
        $this->classifierMock = $this->createMock(Classifier::class);
        
        // Set up environment variables for testing
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_SQLITE_PATH'] = ':memory:';
        
        // Create MessageHandler instance with mocks
        $this->messageHandler = new MessageHandler($this->botMock, $this->classifierMock);
    }
    
    public function testHandleTextMessage(): void
    {
        // Configure classifier mock to return a specific category
        $this->classifierMock->method('classify')
            ->willReturn('youtube');
        
        // Configure bot mock to return true for sendMessage
        $this->botMock->method('sendMessage')
            ->willReturn(true);
        
        // Create a mock Update object
        $updateMock = $this->createMock(Update::class);
        $messageMock = $this->createMock(Message::class);
        
        // Configure message mock
        $messageMock->method('getText')
            ->willReturn('https://www.youtube.com/watch?v=dQw4w9WgXcQ');
        $messageMock->method('getChat')
            ->willReturn(['id' => 123456]);
        $messageMock->method('getFrom')
            ->willReturn(['id' => 123456]);
        
        // Configure update mock to return our message mock
        $updateMock->method('getMessage')
            ->willReturn($messageMock);
        
        // Test handling a text message
        $result = $this->messageHandler->handleUpdate($updateMock);
        
        // Since we can't easily test the database operations in a unit test,
        // we'll just verify that the method completes without errors
        $this->assertTrue($result);
    }
    
    public function testHandleCallbackQuery(): void
    {
        // Configure bot mock to return true for sendMessage and answerCallbackQuery
        $this->botMock->method('sendMessage')
            ->willReturn(true);
        $this->botMock->method('answerCallbackQuery')
            ->willReturn(true);
        
        // Create a mock Update object with a callback query
        $updateMock = $this->createMock(Update::class);
        $callbackQueryMock = $this->createMock(\Longman\TelegramBot\Entities\CallbackQuery::class);
        
        // Configure callback query mock
        $callbackQueryMock->method('getData')
            ->willReturn('random_youtube');
        $callbackQueryMock->method('getFrom')
            ->willReturn(['id' => 123456]);
        $callbackQueryMock->method('getId')
            ->willReturn('query123');
        
        // Configure update mock to return our callback query mock
        $updateMock->method('getCallbackQuery')
            ->willReturn($callbackQueryMock);
        $updateMock->method('getMessage')
            ->willReturn(null);
        
        // Test handling a callback query
        $result = $this->messageHandler->handleUpdate($updateMock);
        
        // Since we can't easily test the database operations in a unit test,
        // we'll just verify that the method completes without errors
        $this->assertTrue($result);
    }
    
    public function testHandleInvalidUpdate(): void
    {
        // Create a mock Update object with neither message nor callback query
        $updateMock = $this->createMock(Update::class);
        
        // Configure update mock to return null for both getMessage and getCallbackQuery
        $updateMock->method('getMessage')
            ->willReturn(null);
        $updateMock->method('getCallbackQuery')
            ->willReturn(null);
        
        // Test handling an invalid update
        $result = $this->messageHandler->handleUpdate($updateMock);
        
        // Should return false for invalid updates
        $this->assertFalse($result);
    }
}