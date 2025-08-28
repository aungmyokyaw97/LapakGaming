<?php

namespace Amk\LapakGaming;

use Amk\LapakGaming\Exceptions\LapakGamingException;
use Amk\LapakGaming\Traits\LapakGamingTrait;

class LapakGaming 
{
    use LapakGamingTrait;

    protected $apiKey;
    protected $baseUrl;
    protected $config;
    protected $callbackUrl;
    
    // Order related properties
    protected $userId;
    protected $additionalId; // Zone ID or Server ID
    protected $additionalInformation; // Username ID
    protected $orderDetail; // Detailed Information for Topup Login Game Categories
    protected $countOrder; // Number of quantity of the order
    protected $productCode;
    protected $groupProduct;
    protected $countryCode;
    protected $price;
    protected $partnerReferenceId; // Idempotency identifier
    protected $overrideCallbackUrl;
    protected $transactionId;

    /**
     * Initialize LapakGaming instance.
     */
    public function __construct()
    {
        $this->config = config('lapakgaming');
        
        if (!$this->config) {
            throw LapakGamingException::create('You need to publish lapakgaming config file.');
        }

        $this->loadConfiguration();
    }

    /**
     * Load configuration data.
     */
    private function loadConfiguration()
    {
        $this->apiKey = $this->getConfigData('api_key');
        $this->callbackUrl = $this->getConfigData('callback_url');
        
        $environment = $this->getConfigData('environment', 'development');
        $this->baseUrl = $this->config['endpoints'][$environment] ?? $this->config['endpoints']['development'];
    }

    /**
     * Get configuration data with validation.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     * @throws LapakGamingException
     */
    private function getConfigData($key, $default = null)
    {
        $value = $this->config[$key] ?? $default;
        
        if (is_null($value) && is_null($default) && in_array($key, ['api_key'])) {
            throw LapakGamingException::configError($key);
        }
        
        return $value;
    }

    /**
     * Set product for order creation.
     *
     * @param string $productCode
     * @param float|null $price
     * @return $this
     */
    public function setProduct(string $productCode, $price = null)
    {
        $this->productCode = $productCode;
        $this->price = $price;
        return $this;
    }

    /**
     * Set user information for order.
     *
     * @param string $userId Game User ID
     * @param string|null $additionalId Zone ID or Server ID (optional)
     * @param string|null $additionalInformation Username ID (optional)
     * @return $this
     */
    public function setUser(string $userId, $additionalId = null, $additionalInformation = null)
    {
        $this->userId = $userId;
        $this->additionalId = $additionalId;
        $this->additionalInformation = $additionalInformation;
        return $this;
    }

    /**
     * Set order details for Topup Login Game Categories.
     *
     * @param string $orderDetail Detailed Information (e.g., "Password : 123 Nickname : nick ingame Security code : 1234")
     * @return $this
     */
    public function setOrderDetail(string $orderDetail)
    {
        $this->orderDetail = $orderDetail;
        return $this;
    }

    /**
     * Set order quantity.
     *
     * @param int $countOrder Number of quantity of the order
     * @return $this
     */
    public function setQuantity(int $countOrder)
    {
        $this->countOrder = $countOrder;
        return $this;
    }

    /**
     * Set partner reference ID for idempotency.
     *
     * @param string $partnerReferenceId Unique identifier to prevent duplicate orders
     * @return $this
     */
    public function setPartnerReferenceId(string $partnerReferenceId)
    {
        $this->partnerReferenceId = $partnerReferenceId;
        return $this;
    }

    /**
     * Set country code for order.
     * 
     * Supported country codes:
     * - id: Indonesia (default)
     * - my: Malaysia
     * - ph: Philippines  
     * - th: Thailand
     * - us: United States
     * - br: Brazil
     * - vn: Vietnam
     *
     * @param string $countryCode
     * @return $this
     */
    public function setCountryCode(string $countryCode)
    {
        $this->countryCode = strtolower($countryCode);
        return $this;
    }

    /**
     * Set transaction ID for order checking.
     *
     * @param string $transactionId
     * @return $this
     */
    public function setTransactionId(string $transactionId)
    {
        $this->transactionId = $transactionId;
        return $this;
    }

    /**
     * Set group product and country code for best products.
     * 
     * Supported country codes:
     * - id: Indonesia
     * - my: Malaysia
     * - ph: Philippines  
     * - th: Thailand
     * - us: United States
     * - br: Brazil
     * - vn: Vietnam
     *
     * @param string $groupProduct
     * @param string|null $countryCode Optional country code (only set if provided)
     * @return $this
     */
    public function setGroupProduct(string $groupProduct, string $countryCode = null)
    {
        $this->groupProduct = $groupProduct;
        if ($countryCode !== null) {
            $this->countryCode = strtolower($countryCode); // Only set if provided
        }
        return $this;
    }

    /**
     * Override callback URL for specific order.
     *
     * @param string $callbackUrl
     * @return $this
     */
    public function setCallbackUrl(string $callbackUrl)
    {
        $this->overrideCallbackUrl = $callbackUrl;
        return $this;
    }

