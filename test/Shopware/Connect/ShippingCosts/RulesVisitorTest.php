<?php

namespace Shopware\Connect\ShippingCosts;

class RulesVisitorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_recursively_visits_rules()
    {
        $visitor = $this->getMockBuilder(RulesVisitor::class)->getMockForAbstractClass();

        $p = $this->anything();

        $visitor->expects($this->at(0))->method('startVisitRules')->with($p);
        $visitor->expects($this->at(1))->method('startVisitRule')->with($p);
        $visitor->expects($this->at(2))->method('visitCountryDecorator')->with($p);
        $visitor->expects($this->at(3))->method('visitMinimumBasketValue')->with($p);
        $visitor->expects($this->at(4))->method('visitWeightDecorator')->with($p);
        $visitor->expects($this->at(5))->method('visitFixedPrice')->with($p);
        $visitor->expects($this->at(6))->method('stopVisitRule')->with($p);
        $visitor->expects($this->at(7))->method('startVisitRule')->with($p);
        $visitor->expects($this->at(8))->method('visitUnitPrice')->with($p);
        $visitor->expects($this->at(9))->method('stopVisitRule')->with($p);
        $visitor->expects($this->at(10))->method('stopVisitRules')->with($p);

        $visitor->visit(new Rules(['rules' => [
            new Rule\CountryDecorator([
                'delegatee' => new Rule\MinimumBasketValue([
                    'delegatee' => new Rule\WeightDecorator([
                        'delegatee' => new Rule\FixedPrice()
                    ])
                ])
            ]),
            new Rule\UnitPrice(),
        ]]));
    }
}
