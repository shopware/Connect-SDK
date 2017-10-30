<?php

namespace Shopware\Connect\ShippingCostCalculator;

use Shopware\Connect\ShippingCostCalculator;
use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\ShippingCosts\Rules;
use Shopware\Connect\Struct;
use Shopware\Connect\Struct\Order;
use Shopware\Connect\Struct\Shipping;
use Shopware\Connect\ShippingRuleParser;

class ProductCalculatorTest extends \PHPUnit_Framework_TestCase
{
    private $aggregate;
    private $gateway;
    private $calculator;

    public function setUp()
    {
        $this->aggregate = $this->createMock(ShippingCostCalculator::class);
        $this->calculator = new ProductCalculator(
            $this->aggregate,
            new ShippingRuleParser\Validator(
                new ShippingRuleParser\Google(),
                new Struct\VerificatorDispatcher(
                    array(
                        'Shopware\\Connect\\Struct\\ShippingRules' =>
                            new Struct\Verificator\ShippingRules(),
                        'Shopware\\Connect\\ShippingCosts\\Rule\\Product' =>
                            new Struct\Verificator\ProductRule(),
                    )
                )
            )
        );

        $this->aggregate->method('calculateShippingCosts')->with($this->anything())->willReturn(
            new \Shopware\Connect\Struct\Shipping(array(
                'isShippable' => true,
                'shippingCosts' => .0,
                'grossShippingCosts' => .0,
            ))
        );
    }

