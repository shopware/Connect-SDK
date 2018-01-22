<?php

namespace Shopware\Connect\ShippingCosts;

use Shopware\Connect\Struct\Order;

abstract class RuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get valid order
     *
     * @return Order
     */
    protected function getValidOrder()
    {
        return new Order(
            [
                // @TODO: Fill order with values, as required
            ]
        );
    }
}
