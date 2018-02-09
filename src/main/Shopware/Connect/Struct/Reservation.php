<?php
/**
 * This file is part of the Shopware Connect SDK Component.
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 */

namespace Shopware\Connect\Struct;

use Shopware\Connect\Struct;

/**
 * Struct class representing a multi-shop reservation
 *
 * The SDK is licensed under MIT license. (c) Shopware AG and Qafoo GmbH
 * @api
 */
class Reservation extends Struct
{
    /**
     * Indicator if reservation failed or not
     *
     * @var bool
     */
    public $success = false;

    /**
     * Messages from shops, where the reservation failed.
     *
     * @var array
     */
    public $messages = [];

    /**
     * Orders per shop
     *
     * @var Struct\Order[]
     */
    public $orders = [];

    /**
     * Overall shipping costs for the reservation
     *
     * @var Struct\Shipping
     */
    public $aggregatedShippingCosts;
}
