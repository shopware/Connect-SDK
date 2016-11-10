<?php

namespace Shopware\Connect;


class CountriesTest extends \PHPUnit_Framework_TestCase
{
    public function testAvailableUnits()
    {
        $countries = Countries::getAvailableUnits();

        $this->assertContains('DEU', $countries);
    }

    public function testExist()
    {
        $this->assertTrue(Countries::exists('DE'));
        $this->assertTrue(Countries::exists('DEU'));
        $this->assertFalse(Countries::exists('GL'));
    }

    public function testGetISO3()
    {
        $this->assertEquals('DEU', Countries::getISO3('DE'));
    }

    public function testGetISO2()
    {
        $this->assertEquals('DE', Countries::getISO2('DEU'));
    }

    public function testISO3NotFound()
    {
        $this->setExpectedException('InvalidArgumentException', 'Country GL not found.');
        Countries::getISO3('GL');
    }

    public function testISO2NotFound()
    {
        $this->setExpectedException('InvalidArgumentException', 'Country GL not found.');
        Countries::getISO2('GL');
    }
}
 
