<?php
/**
 * This file is part of the Bepado SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\ProductFromShop;

use Shopware\Connect\ProductFromShop;
use Shopware\Connect\Struct;

/**
 * Interface for product providers
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 * @api
 */
class Test implements ProductFromShop
{
    /**
     * Get product data
     *
     * Get product data for all the product IDs specified in the given string
     * array.
     *
     * @param string[] $ids
     * @return Struct\Product[]
     */
    public function getProducts(array $ids)
    {
        return array_map(
            function ($productId) {
                return new Struct\Product(
                    [
                        'sourceId' => (string) $productId,
                        'title' => 'Sindelfingen ' . microtime(),
                        'price' => $productId * .89,
                        'purchasePrice' => $productId * .89,
                        'currency' => 'EUR',
                        'availability' => $productId,
                        'categories' => ['/others'],
                        'vendor' => 'Foo',
                    ]
                );
            },
            $ids
        );
    }

    /**
     * Get all IDs of all exported products
     *
     * @return string[]
     */
    public function getExportedProductIDs()
    {
        return [];
    }

    /**
     * Calculate shipping costs for $order
     *
     * @param Struct\Order $order
     * @return Struct\Shipping
     */
    public function calculateShippingCosts(Struct\Order $order)
    {
        return new Struct\Shipping();
    }

    /**
     * Reserve a product in shop for purchase
     *
     * @param Struct\Order $order
     * @throws \Exception Abort reservation by throwing an exception here.
     * @return void
     */
    public function reserve(Struct\Order $order)
    {
        // Nothing
    }

    /**
     * Buy products mentioned in order
     *
     * Should return the internal order ID.
     *
     * @param Struct\Order $order
     * @throws \Exception Abort buy by throwing an exception,
     *                    but only in very important cases.
     *                    Do validation in {@see reserve} instead.
     * @return string
     */
    public function buy(Struct\Order $order)
    {
        // Nothing
    }

    /**
     * Update payment status of an order processed through bepado.
     *
     * An order can be identified with PaymentStatus#localOrderId
     * and the status be updated in your order locally, when
     * the payment is made in bepado.
     *
     * @param Struct\PaymentStatus $status
     * @return void
     */
    public function updatePaymentStatus(Struct\PaymentStatus $status)
    {
        // Nothing
    }

    public function onPerformSync($since, array $changes)
    {
        // Nothing
    }
}
