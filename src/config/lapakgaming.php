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
        'categories' => '/api/category',
        'products' => '/api/product',
        'all_products' => '/api/all-products', 
        'balance' => '/api/balance',
        'create_order' => '/api/order',
        'check_order' => '/api/order_status',
        'best_products' => '/api/catalogue/group-products',
        'best_products_by_group' => '/api/catalogue/group-products',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Request Settings
    |--------------------------------------------------------------------------
    */
    
    'timeout' => 30, // Request timeout in seconds
    'retry_attempts' => 3, // Number of retry attempts on failure
    
];
