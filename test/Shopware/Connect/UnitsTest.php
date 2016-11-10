<?php

namespace Shopware\Connect;

class UnitsTest extends \PHPUnit_Framework_TestCase
{
    public function testAvailableUnits()
    {
        $units = Units::getAvailableUnits();

        $this->assertContains('kg', $units);
    }

    public function testLocalizedUnits()
    {
        $units = Units::getLocalizedUnits('de');
        $this->assertArrayHasKey('kg', $units);
        $this->assertEquals('Kilogramm', $units['kg']);

        $units = Units::getLocalizedUnits('en');
        $this->assertArrayHasKey('kg', $units);
        $this->assertEquals('Kilogram(s)', $units['kg']);
    }
}
