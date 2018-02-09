<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\Struct\Order;
use Shopware\Connect\ShippingCosts\VatConfig;
use Shopware\Connect\Struct\Shipping;

class DownstreamChargesTest extends \PHPUnit_Framework_TestCase
{
    public function testShippingCostsAlwaysZero()
    {
        $rule = new DownstreamCharges();

        $this->assertEquals(
            new Shipping([
                'rule' => $rule,
                'deliveryWorkDays' => 10,
            ]),
            $rule->getShippingCosts(new Order(), new VatConfig())
        );
    }
}
