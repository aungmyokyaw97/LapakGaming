<?php

namespace Amk\LapakGaming\Exceptions;

use Exception;

class LapakGamingException extends Exception
{
    /**
     * Create a general exception.
     *
     * @param string $message
     * @return static
     */
    public static function create($message)
    {
        return new static($message);
    }

    /**
     * Create a configuration error exception.
     *
     * @param string $key
     * @return static
     */
    public static function configError($key)
    {
        return new static('You must assign ' . $key . ' in config (lapakgaming.php) file.');
    }

    /**
     * Create an API error exception.
     *
     * @param string $code
     * @param string $message
     * @return static
     */
    public static function apiError($code, $message = '')
    {
        $errorMessage = "LapakGaming API Error: {$code}";
        if (!empty($message)) {
            $errorMessage .= " - {$message}";
        }
        return new static($errorMessage);
    }

    /**
     * Create an authentication error exception.
     *
     * @return static
     */
    public static function unauthorized()
    {
        return new static('Unauthorized: Invalid API Key or IP Address not whitelisted.');
    }

    /**
     * Create a product not found exception.
     *
     * @param string $productCode
     * @return static
     */
    public static function productNotFound($productCode = '')
    {
        $message = 'Product not found';
        if (!empty($productCode)) {
            $message .= ": {$productCode}";
        }
        return new static($message);
    }

    /**
     * Create an insufficient balance exception.
     *
     * @return static
     */
    public static function insufficientBalance()
    {
        return new static('Insufficient balance to complete this transaction.');
    }

    /**
     * Create a price mismatch exception.
     *
     * @return static
     */
    public static function priceNotMatch()
    {
        return new static('Price does not match the current product price.');
    }
}
