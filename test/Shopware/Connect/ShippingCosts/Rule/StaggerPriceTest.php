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
        return array(
            array(
                new StaggerPrice(array(
                    'default' => 10,
                )),
                10.
            ),
            array(
                new StaggerPrice(array(
                    'default' => 10,
                    'staggerPrices' => array(
                        20 => 20,
                    )
                )),
                20.
            ),
            array(
                new StaggerPrice(array(
                    'default' => 10,
                    'staggerPrices' => array(
                        20 => 20,
                        50 => 50,
                    )
                )),
                50.
            ),
            array(
                new StaggerPrice(array(
                    'default' => 10,
                    'staggerPrices' => array(
                        50 => 50,
                        20 => 20,
                    )
                )),
                50.
            ),
            array(
                new StaggerPrice(array(
                    'default' => 10,
                    'staggerPrices' => array(
                        '8.65' => 20,
                        '54.7' => 50,
                    )
                )),
                50.
            ),
        );
    }

    /**
     * @dataProvider getStaggerPriceConfigurations
     */
    public function testStaggerPriceConfigurations($rule, $value)
    {
        $this->assertEquals(
            new Struct\Shipping(array(
                'rule' => $rule,
                'shippingCosts' => $value,
                'grossShippingCosts' => $value * 1.1,
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
}
