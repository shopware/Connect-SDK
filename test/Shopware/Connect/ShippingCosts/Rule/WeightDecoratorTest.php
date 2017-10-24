<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\Struct;
use Phake;

class WeightDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_applicable_when_weight_is_less()
    {
        $delegatee = $this->createMock(Rule::class);
        $delegatee->method('isApplicable')->with($this->anything())->willReturn(true);

        $weight = new WeightDecorator(array(
            'maxWeight' => 10,
            'delegatee' => $delegatee
        ));

        self::assertTrue(
            $weight->isApplicable(
                new Struct\Order(
                    array(
                        'orderItems' => array(
                            new Struct\OrderItem(array(
                                'count' => 1,
                                'product' => new Struct\Product(array(
                                    'attributes' => array(
                                        Struct\Product::ATTRIBUTE_WEIGHT => 2
                                    )
                                ))
                            )),
                            new Struct\OrderItem(array(
                                'count' => 1,
                                'product' => new Struct\Product(array(
                                    'attributes' => array(
                                        Struct\Product::ATTRIBUTE_WEIGHT => 3
                                    )
                                ))
                            )),
                        )
                    )
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

        $weight = new WeightDecorator(array(
            'maxWeight' => 10,
            'delegatee' => $delegatee
        ));

        self::assertFalse(
            $weight->isApplicable(
                new Struct\Order(
                    array(
                        'orderItems' => array(
                            new Struct\OrderItem(array(
                                'count' => 10,
                                'product' => new Struct\Product(array(
                                    'attributes' => array(
                                        Struct\Product::ATTRIBUTE_WEIGHT => 2
                                    )
                                ))
                            )),
                        )
                    )
                )
            )
        );
    }
}
