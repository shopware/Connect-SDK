<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct\Change\ToShop;

/**
 * Represents a change in purchase price.
 */
class UpdateOrderStatus extends ToShopChange
{
    /**
     * the local order number
     * @var string
     */
    public $localOrderId;

    /**
     * the tracking numbers to that order
     * @var string
     */
    public $trackingNumber;

    /**
     * the combined Status of all sub-orders
     * @var string
     */
    public $orderStatus;
}
