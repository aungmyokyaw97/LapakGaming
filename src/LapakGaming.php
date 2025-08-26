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
     * Get products by category.
     *
     * @param string $category
     * @return object
     */
    public function getProducts(string $category)
    {
        return $this->makeApiCall('products', ['category' => $category]);
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
     * Check order status.
     *
     * @param string|null $transactionId
     * @return object
     * @throws LapakGamingException
     */
    public function checkOrderStatus($transactionId = null)
    {
        $tid = $transactionId ?: $this->transactionId;
        
        if (!$tid) {
            throw LapakGamingException::create('Transaction ID is required to check order status.');
        }

        return $this->makeApiCall('check_order', ['transaction_id' => $tid]);
    }

    /**
     * Get best products list.
     *
     * @return object
     */
    public function getBestProducts()
    {
        return $this->makeApiCall('best_products');
    }

    /**
     * Get best products by group product.
     *
     * @param string|null $groupProduct
     * @param string|null $countryCode
     * @return object
     * @throws LapakGamingException
     */
    public function getBestProductsByGroup($groupProduct = null, $countryCode = null)
    {
        $group = $groupProduct ?: $this->groupProduct;
        $country = $countryCode ?: $this->countryCode;
        
        if (!$group) {
            throw LapakGamingException::create('Group product is required.');
        }

        $data = ['group_product' => $group];
        
        // Only add country_code if it's been set
        if ($country) {
            $data['country_code'] = strtolower($country);
        }

        return $this->makeApiCall('best_products_by_group', $data);
    }
}
