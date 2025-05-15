<?php

namespace CheckLaterBot;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

/**
 * Service for interacting with ngrok API to retrieve tunnel information
 */
class NgrokService
{
    /**
     * The ngrok API URL
     */
    private const NGROK_API_URL = 'http://localhost:4040/api/tunnels';
    
    /**
     * HTTP client for making requests
     */
    private Client $client;
    
    /**
     * Initialize the service with a Guzzle HTTP client
     */
    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 5, // 5 second timeout
        ]);
    }
    
    /**
     * Get the public HTTPS URL from the running ngrok tunnel
     *
     * @param string $path Optional path to append to the URL (e.g., '/webhook.php')
     * @return string The public HTTPS URL
     * @throws Exception If ngrok is not running or no HTTPS tunnel is found
     */
    public function getPublicUrl(string $path = ''): string
    {
        try {
            $response = $this->client->get(self::NGROK_API_URL);
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (!isset($data['tunnels']) || empty($data['tunnels'])) {
                throw new Exception('No active ngrok tunnels found. Make sure ngrok is running.');
            }
            
            // Find the HTTPS tunnel
            $httpsUrl = null;
            foreach ($data['tunnels'] as $tunnel) {
                if (isset($tunnel['public_url']) && strpos($tunnel['public_url'], 'https://') === 0) {
                    $httpsUrl = $tunnel['public_url'];
                    break;
                }
            }
            
            if ($httpsUrl === null) {
                throw new Exception('No HTTPS tunnel found in ngrok. Make sure you have an HTTPS tunnel configured.');
            }
            
            // Remove trailing slash if present
            $httpsUrl = rtrim($httpsUrl, '/');
            
            // Add path if provided (ensure it starts with a slash)
            if (!empty($path)) {
                $path = '/' . ltrim($path, '/');
                return $httpsUrl . $path;
            }
            
            return $httpsUrl;
        } catch (GuzzleException $e) {
            throw new Exception('Failed to connect to ngrok API: ' . $e->getMessage());
        }
    }
}