    /**
     * Get baskets
     *
     * @return array
     */
    public function getBaskets()
    {
        return array(
            array( // #0
                new \Shopware\Connect\Struct\Order(array(
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => '::Service [3D]:5.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 5.00,
                    'grossShippingCosts' => 5.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate simple general shipping rules",
            ),
            array( // #1
                new \Shopware\Connect\Struct\Order(array(
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 2,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => '::Service [3D]:5.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 5.00 * 2,
                    'grossShippingCosts' => 5.00 * 1.19 * 2,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping rules for multiple products",
            ),
            array( // #2
                new \Shopware\Connect\Struct\Order(array(
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => '::Service [3D]:5.00 EUR',
                            )),
                        )),
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => '::Service [3D]:7.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 5.00 + 7.00,
                    'grossShippingCosts' => (5.00 + 7.00) * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping rules for multiple order items",
            ),
            array( // #3
                new \Shopware\Connect\Struct\Order(array(
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array()),
                        )),
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => '::Service [3D]:7.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 7.00,
                    'grossShippingCosts' => 7.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping costs for a basket with only partially defined shipping rules",
            ),
            array( // #4
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'GB::Service [3D]:5.00 EUR,DE::Service [3D]:7.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 7.00,
                    'grossShippingCosts' => 7.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping costs using the country rule from multiple rules",
            ),
            array( // #5
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'zip' => '45886',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'GB::Service [3D]:5.00 EUR,DE:50*:Service [3D]:7.00 EUR,DE::Service [3D]:9.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 9.00,
                    'grossShippingCosts' => 9.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping costs using a non matching region wildcard rule from multiple rules",
            ),
            array( // #6
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'zip' => '45886',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'GB::Service [3D]:5.00 EUR,DE:45*:Service [3D]:7.00 EUR,DE::Service [3D]:9.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 7.00,
                    'grossShippingCosts' => 7.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping costs using a matching region wildcard rule from multiple rules",
            ),
            array( // #7
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'zip' => '45886',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'GB::Service [3D]:5.00 EUR,DE:45886:Service [3D]:7.00 EUR,DE::Service [3D]:9.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 7.00,
                    'grossShippingCosts' => 7.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping costs using a concrete matching region rule from multiple rules",
            ),
            array( // #8
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'zip' => '45886',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'GB::Service [3D]:5.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => false,
                )),
                "Order is not shippable, if no rule matches",
            ),
            array( // #9
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'zip' => '45886',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'DE::Service [3D]:5.00 EUR',
                            )),
                        )),
                    ),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'GB::Service [3D]:5.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => false,
                )),
                "One order item is not shippable, if no rule matches",
            ),
            array( // #10
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'state' => 'NRW',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'DE:NRW:Service [3D]:5.00 EUR,::Service [3D]:7.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 5.00,
                    'grossShippingCosts' => 5.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping costs using a matching region string rule from multiple rules",
            ),
            array( // #11
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'state' => 'RP',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'DE:NRW:Service [3D]:5.00 EUR,::Service [3D]:7.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'shippingCosts' => 7.00,
                    'grossShippingCosts' => 7.00 * 1.19,
                    'deliveryWorkDays' => 3,
                    'service' => 'Service',
                )),
                "Calculate shipping costs using a non matching region string rule from multiple rules",
            ),
            array( // #12
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'state' => 'NRW',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'DE:NRW:DHL [5D]:5.00 EUR,::Service [3D]:7.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'service' => 'DHL',
                    'deliveryWorkDays' => 5,
                    'shippingCosts' => 5.00,
                    'grossShippingCosts' => 5.00 * 1.19,
                )),
                "Set service name of matched shipping cost rule",
            ),
            array( // #13
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'state' => 'NRW',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'DE:NRW:DHL [5D]:5.00 EUR,::Service [3D]:7.00 EUR',
                            )),
                        )),
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'DE:NRW:DPD [3D]:5.00 EUR,::Service [3D]:7.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'service' => 'DHL, DPD',
                    'deliveryWorkDays' => 5,
                    'shippingCosts' => 10.00,
                    'grossShippingCosts' => 10.00 * 1.19,
                )),
                "Aggregate service names of matched shipping cost rules",
            ),
            array( // #14
                new \Shopware\Connect\Struct\Order(array(
                    'deliveryAddress' => new \Shopware\Connect\Struct\Address(array(
                        'country' => 'DEU',
                        'state' => 'NRW',
                    )),
                    'orderItems' => array(
                        new \Shopware\Connect\Struct\OrderItem(array(
                            'count' => 1,
                            'product' => new \Shopware\Connect\Struct\Product(array(
                                'shipping' => 'DE:NRW:DHL:5.00 EUR',
                            )),
                        )),
                    ),
                )),
                new \Shopware\Connect\Struct\Shipping(array(
                    'isShippable' => true,
                    'service' => 'DHL',
                    'deliveryWorkDays' => 10,
                    'shippingCosts' => 5.00,
                    'grossShippingCosts' => 5.00 * 1.19,
                )),
                "Use default delivery work days",
            ),
        );
    }

    /**
     * @dataProvider getBaskets
     */
    public function testCalculate(Order $order, Shipping $expected, $message)
    {
        $order->providerShop = 1;
        $order->orderShop = 2;

        $shippingCosts = $this->calculator->calculateShippingCosts(new Rules(), $order);

        self::assertInstanceOf('Shopware\Connect\Struct\Shipping', $shippingCosts);
        self::assertEquals($expected, $shippingCosts, "Calculated wrong shipping costs for test: $message", 0.01);
    }

    public function testDoNotCallAggregateWithEmptyOrder()
    {
        $this->aggregate = $this->createMock(ShippingCostCalculator::class);
        $this->calculator = new ProductCalculator(
            $this->aggregate,
            new ShippingRuleParser\Validator(
                new ShippingRuleParser\Google(),
                new Struct\VerificatorDispatcher(
                    array(
                        'Shopware\\Connect\\Struct\\ShippingRules' =>
                            new Struct\Verificator\ShippingRules(),
                        'Shopware\\Connect\\ShippingCosts\\Rule\\Product' =>
                            new Struct\Verificator\ProductRule(),
                    )
                )
            )
        );

        $order = new \Shopware\Connect\Struct\Order(array(
            'orderItems' => array(
                new \Shopware\Connect\Struct\OrderItem(array(
                    'count' => 1,
                    'product' => new \Shopware\Connect\Struct\Product(array(
                        'shipping' => '::Service [3D]:5.00 EUR',
                    )),
                )),
            ),
        ));
        $order->providerShop = 1;
        $order->orderShop = 2;

        $shippingCosts = $this->calculator->calculateShippingCosts(new Rules(), $order);

        $this->aggregate->expects($this->never())->method('calculateShippingCosts');
        self::assertInstanceOf(Shipping::class, $shippingCosts);
    }
}
