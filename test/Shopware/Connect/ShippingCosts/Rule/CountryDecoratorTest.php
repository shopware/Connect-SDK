<?php

namespace Shopware\Connect\ShippingCosts\Rule;

use Shopware\Connect\ShippingCosts\Rule;
use Shopware\Connect\Struct;
use Phake;

class CountryDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_is_applicable_when_from_matching_country()
    {
        $delegatee = $this->createMock(Rule::class);
        $delegatee->method('isApplicable')->with($this->anything())->willReturn(true);

        $country = new CountryDecorator(array(
            'countries' => array('DEU'),
            'delegatee' => $delegatee
        ));

        $this->assertTrue(
            $country->isApplicable(
                new Struct\Order(
                    array(
                        'deliveryAddress' => new Struct\Address(array(
                            'country' => 'DEU',
                        ))
                    )
                )
            )
        );
    }

    /**
     * @test
     */
    public function it_is_not_applicable_when_zip_is_excluded()
    {
        $delegatee = $this->createMock(Rule::class);

        $country = new CountryDecorator(array(
            'countries' => array('DEU'),
            'excludeZipCodes' => array('53'),
            'delegatee' => $delegatee
        ));

        $this->assertFalse(
            $country->isApplicable(
                new Struct\Order(
                    array(
                        'deliveryAddress' => new Struct\Address(array(
                            'country' => 'DEU',
                            'zip' => '53225',
                        ))
                    )
                )
            )
        );
    }
}
