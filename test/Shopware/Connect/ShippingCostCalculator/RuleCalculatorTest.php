<?php

namespace Shopware\Connect\ShippingCostCalculator;

use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\ShippingCosts\Rules;
use Shopware\Connect\ShippingCosts\VatConfig;

class RuleCalculatorTest extends \PHPUnit_Framework_TestCase
{
    private $calculator;

    public function setUp()
    {
        $this->calculator = new RuleCalculator();
    }

    public function testCalculateMixedVatUsesDominantStrategy()
    {
        $result = $this->calculator->calculateShippingCosts(
            new Rules(
                [
                    'vatMode' => Rules::VAT_DOMINATING,
                    'rules' => [
                        new Rule\FixedPrice(
                            [
                                'price' => 10,
                            ]
                        )
                    ]
                ]
            ),
            new \Shopware\Connect\Struct\Order(
                [
                    'orderShop' => 2,
                    'providerShop' => 1,
                    'products' => [
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 20,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'freeDelivery' => false,
                                        'vat' => 0.07,
                                        'price' => 5,
                                    ]
                                ),
                            ]
                        ),
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'freeDelivery' => false,
                                        'vat' => 0.19,
                                        'price' => 10,
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            ),
            'test'
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result);
        $this->assertEquals(10, $result->shippingCosts);
        $this->assertEquals(10.7, $result->grossShippingCosts);
    }

    public function testCalculateWithMixedVatProductsUsesMaxVat()
    {
        $result = $this->calculator->calculateShippingCosts(
            new Rules(
                [
                    'vatMode' => Rules::VAT_MAX,
                    'rules' => [
                        new Rule\FixedPrice(
                            [
                                'price' => 10,
                            ]
                        )
                    ]
                ]
            ),
            new \Shopware\Connect\Struct\Order(
                [
                    'orderShop' => 2,
                    'providerShop' => 1,
                    'products' => [
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'freeDelivery' => false,
                                        'vat' => 0.07,
                                    ]
                                ),
                            ]
                        ),
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'freeDelivery' => false,
                                        'vat' => 0.19,
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            ),
            'test'
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result);
        $this->assertEquals(10, $result->shippingCosts);
        $this->assertEquals(11.9, $result->grossShippingCosts);
    }

    public function testCalculateVatModeFixedVat()
    {
        $result = $this->calculator->calculateShippingCosts(
            new Rules(
                [
                    'vatMode' => Rules::VAT_FIX,
                    'vat' => 0.07,
                    'rules' => [
                        new Rule\FixedPrice(
                            [
                                'price' => 10,
                            ]
                        )
                    ]
                ]
            ),
            new \Shopware\Connect\Struct\Order(
                [
                    'orderShop' => 2,
                    'providerShop' => 1,
                    'products' => [
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'freeDelivery' => false,
                                        'vat' => 0.19,
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            ),
            'test'
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result);
        $this->assertEquals(10, $result->shippingCosts);
        $this->assertEquals(10.7, $result->grossShippingCosts);
    }

    public function testCalculateVatModeProporitionately()
    {
        $result = $this->calculator->calculateShippingCosts(
            new Rules(
                [
                    'vatMode' => Rules::VAT_PROPORTIONATELY,
                    'rules' => [
                        new Rule\FixedPrice(
                            [
                                'price' => 10,
                            ]
                        )
                    ]
                ]
            ),
            new \Shopware\Connect\Struct\Order(
                [
                    'orderShop' => 2,
                    'providerShop' => 1,
                    'products' => [
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'vat' => 0.19,
                                        'purchasePrice' => 100,
                                    ]
                                ),
                            ]
                        ),
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'freeDelivery' => false,
                                        'purchasePrice' => 10,
                                        'vat' => 0.07,
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            ),
            'test'
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result);
        $this->assertEquals(10, $result->shippingCosts);
        $this->assertEquals(11.790909090909, $result->grossShippingCosts);
    }

    public function testCalculateVatModeProporitionatelySingleOrderItem()
    {
        $result = $this->calculator->calculateShippingCosts(
            new Rules(
                [
                    'vatMode' => Rules::VAT_PROPORTIONATELY,
                    'rules' => [
                        new Rule\FixedPrice(
                            [
                                'price' => 10,
                            ]
                        )
                    ]
                ]
            ),
            new \Shopware\Connect\Struct\Order(
                [
                    'orderShop' => 2,
                    'providerShop' => 1,
                    'products' => [
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'vat' => 0.19,
                                        'purchasePrice' => 100,
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            ),
            'test'
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result);
        $this->assertEquals(10, $result->shippingCosts);
        $this->assertEquals(11.9, $result->grossShippingCosts);
    }

    public function testFindMinimumShippingCostsRule()
    {
        $result = $this->calculator->calculateShippingCosts(
            new Rules(
                [
                    'vatMode' => Rules::VAT_FIX,
                    'vat' => 0.07,
                    'rules' => [
                        new Rule\FixedPrice(
                            [
                                'price' => 10,
                            ]
                        ),
                        new Rule\FixedPrice(
                            [
                                'price' => 5,
                            ]
                        )
                    ]
                ]
            ),
            new \Shopware\Connect\Struct\Order(
                [
                    'orderShop' => 2,
                    'providerShop' => 1,
                    'products' => [
                        new \Shopware\Connect\Struct\OrderItem(
                            [
                                'count' => 1,
                                'product' => new \Shopware\Connect\Struct\Product(
                                    [
                                        'shopId' => 1,
                                        'freeDelivery' => false,
                                        'vat' => 0.19,
                                    ]
                                ),
                            ]
                        ),
                    ],
                ]
            ),
            'test'
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result);
        $this->assertEquals(5, $result->shippingCosts);
        $this->assertEquals(5.35, $result->grossShippingCosts);
    }

    public function testGrossShippingCosts()
    {
        $result = $this->calculator->calculateShippingCosts(
            new Rules([
                'vatConfig' => new VatConfig([
                    'mode' => Rules::VAT_FIX,
                    'vat' => 0.07,
                    'isNet' => false,
                ]),
                'rules' => [
                    new Rule\FixedPrice([
                        'price' => 10,
                    ]),
                ]
            ]),
            new \Shopware\Connect\Struct\Order([
                'orderShop' => 2,
                'providerShop' => 1,
                'products' => [
                    new \Shopware\Connect\Struct\OrderItem([
                        'count' => 1,
                        'product' => new \Shopware\Connect\Struct\Product([
                            'shopId' => 1,
                            'freeDelivery' => false,
                            'vat' => 0.19,
                        ]),
                    ]),
                ],
            ]),
            'test'
        );

        $this->assertInstanceOf('Shopware\Connect\Struct\Shipping', $result);
        $this->assertEquals(9.35, $result->shippingCosts, 'Wrong net shipping costs', .01);
        $this->assertEquals(10, $result->grossShippingCosts);
    }
}
