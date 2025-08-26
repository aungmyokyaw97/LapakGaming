<?php

namespace Amk\LapakGaming\Traits;

use Illuminate\Support\Facades\Http;
use Amk\LapakGaming\Exceptions\LapakGamingException;

trait LapakGamingTrait
{
    /**
     * Make API call to LapakGaming.
     *
     * @param string $endpoint
     * @param array $data
     * @param string $method
     * @return object
     * @throws LapakGamingException
     */
    protected function makeApiCall(string $endpoint, array $data = [], string $method = 'GET')
    {
        $url = $this->buildUrl($endpoint);
        $headers = $this->buildHeaders();
        
        try {
            $response = $this->sendRequest($method, $url, $data, $headers);
            return $this->handleResponse($response);
        } catch (\Exception $e) {
            throw LapakGamingException::create('API request failed: ' . $e->getMessage());
        }
    }

    /**
     * Build full API URL.
     *
     * @param string $endpoint
     * @return string
     */
    private function buildUrl(string $endpoint): string
    {
        $path = $this->config['api_paths'][$endpoint] ?? '';
        return rtrim($this->baseUrl, '/') . $path;
    }

    /**
     * Build request headers.
     *
     * @return array
     */
    private function buildHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    /**
     * Send HTTP request.
     *
     * @param string $method
     * @param string $url
     * @param array $data
     * @param array $headers
     * @return \Illuminate\Http\Client\Response
     */
    private function sendRequest(string $method, string $url, array $data, array $headers)
    {
        $timeout = $this->config['timeout'] ?? 30;
        
        $http = Http::timeout($timeout)->withHeaders($headers);

        if ($method === 'POST') {
            return $http->post($url, $data);
        } elseif ($method === 'PUT') {
            return $http->put($url, $data);
        } else {
            return $http->get($url, $data);
        }
    }

    /**
     * Handle API response.
     *
     * @param \Illuminate\Http\Client\Response $response
     * @return object
     * @throws LapakGamingException
     */
    private function handleResponse($response)
    {
        // Handle HTTP errors
        if ($response->status() === 401) {
            throw LapakGamingException::unauthorized();
        }

        if ($response->status() === 429) {
            throw LapakGamingException::create('Too many requests. Please try again later.');
        }

        if ($response->status() >= 500) {
            throw LapakGamingException::create('Internal server error. Please try again later.');
        }

        if (!$response->successful()) {
            throw LapakGamingException::create('API request failed with status: ' . $response->status());
        }

        $data = $response->object();

        // Handle API-specific error codes
        if (isset($data->code)) {
            $this->handleApiErrorCode($data->code, $data);
        }

        return $data;
    }

    /**
     * Handle LapakGaming API specific error codes.
     *
     * @param string $code
     * @param object $data
     * @throws LapakGamingException
     */
    private function handleApiErrorCode(string $code, object $data)
    {
        switch ($code) {
            case 'SUCCESS':
                // No error, continue
                break;
                
            case 'UNAUTHORIZED':
                throw LapakGamingException::unauthorized();
                
            case 'PRODUCT_NOT_FOUND':
                throw LapakGamingException::productNotFound($this->productCode ?? '');
                
            case 'PRODUCT_EMPTY':
                throw LapakGamingException::create('Product is currently out of stock.');
                
            case 'PROVIDER_NOT_FOUND':
                throw LapakGamingException::create('Product provider not found.');
                
            case 'PRICE_NOT_MATCH':
                throw LapakGamingException::priceNotMatch();
                
            case 'PROVIDER_INACTIVE':
                throw LapakGamingException::create('Product provider is currently inactive.');
                
            case 'TID_NOT_FOUND':
                throw LapakGamingException::create('Transaction ID not found.');
                
            case 'USER_ID_CONTAIN_SPACE':
                throw LapakGamingException::create('User ID cannot contain spaces.');
                
            case 'STOCK_NOT_FOUND':
                throw LapakGamingException::create('Product stock not found.');
                
            case 'USER_ID_EMPTY':
                throw LapakGamingException::create('User ID cannot be empty.');
                
            case 'INSUFFICIENT_BALANCE':
                throw LapakGamingException::insufficientBalance();
                
            case 'SYSTEM_ERROR':
                throw LapakGamingException::create('System error occurred. Please try again later.');
                
            case 'UNKNOWN_ERROR':
                throw LapakGamingException::create('Unknown error occurred. Please contact support.');
                
            case 'NOT_ALLOWED':
                throw LapakGamingException::create('Operation not allowed.');
                
            default:
                if ($code !== 'SUCCESS') {
                    $message = isset($data->message) ? $data->message : 'Unknown API error';
                    throw LapakGamingException::apiError($code, $message);
                }
        }
    }

    /**
     * Retry API call on failure.
     *
     * @param callable $callback
     * @param int $maxAttempts
     * @return mixed
     * @throws LapakGamingException
     */
    protected function retryApiCall(callable $callback, int $maxAttempts = null)
    {
        $attempts = $maxAttempts ?: ($this->config['retry_attempts'] ?? 3);
        $lastException = null;

        for ($i = 0; $i < $attempts; $i++) {
            try {
                return $callback();
            } catch (LapakGamingException $e) {
                $lastException = $e;
                
                // Don't retry on certain errors
                if (in_array($e->getMessage(), [
                    'UNAUTHORIZED', 'PRODUCT_NOT_FOUND', 'USER_ID_EMPTY', 
                    'PRICE_NOT_MATCH', 'INSUFFICIENT_BALANCE'
                ])) {
                    throw $e;
                }

                if ($i < $attempts - 1) {
                    sleep(1); // Wait 1 second before retry
                }
            }
        }

        throw $lastException;
    }
}
