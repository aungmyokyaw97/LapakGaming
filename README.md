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

### Check Balance
```php
$balance = LapakGaming::getBalance();
```

### Create Order

## Order Parameters Explained

Based on LapakGaming API documentation, here are all the parameters you can use when creating orders:

### Required Parameters

#### **Product Identification** (Choose One)
- **product_code**: Specific product code from `getProductsByCategory()` or `getProductsByCode()`
- **group_product**: Group product code from `getBestProductsByCategory()` or `getBestProductsByGroupCode()`

#### **Order Quantity**
- **count_order**: Number of items to order (defaults to 1)

### Optional Parameters

#### **User Information** (Game-specific)
- **user_id**: Game User ID (required for most games, optional for vouchers)
- **additional_id**: Zone ID or Server ID (required for some games)
- **additional_information**: Username ID (required for some games)

#### **Login Game Details**
- **orderdetail**: Detailed information for games requiring login credentials
  - Example: "Password : 123 Nickname : nick ingame Security code : 1234"

#### **Validation & Control**
- **price**: Price validation to prevent order if price changed
- **country_code**: Country specification (optional, default: 'id')
- **partner_reference_id**: Unique identifier to prevent duplicate orders
- **override_callback_url**: Custom callback URL for this order

## Order Examples

### Basic Order with Product Code
```php
// Simplest order - quantity defaults to 1
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789') // Game User ID
                   ->createOrder();
```

### Order with Zone/Server Information
```php
// For games requiring zone or server ID
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789', '2345') // User ID + Zone/Server ID
                   ->createOrder();
```

### Order with Complete Additional Information
```php
// For games requiring username
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789', '2345', 'additional_information') // User ID + Zone + Username
                   ->createOrder();
```

### Order with Price Validation
```php
// Validate price to prevent order if price changed
$order = LapakGaming::setProduct('ML78_8-S2', 15000) // Product code + expected price
                   ->setUser('123456789', '2345', 'additional_information')
                   ->createOrder();
```

### Order with Custom Quantity
```php
// Order multiple items
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789')
                   ->setQuantity(5) // Order 5 items
                   ->createOrder();
```

### Order with Country Code
```php
// Specify country for the order
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789')
                   ->setCountryCode('my') // Malaysia
                   ->createOrder();
```

### Order with Login Game Details
```php
// For games requiring login credentials
$order = LapakGaming::setProduct('LOGIN_GAME_PRODUCT')
                   ->setUser('123456789')
                   ->setOrderDetail('Password : mypass123 Nickname : PlayerOne Security code : 9876')
                   ->createOrder();
```

### Order with Idempotency Protection
```php
// Prevent duplicate orders with unique reference ID
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789')
                   ->setPartnerReferenceId('order-' . time()) // Unique reference
                   ->createOrder();
```

### Order with Custom Callback URL
```php
// Override default callback URL for this order
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789')
                   ->setCallbackUrl('https://yoursite.com/custom-callback') // Custom callback
                   ->createOrder();
```

### Order using Group Product
```php
// Order using group product instead of specific product code
$order = LapakGaming::setGroupProduct('mobile-legends')
                   ->setUser('123456789', '2345')
                   ->setCountryCode('id') // Indonesia
                   ->createOrder();
```

### Complete Advanced Order
```php
// Order with all possible parameters
$order = LapakGaming::setProduct('ML78_8-S2', 15000) // Product + price validation
                   ->setUser('123456789', '2345', 'additional_information') // Complete user info
                   ->setQuantity(2) // Multiple items
                   ->setCountryCode('my') // Malaysia
                   ->setOrderDetail('Password : 123 Nickname : nick Security code : 1234') // Login details
                   ->setPartnerReferenceId('unique-order-' . time()) // Idempotency
                   ->setCallbackUrl('https://yoursite.com/lapakgaming/callback') // Custom callback
                   ->createOrder();
```

### Voucher Orders
```php
// For voucher products (user_id often not required)
$order = LapakGaming::setProduct('VOUCHER_CODE')
                   ->setQuantity(1)
                   ->createOrder();
```

## Method Chaining Guide

You can chain methods in any order, but `createOrder()` must be called last:

```php
// All these are equivalent
$order = LapakGaming::setProduct('ML78_8-S2')
                   ->setUser('123456789')
                   ->setQuantity(2)
                   ->createOrder();

$order = LapakGaming::setQuantity(2)
                   ->setProduct('ML78_8-S2')
                   ->setUser('123456789')
                   ->createOrder();

$order = LapakGaming::setUser('123456789')
                   ->setQuantity(2)
                   ->setProduct('ML78_8-S2')
                   ->createOrder();
```

## Parameter Usage Summary

| Method | Parameter | Required | Example |
|--------|-----------|----------|---------|
| `setProduct()` | product_code, price | product_code required | `setProduct('ML78_8-S2', 15000)` |
| `setGroupProduct()` | group_product, country | group_product required | `setGroupProduct('mobile-legends', 'id')` |
| `setUser()` | user_id, additional_id, additional_info | user_id for most games | `setUser('123456789', '2345', 'additional_information')` |
| `setQuantity()` | count_order | optional (default: 1) | `setQuantity(5)` |
| `setCountryCode()` | country_code | optional | `setCountryCode('my')` |
| `setOrderDetail()` | orderdetail | optional | `setOrderDetail('Password : 123')` |
| `setPartnerReferenceId()` | partner_reference_id | optional | `setPartnerReferenceId('unique-123')` |
| `setCallbackUrl()` | override_callback_url | optional | `setCallbackUrl('https://site.com/cb')` |

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
- **product_code** OR **group_product**: Either product code or group product must be provided

### Optional Parameters
- **additional_id**: Zone ID or Server ID (second parameter in `setUser()`)
- **additional_information**: Username ID (third parameter in `setUser()`)
- **count_order**: Number of quantity of the order (use `setQuantity()`)
- **orderdetail**: Detailed information for Topup Login Game Categories (use `setOrderDetail()`)
- **country_code**: Country specification (use `setCountryCode()` or `setGroupProduct()`)
- **price**: Price validation during order process (use `setProduct()`)
- **partner_reference_id**: Idempotency identifier (use `setPartnerReferenceId()`)
- **override_callback_url**: Custom callback URL (use `setCallbackUrl()`)

### Default Values
- **count_order**: Defaults to 1 if `setQuantity()` is not called

## License

MIT License
