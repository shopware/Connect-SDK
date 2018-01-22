<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\Struct;

class WeightDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_applicable_when_weight_is_less()
    {
        $delegatee = $this->createMock(Rule::class);
        $delegatee->method('isApplicable')->with($this->anything())->willReturn(true);

        $weight = new WeightDecorator([
            'maxWeight' => 10,
            'delegatee' => $delegatee
        ]);

        self::assertTrue(
            $weight->isApplicable(
                new Struct\Order(
                    [
                        'orderItems' => [
                            new Struct\OrderItem([
                                'count' => 1,
                                'product' => new Struct\Product([
                                    'attributes' => [
                                        Struct\Product::ATTRIBUTE_WEIGHT => 2
                                    ]
                                ])
                            ]),
                            new Struct\OrderItem([
                                'count' => 1,
                                'product' => new Struct\Product([
                                    'attributes' => [
                                        Struct\Product::ATTRIBUTE_WEIGHT => 3
                                    ]
                                ])
                            ]),
                        ]
                    ]
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_is_not_applicable_when_big_count_is_larger_max_weight()
    {
        $delegatee = $this->createMock(Rule::class);
        $delegatee->method('isApplicable')->with($this->anything())->willReturn(true);

        $weight = new WeightDecorator([
            'maxWeight' => 10,
            'delegatee' => $delegatee
        ]);

        self::assertFalse(
            $weight->isApplicable(
                new Struct\Order(
                    [
                        'orderItems' => [
                            new Struct\OrderItem([
                                'count' => 10,
                                'product' => new Struct\Product([
                                    'attributes' => [
                                        Struct\Product::ATTRIBUTE_WEIGHT => 2
                                    ]
                                ])
                            ]),
                        ]
                    ]
                )
            )
        );
    }
}
