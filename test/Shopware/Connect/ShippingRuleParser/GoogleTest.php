<?php

namespace Shopware\Connect\ShippingRuleParser;

use Shopware\Connect\Exception\ParserException;
use Shopware\Connect\Struct\ShippingRules;
use Shopware\Connect\ShippingCosts\Rule;
use Symfony\Component\CssSelector\Exception\ParseException;

class GoogleTest extends \PHPUnit_Framework_TestCase
{
    public function dataFromString()
    {
        return array(
            array(
                '',
                null,
            ),
            array(
                ':::7.95 USD',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'price' => 7.95,
                            'currency' => 'USD'
                        )),
                    )
                )),
            ),
            array(
                'GB:::7.95 USD',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'GBR',
                            'price' => 7.95,
                            'currency' => 'USD'
                        )),
                    )
                )),
            ),
            array(
                'GB:MA:Ground:5.95 USD,GB:024*:Ground:7.95 USD',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'GBR',
                            'region' => 'MA',
                            'service' => 'Ground',
                            'price' => 5.95,
                            'currency' => 'USD'
                        )),
                        new Rule\Product(array(
                            'country' => 'GBR',
                            'zipRange' => '024*',
                            'service' => 'Ground',
                            'price' => 7.95,
                            'currency' => 'USD'
                        )),
                    )
                )),
            ),
            array(
                'DE::Standard:4.95 EUR,DE::Express:10.00 EUR',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'DEU',
                            'service' => 'Standard',
                            'price' => 4.95,
                            'currency' => 'EUR'
                        )),
                        new Rule\Product(array(
                            'country' => 'DEU',
                            'region' => '',
                            'service' => 'Express',
                            'price' => 10.00,
                            'currency' => 'EUR'
                        )),
                    )
                ))
            ),
            array(
                'DE:53*:Standard [3D]:4.95 EUR',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'DEU',
                            'zipRange' => '53*',
                            'service' => 'Standard',
                            'deliveryWorkDays' => 3,
                            'price' => 4.95,
                            'currency' => 'EUR'
                        )),
                    )
                ))
            ),
            array(
                'DE:NRW:Standard [3D]:4.95 EUR',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'DEU',
                            'region' => 'NRW',
                            'service' => 'Standard',
                            'deliveryWorkDays' => 3,
                            'price' => 4.95,
                            'currency' => 'EUR'
                        )),
                    )
                ))
            ),
            array(
                'DE:53*:Standard [24H]:4.95 EUR',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'DEU',
                            'zipRange' => '53*',
                            'service' => 'Standard',
                            'deliveryWorkDays' => 1,
                            'price' => 4.95,
                            'currency' => 'EUR'
                        )),
                    )
                ))
            ),
            array(
                'DE:::3.95 EUR,AT:::9.95 EUR',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'DEU',
                            'zipRange' => null,
                            'service' => '',
                            'deliveryWorkDays' => null,
                            'price' => 3.95,
                            'currency' => 'EUR'
                        )),
                        new Rule\Product(array(
                            'country' => 'AUT',
                            'zipRange' => null,
                            'service' => '',
                            'deliveryWorkDays' => null,
                            'price' => 9.95,
                            'currency' => 'EUR'
                        )),
                    )
                )),
            ),
            array(
                'DE:::0 EUR',
                new ShippingRules(array(
                    'rules' => array(
                        new Rule\Product(array(
                            'country' => 'DEU',
                            'zipRange' => null,
                            'service' => '',
                            'deliveryWorkDays' => null,
                            'price' => 0.0,
                            'currency' => 'EUR'
                        )),
                    )
                )),
            )
        );
    }

    /**
     * @dataProvider dataFromString
     */
    public function testFromString($original, $expected)
    {
        $parser = new Google();

        $actual = $parser->parseString($original);
        $this->assertEquals($expected, $actual);
    }

    /**
     * Get parse errors
     *
     * @return array
     */
    public function getParseErrors()
    {
        return array(
            array(
                ':',
                'Unexpected end of input at position 1 – expected one of: Element separator ":", Zip code or region (eg. 45886 or 45*), Region identifier (eg. NRW), Country Code (ISO 3166-1) (eg. DE)'
            ),
            array(
                ':::',
                'Unexpected end of input at position 3 – expected one of: Price (english locale) (eg. 1.95)'
            ),
            array(
                'invalid:::',
                'Unexpected Delivery name (free text) at position 0 – expected one of: Element separator ":", Country Code (ISO 3166-1) (eg. DE)'
            ),
            array(
                ':foo::',
                'Unexpected Delivery name (free text) at position 1 – expected one of: Element separator ":", Zip code or region (eg. 45886 or 45*), Region identifier (eg. NRW), Country Code (ISO 3166-1) (eg. DE)'
            ),
            array(
                '::foo []:',
                'Unexpected random text at position 6 – expected one of: Element separator ":", Delivery name (free text)'
            ),
            array(
                ':::7,45 EUR',
                'Unexpected Rule separator "," at position 4 – expected one of: Currency code (ISO 4217) (eg. EUR)'
            ),
        );
    }

    /**
     * @dataProvider getParseErrors
     */
    public function testParseError($input, $error)
    {
        $this->expectException(ParserException::class);
        $this->expectExceptionMessage($error);
        $parser = new Google();
        $parser->parseString($input);
    }
}
