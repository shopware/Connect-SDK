<?php

namespace Shopware\Connect\Struct;

class ProductTest extends \PHPUnit_Framework_TestCase
{
    public function testFreeDeliveryBackwardsCompatibility()
    {
        $product = new Product();

        $this->assertfalse($product->freeDelivery);
        $product->freeDelivery = true;
    }

    public function testVariantString()
    {
        $product = new Product();
        $product->variant['color'] = 'red';
        $product->variant['size'] = 'XL';

        $this->assertEquals('color=red;size=XL', $product->getVariantString());
    }
}
