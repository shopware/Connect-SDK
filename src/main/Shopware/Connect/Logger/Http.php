<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Logger;

use Shopware\Connect\Logger;
use Shopware\Connect\HttpClient;
use Shopware\Connect\Struct;

/**
 * Base class for logger implementations
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */
class Http extends Logger
{
    /**
     * HTTP Client
     *
     * @var HttpClient
     */
    protected $httpClient;

    /**
     * API Key
     *
     * @var string
     */
    protected $apiKey;

    public function __construct(
        HttpClient $httpClient,
        $apiKey
    ) {
        $this->httpClient = $httpClient;
        $this->apiKey = $apiKey;
    }

    /**
     * Log order
     *
     * @param Struct\Order $order
     * @return void
     */
    protected function doLog(Struct\Order $order)
    {
        $hash = hash_hmac("sha256", $order->localOrderId . $order->orderShop . $order->providerShop, $this->apiKey);

        $response = $this->httpClient->request(
            'POST',
            '/transaction',
            json_encode($order),
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Order-Hash: ' . $hash
            )
        );

        if ($response->status >= 400) {
            $message = null;
            if ($error = json_decode($response->body)) {
                $message = $error->message;
            }
            throw new \RuntimeException("Logging failed: " . $message);
        }

        return json_decode($response->body);
    }

    /**
     * Confirm logging
     *
     * @param string $logTransactionId
     * @return void
     */
    public function confirm($logTransactionId)
    {
        $hash = hash_hmac("sha256", $logTransactionId, $this->apiKey);

        $response = $this->httpClient->request(
            'POST',
            '/transaction/confirm',
            json_encode($logTransactionId),
            array(
                'Content-Type: application/json',
                'X-Shopware-Connect-Order-Hash: ' . $hash
            )
        );

        if ($response->status >= 400) {
            $message = null;
            if ($error = json_decode($response->body)) {
                $message = $error->message;
            }
            throw new \RuntimeException("Logging confirmation failed: " . $message);
        }

        return;
    }
}
