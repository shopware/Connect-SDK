<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\Struct;
use Shopware\Connect\ShippingCosts\VatConfig;

class StaggerPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Get stagger price configurations
     *
     * @return array
     */
    public function getStaggerPriceConfigurations()
    {
        return [
            [
                new StaggerPrice([
                    'default' => 10,
                ]),
                10.
            ],
            [
                new StaggerPrice([
                    'default' => 10,
                    'staggerPrices' => [
                        20 => 20,
                    ]
                ]),
                20.
            ],
            [
                new StaggerPrice([
                    'default' => 10,
                    'staggerPrices' => [
                        20 => 20,
                        50 => 50,
                    ]
                ]),
                50.
            ],
            [
                new StaggerPrice([
                    'default' => 10,
                    'staggerPrices' => [
                        50 => 50,
                        20 => 20,
                    ]
                ]),
                50.
            ],
            [
                new StaggerPrice([
                    'default' => 10,
                    'staggerPrices' => [
                        '8.65' => 20,
                        '54.7' => 50,
                    ]
                ]),
                50.
            ],
        ];
    }

    /**
     * @dataProvider getStaggerPriceConfigurations
     */
    public function testStaggerPriceConfigurations($rule, $value)
    {
        $this->assertEquals(
            new Struct\Shipping([
                'rule' => $rule,
                'shippingCosts' => $value,
                'grossShippingCosts' => $value * 1.1,
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
}
