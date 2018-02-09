<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\Struct;
use Shopware\Connect\ShippingCosts\VatConfig;

class MinimumBasketValueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function call_delegatee_if_minimum_value_is_surpassed()
    {
        $order = $this->getOrder();
        $delegatee = $this->createMock(Rule::class);
        $delegatee->method('isApplicable')->with($order)->willReturn(true);

        $rule = new MinimumBasketValue([
            'minimum' => 100,
            'delegatee' => $delegatee
        ]);

        $delegatee->expects($this->once())->method('isApplicable')->with($order);
        self::assertTrue($rule->isApplicable($order));
    }

    /**
     * @test
     */
    public function not_call_delegatee_if_minimum_value_is_surpassed()
    {
        $order = $this->getOrder();
        $delegatee = $this->createMock(Rule::class);

        $rule = new MinimumBasketValue([
            'minimum' => 200,
            'delegatee' => $delegatee
        ]);

        $delegatee->expects($this->never())->method('isApplicable');
        $delegatee->expects($this->never())->method('getShippingCosts');
        self::assertFalse($rule->isApplicable($order));
    }

    /**
     * @test
     */
    public function delegate_shipping_cost_calculation()
    {
        $order = $this->getOrder();
        $vatConfig = new VatConfig();
        $delegatee = $this->createMock(Rule::class);
        $delegatee->method('getShippingCosts')->with($order, $vatConfig)->willReturn(42);

        $rule = new MinimumBasketValue([
            'minimum' => 200,
            'delegatee' => $delegatee
        ]);
        $delegatee->expects($this->once())->method('getShippingCosts')->with($order, $vatConfig);

        self::assertSame(
            42,
            $rule->getShippingCosts($order, $vatConfig)
        );
    }

    /**
     * Get test order
     *
     * @return Order
     */
    private function getOrder()
    {
        return new Struct\Order([
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
        ]);
    }
}
