<?php

namespace Shopware\Connect\ShippingCosts;

use Phake;

class RulesVisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_recursively_visits_rules()
    {
        $visitor = \Phake::partialMock('Shopware\Connect\ShippingCosts\RulesVisitor');
        $visitor->visit(new Rules(array('rules' => array(
            new Rule\CountryDecorator(array(
                'delegatee' => new Rule\MinimumBasketValue(array(
                    'delegatee' => new Rule\WeightDecorator(array(
                        'delegatee' => new Rule\FixedPrice()
                    ))
                ))
            )),
            new Rule\UnitPrice(),
        ))));

        $p = Phake::anyParameters();

        Phake::inOrder(
            Phake::verify($visitor)->startVisitRules($p),
            Phake::verify($visitor, \Phake::times(2))->startVisitRule($p),
            Phake::verify($visitor)->visitMinimumBasketValue($p),
            Phake::verify($visitor)->visitWeightDecorator($p),
            Phake::verify($visitor)->visitFixedPrice($p),
            Phake::verify($visitor, \Phake::times(2))->stopVisitRule($p),
            Phake::verify($visitor, \Phake::times(2))->startVisitRule($p),
            Phake::verify($visitor)->visitUnitPrice($p),
            Phake::verify($visitor, \Phake::times(2))->stopVisitRule($p),
            Phake::verify($visitor)->stopVisitRules($p)
        );
    }
}
