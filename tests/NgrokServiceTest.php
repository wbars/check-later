<?php

namespace CheckLaterBot\Tests;

use CheckLaterBot\NgrokService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class NgrokServiceTest extends TestCase
{
    /**
     * Test successful retrieval of ngrok URL
     */
    public function testGetPublicUrlSuccess()
    {
        // Mock response with valid ngrok data
        $mockResponse = [
            'tunnels' => [
                [
                    'name' => 'command_line',
                    'uri' => '/api/tunnels/command_line',
                    'public_url' => 'https://abcd1234.ngrok.io',
                    'proto' => 'https',
                    'config' => ['addr' => 'http://localhost:8080', 'inspect' => true],
                    'metrics' => ['conns' => ['count' => 0, 'gauge' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0], 'http' => ['count' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0]]
                ],
                [
                    'name' => 'command_line (http)',
                    'uri' => '/api/tunnels/command_line%20%28http%29',
                    'public_url' => 'http://abcd1234.ngrok.io',
                    'proto' => 'http',
                    'config' => ['addr' => 'http://localhost:8080', 'inspect' => true],
                    'metrics' => ['conns' => ['count' => 0, 'gauge' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0], 'http' => ['count' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0]]
                ]
            ],
            'uri' => '/api/tunnels'
        ];

        // Create a mock handler
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create NgrokService with mocked client
        $ngrokService = new NgrokService();
        $this->setPrivateProperty($ngrokService, 'client', $client);

        // Test without path
        $url = $ngrokService->getPublicUrl();
        $this->assertEquals('https://abcd1234.ngrok.io', $url);
    }
    
    /**
     * Test URL with path
     */
    public function testGetPublicUrlWithPath()
    {
        // Mock response with valid ngrok data
        $mockResponse = [
            'tunnels' => [
                [
                    'name' => 'command_line',
                    'uri' => '/api/tunnels/command_line',
                    'public_url' => 'https://abcd1234.ngrok.io',
                    'proto' => 'https',
                    'config' => ['addr' => 'http://localhost:8080', 'inspect' => true],
                    'metrics' => ['conns' => ['count' => 0, 'gauge' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0], 'http' => ['count' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0]]
                ]
            ],
            'uri' => '/api/tunnels'
        ];

        // Create a mock handler
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create NgrokService with mocked client
        $ngrokService = new NgrokService();
        $this->setPrivateProperty($ngrokService, 'client', $client);

        // Test with path
        $url = $ngrokService->getPublicUrl('/webhook.php');
        $this->assertEquals('https://abcd1234.ngrok.io/webhook.php', $url);
    }

    /**
     * Test handling of empty tunnels response
     */
    public function testGetPublicUrlEmptyTunnels()
    {
        // Mock response with no tunnels
        $mockResponse = [
            'tunnels' => [],
            'uri' => '/api/tunnels'
        ];

        // Create a mock handler
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create NgrokService with mocked client
        $ngrokService = new NgrokService();
        $this->setPrivateProperty($ngrokService, 'client', $client);

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No active ngrok tunnels found');
        
        $ngrokService->getPublicUrl();
    }

    /**
     * Test handling of no HTTPS tunnel
     */
    public function testGetPublicUrlNoHttpsTunnel()
    {
        // Mock response with only HTTP tunnel
        $mockResponse = [
            'tunnels' => [
                [
                    'name' => 'command_line (http)',
                    'uri' => '/api/tunnels/command_line%20%28http%29',
                    'public_url' => 'http://abcd1234.ngrok.io',
                    'proto' => 'http',
                    'config' => ['addr' => 'http://localhost:8080', 'inspect' => true],
                    'metrics' => ['conns' => ['count' => 0, 'gauge' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0], 'http' => ['count' => 0, 'rate1' => 0, 'rate5' => 0, 'rate15' => 0, 'p50' => 0, 'p90' => 0, 'p95' => 0, 'p99' => 0]]
                ]
            ],
            'uri' => '/api/tunnels'
        ];

        // Create a mock handler
        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/json'], json_encode($mockResponse))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create NgrokService with mocked client
        $ngrokService = new NgrokService();
        $this->setPrivateProperty($ngrokService, 'client', $client);

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No HTTPS tunnel found');
        
        $ngrokService->getPublicUrl();
    }

    /**
     * Test handling of connection error
     */
    public function testGetPublicUrlConnectionError()
    {
        // Create a mock handler with a request exception
        $mock = new MockHandler([
            new RequestException('Connection error', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        // Create NgrokService with mocked client
        $ngrokService = new NgrokService();
        $this->setPrivateProperty($ngrokService, 'client', $client);

        // Expect exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Failed to connect to ngrok API');
        
        $ngrokService->getPublicUrl();
    }

    /**
     * Helper method to set private property value
     */
    private function setPrivateProperty($object, $propertyName, $value)
    {
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}