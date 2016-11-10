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
        $rule = new UnitPrice(array(
            'price' => 10,
        ));

        $this->assertEquals(
            new Struct\Shipping(array(
                'rule' => $rule,
                'shippingCosts' => 50.,
                'grossShippingCosts' => 55.,
                'deliveryWorkDays' => 10,
            )),
            $rule->getShippingCosts(
                new Struct\Order(array(
                    'orderItems' => array(
                        new Struct\OrderItem(array(
                            'count' => 2,
                            'product' => new Struct\Product(array(
                                'purchasePrice' => 20,
                                'vat' => 0.19
                            ))
                        )),
                        new Struct\OrderItem(array(
                            'count' => 3,
                            'product' => new Struct\Product(array(
                                'purchasePrice' => 20,
                                'vat' => 0.19
                            ))
                        )),
                    )
                )),
                new VatConfig(array(
                    'vat' => 0.1,
                    'isNet' => true,
                ))
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
        $rule = new UnitPrice(array(
            'price' => 10,
        ));

        $this->assertEquals(
            new Struct\Shipping(array(
                'rule' => $rule,
                'shippingCosts' => 18.18,
                'grossShippingCosts' => 20.,
                'deliveryWorkDays' => 10,
            )),
            $rule->getShippingCosts(
                new Struct\Order(array(
                    'orderItems' => array(
                        new Struct\OrderItem(array(
                            'count' => 2,
                            'product' => new Struct\Product(array(
                                'purchasePrice' => 20,
                                'vat' => 0.19
                            ))
                        )),
                    )
                )),
                new VatConfig(array(
                    'vat' => 0.1,
                    'isNet' => false,
                ))
            ),
            'Wrong shipping costs',
            0.01
        );
    }
}