    /**
     * Get all categories.
     *
     * @return object
     */
    public function getCategories()
    {
        return $this->makeApiCall('categories');
    }

    /**
     * Get products by category code.
     *
     * @param string $categoryCode Category code (e.g., 'mobile-legends', 'VAL')
     * @param string|null $countryCode Country code (e.g., 'id', 'my', 'ph') - optional
     * @return object
     */
    public function getProductsByCategory(string $categoryCode, string $countryCode = null)
    {
        $data = ['category_code' => $categoryCode];
        
        if ($countryCode) {
            $data['country_code'] = strtolower($countryCode);
        }

        return $this->makeApiCall('products', $data);
    }

    /**
     * Get products by product code.
     *
     * @param string $productCode Product code (e.g., 'VAL1650-S14', 'ML78_8-S2')
     * @param string|null $countryCode Country code (e.g., 'id', 'my', 'ph') - optional
     * @return object
     */
    public function getProductsByCode(string $productCode, string $countryCode = null)
    {
        $data = ['product_code' => $productCode];
        
        // Only add country_code if explicitly provided
        if ($countryCode) {
            $data['country_code'] = strtolower($countryCode);
        }

        return $this->makeApiCall('products', $data);
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use getProductsByCode() instead
     */
    public function getProduct(string $productCode, string $countryCode = null)
    {
        return $this->getProductsByCode($productCode, $countryCode);
    }

    /**
     * Get products with both category and product filters.
     *
     * @param string $categoryCode Category code
     * @param string $productCode Product code
     * @param string|null $countryCode Country code - optional
     * @return object
     */
    public function getProductsByCategoryAndCode(string $categoryCode, string $productCode, string $countryCode = null)
    {
        $data = [
            'category_code' => $categoryCode,
            'product_code' => $productCode
        ];
        
        if ($countryCode) {
            $data['country_code'] = strtolower($countryCode);
        }

        return $this->makeApiCall('products', $data);
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use getProductsByCategory() instead
     */
    public function getProducts(string $categoryCode)
    {
        return $this->getProductsByCategory($categoryCode);
    }

    /**
     * Get all products.
     *
     * @return object
     */
    public function getAllProducts()
    {
        return $this->makeApiCall('all_products');
    }

    /**
     * Get reseller balance.
     *
     * @return object
     */
    public function getBalance()
    {
        return $this->makeApiCall('balance');
    }

    /**
     * Create a new order.
     *
     * @return object
     * @throws LapakGamingException
     */
    public function createOrder()
    {
        // Validate required parameters
        if (!$this->userId) {
            throw LapakGamingException::create('User ID is required for order creation.');
        }

        if (!$this->productCode && !$this->groupProduct) {
            throw LapakGamingException::create('Either product_code or group_product is required for order creation.');
        }

        // Build order data
        $data = [
            'user_id' => $this->userId,
            'count_order' => $this->countOrder ?: 1, // Default quantity is 1
        ];

        // Required: Either product_code or group_product
        if ($this->productCode) {
            $data['product_code'] = $this->productCode;
        }
        
        if ($this->groupProduct) {
            $data['group_product'] = $this->groupProduct;
        }

        // Optional parameters
        if ($this->additionalId) $data['additional_id'] = $this->additionalId;
        if ($this->additionalInformation) $data['additional_information'] = $this->additionalInformation;
        if ($this->orderDetail) $data['orderdetail'] = $this->orderDetail;
        if ($this->countryCode) $data['country_code'] = $this->countryCode;
        if ($this->price) $data['price'] = $this->price;
        if ($this->partnerReferenceId) $data['partner_reference_id'] = $this->partnerReferenceId;
        if ($this->overrideCallbackUrl) $data['override_callback_url'] = $this->overrideCallbackUrl;

        return $this->makeApiCall('create_order', $data, 'POST');
    }

    /**
     * Check order status by transaction ID.
     * API: https://dev.lapakgaming.com/api/order_status?tid=RA171341142175668140
     *
     * @param string $transactionId Transaction ID (tid)
     * @return object
     */
    public function checkOrderStatusByTid(string $transactionId)
    {
        $data = ['tid' => $transactionId];
        return $this->makeApiCall('check_order', $data);
    }

    /**
     * Check order status by partner reference ID.
     * API: https://dev.lapakgaming.com/api/order_status?partner_reference_id=R123
     *
     * @param string $partnerReferenceId Partner reference ID
     * @return object
     */
    public function checkOrderStatusByRefId(string $partnerReferenceId)
    {
        $data = ['partner_reference_id' => $partnerReferenceId];
        return $this->makeApiCall('check_order', $data);
    }

    /**
     * Check order status with both transaction ID and partner reference ID.
     * API: https://dev.lapakgaming.com/api/order_status?tid=RA171341142175668140&partner_reference_id=R123
     *
     * @param string $transactionId Transaction ID (tid)
     * @param string $partnerReferenceId Partner reference ID
     * @return object
     */
    public function checkOrderStatusByTidAndRefId(string $transactionId, string $partnerReferenceId)
    {
        $data = [
            'tid' => $transactionId,
            'partner_reference_id' => $partnerReferenceId
        ];
        return $this->makeApiCall('check_order', $data);
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use checkOrderStatusByRefId() instead
     */
    public function checkOrderStatusByReferenceId(string $partnerReferenceId)
    {
        return $this->checkOrderStatusByRefId($partnerReferenceId);
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use checkOrderStatusByTidAndRefId() instead
     */
    public function checkOrderStatusByTidAndReferenceId(string $transactionId, string $partnerReferenceId)
    {
        return $this->checkOrderStatusByTidAndRefId($transactionId, $partnerReferenceId);
    }

    /**
     * Check order status with flexible parameters.
     * API: https://dev.lapakgaming.com/api/order_status?tid=RA171341142175668140&partner_reference_id=R123
     *
     * @param string|null $transactionId Transaction ID (tid) - optional
     * @param string|null $partnerReferenceId Partner reference ID - optional
     * @return object
     * @throws LapakGamingException If neither parameter is provided
     */
    public function checkOrderStatusBy(string $transactionId = null, string $partnerReferenceId = null)
    {
        if (!$transactionId && !$partnerReferenceId) {
            throw LapakGamingException::create('Either transaction ID (tid) or partner reference ID is required to check order status.');
        }

        $data = [];
        
        if ($transactionId) {
            $data['tid'] = $transactionId;
        }
        
        if ($partnerReferenceId) {
            $data['partner_reference_id'] = $partnerReferenceId;
        }

        return $this->makeApiCall('check_order', $data);
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use checkOrderStatusByTid() instead
     */
    public function checkOrderStatus($transactionId = null)
    {
        $tid = $transactionId ?: $this->transactionId;
        
        if (!$tid) {
            throw LapakGamingException::create('Transaction ID is required. Use checkOrderStatusByTid() or checkOrderStatusBy() instead.');
        }

        return $this->checkOrderStatusByTid($tid);
    }

    /**
     * Get best products by category code.
     * API: https://dev.lapakgaming.com/api/catalogue/group-products?category_code=ML&country_code=id
     *
     * @param string $categoryCode Category code (e.g., 'ML', 'VAL') - required
     * @param string|null $countryCode Country code (e.g., 'id', 'my', 'ph') - optional
     * @return object
     */
    public function getBestProductsByCategory(string $categoryCode, string $countryCode = null)
    {
        $data = ['category_code' => $categoryCode];
        
        // Only add country_code if explicitly provided
        if ($countryCode) {
            $data['country_code'] = strtolower($countryCode);
        }

        return $this->makeApiCall('best_products', $data);
    }

    /**
     * Get best products by group product code.
     * API: https://dev.lapakgaming.com/api/catalogue/group-products?group_product_code=ML1288_166&country_code=id
     *
     * @param string $groupProductCode Group product code (e.g., 'ML1288_166') - required
     * @param string|null $countryCode Country code (e.g., 'id', 'my', 'ph') - optional
     * @return object
     */
    public function getBestProductsByGroupCode(string $groupProductCode, string $countryCode = null)
    {
        $data = ['group_product_code' => $groupProductCode];
        
        // Only add country_code if explicitly provided
        if ($countryCode) {
            $data['country_code'] = strtolower($countryCode);
        }

        return $this->makeApiCall('best_products', $data);
    }

    /**
     * Get best products with both category and group product filters.
     * API: https://dev.lapakgaming.com/api/catalogue/group-products?category_code=ML&group_product_code=ML1288_166&country_code=id
     *
     * @param string $categoryCode Category code - required
     * @param string $groupProductCode Group product code - required
     * @param string|null $countryCode Country code - optional
     * @return object
     */
    public function getBestProductsByCategoryAndGroupCode(string $categoryCode, string $groupProductCode, string $countryCode = null)
    {
        $data = [
            'category_code' => $categoryCode,
            'group_product_code' => $groupProductCode
        ];
        
        // Only add country_code if explicitly provided
        if ($countryCode) {
            $data['country_code'] = strtolower($countryCode);
        }

        return $this->makeApiCall('best_products', $data);
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use getBestProductsByCategory() instead
     */
    public function getBestProducts()
    {
        throw LapakGamingException::create('getBestProducts() requires parameters. Use getBestProductsByCategory() or getBestProductsByGroupCode() instead.');
    }

    /**
     * Legacy method for backward compatibility.
     * @deprecated Use getBestProductsByGroupCode() instead
     */
    public function getBestProductsByGroup($groupProduct = null, $countryCode = null)
    {
        if (!$groupProduct) {
            throw LapakGamingException::create('Group product code is required. Use getBestProductsByGroupCode() instead.');
        }
        return $this->getBestProductsByGroupCode($groupProduct, $countryCode);
    }
}
