# LapakGaming Reseller API

Laravel package for [LapakGaming](https://www.lapakgaming.com/) Reseller API integration.

## About

This package provides a simple and efficient method to integrate with LapakGaming Reseller API using Laravel. It supports all major operations including product management, order creation, and balance checking.

## Installation

```shell
composer require aungmyokyaw/lapakgaming
```

## Configuration

You will need to publish the configuration file to your application:

```shell
php artisan vendor:publish --tag="lapakgaming-config"
```

After publishing, you will find the configuration file at `config/lapakgaming.php`. Fill out the necessary data:

- `api_key`: Your LapakGaming API Secret Key
- `environment`: 'development' or 'production'
- `callback_url`: Your webhook callback URL (optional)

## Usage Examples

### Get Categories
```php
$categories = LapakGaming::getCategories();
```

### Get Products

#### Get Products by Category
```php
// Get all products in a category
$products = LapakGaming::getProductsByCategory('VAL');

// Get products in category for specific country
$products = LapakGaming::getProductsByCategory('VAL', 'id');
```

#### Get Products by Product Code
```php
// Get products by product code
$products = LapakGaming::getProductsByCode('VAL1650-S14');

// Get products by code for specific country
$products = LapakGaming::getProductsByCode('VAL1650-S14', 'my');
```

#### Get Products with Both Filters
```php
// Filter by both category and product code
$products = LapakGaming::getProductsByCategoryAndCode('VAL', 'VAL1650-S14', 'id');
```

### Get All Products
```php
$allProducts = LapakGaming::getAllProducts();
```

### Check Balance
```php
$balance = LapakGaming::getBalance();
```

### Create Order

#### Basic Order (using product_code)
```php
// Quantity defaults to 1 if not specified
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789', '2345', 'username123')
                   ->createOrder();

// Or explicitly set quantity
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789', '2345', 'username123')
                   ->setQuantity(2)
                   ->createOrder();
```

#### Advanced Order with All Parameters
```php
$order = LapakGaming::setProduct('ML78_8-S2', 15000)
                   ->setUser('123456789', '2345', 'username123')
                   ->setQuantity(1)
                   ->setCountryCode('id') // Set country code for order
                   ->setOrderDetail('Password : 123 Nickname : nick ingame Security code : 1234')
                   ->setPartnerReferenceId('unique-ref-123')
                   ->setCallbackUrl('https://yoursite.com/custom-callback')
                   ->createOrder();
```

#### Order using Group Product
```php
// Without country code - API will use LapakGaming's default
$order = LapakGaming::setGroupProduct('mobile-legends')
                   ->setUser('123456789', '2345')
                   ->createOrder(); // Quantity defaults to 1

// With country code via setGroupProduct()
$order = LapakGaming::setGroupProduct('mobile-legends', 'id')
                   ->setUser('123456789', '2345')
                   ->createOrder();

// Or set country code separately
$order = LapakGaming::setGroupProduct('mobile-legends')
                   ->setCountryCode('my') // Malaysia
                   ->setUser('123456789', '2345')
                   ->setQuantity(2)
                   ->createOrder();
```

### Check Order Status

#### Check Status by Transaction ID
```php
// Check by transaction ID (tid)
$status = LapakGaming::checkOrderStatusByTid('RA171341142175668140');
```

#### Check Status by Partner Reference ID
```php
// Check by partner reference ID
$status = LapakGaming::checkOrderStatusByRefId('R123');
```

#### Check Status with Both Parameters
```php
// Check with both transaction ID and partner reference ID
$status = LapakGaming::checkOrderStatusByTidAndRefId('RA171341142175668140', 'R123');
```

#### Check Status with Flexible Parameters
```php
// Check by transaction ID only
$status = LapakGaming::checkOrderStatusBy('RA171341142175668140');

// Check by partner reference ID only
$status = LapakGaming::checkOrderStatusBy(null, 'R123');

// Check by both
$status = LapakGaming::checkOrderStatusBy('RA171341142175668140', 'R123');
```

### Get Best Products

#### Get Best Products by Category
```php
// Get best products by category
$bestProducts = LapakGaming::getBestProductsByCategory('ML');

// Get best products by category for specific country
$bestProducts = LapakGaming::getBestProductsByCategory('ML', 'id');
```

#### Get Best Products by Group Product Code
```php
// Get best products by group product code
$bestProducts = LapakGaming::getBestProductsByGroupCode('ML1288_166');

// Get best products by group code for specific country
$bestProducts = LapakGaming::getBestProductsByGroupCode('ML1288_166', 'my');
```

#### Get Best Products with Both Filters
```php
// Filter by both category and group product code
$bestProducts = LapakGaming::getBestProductsByCategoryAndGroupCode('ML', 'ML1288_166', 'id');
```

## Supported Country Codes

When using group products or country-specific operations, use these country codes:

- `id` - Indonesia (default)
- `my` - Malaysia  
- `ph` - Philippines
- `th` - Thailand
- `us` - United States
- `br` - Brazil
- `vn` - Vietnam

## Order Parameters

### Required Parameters
- **user_id**: Game User ID
- **count_order**: Number of quantity of the order (use `setQuantity()`)
- **product_code** OR **group_product**: Either product code or group product must be provided

### Optional Parameters
- **additional_id**: Zone ID or Server ID (second parameter in `setUser()`)
- **additional_information**: Username ID (third parameter in `setUser()`)
- **orderdetail**: Detailed information for Topup Login Game Categories (use `setOrderDetail()`)
- **country_code**: Country specification (use `setCountryCode()` or `setGroupProduct()`)
- **price**: Price validation during order process (use `setProduct()`)
- **partner_reference_id**: Idempotency identifier (use `setPartnerReferenceId()`)
- **override_callback_url**: Custom callback URL (use `setCallbackUrl()`)

### Default Values
- **count_order**: Defaults to 1 if `setQuantity()` is not called
- **country_code**: Only sent to API if explicitly set via `setCountryCode()` or `setGroupProduct()`

## License

MIT License
