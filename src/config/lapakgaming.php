<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | LapakGaming API Configuration
    |--------------------------------------------------------------------------
    */
    
    'api_key' => env('LAPAKGAMING_API_KEY', ''), // Your API Secret Key
    
    'environment' => env('LAPAKGAMING_ENVIRONMENT', 'development'), // development or production
    
    'callback_url' => env('LAPAKGAMING_CALLBACK_URL', ''), // Your webhook callback URL (optional)
    
    /*
    |--------------------------------------------------------------------------
    | API Endpoints
    |--------------------------------------------------------------------------
    */
    
    'endpoints' => [
        'development' => 'https://dev.lapakgaming.com',
        'production' => 'https://www.lapakgaming.com',
    ],
    
    'api_paths' => [
        'categories' => '/api/get-categories',
        'products' => '/api/get-products',
        'all_products' => '/api/get-all-products', 
        'balance' => '/api/get-balance',
        'create_order' => '/api/create-order',
        'check_order' => '/api/check-order-status',
        'best_products' => '/api/get-best-products',
        'best_products_by_group' => '/api/get-best-products-by-group',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Request Settings
    |--------------------------------------------------------------------------
    */
    
    'timeout' => 30, // Request timeout in seconds
    'retry_attempts' => 3, // Number of retry attempts on failure
    
];
