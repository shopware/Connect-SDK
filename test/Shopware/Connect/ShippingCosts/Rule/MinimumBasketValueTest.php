<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\Struct;
use Shopware\Connect\ShippingCosts\VatConfig;
use Phake;

class MinimumBasketValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function call_delegatee_if_minimum_value_is_surpassed()
    {
        $order = $this->getOrder();
        $delegatee = Phake::mock('Shopware\Connect\ShippingCosts\Rule');
        Phake::when($delegatee)->isApplicable($order)->thenReturn(true);

        $rule = new MinimumBasketValue(array(
            'minimum' => 100,
            'delegatee' => $delegatee
        ));

        $this->assertTrue($rule->isApplicable($order));
        Phake::verify($delegatee, Phake::times(1))->isApplicable($order);
    }

    /**
     * @test
     */
    public function not_call_delegatee_if_minimum_value_is_surpassed()
    {
        $order = $this->getOrder();
        $delegatee = Phake::mock('Shopware\Connect\ShippingCosts\Rule');

        $rule = new MinimumBasketValue(array(
            'minimum' => 200,
            'delegatee' => $delegatee
        ));

        $this->assertFalse($rule->isApplicable($order));
        Phake::verifyNoInteraction($delegatee);
    }

    /**
     * @test
     */
    public function delegate_shipping_cost_calculation()
    {
        $order = $this->getOrder();
        $vatConfig = new VatConfig();
        $delegatee = Phake::mock('Shopware\Connect\ShippingCosts\Rule');
        Phake::when($delegatee)->getShippingCosts($order, $vatConfig)->thenReturn(42);

        $rule = new MinimumBasketValue(array(
            'minimum' => 200,
            'delegatee' => $delegatee
        ));

        $this->assertSame(
            42,
            $rule->getShippingCosts($order, $vatConfig)
        );
        Phake::verify($delegatee, Phake::times(1))->getShippingCosts($order, $vatConfig);
    }

    /**
     * Get test order
     *
     * @return Order
     */
    private function getOrder()
    {
        return new Struct\Order(array(
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
        ));
    }
}
