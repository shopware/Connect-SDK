<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\Struct;
use Shopware\Connect\ShippingCosts\VatConfig;

class UnitPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_multiplies_price_by_unit()
    {
        $rule = new UnitPrice([
            'price' => 10,
        ]);

        $this->assertEquals(
            new Struct\Shipping([
                'rule' => $rule,
                'shippingCosts' => 50.,
                'grossShippingCosts' => 55.,
                'deliveryWorkDays' => 10,
            ]),
            $rule->getShippingCosts(
                new Struct\Order([
                    'orderItems' => [
                        new Struct\OrderItem([
                            'count' => 2,
                            'product' => new Struct\Product([
                                'purchasePrice' => 20,
                                'vat' => 0.19
                            ])
                        ]),
                        new Struct\OrderItem([
                            'count' => 3,
                            'product' => new Struct\Product([
                                'purchasePrice' => 20,
                                'vat' => 0.19
                            ])
                        ]),
                    ]
                ]),
                new VatConfig([
                    'vat' => 0.1,
                    'isNet' => true,
                ])
            ),
            'Wrong shipping costs',
            0.01
        );
    }

    /**
     * @test
     */
    public function it_multiplies_price_by_unit_gross_shipping_costs()
    {
        $rule = new UnitPrice([
            'price' => 10,
        ]);

        $this->assertEquals(
            new Struct\Shipping([
                'rule' => $rule,
                'shippingCosts' => 18.18,
                'grossShippingCosts' => 20.,
                'deliveryWorkDays' => 10,
            ]),
            $rule->getShippingCosts(
                new Struct\Order([
                    'orderItems' => [
                        new Struct\OrderItem([
                            'count' => 2,
                            'product' => new Struct\Product([
                                'purchasePrice' => 20,
                                'vat' => 0.19
                            ])
                        ]),
                    ]
                ]),
                new VatConfig([
                    'vat' => 0.1,
                    'isNet' => false,
                ])
            ),
            'Wrong shipping costs',
            0.01
        );
    }
}
